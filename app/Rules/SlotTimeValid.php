<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Bentuk slot waktu reservasi (BR-1, BR-2).
 *
 * Slot operasional 07.00–20.00, kelipatan 30 menit, durasi 1–8 slot.
 * Membaca date + start_time + end_time dari data request (DataAwareRule),
 * sehingga satu kartu berlaku untuk pasangan mulai–selesai.
 */
class SlotTimeValid implements DataAwareRule, ValidationRule
{
    public const OPEN_HOUR = 7;

    public const CLOSE_HOUR = 20;

    public const SLOT_MINUTES = 30;

    public const MAX_SLOTS = 8;

    /** @var array<string, mixed> */
    protected array $data = [];

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
        $date = $this->data['date'] ?? null;
        $startTime = $this->data['start_time'] ?? null;
        $endTime = $this->data['end_time'] ?? null;

        if (! is_string($date) || ! is_string($startTime) || ! is_string($endTime)) {
            return;
        }

        if (! preg_match('/^\d{2}:\d{2}$/', $startTime) || ! preg_match('/^\d{2}:\d{2}$/', $endTime)) {
            return;
        }

        try {
            $timezone = config('app.timezone');
            $start = Carbon::parse("{$date} {$startTime}", $timezone);
            $end = Carbon::parse("{$date} {$endTime}", $timezone);
        } catch (\Exception) {
            return;
        }

        if ($start->minute % self::SLOT_MINUTES !== 0 || $end->minute % self::SLOT_MINUTES !== 0) {
            $fail('Waktu mulai dan selesai harus kelipatan 30 menit (contoh: 07:00, 07:30).');

            return;
        }

        $open = $start->copy()->setTime(self::OPEN_HOUR, 0);
        $close = $start->copy()->setTime(self::CLOSE_HOUR, 0);

        if ($start->lt($open) || $end->gt($close)) {
            $fail('Reservasi hanya dapat dilakukan pada jam operasional 07.00–20.00.');

            return;
        }

        if ($end->lte($start)) {
            $fail('Waktu selesai harus lebih besar dari waktu mulai.');

            return;
        }

        $slots = (int) ($start->diffInMinutes($end) / self::SLOT_MINUTES);

        if ($slots < 1 || $slots > self::MAX_SLOTS) {
            $fail('Durasi reservasi minimal 30 menit dan maksimal 4 jam.');
        }
    }
}
