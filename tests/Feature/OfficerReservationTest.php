<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function officerReservationCarbon(string $date, string $time): Carbon
{
    return Carbon::parse("{$date} {$time}", config('app.timezone'));
}

function makeOfficerReservationActors(): array
{
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $requester = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);

    $reservation = Reservation::factory()->create([
        'user_id' => $requester->id,
        'facility_id' => $facility->id,
        'start_time' => officerReservationCarbon('2030-02-01', '08:00'),
        'end_time' => officerReservationCarbon('2030-02-01', '09:00'),
    ]);

    return [$facility, $reservation, $officer];
}

it('forbids guests and pengguna from officer reservation pages', function () {
    [, $reservation] = makeOfficerReservationActors();

    $this->get(route('petugas.dashboard'))->assertRedirect(route('login'));
    $this->get(route('petugas.reservasi.index'))->assertRedirect(route('login'));
    $this->get(route('petugas.reservasi.show', $reservation))->assertRedirect(route('login'));
    $this->post(route('petugas.reservasi.approve', $reservation))->assertRedirect(route('login'));

    $pengguna = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $this->actingAs($pengguna)->get(route('petugas.reservasi.index'))->assertForbidden();
    $this->actingAs($pengguna)->post(route('petugas.reservasi.approve', $reservation))->assertForbidden();
});

it('lets officer view the queue and its filters', function () {
    [$facility, $reservation, $officer] = makeOfficerReservationActors();

    $this->actingAs($officer)
        ->get(route('petugas.reservasi.index'))
        ->assertOk()
        ->assertSee($reservation->facility->name)
        ->assertSee($reservation->user->name);
});

it('filters the queue by status', function () {
    [$facility, $reservation, $officer] = makeOfficerReservationActors();

    $otherUser = User::factory()->create(['name' => 'Kasus Filter Khusus', 'role' => 'pengguna', 'account_status' => 'aktif']);
    Reservation::factory()->create([
        'user_id' => $otherUser->id,
        'facility_id' => $facility->id,
        'status' => 'approved',
        'start_time' => officerReservationCarbon('2030-02-02', '10:00'),
        'end_time' => officerReservationCarbon('2030-02-02', '11:00'),
    ]);

    $this->actingAs($officer)
        ->get(route('petugas.reservasi.index', ['status' => 'approved']))
        ->assertOk()
        ->assertSee('Kasus Filter Khusus');

    $this->actingAs($officer)
        ->get(route('petugas.reservasi.index', ['status' => 'rejected']))
        ->assertOk()
        ->assertDontSee('Kasus Filter Khusus');
});

it('lets officer approve a pending reservation through the route (BR-7)', function () {
    [, $reservation, $officer] = makeOfficerReservationActors();

    $this->actingAs($officer)
        ->post(route('petugas.reservasi.approve', $reservation))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($reservation->fresh()->status)->toBe('approved')
        ->and($reservation->fresh()->decided_by)->toBe($officer->id)
        ->and($reservation->fresh()->decided_at)->not->toBeNull();
});

it('flashes an error when approving a conflicting pending reservation (BR-7)', function () {
    [$facility, $first, $officer] = makeOfficerReservationActors();

    $other = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $second = Reservation::factory()->create([
        'user_id' => $other->id,
        'facility_id' => $facility->id,
        'start_time' => officerReservationCarbon('2030-02-01', '08:30'),
        'end_time' => officerReservationCarbon('2030-02-01', '09:00'),
    ]);

    $this->actingAs($officer)->post(route('petugas.reservasi.approve', $first))->assertRedirect();

    $this->actingAs($officer)
        ->post(route('petugas.reservasi.approve', $second))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($second->fresh()->status)->toBe('pending');
});

it('requires a reason of at least 10 characters to reject (BR-9)', function () {
    [, $reservation, $officer] = makeOfficerReservationActors();

    $this->actingAs($officer)
        ->post(route('petugas.reservasi.reject', $reservation), ['reason' => 'Pendek'])
        ->assertSessionHasErrors('reason');

    expect($reservation->fresh()->status)->toBe('pending');
});

it('rejects a pending reservation with a persisted reason', function () {
    [, $reservation, $officer] = makeOfficerReservationActors();

    $this->actingAs($officer)
        ->post(route('petugas.reservasi.reject', $reservation), ['reason' => 'Slot tersebut sudah dipakai kegiatan lain.'])
        ->assertRedirect()
        ->assertSessionHas('success');

    $fresh = $reservation->fresh();

    expect($fresh->status)->toBe('rejected')
        ->and($fresh->reject_reason)->toBe('Slot tersebut sudah dipakai kegiatan lain.')
        ->and($fresh->decided_by)->toBe($officer->id);
});

it('requires a cancel reason of at least 10 characters (BR-9)', function () {
    [, $reservation, $officer] = makeOfficerReservationActors();
    $reservation->update(['status' => 'approved']);

    $this->actingAs($officer)
        ->post(route('petugas.reservasi.cancel', $reservation), ['cancel_reason' => 'Singkat'])
        ->assertSessionHasErrors('cancel_reason');

    expect($reservation->fresh()->status)->toBe('approved');
});

it('cancels a pending reservation by officer with a persisted reason (BR-9)', function () {
    [, $reservation, $officer] = makeOfficerReservationActors();

    $this->actingAs($officer)
        ->post(route('petugas.reservasi.cancel', $reservation), ['cancel_reason' => 'Jadwal bertabrakan dengan renovasi fasilitas.'])
        ->assertRedirect()
        ->assertSessionHas('success');

    $fresh = $reservation->fresh();

    expect($fresh->status)->toBe('cancelled_by_officer')
        ->and($fresh->cancel_reason)->toBe('Jadwal bertabrakan dengan renovasi fasilitas.')
        ->and($fresh->decided_by)->toBe($officer->id);
});

it('cancels an approved reservation when the facility enters repair (BR-16)', function () {
    [$facility, $reservation] = makeOfficerReservationActors();
    $reservation->update(['status' => 'approved']);

    $this->actingAs(User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']))
        ->post(route('petugas.reservasi.cancel', $reservation), ['cancel_reason' => 'Fasilitas masuk perbaikan dan tidak dapat dipakai.'])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($reservation->fresh()->status)->toBe('cancelled_by_officer');
});

it('does not allow cancelling an already rejected reservation', function () {
    [, $reservation, $officer] = makeOfficerReservationActors();
    $reservation->update(['status' => 'rejected']);

    $this->actingAs($officer)
        ->post(route('petugas.reservasi.cancel', $reservation), ['cancel_reason' => 'Mencoba membatalkan reservasi yang sudah ditolak.'])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($reservation->fresh()->status)->toBe('rejected');
});
