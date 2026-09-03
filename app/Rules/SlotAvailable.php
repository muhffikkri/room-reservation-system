<?php

namespace App\Rules;

use App\Models\Facility;
use App\Models\Reservation;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Ketersediaan slot saat pengajuan (BR-3, BR-4, BR-5, BR-6).
 *
 * Pengecekan Overlap didelegasikan ke NoApprovedOverlap agar definisi
 * bentrok hanya hidup di satu tempat.
 */
class SlotAvailable implements DataAwareRule, ValidationRule
{
    /** @var array<string, mixed> */
    protected array $data = [];

    public function __construct(protected int $userId) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $facilityId = $this->data['facility_id'] ?? null;
        $date = $this->data['date'] ?? null;
        $startTime = $this->data['start_time'] ?? null;
        $endTime = $this->data['end_time'] ?? null;

        if (! is_numeric($facilityId) || ! is_string($date) || ! is_string($startTime) || ! is_string($endTime)) {
            return;
        }

        $facility = Facility::find($facilityId);

        if ($facility === null) {
            return;
        }

        if ($facility->status !== 'aktif') {
            $fail('Fasilitas tidak dapat direservasi karena berstatus '.$facility->status.'.');

            return;
        }

        try {
            $timezone = config('app.timezone');
            $start = Carbon::parse("{$date} {$startTime}", $timezone);
            $end = Carbon::parse("{$date} {$endTime}", $timezone);
        } catch (\Exception) {
            return;
        }

        if ($start->lt(Carbon::now($timezone)->addMinutes(30))) {
            $fail('Waktu mulai minimal 30 menit dari sekarang.');

            return;
        }

        $pendingToday = Reservation::where('user_id', $this->userId)
            ->pending()
            ->whereDate('start_time', $start->toDateString())
            ->count();

        if ($pendingToday >= 2) {
            $fail('Maksimal 2 reservasi pending per hari untuk satu pengguna.');

            return;
        }

        (new NoApprovedOverlap((int) $facilityId, $start, $end))->validate($attribute, $value, $fail);
    }
}
