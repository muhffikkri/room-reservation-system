<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use App\Services\RecapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

/**
 * Hours available in a day come from the booking window: 07:00-20:00 = 13.
 * A facility booked across that whole window must report 100% occupancy.
 */
function bookWholeOperatingDay(Facility $facility, User $user, string $date): void
{
    Reservation::factory()->approved()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'start_time' => $date.' 07:00:00',
        'end_time' => $date.' 20:00:00',
    ]);
}

it('reports 100% occupancy for a single fully booked day', function (): void {
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    bookWholeOperatingDay($facility, $user, '2026-09-10');

    $recap = app(RecapService::class)->getOccupancyRecap(
        Carbon::parse('2026-09-10')->startOfDay(),
        Carbon::parse('2026-09-10')->endOfDay()
    );

    $row = $recap['data'][0];

    expect((float) $row['total_approved_hours'])->toBe(13.0)
        ->and((float) $row['max_possible_hours'])->toBe(13.0)
        ->and((float) $row['occupancy_rate'])->toBe(100.0);
});

it('derives the available hours from the number of calendar days', function (string $start, string $end, int $days): void {
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    bookWholeOperatingDay($facility, $user, $start);

    $recap = app(RecapService::class)->getOccupancyRecap(
        Carbon::parse($start)->startOfDay(),
        Carbon::parse($end)->endOfDay()
    );

    expect((int) $recap['data'][0]['max_possible_hours'])->toBe($days * 13);
})->with([
    'one day' => ['2026-09-10', '2026-09-10', 1],
    'thirty days' => ['2026-09-01', '2026-09-30', 30],
    'a full year' => ['2026-01-01', '2026-12-31', 365],
]);

it('reports a half booked day as 50%', function (): void {
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    Reservation::factory()->approved()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'start_time' => '2026-09-10 07:00:00',
        'end_time' => '2026-09-10 13:30:00',
    ]);

    $recap = app(RecapService::class)->getOccupancyRecap(
        Carbon::parse('2026-09-10')->startOfDay(),
        Carbon::parse('2026-09-10')->endOfDay()
    );

    expect((float) $recap['data'][0]['occupancy_rate'])->toBe(50.0);
});
