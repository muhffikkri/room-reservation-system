<?php

namespace App\Models;

use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'facility_id', 'category', 'description', 'photo', 'status', 'resolution_note', 'handled_by', 'handled_at'])]
/**
 * Laporan kerusakan fasilitas, yaitu status terkini (baru, diproses,
 * selesai, ditolak). Riwayat setiap perpindahan status tinggal di model
 * ReportUpdate sebagai audit yang hanya boleh bertambah (§9.2). Aturan
 * tutup laporan (catatan resolusi wajib, BR-10) ditegakkan ReportService,
 * bukan di sini. Model ini hanya menyimpan relasi.
 */
class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'handled_at' => 'datetime',
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

    public function updates(): HasMany
    {
        return $this->hasMany(ReportUpdate::class);
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
