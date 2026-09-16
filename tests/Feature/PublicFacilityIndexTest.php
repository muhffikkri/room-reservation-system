<?php

use App\Models\Facility;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('displays the list of facilities on public catalog index', function () {
    $facility1 = Facility::factory()->create([
        'name' => 'Aula Terpadu',
        'type' => 'aula',
        'location' => 'Gedung A',
        'capacity' => 150,
        'status' => 'aktif',
    ]);

    $facility2 = Facility::factory()->create([
        'name' => 'Lab Multimedia',
        'type' => 'laboratorium',
        'location' => 'Gedung B',
        'capacity' => 30,
        'status' => 'aktif',
    ]);

    $response = $this->get('/fasilitas');

    $response->assertOk()
        ->assertSee('Katalog Fasilitas Kampus')
        ->assertSee('Aula Terpadu')
        ->assertSee('Lab Multimedia');
});

it('filters facilities by keyword q', function () {
    Facility::factory()->create(['name' => 'Laboratorium Fisika']);
    Facility::factory()->create(['name' => 'Aula Utama Serbaguna']);

    $this->get('/fasilitas?q=Fisika')
        ->assertOk()
        ->assertSee('Laboratorium Fisika')
        ->assertDontSee('Aula Utama Serbaguna');
});

it('filters facilities by tipe', function () {
    Facility::factory()->create(['name' => 'Aula Pertemuan', 'type' => 'aula']);
    Facility::factory()->create(['name' => 'Lab Jaringan', 'type' => 'laboratorium']);

    $this->get('/fasilitas?tipe=aula')
        ->assertOk()
        ->assertSee('Aula Pertemuan')
        ->assertDontSee('Lab Jaringan');
});

it('filters facilities by lokasi', function () {
    Facility::factory()->create(['name' => 'Ruang 101', 'location' => 'Gedung Sains']);
    Facility::factory()->create(['name' => 'Ruang 201', 'location' => 'Gedung Teknik']);

    $this->get('/fasilitas?lokasi=Sains')
        ->assertOk()
        ->assertSee('Ruang 101')
        ->assertDontSee('Ruang 201');
});

it('filters facilities by kapasitas_min', function () {
    Facility::factory()->create(['name' => 'Ruang Kecil', 'capacity' => 15]);
    Facility::factory()->create(['name' => 'Ruang Sedang', 'capacity' => 45]);
    Facility::factory()->create(['name' => 'Ruang Besar', 'capacity' => 120]);

    $this->get('/fasilitas?kapasitas_min=40')
        ->assertOk()
        ->assertSee('Ruang Sedang')
        ->assertSee('Ruang Besar')
        ->assertDontSee('Ruang Kecil');
});

it('shows friendly empty message when no facility matches the query', function () {
    Facility::factory()->create(['name' => 'Aula Terpadu']);

    $this->get('/fasilitas?q=kata_kunci_tidak_ada_xyz123')
        ->assertOk()
        ->assertSee('Fasilitas tidak ditemukan')
        ->assertDontSee('Aula Terpadu');
});

it('rejects non-numeric kapasitas_min with validation error instead of 500', function () {
    $response = $this->get('/fasilitas?kapasitas_min=abc');

    $response->assertSessionHasErrors(['kapasitas_min']);
    $response->assertStatus(302);
});

it('rejects negative or zero kapasitas_min with validation error instead of 500', function () {
    $response = $this->get('/fasilitas?kapasitas_min=-5');

    $response->assertSessionHasErrors(['kapasitas_min']);
    $response->assertStatus(302);
});

it('rejects invalid facility tipe with validation error instead of 500', function () {
    $response = $this->get('/fasilitas?tipe=tipe_ngawur');

    $response->assertSessionHasErrors(['tipe']);
    $response->assertStatus(302);
});
