<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('forbids pengguna from the admin verification page', function () {
    $user = User::factory()->create([
        'role' => 'pengguna',
        'account_status' => 'aktif',
    ]);

    $this->actingAs($user)->get('/admin/pengguna/verifikasi')->assertForbidden();
});

it('forbids petugas from the admin verification page', function () {
    $user = User::factory()->create([
        'role' => 'petugas',
        'account_status' => 'aktif',
    ]);

    $this->actingAs($user)->get('/admin/pengguna/verifikasi')->assertForbidden();
});

it('allows admin to open the verification page', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $this->actingAs($admin)->get('/admin/pengguna/verifikasi')->assertOk();
});

it('redirects pending accounts away from admin routes', function () {
    $pending = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'pending',
    ]);

    $response = $this->actingAs($pending)->get('/admin/pengguna/verifikasi');

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'Akun Anda menunggu verifikasi admin.');
    $this->assertGuest();
});

it('rejects a pending account by admin', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $user = User::factory()->create([
        'email' => 'to-reject@student.kampus.test',
        'account_status' => 'pending',
    ]);

    $this->actingAs($admin)->patch("/admin/pengguna/{$user->id}/tolak")->assertRedirect();

    expect($user->fresh()->account_status)->toBe('ditolak');
});

it('verifies a pending account by admin', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $user = User::factory()->create([
        'email' => 'to-verify@student.kampus.test',
        'account_status' => 'pending',
    ]);

    $this->actingAs($admin)->patch("/admin/pengguna/{$user->id}/verifikasi")->assertRedirect();

    expect($user->fresh()->account_status)->toBe('aktif');
});

it('returns 404 when verifying a non-pending account', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $activeUser = User::factory()->create([
        'account_status' => 'aktif',
    ]);

    $this->actingAs($admin)->patch("/admin/pengguna/{$activeUser->id}/verifikasi")->assertNotFound();
});

it('returns 404 when rejecting a non-pending account', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $activeUser = User::factory()->create([
        'account_status' => 'aktif',
    ]);

    $this->actingAs($admin)->patch("/admin/pengguna/{$activeUser->id}/tolak")->assertNotFound();
});

it('forbids petugas from verifying accounts', function () {
    $petugas = User::factory()->create([
        'role' => 'petugas',
        'account_status' => 'aktif',
    ]);

    $pending = User::factory()->create([
        'account_status' => 'pending',
    ]);

    $this->actingAs($petugas)->patch("/admin/pengguna/{$pending->id}/verifikasi")->assertForbidden();
});
