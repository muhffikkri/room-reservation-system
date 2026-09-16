<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('allows active authenticated users to access reservation create form', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['name' => 'Aula Terpadu', 'status' => 'aktif']);
    Facility::factory()->create(['name' => 'Gudang Rusak', 'status' => 'nonaktif']);

    $response = $this->actingAs($user)->get(route('reservasi.create'));

    $response->assertStatus(200)
        ->assertSee('Aula Terpadu')
        ->assertDontSee('Gudang Rusak');
});

it('successfully stores a valid reservation and redirects to show', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    $futureDate = Carbon::tomorrow()->format('Y-m-d');

    $response = $this->actingAs($user)->post(route('reservasi.store'), [
        'facility_id' => $facility->id,
        'date' => $futureDate,
        'start_time' => '08:00',
        'end_time' => '10:00',
        'purpose' => 'Rapat koordinasi kegiatan kampus',
    ]);

    $reservation = Reservation::where('user_id', $user->id)->first();

    expect($reservation)->not->toBeNull()
        ->and($reservation->status)->toBe('pending')
        ->and($reservation->facility_id)->toBe($facility->id)
        ->and($reservation->purpose)->toBe('Rapat koordinasi kegiatan kampus');

    $response->assertRedirect(route('reservasi.show', $reservation));
    $response->assertSessionHas('success');
});

it('rejects reservation submission if purpose is less than 10 characters', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    $response = $this->actingAs($user)->post(route('reservasi.store'), [
        'facility_id' => $facility->id,
        'date' => Carbon::tomorrow()->format('Y-m-d'),
        'start_time' => '08:00',
        'end_time' => '09:00',
        'purpose' => 'Rapat', // kurang dari 10 karakter
    ]);

    $response->assertSessionHasErrors(['purpose']);
    expect(Reservation::count())->toBe(0);
});

it('rejects reservation submission if end time is before or equal to start time', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    $response = $this->actingAs($user)->post(route('reservasi.store'), [
        'facility_id' => $facility->id,
        'date' => Carbon::tomorrow()->format('Y-m-d'),
        'start_time' => '10:00',
        'end_time' => '09:00',
        'purpose' => 'Kegiatan praktikum mandiri mahasiswa',
    ]);

    $response->assertSessionHasErrors(['end_time']);
    expect(Reservation::count())->toBe(0);
});

it('rejects reservation when overlapping with an approved reservation (BR-6)', function () {
    $user1 = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $user2 = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    $tomorrow = Carbon::tomorrow();
    $start = $tomorrow->copy()->setTime(8, 0);
    $end = $tomorrow->copy()->setTime(10, 0);

    // Sudah ada reservasi approved dari orang lain di jam 08.00 - 10.00
    Reservation::factory()->create([
        'user_id' => $user1->id,
        'facility_id' => $facility->id,
        'status' => 'approved',
        'start_time' => $start,
        'end_time' => $end,
    ]);

    // User2 mencoba memesan di jam 09.00 - 11.00 (overlap)
    $response = $this->actingAs($user2)->post(route('reservasi.store'), [
        'facility_id' => $facility->id,
        'date' => $tomorrow->format('Y-m-d'),
        'start_time' => '09:00',
        'end_time' => '11:00',
        'purpose' => 'Seminar kepemimpinan mahasiswa',
    ]);

    $response->assertSessionHasErrors();
    expect(Reservation::where('user_id', $user2->id)->count())->toBe(0);
});
