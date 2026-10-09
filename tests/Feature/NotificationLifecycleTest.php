<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\ReservationStatusChanged;
use App\Notifications\ReservationSubmitted;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

/**
 * Fixture memakai awal 2030 sebagai "masa depan yang sah" agar rentang slot
 * tetap berada di dalam jendela pemesanan BR-3 (maksimal 365 hari).
 */
beforeEach(function () {
    $this->travelTo(Carbon::parse('2030-01-01 07:00', config('app.timezone')));
});

function notificationLifecycleSlot(string $date, string $time): Carbon
{
    return Carbon::parse("{$date} {$time}", config('app.timezone'));
}

/**
 * @return array{0: Facility, 1: User, 2: User} [fasilitas, pemohon, petugas]
 */
function notificationLifecycleActors(): array
{
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $owner = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);

    return [$facility, $owner, $officer];
}

function notificationLifecyclePending(Facility $facility, User $owner, string $date, string $start, string $end): Reservation
{
    return app(ReservationService::class)->create(
        $owner,
        $facility,
        notificationLifecycleSlot($date, $start),
        notificationLifecycleSlot($date, $end),
        'Kegiatan organisasi membutuhkan ruang rapat besar.',
    );
}

it('notifies every active petugas when a pengguna submits a reservation', function () {
    [$facility, $owner, $officer] = notificationLifecycleActors();
    $secondOfficer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    $pendingOfficer = User::factory()->create(['role' => 'petugas', 'account_status' => 'pending']);
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);

    $reservation = notificationLifecyclePending($facility, $owner, '2030-01-03', '09:00', '10:00');

    expect($officer->notifications()->count())->toBe(1)
        ->and($secondOfficer->notifications()->count())->toBe(1)
        ->and($pendingOfficer->notifications()->count())->toBe(0)
        ->and($admin->notifications()->count())->toBe(0);

    $notification = $officer->notifications()->firstOrFail();

    expect($notification->type)->toBe(ReservationSubmitted::class)
        ->and($notification->data['type'])->toBe('reservation_submitted')
        ->and($notification->data['message'])->toContain($owner->name)
        ->and($notification->data['reservation_id'])->toBe($reservation->id)
        ->and($notification->data['url'])->toBe(route('petugas.reservasi.show', $reservation))
        ->and($notification->read_at)->toBeNull();
});

it('notifies the owner when a petugas approves their reservation', function () {
    [$facility, $owner, $officer] = notificationLifecycleActors();
    $reservation = notificationLifecyclePending($facility, $owner, '2030-01-03', '09:00', '10:00');

    app(ReservationService::class)->approve($reservation, $officer);

    $notification = $owner->notifications()->firstOrFail();

    expect($owner->notifications()->count())->toBe(1)
        ->and($notification->type)->toBe(ReservationStatusChanged::class)
        ->and($notification->data['status'])->toBe('approved')
        ->and($notification->data['message'])->toContain(Reservation::statusLabel('approved'))
        ->and($notification->data['url'])->toBe(route('reservasi.show', $reservation));
});

it('notifies the owner when a petugas rejects their reservation', function () {
    [$facility, $owner, $officer] = notificationLifecycleActors();
    $reservation = notificationLifecyclePending($facility, $owner, '2030-01-03', '09:00', '10:00');

    app(ReservationService::class)->reject($reservation, $officer, 'Ruangan sedang dipakai kegiatan lain.');

    $notification = $owner->notifications()->firstOrFail();

    expect($owner->notifications()->count())->toBe(1)
        ->and($notification->data['status'])->toBe('rejected')
        ->and($notification->data['message'])->toContain(Reservation::statusLabel('rejected'));
});

it('notifies the owner when a petugas cancels their reservation', function () {
    [$facility, $owner, $officer] = notificationLifecycleActors();
    $reservation = notificationLifecyclePending($facility, $owner, '2030-01-03', '09:00', '10:00');

    app(ReservationService::class)->approve($reservation, $officer);
    $owner->notifications()->delete();

    app(ReservationService::class)->cancel($reservation, $officer, 'Fasilitas masuk masa perbaikan.');

    $notification = $owner->notifications()->firstOrFail();

    expect($notification->data['status'])->toBe('cancelled_by_officer')
        ->and($notification->data['message'])->toContain(Reservation::statusLabel('cancelled_by_officer'));
});

it('notifies the owner when the system cancels a stale reservation', function () {
    [$facility, $owner] = notificationLifecycleActors();

    $reservation = Reservation::factory()->create([
        'user_id' => $owner->id,
        'facility_id' => $facility->id,
        'status' => 'pending',
        'start_time' => now()->addMinutes(30),
        'end_time' => now()->addMinutes(90),
        'purpose' => 'Kegiatan yang menunggu dan akan kedaluwarsa.',
    ]);

    $expired = app(ReservationService::class)->expireStale();

    $notification = $owner->notifications()->firstOrFail();

    expect($expired)->toBe(1)
        ->and($reservation->fresh()->status)->toBe('cancelled_by_system')
        ->and($notification->data['status'])->toBe('cancelled_by_system')
        ->and($notification->data['message'])->not->toContain('Dibatalkan oleh Sistem');
});
