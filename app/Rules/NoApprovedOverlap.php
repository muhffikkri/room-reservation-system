<?php

namespace App\Rules;

use App\Models\Reservation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Satu-satunya pemilik definisi Overlap (BR-6): slot dianggap terisi bila
 * ada reservasi approved pada fasilitas sama dengan
 * start_time < end_baru AND end_time > start_baru.
 *
 * Dipakai saat pengajuan (bersama Rules slot lain di ReservationService)
 * dan dicek ulang saat approve di dalam transaksi (BR-7). Overlap dengan pending lain
 * diperbolehkan masuk antrian.
 */
class NoApprovedOverlap implements ValidationRule
{
    public function __construct(
        protected int $facilityId,
        protected Carbon $start,
        protected Carbon $end,
        protected ?int $ignoreId = null,
    ) {}

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Definisi penghalang hidup di model (satu pemilik, BR-6);
        // Rules ini hanya meneruskannya ke validator.
        $conflict = Reservation::blockingOverlap($this->facilityId, $this->start, $this->end, $this->ignoreId)
            ->exists();

        if ($conflict) {
            $fail('Slot waktu tersebut sudah dipesan (bentrok dengan reservasi yang disetujui).');
        }
    }
}
