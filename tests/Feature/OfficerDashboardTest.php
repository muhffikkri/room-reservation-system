<?php

use App\Models\Facility;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows officer to view officer dashboard', function () {
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);

    $response = $this->actingAs($officer)->get(route('petugas.dashboard'));

    $response->assertStatus(200)
        ->assertSee('Dashboard Petugas')
        ->assertSee('Reservasi Menunggu')
        ->assertSee('Laporan Baru');
});

it('orders dashboard reports with active ones above finished ones', function () {
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $activeFacility = Facility::factory()->create(['name' => 'Fasilitas Masih Aktif', 'status' => 'aktif']);
    $closedFacility = Facility::factory()->create(['name' => 'Fasilitas Sudah Tutup', 'status' => 'aktif']);

    Report::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $closedFacility->id,
        'status' => 'selesai',
        'resolution_note' => 'Sudah diperbaiki',
        'created_at' => now()->addDay(),
    ]);
    Report::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $activeFacility->id,
        'status' => 'baru',
        'created_at' => now()->subDay(),
    ]);

    $this->actingAs($officer)
        ->get(route('petugas.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Fasilitas Masih Aktif', 'Fasilitas Sudah Tutup'])
        ->assertSee('Baru')
        ->assertSee('Selesai');
});

it('prevents regular pengguna from accessing officer dashboard', function () {
    $pengguna = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    $response = $this->actingAs($pengguna)->get(route('petugas.dashboard'));

    $response->assertStatus(403);
});
