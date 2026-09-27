<?php

namespace App\Models;

use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'facility_id', 'purpose', 'start_time', 'end_time', 'status', 'reject_reason', 'cancel_reason', 'decided_by', 'decided_at'])]
/**
 * Reservasi fasilitas oleh pengguna (§4.3, alur §9.1).
 *
 * Status approved memblokir slot (BR-6). Scope overlap TIDAK memfilter
 * status dengan sengaja dan pemanggil yang menentukan, karena reservasi
 * pending boleh masuk antrean dan hanya approved yang memblokir.
 * Definisi overlap memakai interval setengah terbuka: interval yang hanya
 * bersinggungan (contoh 09.00-11.00 vs 11.00-12.00) tidak bentrok. Contoh
 * antrean: dua pending 08.00-09.00 boleh berdampingan, lalu petugas
 * menyetujui satu dan sistem otomatis menolak sisanya (rejected_by_system).
 */
class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory;

    /**
     * Seluruh status reservasi, urut dari antrean ke status terminal.
     *
     * Satu-satunya sumber kebenaran untuk urutan antrean petugas, tab
     * rekapitulasi, dan daftar filter status. Menambah status baru cukup
     * di sini; tidak ada lagi salinan daftar di controller maupun view.
     *
     * @var list<string>
     */
    public const ORDERED_STATUSES = [
        'pending',
        'approved',
        'rejected',
        'rejected_by_system',
        'cancelled_by_user',
        'cancelled_by_officer',
        'cancelled_by_system',
    ];

    /**
     * Status reservasi yang masih dapat dibatalkan, baik oleh pemilik
     * maupun oleh petugas (BR-8, BR-9).
     *
     * @var list<string>
     */
    public const PENDING_APPROVED = ['pending', 'approved'];

    /**
     * Status penolakan, baik oleh petugas maupun otomatis oleh sistem.
     *
     * @var list<string>
     */
    public const REJECTED = ['rejected', 'rejected_by_system'];

    /**
     * Status pembatalan, oleh pengguna, petugas, atau sistem.
     *
     * @var list<string>
     */
    public const CANCELLED = ['cancelled_by_user', 'cancelled_by_officer', 'cancelled_by_system'];

    /**
     * Label kanonik per status, dipakai seluruh halaman petugas, notifikasi,
     * dan rekap.
     *
     * Halaman pengguna sengaja menampilkan "Gagal" untuk cancelled_by_system
     * alih-alih label kanonik; itu keputusan tampilan, bukan domain, jadi
     * override-nya tetap di view.
     *
     * @var array<string, string>
     */
    public const LABELS = [
        'pending' => 'Menunggu Persetujuan',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'rejected_by_system' => 'Ditolak oleh Sistem',
        'cancelled_by_user' => 'Dibatalkan Pengguna',
        'cancelled_by_officer' => 'Dibatalkan Petugas',
        'cancelled_by_system' => 'Dibatalkan oleh Sistem',
    ];

    /**
     * Label kanonik sebuah status.
     */
    public static function statusLabel(string $status): string
    {
        return self::LABELS[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Reservasi masih menunggu keputusan petugas, sehingga aksi setujui dan
     * tolak tersedia.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Reservasi berstatus pending atau approved, sehingga aksi batalkan
     * tersedia. Permission tetap diperiksa terpisah oleh ReservationPolicy.
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, self::PENDING_APPROVED, true);
    }

    /**
     * Overlap interval setengah terbuka (BR-6): reservasi dianggap bentrok bila
     * start_time < end_baru AND end_time > start_baru. Interval yang hanya
     * bersinggungan (09.00-11.00 vs 11.00-12.00) boleh berurutan.
     */
    public function scopeOverlap(Builder $query, int $facilityId, mixed $start, mixed $end): Builder
    {
        return $query
            ->where('facility_id', $facilityId)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start);
    }

    /**
     * Satu-satunya pemilik cek penghalang Slot (BR-6): hanya reservasi
     * approved yang menghalangi; antrean pending tidak ikut dihitung.
     * $ignoreId mengecualikan reservasi yang sedang diputuskan (BR-7).
     */
    public function scopeBlockingOverlap(Builder $query, int $facilityId, mixed $start, mixed $end, ?int $ignoreId = null): Builder
    {
        return $query->approved()
            ->overlap($facilityId, $start, $end)
            ->when($ignoreId !== null, fn (Builder $inner) => $inner->where('id', '!=', $ignoreId));
    }
}
