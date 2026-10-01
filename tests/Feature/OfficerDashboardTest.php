<?php

use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

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

it('limits the queue preview in SQL rather than after loading every row', function () {
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    for ($i = 0; $i < 12; $i++) {
        Reservation::factory()->create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'status' => 'pending',
            'start_time' => now()->addDays(3)->setTime(8, 0),
            'end_time' => now()->addDays(3)->setTime(9, 0),
        ]);
    }

    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $this->actingAs($officer)->get(route('petugas.dashboard'))->assertOk();

    $pendingQueries = array_values(array_filter(
        $queries,
        fn (string $sql) => str_contains($sql, 'from `reservations`') && str_contains($sql, 'order by')
    ));

    // Pratinjau harus dipangkas di database. Kalau tidak, kueri antrean
    // berjalan tanpa limit dan seluruh baris ditransfer untuk lima baris.
    expect($pendingQueries)->not->toBeEmpty();

    foreach ($pendingQueries as $sql) {
        expect(strtolower($sql))->toContain('limit 5');
    }
});

it('still reports the full pending total while previewing five rows', function () {
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    for ($i = 0; $i < 7; $i++) {
        Reservation::factory()->create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'status' => 'pending',
            'start_time' => now()->addDays(3)->setTime(8, 0),
            'end_time' => now()->addDays(3)->setTime(9, 0),
        ]);
    }

    $response = $this->actingAs($officer)->get(route('petugas.dashboard'));

    // Jumlah total harus tetap utuh walau hanya lima baris yang dirender.
    $response->assertOk()
        ->assertViewHas('pendingReservationCount', 7)
        ->assertViewHas('pendingReservations', fn ($rows) => $rows->count() === 5);
});

it('prevents regular pengguna from accessing officer dashboard', function () {
    $pengguna = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    $response = $this->actingAs($pengguna)->get(route('petugas.dashboard'));

    $response->assertStatus(403);
});
