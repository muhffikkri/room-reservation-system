<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

/**
 * Proyeksi publik (jadwal) dan proyeksi booking (form) berbagi fakta slot
 * yang sama — overlap approved dan status fasilitas — namun lead time hanya
 * membatasi pemilihan booking (BR-3).
 */
it('keeps booking and public projections agreed on overlap and facility state', function () {
    Carbon::setTestNow(Carbon::parse('2026-09-09 10:00:00'));

    try {
        $facility = Facility::factory()->create(['status' => 'aktif']);
        $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

        Reservation::factory()->create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'status' => 'approved',
            'start_time' => '2026-09-09 11:00:00',
            'end_time' => '2026-09-09 12:00:00',
        ]);

        $availability = app(ReservationAvailability::class);
        $approved = $availability->approvedForDay(
            $facility->id,
            $availability->dayStart(Carbon::parse('2026-09-09')),
            $availability->dayEnd(Carbon::parse('2026-09-09')),
        );

        $booking = $availability->bookingSlots($facility, Carbon::parse('2026-09-09'), $approved);
        $public = $availability->publicScheduleSlots($facility, Carbon::parse('2026-09-09'), $approved);

        expect(collect($booking)->filter(fn ($slot): bool => $slot['state'] === 'booked')->pluck('start')->all())
            ->toBe(['11:00', '11:30']);
        expect(collect($public)->filter(fn ($slot): bool => $slot['state'] === 'booked')->pluck('start')->all())
            ->toBe(['11:00', '11:30']);

        $facility->update(['status' => 'perbaikan']);

        expect(collect($availability->bookingSlots($facility, Carbon::parse('2026-09-09'), collect()))
            ->every(fn ($slot): bool => $slot['state'] === 'inactive'))->toBeTrue();
        expect(collect($availability->publicScheduleSlots($facility, Carbon::parse('2026-09-09'), collect()))
            ->every(fn ($slot): bool => $slot['state'] === 'inactive'))->toBeTrue();

        $facility->update(['status' => 'aktif']);
    } finally {
        Carbon::setTestNow();
    }
});

it('enforces the one-hour lead time on booking but not on the public schedule', function () {
    Carbon::setTestNow(Carbon::parse('2026-09-09 10:00:00'));

    try {
        $facility = Facility::factory()->create(['status' => 'aktif']);
        $availability = app(ReservationAvailability::class);

        $booking = collect($availability->bookingSlots($facility, Carbon::parse('2026-09-09'), collect()));
        $public = collect($availability->publicScheduleSlots($facility, Carbon::parse('2026-09-09'), collect()));

        // 10:00 dan 10:30 masih dalam jendela lead time (mulai < now + 60 mnt),
        // sehingga tersedia di jadwal publik tetapi 'past' di proyeksi booking.
        expect($booking->firstWhere('start', '10:00')['state'])->toBe('past');
        expect($booking->firstWhere('start', '10:30')['state'])->toBe('past');
        expect($public->firstWhere('start', '10:00')['state'])->toBe('available');
        expect($public->firstWhere('start', '10:30')['state'])->toBe('available');

        // Slot mulai tepat now + 60 mnt (11:00) boleh di-book.
        expect($booking->firstWhere('start', '11:00')['state'])->toBe('available');

        // Format waktu kanonik H:i di kedua proyeksi.
        expect($booking->every(fn ($slot): bool => preg_match('/^\d{2}:\d{2}$/', $slot['start']) === 1))->toBeTrue();
    } finally {
        Carbon::setTestNow();
    }
});
