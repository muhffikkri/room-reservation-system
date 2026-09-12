<?php

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

it('prevents regular pengguna from accessing officer dashboard', function () {
    $pengguna = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    $response = $this->actingAs($pengguna)->get(route('petugas.dashboard'));

    $response->assertStatus(403);
});
