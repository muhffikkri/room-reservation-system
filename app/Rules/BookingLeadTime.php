<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Batas mepet pengajuan (BR-5): mulai minimal 30 menit dari sekarang.
 */
class BookingLeadTime implements ValidationRule
{
    public function __construct(protected Carbon $start) {}

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->start->lt(Carbon::now(config('app.timezone'))->addMinutes(30))) {
            $fail('Waktu mulai minimal 30 menit dari sekarang.');
        }
    }
}
