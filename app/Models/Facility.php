<?php

namespace App\Models;

use Database\Factories\FacilityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type', 'location', 'capacity', 'description', 'photo', 'status', 'repair_report_id'])]
class Facility extends Model
{
    /** @use HasFactory<FacilityFactory> */
    use HasFactory;

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function repairReport(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'repair_report_id');
    }

    /**
     * Menyaring fasilitas aktif. Hanya fasilitas aktif yang dapat user
     * reservasi (BR-5, BR-12).
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Filter halaman daftar fasilitas: keyword nama, tipe, lokasi, dan
     * kapasitas minimum (§6 fasilitas.index).
     */
    public function scopeSearch(Builder $query, ?string $keyword, ?string $type, ?string $location, ?int $minCapacity): Builder
    {
        $keyword = strip_tags($keyword ?? '');
        $location = strip_tags($location ?? '');

        return $query
            ->when($keyword, fn (Builder $q): Builder => $q->where('name', 'like', '%'.$keyword.'%'))
            ->when($type, fn (Builder $q): Builder => $q->where('type', $type))
            ->when($location, fn (Builder $q): Builder => $q->where('location', 'like', '%'.$location.'%'))
            ->when($minCapacity, fn (Builder $q): Builder => $q->where('capacity', '>=', $minCapacity));
    }
}
