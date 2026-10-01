<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;

class MaxWords implements ValidationRule
{
    public function __construct(private readonly int $maxWords) {}

    /**
     * Run the validation rule.
     *
     * Dipakai untuk field yang batasnya dihitung per kata, bukan per karakter,
     * sehingga angka batasnya diambil dari pemilik nilai yang sama dengan yang
     * dipakai saat merender.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if (Str::wordCount($value) > $this->maxWords) {
            $fail("The :attribute field must not be longer than {$this->maxWords} words.");
        }
    }
}
