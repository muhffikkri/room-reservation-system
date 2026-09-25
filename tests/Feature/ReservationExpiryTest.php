<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array{0: Facility, 1: User, 2: User} [facility, pemilik, petugas]
 */
function makeExpiryActors(): array
{
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $owner = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);

    return [$facility, $owner, $officer];
}

function createPendingReservation(Facility $facility, User $owner, int $minutesFromNow): Reservation
{
    $start = now()->addMinutes($minutesFromNow);

    return Reservation::factory()->create([
        'user_id' => $owner->id,
        'facility_id' => $facility->id,
        'status' => 'pending',
        'start_time' => $start,
        'end_time' => $start->copy()->addHour(),
    ]);
}

it('auto-cancels stale pending reservations on the pengguna dashboard', function () {
    [, $owner] = makeExpiryActors();
    $reservation = createPendingReservation(Facility::factory()->create(['status' => 'aktif']), $owner, 30);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSessionHas('info')
        ->assertSee('otomatis dibatalkan sistem')
        ->assertSee('Gagal')
        ->assertDontSee('Dibatalkan oleh Sistem');

    $fresh = $reservation->fresh();

    expect($fresh->status)->toBe('cancelled_by_system')
        ->and($fresh->cancel_reason)->not->toBeNull()
        ->and($fresh->decided_at)->not->toBeNull()
        ->and($fresh->decided_by)->toBeNull();
});

it('shows Gagal with a flash message on the reservation detail', function () {
    [, $owner] = makeExpiryActors();
    $reservation = createPendingReservation(Facility::factory()->create(['status' => 'aktif']), $owner, 30);

    $this->actingAs($owner)
        ->get(route('reservasi.show', $reservation))
        ->assertOk()
        ->assertSessionHas('info')
        ->assertSee('Gagal')
        ->assertSee('Alasan Pembatalan Otomatis oleh Sistem:', false);

    expect($reservation->fresh()->status)->toBe('cancelled_by_system');
});

it('shows Gagal on the reservation history list', function () {
    [, $owner] = makeExpiryActors();
    $reservation = createPendingReservation(Facility::factory()->create(['status' => 'aktif']), $owner, 45);

    $this->actingAs($owner)
        ->get(route('reservasi.index'))
        ->assertOk()
        ->assertSessionHas('info')
        ->assertSee('Gagal');

    expect($reservation->fresh()->status)->toBe('cancelled_by_system');
});

it('leaves pending reservations before the deadline untouched', function () {
    [, $owner] = makeExpiryActors();
    $reservation = createPendingReservation(Facility::factory()->create(['status' => 'aktif']), $owner, 180);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSessionMissing('info')
        ->assertDontSee('Gagal');

    expect($reservation->fresh()->status)->toBe('pending');
});

it('auto-cancels stale pending reservations on the officer dashboard and queue', function () {
    [, $owner, $officer] = makeExpiryActors();
    $reservation = createPendingReservation(Facility::factory()->create(['status' => 'aktif']), $owner, 30);

    $this->actingAs($officer)
        ->get(route('petugas.dashboard'))
        ->assertOk()
        ->assertSessionHas('info');

    expect($reservation->fresh()->status)->toBe('cancelled_by_system');
});

it('shows Dibatalkan oleh Sistem instead of Gagal on the officer detail page', function () {
    [, $owner, $officer] = makeExpiryActors();
    $reservation = createPendingReservation(Facility::factory()->create(['name' => 'Ruang Kadaluarsa', 'status' => 'aktif']), $owner, 30);

    $this->actingAs($officer)
        ->get(route('petugas.reservasi.show', $reservation))
        ->assertOk()
        ->assertSee('>Dibatalkan oleh Sistem</span>', false)
        ->assertDontSee('Gagal');
});

it('blocks approving a reservation past the approval deadline', function () {
    [, $owner, $officer] = makeExpiryActors();
    $reservation = createPendingReservation(Facility::factory()->create(['status' => 'aktif']), $owner, 30);

    $this->actingAs($officer)
        ->post(route('petugas.reservasi.approve', $reservation))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($reservation->fresh()->status)->toBe('cancelled_by_system');
});

it('still allows approving a pending reservation inside the approval window', function () {
    [, $owner, $officer] = makeExpiryActors();
    $reservation = createPendingReservation(Facility::factory()->create(['status' => 'aktif']), $owner, 240);

    $this->actingAs($officer)
        ->post(route('petugas.reservasi.approve', $reservation))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($reservation->fresh()->status)->toBe('approved');
});

it('expires stale pending reservations when a new reservation is created', function () {
    [, $owner] = makeExpiryActors();
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $stale = createPendingReservation($facility, $owner, 30);

    $this->actingAs($owner)
        ->post(route('reservasi.store'), [
            'facility_id' => $facility->id,
            'date' => now()->addDays(3)->format('Y-m-d'),
            'start_time' => '08:00',
            'end_time' => '09:00',
            'purpose' => 'Kegiatan organisasi membutuhkan ruang rapat besar.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($stale->fresh()->status)->toBe('cancelled_by_system');
});

it('filters the officer queue by the cancelled_by_system status', function () {
    [, $owner, $officer] = makeExpiryActors();
    $staleFacility = Facility::factory()->create(['name' => 'Fasilitas Kadaluarsa', 'status' => 'aktif']);
    $freshFacility = Facility::factory()->create(['name' => 'Fasilitas Aktif Antrean', 'status' => 'aktif']);
    $stale = createPendingReservation($staleFacility, $owner, 30);
    $fresh = createPendingReservation($freshFacility, $owner, 180);

    $this->actingAs($officer)
        ->get(route('petugas.reservasi.index', ['status' => 'cancelled_by_system']))
        ->assertOk()
        ->assertSee('Fasilitas Kadaluarsa')
        ->assertDontSee('Fasilitas Aktif Antrean');

    expect($stale->fresh()->status)->toBe('cancelled_by_system')
        ->and($fresh->fresh()->status)->toBe('pending');
});

it('expires stale pending reservations in the officer ajax queue', function () {
    [, $owner, $officer] = makeExpiryActors();
    createPendingReservation(Facility::factory()->create(['status' => 'aktif']), $owner, 30);

    $response = $this->actingAs($officer)
        ->get(route('petugas.reservasi.ajax', ['status' => 'cancelled_by_system']))
        ->assertOk();

    expect($response->json('reservations.0.status'))->toBe('cancelled_by_system');
});
