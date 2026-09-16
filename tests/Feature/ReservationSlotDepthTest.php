<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use App\Rules\BookingLeadTime;
use App\Rules\FacilityBookable;
use App\Rules\PendingQuota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function depthActors(): array
{
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    return [$facility, $user];
}

function depthCarbon(string $date, string $time): Carbon
{
    return Carbon::parse("{$date} {$time}", config('app.timezone'));
}

function collectFailures(object $rule): array
{
    $failures = [];
    $rule->validate('slot', true, function (string $message) use (&$failures): void {
        $failures[] = $message;
    });

    return $failures;
}

it('lets the pending queue through but blocks on approved overlap', function () {
    [$facility, $user] = depthActors();

    Reservation::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'status' => 'pending',
        'start_time' => depthCarbon('2030-02-01', '08:00'),
        'end_time' => depthCarbon('2030-02-01', '09:00'),
    ]);

    expect(Reservation::blockingOverlap(
        $facility->id,
        depthCarbon('2030-02-01', '08:30'),
        depthCarbon('2030-02-01', '09:00'),
    )->exists())->toBeFalse();

    Reservation::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'status' => 'approved',
        'start_time' => depthCarbon('2030-02-01', '10:00'),
        'end_time' => depthCarbon('2030-02-01', '11:00'),
    ]);

    expect(Reservation::blockingOverlap(
        $facility->id,
        depthCarbon('2030-02-01', '10:30'),
        depthCarbon('2030-02-01', '11:00'),
    )->exists())->toBeTrue();
});

it('excludes the reservation being decided from its own overlap check', function () {
    [$facility, $user] = depthActors();

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'status' => 'approved',
        'start_time' => depthCarbon('2030-02-02', '08:00'),
        'end_time' => depthCarbon('2030-02-02', '09:00'),
    ]);

    $overlapping = [depthCarbon('2030-02-02', '08:30'), depthCarbon('2030-02-02', '09:00')];

    expect(Reservation::blockingOverlap($facility->id, ...$overlapping)->exists())->toBeTrue();
    expect(Reservation::blockingOverlap($facility->id, $overlapping[0], $overlapping[1], $reservation->id)->exists())->toBeFalse();
});

it('fails closed when the facility is missing or not aktif', function () {
    [$facility] = depthActors();

    expect(collectFailures(new FacilityBookable(999999)))
        ->toBe(['Fasilitas tidak ditemukan.']);

    $facility->update(['status' => 'perbaikan']);

    expect(collectFailures(new FacilityBookable($facility->id)))
        ->toBe(['Fasilitas tidak dapat direservasi karena berstatus perbaikan.']);

    $facility->update(['status' => 'aktif']);

    expect(collectFailures(new FacilityBookable($facility->id)))->toBeEmpty();
});

it('rejects a start less than 30 minutes from now', function () {
    $timezone = config('app.timezone');
    $start = Carbon::now($timezone)->addMinutes(10)->second(0);
    $start->minute((int) floor($start->minute / 30) * 30);

    expect(collectFailures(new BookingLeadTime($start)))
        ->toBe(['Waktu mulai minimal 30 menit dari sekarang.']);

    expect(collectFailures(new BookingLeadTime(depthCarbon('2030-02-03', '08:00'))))->toBeEmpty();
});

it('rejects the third pending reservation on the same day', function () {
    [$facility, $user] = depthActors();

    Reservation::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'status' => 'pending',
        'start_time' => depthCarbon('2030-02-04', '08:00'),
        'end_time' => depthCarbon('2030-02-04', '09:00'),
    ]);
    Reservation::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'status' => 'pending',
        'start_time' => depthCarbon('2030-02-04', '10:00'),
        'end_time' => depthCarbon('2030-02-04', '11:00'),
    ]);

    expect(collectFailures(new PendingQuota($user->id, depthCarbon('2030-02-04', '13:00'))))
        ->toBe(['Maksimal 2 reservasi pending per hari untuk satu pengguna.']);

    $other = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    expect(collectFailures(new PendingQuota($other->id, depthCarbon('2030-02-04', '13:00'))))->toBeEmpty();
});
