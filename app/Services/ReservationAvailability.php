<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Satu-satunya pemilik keputusan ketersediaan slot reservasi (BR-1..BR-8, BR-12).
 *
 * Modul yang "dalam": semua fakta penjadwalan tinggal di sini — jam operasional,
 * interval/durasi slot, lead time, kelayakan fasilitas, kuota pending, overlap
 * approved, state slot kanonik, dan pemformatan waktu. Controller hanya adaptor
 * yang meminta proyeksi; ReservationService tetap memegang mutasi, penguncian,
 * dan kepemilikan transaksi.
 *
 * Proyeksi publik tidak menerapkan lead time (hanya menolak slot yang sudah
 * lewat), sedangkan proyeksi booking menerapkan lead time 60 menit — bedanya
 * diuji eksplisit agar tidak saling menyimpang diam-diam.
 */
class ReservationAvailability
{
    public const OPEN_HOUR = 7;

    public const CLOSE_HOUR = 20;

    public const SLOT_MINUTES = 30;

    public const SLOT_COUNT = 26;

    public const MAX_DURATION_SLOTS = 8;

    public const MAX_DURATION_MINUTES = 240;

    public const LEAD_TIME_MINUTES = 60;

    public const TIME_FORMAT = 'H:i';

    public function openHour(): int
    {
        return self::OPEN_HOUR;
    }

    public function closeHour(): int
    {
        return self::CLOSE_HOUR;
    }

    public function slotMinutes(): int
    {
        return self::SLOT_MINUTES;
    }

    public function slotCount(): int
    {
        return self::SLOT_COUNT;
    }

    public function maxDurationSlots(): int
    {
        return self::MAX_DURATION_SLOTS;
    }

    public function maxDurationMinutes(): int
    {
        return self::MAX_DURATION_MINUTES;
    }

    public function leadTimeMinutes(): int
    {
        return self::LEAD_TIME_MINUTES;
    }

    public function timeFormat(): string
    {
        return self::TIME_FORMAT;
    }

    public function dayStart(Carbon $date): Carbon
    {
        return $date->copy()->startOfDay()->setTime(self::OPEN_HOUR, 0);
    }

    public function dayEnd(Carbon $date): Carbon
    {
        return $this->dayStart($date)->addMinutes(self::SLOT_COUNT * self::SLOT_MINUTES);
    }

    /**
     * @return array<int, array{start: Carbon, end: Carbon}>
     */
    public function slotsForDay(Carbon $date): array
    {
        $dayStart = $this->dayStart($date);
        $slots = [];

        for ($i = 0; $i < self::SLOT_COUNT; $i++) {
            $start = $dayStart->copy()->addMinutes($i * self::SLOT_MINUTES);
            $slots[] = [
                'start' => $start,
                'end' => $start->copy()->addMinutes(self::SLOT_MINUTES),
            ];
        }

        return $slots;
    }

    public function formatTime(Carbon $time): string
    {
        return $time->format(self::TIME_FORMAT);
    }

    /**
     * Opsi waktu mulai 07:00–20:00 dengan interval 30 menit.
     *
     * @return array<int, string>
     */
    public function timeOptions(): array
    {
        $options = [];
        $time = Carbon::createFromTime(self::OPEN_HOUR, 0);

        for ($i = 0; $i <= self::SLOT_COUNT; $i++) {
            $options[] = $time->format(self::TIME_FORMAT);
            $time->addMinutes(self::SLOT_MINUTES);
        }

        return $options;
    }

    public function isFacilityBookable(Facility $facility): bool
    {
        return $facility->status === 'aktif';
    }

    public function facilityUnavailabilityError(?Facility $facility): ?string
    {
        if ($facility === null || ! $facility->exists) {
            return 'Fasilitas tidak ditemukan.';
        }

        if ($facility->status === 'aktif') {
            return null;
        }

        return 'Fasilitas tidak dapat direservasi karena berstatus '.$facility->status.'.';
    }

    public function leadTimeCutoff(): Carbon
    {
        return Carbon::now(config('app.timezone'))->addMinutes(self::LEAD_TIME_MINUTES);
    }

    public function isWithinLeadTime(Carbon $start): bool
    {
        return $start->lt($this->leadTimeCutoff());
    }

    public function leadTimeError(Carbon $start): ?string
    {
        return $this->isWithinLeadTime($start) ? 'Waktu mulai minimal 1 jam dari sekarang.' : null;
    }

    public function isInPast(Carbon $start): bool
    {
        return $start->lt(Carbon::now(config('app.timezone')));
    }

    /**
     * @return array<int, array{start: string, end: string, state: string}>
     */
    public function publicScheduleSlots(Facility $facility, Carbon $date, iterable $approved): array
    {
        return $this->projectSlots($facility, $date, $approved, bookingGate: false);
    }

    /**
     * @return array<int, array{start: string, end: string, state: string}>
     */
    public function bookingSlots(Facility $facility, Carbon $date, iterable $approved): array
    {
        return $this->projectSlots($facility, $date, $approved, bookingGate: true);
    }

    /**
     * @return array<int, array{start: string, end: string, state: string}>
     */
    private function projectSlots(Facility $facility, Carbon $date, iterable $approved, bool $bookingGate): array
    {
        $approved = $approved instanceof Collection ? $approved : collect($approved);
        $slots = [];

        foreach ($this->slotsForDay($date) as $slot) {
            $slots[] = [
                'start' => $this->formatTime($slot['start']),
                'end' => $this->formatTime($slot['end']),
                'state' => $this->slotState($facility, $slot['start'], $approved, $bookingGate),
            ];
        }

        return $slots;
    }

    /**
     * State kanonik sebuah slot: inactive (fasilitas tidak layak), past (tidak
     * bisa dipilih karena waktu), booked (ada approved yang overlap), available.
     */
    private function slotState(Facility $facility, Carbon $start, Collection $approved, bool $bookingGate): string
    {
        if (! $this->isFacilityBookable($facility)) {
            return 'inactive';
        }

        $blockedByTime = $bookingGate
            ? $this->isWithinLeadTime($start)
            : $this->isInPast($start);

        if ($blockedByTime) {
            return 'past';
        }

        return $this->hasApprovedOverlap($approved, $start, $start->copy()->addMinutes(self::SLOT_MINUTES))
            ? 'booked'
            : 'available';
    }

    /**
     * Definisi overlap tunggal (BR-6): slot dianggap terisi bila ada reservasi
     * approved pada fasilitas sama dengan start_time <= end_baru AND
     * end_time >= start_baru — interval yang bersinggungan ikut terhitung.
     *
     * @param  iterable<int, Reservation>  $approved
     */
    public function hasApprovedOverlap(iterable $approved, Carbon $start, Carbon $end): bool
    {
        foreach ($approved as $reservation) {
            if ($reservation->start_time->lte($end) && $reservation->end_time->gte($start)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    public function slotTimeErrors(Carbon $start, Carbon $end): array
    {
        if ($start->minute % self::SLOT_MINUTES !== 0 || $end->minute % self::SLOT_MINUTES !== 0) {
            return ['Waktu mulai dan selesai harus kelipatan 30 menit (contoh: 07:00, 07:30).'];
        }

        if ($start->lt($this->dayStart($start)) || $end->gt($this->dayEnd($start))) {
            return ['Reservasi hanya dapat dilakukan pada jam operasional 07.00–20.00.'];
        }

        if ($end->lte($start)) {
            return ['Waktu selesai harus lebih besar dari waktu mulai.'];
        }

        $slots = (int) ($start->diffInMinutes($end) / self::SLOT_MINUTES);

        if ($slots < 1 || $slots > self::MAX_DURATION_SLOTS) {
            return ['Durasi reservasi minimal 30 menit dan maksimal 4 jam.'];
        }

        return [];
    }

    public function isValidSlot(Carbon $start, Carbon $end): bool
    {
        return $this->slotTimeErrors($start, $end) === [];
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function approvedForDay(int $facilityId, Carbon $dayStart, Carbon $dayEnd): Collection
    {
        return Reservation::approved()
            ->overlap($facilityId, $dayStart, $dayEnd)
            ->get(['start_time', 'end_time']);
    }

    public function hasBlockingOverlap(int $facilityId, Carbon $start, Carbon $end, ?int $ignoreId = null): bool
    {
        return Reservation::blockingOverlap($facilityId, $start, $end, $ignoreId)->exists();
    }

    public function overlapError(int $facilityId, Carbon $start, Carbon $end, ?int $ignoreId = null): ?string
    {
        return $this->hasBlockingOverlap($facilityId, $start, $end, $ignoreId)
            ? 'Maaf, fasilitas ini sudah dipesan pada jam yang sama (atau overlap). Permohonan Anda ditolak.'
            : null;
    }

    public function pendingCountOnDay(int $userId, Carbon $start): int
    {
        return Reservation::where('user_id', $userId)
            ->pending()
            ->whereDate('start_time', $start->toDateString())
            ->count();
    }

    public function pendingQuotaError(int $userId, Carbon $start): ?string
    {
        return $this->pendingCountOnDay($userId, $start) >= 2
            ? 'Maksimal 2 reservasi pending per hari untuk satu pengguna.'
            : null;
    }
}
