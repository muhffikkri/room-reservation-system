<?php

namespace App\Models;

use Database\Factories\FacilityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type', 'location', 'capacity', 'description', 'photo', 'status'])]
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

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    public function scopeSearch(Builder $query, ?string $keyword, ?string $type, ?string $location, ?int $minCapacity): Builder
    {
        return $query
            ->when($keyword, fn (Builder $q): Builder => $q->where('name', 'like', "%{$keyword}%"))
            ->when($type, fn (Builder $q): Builder => $q->where('type', $type))
            ->when($location, fn (Builder $q): Builder => $q->where('location', 'like', "%{$location}%"))
            ->when($minCapacity, fn (Builder $q): Builder => $q->where('capacity', '>=', $minCapacity));
    }
}
