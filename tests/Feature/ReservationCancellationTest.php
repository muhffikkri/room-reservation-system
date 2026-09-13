<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use App\Policies\ReservationPolicy;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

uses(RefreshDatabase::class);

it('allows a user to cancel their own pending reservation when more than 1 hour before start (BR-8)', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    // Set waktu mulai 3 jam dari sekarang
    $startTime = Carbon::now()->addHours(3);
    $endTime = $startTime->copy()->addHour();

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'status' => 'pending',
        'start_time' => $startTime,
        'end_time' => $endTime,
    ]);

    $service = app(ReservationService::class);
    $updated = $service->cancelByUser($reservation, $user);

    expect($updated->status)->toBe('cancelled_by_user')
        ->and($reservation->fresh()->status)->toBe('cancelled_by_user');
});

it('allows a user to cancel their own approved reservation when more than 1 hour before start (BR-8)', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    $startTime = Carbon::now()->addHours(2);
    $endTime = $startTime->copy()->addHour();

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'status' => 'approved',
        'start_time' => $startTime,
        'end_time' => $endTime,
    ]);

    $service = app(ReservationService::class);
    $updated = $service->cancelByUser($reservation, $user);

    expect($updated->status)->toBe('cancelled_by_user')
        ->and($reservation->fresh()->status)->toBe('cancelled_by_user');
});

it('rejects cancellation when start time is less than 1 hour away (BR-8)', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    // Mulai 45 menit dari sekarang (kurang dari 1 jam)
    $startTime = Carbon::now()->addMinutes(45);
    $endTime = $startTime->copy()->addHour();

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'status' => 'pending',
        'start_time' => $startTime,
        'end_time' => $endTime,
    ]);

    $service = app(ReservationService::class);

    expect(fn() => $service->cancelByUser($reservation, $user))
        ->toThrow(ConflictHttpException::class, 'Reservasi hanya dapat dibatalkan paling lambat 1 jam sebelum waktu mulai.');
});

it('rejects cancellation of another users reservation', function () {
    $userA = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $userB = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    $startTime = Carbon::now()->addHours(3);
    $endTime = $startTime->copy()->addHour();

    $reservation = Reservation::factory()->create([
        'user_id' => $userA->id,
        'facility_id' => $facility->id,
        'status' => 'pending',
        'start_time' => $startTime,
        'end_time' => $endTime,
    ]);

    $service = app(ReservationService::class);

    expect(fn() => $service->cancelByUser($reservation, $userB))
        ->toThrow(AccessDeniedHttpException::class, 'Anda hanya dapat membatalkan reservasi milik Anda sendiri.');
});

it('rejects cancellation when status is not pending or approved', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    $startTime = Carbon::now()->addHours(3);
    $endTime = $startTime->copy()->addHour();

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'status' => 'rejected',
        'reject_reason' => 'Ruangan tidak dapat dipinjam.',
        'start_time' => $startTime,
        'end_time' => $endTime,
    ]);

    $service = app(ReservationService::class);

    expect(fn() => $service->cancelByUser($reservation, $user))
        ->toThrow(ConflictHttpException::class, 'Hanya reservasi berstatus pending atau approved yang dapat dibatalkan.');
});

it('checks ReservationPolicy authorization rules correctly', function () {
    $policy = new ReservationPolicy;

    $owner = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $otherUser = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $petugas = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);

    $facility = Facility::factory()->create(['status' => 'aktif']);

    $validReservation = Reservation::factory()->create([
        'user_id' => $owner->id,
        'facility_id' => $facility->id,
        'status' => 'pending',
        'start_time' => Carbon::now()->addHours(2),
        'end_time' => Carbon::now()->addHours(3),
    ]);

    $urgentReservation = Reservation::factory()->create([
        'user_id' => $owner->id,
        'facility_id' => $facility->id,
        'status' => 'pending',
        'start_time' => Carbon::now()->addMinutes(30),
        'end_time' => Carbon::now()->addHours(2),
    ]);

    // Test view policy
    expect($policy->view($owner, $validReservation))->toBeTrue()
        ->and($policy->view($petugas, $validReservation))->toBeTrue()
        ->and($policy->view($admin, $validReservation))->toBeTrue()
        ->and($policy->view($otherUser, $validReservation))->toBeFalse();

    // Test cancel policy
    expect($policy->cancel($owner, $validReservation))->toBeTrue()
        ->and($policy->cancel($owner, $urgentReservation))->toBeFalse()
        ->and($policy->cancel($otherUser, $validReservation))->toBeFalse();
});

it('stores the cancellation reason when cancelled by user', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    $startTime = Carbon::now()->addHours(3);
    $endTime = $startTime->copy()->addHour();

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'status' => 'pending',
        'start_time' => $startTime,
        'end_time' => $endTime,
    ]);

    $service = app(ReservationService::class);
    $updated = $service->cancelByUser($reservation, $user, 'Dibatalkan karena bentrok dengan jadwal ujian');

    expect($updated->status)->toBe('cancelled_by_user')
        ->and($updated->cancel_reason)->toBe('Dibatalkan karena bentrok dengan jadwal ujian');
});

it('allows cancellation through HTTP delete endpoint with required reason', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    $startTime = Carbon::now()->addHours(4);
    $endTime = $startTime->copy()->addHour();

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'status' => 'pending',
        'start_time' => $startTime,
        'end_time' => $endTime,
    ]);

    $response = $this->actingAs($user)->delete(route('reservasi.destroy', $reservation), [
        'cancel_reason' => 'Perubahan mendadak pada susunan panitia',
    ]);

    $response->assertRedirect(route('reservasi.show', $reservation));
    $response->assertSessionHas('success');

    $reservation->refresh();
    expect($reservation->status)->toBe('cancelled_by_user')
        ->and($reservation->cancel_reason)->toBe('Perubahan mendadak pada susunan panitia');
});
