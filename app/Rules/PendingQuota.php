<?php

namespace App\Rules;

use App\Models\Reservation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Kuota antrean pengguna (BR-4): maksimal 2 reservasi pending per hari.
 */
class PendingQuota implements ValidationRule
{
    public function __construct(protected int $userId, protected Carbon $start) {}

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pendingToday = Reservation::where('user_id', $this->userId)
            ->pending()
            ->whereDate('start_time', $this->start->toDateString())
            ->count();

        if ($pendingToday >= 2) {
            $fail('Maksimal 2 reservasi pending per hari untuk satu pengguna.');
        }
    }
}
