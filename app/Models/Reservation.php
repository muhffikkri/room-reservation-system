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
 * Contoh antrean: dua pending 08.00-09.00 boleh berdampingan, lalu
 * petugas menyetujui satu dan sistem menolak yang lain dengan 409.
 */
class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory;

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

    public function scopeOverlap(Builder $query, int $facilityId, mixed $start, mixed $end): Builder
    {
        return $query
            ->where('facility_id', $facilityId)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start);
    }
}
