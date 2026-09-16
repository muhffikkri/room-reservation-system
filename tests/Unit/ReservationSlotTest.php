<?php

use App\Rules\SlotTimeValid;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class);

function slotPasses(string $date, string $start, string $end): bool
{
    $failures = [];

    $timezone = config('app.timezone');
    $rule = new SlotTimeValid(
        Carbon::parse("{$date} {$start}", $timezone),
        Carbon::parse("{$date} {$end}", $timezone),
    );
    $rule->validate('start_time', $start, function (string $message) use (&$failures): void {
        $failures[] = $message;
    });

    return $failures === [];
}

it('accepts a valid 30-minute slot', function () {
    expect(slotPasses('2030-01-06', '07:00', '07:30'))->toBeTrue();
});

it('rejects a start that is not a 30-minute multiple', function () {
    expect(slotPasses('2030-01-06', '07:15', '07:45'))->toBeFalse();
});

it('rejects a slot before opening hours', function () {
    expect(slotPasses('2030-01-06', '06:30', '07:00'))->toBeFalse();
});

it('rejects a slot ending past closing time but allows ending exactly at 20:00', function () {
    expect(slotPasses('2030-01-06', '20:00', '20:30'))->toBeFalse();
    expect(slotPasses('2030-01-06', '19:30', '20:00'))->toBeTrue();
});

it('rejects a duration longer than 8 slots', function () {
    expect(slotPasses('2030-01-06', '07:00', '11:30'))->toBeFalse();
});

it('rejects an end that is not after the start', function () {
    expect(slotPasses('2030-01-06', '09:00', '08:00'))->toBeFalse();
});
