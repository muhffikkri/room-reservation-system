<?php

use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('forbids guests from the admin dashboard', function () {
    $this->get('/admin')->assertRedirect(route('login'));
});

it('forbids pengguna and petugas from the admin dashboard', function () {
    foreach (['pengguna', 'petugas'] as $role) {
        $user = User::factory()->create([
            'role' => $role,
            'account_status' => 'aktif',
        ]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }
});

it('shows an empty admin dashboard when there is no queue', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Dashboard Admin')
        ->assertSee('Tidak ada reservasi yang menunggu.')
        ->assertSee('Tidak ada laporan baru.')
        ->assertSee('Tidak ada akun yang menunggu verifikasi.');
});

it('summarizes reservations, reports, repair facilities, and pending accounts', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $user = User::factory()->create([
        'role' => 'pengguna',
        'account_status' => 'aktif',
    ]);

    $facility = Facility::factory()->create([
        'name' => 'Aula Terpadu',
        'status' => 'aktif',
    ]);

    Reservation::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'status' => 'pending',
        'start_time' => now()->tomorrow()->setTime(9, 0),
        'end_time' => now()->tomorrow()->setTime(10, 0),
    ]);

    Report::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'status' => 'baru',
    ]);

    Facility::factory()->create([
        'name' => 'Proyektor Rusak',
        'status' => 'perbaikan',
    ]);

    User::factory()->create([
        'role' => 'pengguna',
        'account_status' => 'pending',
        'email' => 'menunggu@kampus.test',
    ]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Aula Terpadu')
        ->assertSee('Fasilitas Dalam Perbaikan')
        ->assertSee('menunggu@kampus.test')
        ->assertSee('Pending');
});
