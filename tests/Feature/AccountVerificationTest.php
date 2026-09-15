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

it('rejects verifying a non-pending account with a conflict message', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $activeUser = User::factory()->create([
        'role' => 'pengguna',
        'account_status' => 'aktif',
    ]);

    $this->actingAs($admin)->patch("/admin/pengguna/{$activeUser->id}/verifikasi")
        ->assertRedirect(route('admin.pengguna.verifikasi'))
        ->assertSessionHas('error', 'Akun tersebut sudah diproses admin lain.');
});

it('rejects rejecting a non-pending account with a conflict message', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $activeUser = User::factory()->create([
        'role' => 'pengguna',
        'account_status' => 'aktif',
    ]);

    $this->actingAs($admin)->patch("/admin/pengguna/{$activeUser->id}/tolak")
        ->assertRedirect(route('admin.pengguna.verifikasi'))
        ->assertSessionHas('error', 'Akun tersebut sudah diproses admin lain.');
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

it('returns 404 when verifying a non-pengguna pending account', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $petugasPending = User::factory()->create([
        'role' => 'petugas',
        'account_status' => 'pending',
    ]);

    $this->actingAs($admin)->patch("/admin/pengguna/{$petugasPending->id}/verifikasi")->assertNotFound();
    expect($petugasPending->fresh()->account_status)->toBe('pending');
});

it('records audit columns when verifying and rejecting', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $toVerify = User::factory()->create(['account_status' => 'pending']);
    $this->actingAs($admin)->patch("/admin/pengguna/{$toVerify->id}/verifikasi")->assertRedirect();
    expect($toVerify->fresh()->verified_by)->toBe($admin->id)
        ->and($toVerify->fresh()->verified_at)->not->toBeNull();

    $toReject = User::factory()->create(['account_status' => 'pending']);
    $this->actingAs($admin)->patch("/admin/pengguna/{$toReject->id}/tolak")->assertRedirect();
    expect($toReject->fresh()->rejected_by)->toBe($admin->id)
        ->and($toReject->fresh()->rejected_at)->not->toBeNull();
});

it('hides non-pengguna pending accounts from the verification list', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $penggunaPending = User::factory()->create(['role' => 'pengguna', 'account_status' => 'pending']);
    $petugasPending = User::factory()->create(['role' => 'petugas', 'account_status' => 'pending']);

    $response = $this->actingAs($admin)->get('/admin/pengguna/verifikasi')->assertOk();

    $response->assertSee($penggunaPending->email);
    $response->assertDontSee($petugasPending->email);
});
