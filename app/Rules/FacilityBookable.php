<?php

namespace App\Rules;

use App\Models\Facility;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Kesiapan fasilitas untuk dipesan (BR-3).
 *
 * Fasilitas harus ada dan berstatus aktif. Tidak ada lolos diam-diam:
 * fasilitas yang hilang atau tidak aktif selalu gagal dengan jelas.
 */
class FacilityBookable implements ValidationRule
{
    public function __construct(protected int $facilityId) {}

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $facility = Facility::find($this->facilityId);

        if ($facility === null) {
            $fail('Fasilitas tidak ditemukan.');

            return;
        }

        if ($facility->status !== 'aktif') {
            $fail('Fasilitas tidak dapat direservasi karena berstatus '.$facility->status.'.');
        }
    }
}
