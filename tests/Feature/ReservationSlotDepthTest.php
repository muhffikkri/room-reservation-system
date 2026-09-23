<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function depthAvailability(): ReservationAvailability
{
    return app(ReservationAvailability::class);
}

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

it('lets the pending queue through but blocks on approved overlap', function () {
    [$facility, $user] = depthActors();

    Reservation::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'status' => 'pending',
        'start_time' => depthCarbon('2030-02-01', '08:00'),
        'end_time' => depthCarbon('2030-02-01', '09:00'),
    ]);

    expect(depthAvailability()->hasBlockingOverlap(
        $facility->id,
        depthCarbon('2030-02-01', '08:30'),
        depthCarbon('2030-02-01', '09:00'),
    ))->toBeFalse();

    Reservation::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'status' => 'approved',
        'start_time' => depthCarbon('2030-02-01', '10:00'),
        'end_time' => depthCarbon('2030-02-01', '11:00'),
    ]);

    expect(depthAvailability()->hasBlockingOverlap(
        $facility->id,
        depthCarbon('2030-02-01', '10:30'),
        depthCarbon('2030-02-01', '11:00'),
    ))->toBeTrue();
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

    expect(depthAvailability()->hasBlockingOverlap(
        $facility->id,
        depthCarbon('2030-02-02', '08:30'),
        depthCarbon('2030-02-02', '09:00'),
    ))->toBeTrue();

    expect(depthAvailability()->hasBlockingOverlap(
        $facility->id,
        depthCarbon('2030-02-02', '08:30'),
        depthCarbon('2030-02-02', '09:00'),
        $reservation->id,
    ))->toBeFalse();
});

it('fails closed when the facility is missing or not aktif', function () {
    [$facility] = depthActors();

    expect(depthAvailability()->facilityUnavailabilityError(Facility::find(999999)))
        ->toBe('Fasilitas tidak ditemukan.');

    $facility->update(['status' => 'perbaikan']);

    expect(depthAvailability()->facilityUnavailabilityError($facility))
        ->toBe('Fasilitas tidak dapat direservasi karena berstatus perbaikan.');

    $facility->update(['status' => 'aktif']);

    expect(depthAvailability()->facilityUnavailabilityError($facility))->toBeNull();
});

it('rejects a start less than 60 minutes from now', function () {
    $timezone = config('app.timezone');
    $start = Carbon::now($timezone)->addMinutes(10)->second(0);
    $start->minute((int) floor($start->minute / 30) * 30);

    expect(depthAvailability()->leadTimeError($start))
        ->toBe('Waktu mulai minimal 1 jam dari sekarang.');

    expect(depthAvailability()->leadTimeError(depthCarbon('2030-02-03', '08:00')))->toBeNull();
});

it('accepts a start exactly 60 minutes from now', function () {
    Carbon::setTestNow(Carbon::parse('2026-09-09 09:00:00'));

    $cutoff = depthAvailability()->leadTimeCutoff();

    expect(depthAvailability()->leadTimeError($cutoff))->toBeNull();

    Carbon::setTestNow();
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

    expect(depthAvailability()->pendingQuotaError($user->id, depthCarbon('2030-02-04', '13:00')))
        ->toBe('Maksimal 2 reservasi pending per hari untuk satu pengguna.');

    $other = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    expect(depthAvailability()->pendingQuotaError($other->id, depthCarbon('2030-02-04', '13:00')))->toBeNull();
});
