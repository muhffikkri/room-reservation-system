<?php

use App\Services\ReservationAvailability;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class);

function slotAvailability(): ReservationAvailability
{
    return app(ReservationAvailability::class);
}

function slotIsValid(string $date, string $start, string $end): bool
{
    $timezone = config('app.timezone');

    return slotAvailability()->isValidSlot(
        Carbon::parse("{$date} {$start}", $timezone),
        Carbon::parse("{$date} {$end}", $timezone),
    );
}

it('accepts a valid 30-minute slot', function () {
    expect(slotIsValid('2030-01-06', '07:00', '07:30'))->toBeTrue();
});

it('rejects a start that is not a 30-minute multiple', function () {
    expect(slotIsValid('2030-01-06', '07:15', '07:45'))->toBeFalse();
});

it('rejects a slot before opening hours', function () {
    expect(slotIsValid('2030-01-06', '06:30', '07:00'))->toBeFalse();
});

it('rejects a slot ending past closing time but allows ending exactly at 20:00', function () {
    expect(slotIsValid('2030-01-06', '20:00', '20:30'))->toBeFalse();
    expect(slotIsValid('2030-01-06', '19:30', '20:00'))->toBeTrue();
});

it('rejects a duration longer than 8 slots', function () {
    expect(slotIsValid('2030-01-06', '07:00', '11:30'))->toBeFalse();
});

it('rejects an end that is not after the start', function () {
    expect(slotIsValid('2030-01-06', '09:00', '08:00'))->toBeFalse();
});

it('exposes the availability interface constants', function () {
    $availability = slotAvailability();

    expect($availability->openHour())->toBe(7)
        ->and($availability->closeHour())->toBe(20)
        ->and($availability->slotMinutes())->toBe(30)
        ->and($availability->slotCount())->toBe(26)
        ->and($availability->maxDurationSlots())->toBe(8)
        ->and($availability->maxDurationMinutes())->toBe(240)
        ->and($availability->leadTimeMinutes())->toBe(60)
        ->and($availability->timeFormat())->toBe('H:i');
});

it('returns slot errors for each violation', function () {
    $availability = slotAvailability();
    $timezone = config('app.timezone');

    expect($availability->slotTimeErrors(
        Carbon::parse('2030-01-06 07:15', $timezone),
        Carbon::parse('2030-01-06 07:45', $timezone),
    ))->toBe(['Waktu mulai dan selesai harus kelipatan 30 menit (contoh: 07:00, 07:30).']);

    expect($availability->slotTimeErrors(
        Carbon::parse('2030-01-06 06:30', $timezone),
        Carbon::parse('2030-01-06 07:00', $timezone),
    ))->toBe(['Reservasi hanya dapat dilakukan pada jam operasional 07.00–20.00.']);

    expect($availability->slotTimeErrors(
        Carbon::parse('2030-01-06 07:00', $timezone),
        Carbon::parse('2030-01-06 11:30', $timezone),
    ))->toBe(['Durasi reservasi minimal 30 menit dan maksimal 4 jam.']);
});

it('formats all slot times with the canonical H:i format', function () {
    $date = Carbon::parse('2030-01-06', config('app.timezone'));
    $slots = slotAvailability()->slotsForDay($date);

    expect($slots)->toHaveCount(26);

    foreach ($slots as $slot) {
        expect($slot['start'])->toBeInstanceOf(Carbon::class);
        expect(slotAvailability()->formatTime($slot['start']))->toMatch('/^\d{2}:\d{2}$/');
    }

    expect(slotAvailability()->formatTime($slots[0]['start']))->toBe('07:00')
        ->and(slotAvailability()->formatTime($slots[array_key_last($slots)]['end']))->toBe('20:00');
});
