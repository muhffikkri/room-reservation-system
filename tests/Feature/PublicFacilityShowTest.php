<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('displays public facility detail page with general info', function () {
    $facility = Facility::factory()->create([
        'name' => 'Aula Serbaguna Gedung B',
        'type' => 'aula',
        'location' => 'Gedung B Lantai 3',
        'capacity' => 250,
        'description' => 'Aula serbaguna dengan sound system lengkap.',
        'status' => 'aktif',
    ]);

    $response = $this->get("/fasilitas/{$facility->id}");

    $response->assertOk()
        ->assertSee('Aula Serbaguna Gedung B')
        ->assertSee('Gedung B Lantai 3')
        ->assertSee('250 Orang')
        ->assertSee('Aula serbaguna dengan sound system lengkap.')
        ->assertSee('Lihat Jadwal Slot Ketersediaan');
});

it('does not leak reservation applicant names or purposes on facility detail (BR-13)', function () {
    $user = User::factory()->create([
        'name' => 'Budi Pemohon Rahasia',
        'email' => 'budi.rahasia@kampus.test',
    ]);

    $facility = Facility::factory()->create([
        'name' => 'Lab Komputer A',
        'status' => 'aktif',
    ]);

    Reservation::create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'purpose' => 'Rapat Rahasia Internal Senat',
        'start_time' => now()->addDay()->setTime(8, 0),
        'end_time' => now()->addDay()->setTime(10, 0),
        'status' => 'approved',
    ]);

    $response = $this->get("/fasilitas/{$facility->id}");

    $response->assertOk()
        ->assertSee('Lab Komputer A')
        ->assertDontSee('Budi Pemohon Rahasia')
        ->assertDontSee('budi.rahasia@kampus.test')
        ->assertDontSee('Rapat Rahasia Internal Senat');
});

it('displays appropriate status banner for facility under repair', function () {
    $facility = Facility::factory()->create([
        'name' => 'Proyektor Portable',
        'type' => 'alat',
        'status' => 'perbaikan',
    ]);

    $response = $this->get("/fasilitas/{$facility->id}");

    $response->assertOk()
        ->assertSee('Proyektor Portable')
        ->assertSee('Sedang Dalam Perbaikan')
        ->assertSee('Reservasi Tidak Tersedia');
});

it('returns 404 for non-existent facility id', function () {
    $this->get('/fasilitas/999999')->assertNotFound();
});
