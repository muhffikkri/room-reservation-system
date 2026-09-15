<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('redirects pengguna to the user dashboard after login', function () {
    User::factory()->create([
        'email' => 'pengguna-home@student.kampus.test',
        'password' => 'rahasia123',
        'role' => 'pengguna',
        'account_status' => 'aktif',
    ]);

    $response = $this->post('/login', [
        'email' => 'pengguna-home@student.kampus.test',
        'password' => 'rahasia123',
    ]);

    $response->assertRedirect(route('dashboard'));
});

it('redirects petugas to the officer dashboard after login', function () {
    User::factory()->create([
        'email' => 'petugas-home@kampus.test',
        'password' => 'rahasia123',
        'role' => 'petugas',
        'account_status' => 'aktif',
    ]);

    $response = $this->post('/login', [
        'email' => 'petugas-home@kampus.test',
        'password' => 'rahasia123',
    ]);

    $response->assertRedirect(route('petugas.dashboard'));
});

it('redirects admin to the admin dashboard after login', function () {
    User::factory()->create([
        'email' => 'admin-home@kampus.test',
        'password' => 'rahasia123',
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $response = $this->post('/login', [
        'email' => 'admin-home@kampus.test',
        'password' => 'rahasia123',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
});

it('normalizes phone to +62 format on registration', function () {
    $this->post('/register', [
        'name' => 'Normalisasi',
        'email' => '  NORMAL@student.kampus.test  ',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'identity' => '2110512200',
        'phone' => '0812 3456 789',
    ])->assertRedirect(route('login'));

    $this->assertDatabaseHas('users', [
        'email' => 'normal@student.kampus.test',
        'identity' => '2110512200',
        'phone' => '+628123456789',
    ]);
});

it('rejects duplicate identity with a field error', function () {
    User::factory()->create(['identity' => '2110512300']);

    $response = $this->post('/register', [
        'name' => 'Duplikat Identitas',
        'email' => 'duplikat-identitas@student.kampus.test',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'identity' => '2110512300',
        'phone' => '081200002300',
    ]);

    $response->assertSessionHasErrors('identity');
    $this->assertDatabaseMissing('users', ['email' => 'duplikat-identitas@student.kampus.test']);
});

it('rejects duplicate phone after normalization with a field error', function () {
    User::factory()->create(['phone' => '+628120000400']);

    $response = $this->post('/register', [
        'name' => 'Duplikat Telepon',
        'email' => 'duplikat-phone@student.kampus.test',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'identity' => '2110512400',
        'phone' => '08120000400',
    ]);

    $response->assertSessionHasErrors('phone');
    $this->assertDatabaseMissing('users', ['email' => 'duplikat-phone@student.kampus.test']);
});

it('clears remember token when role changes', function () {
    $user = User::factory()->create([
        'remember_token' => 'token-lama',
        'role' => 'pengguna',
        'account_status' => 'aktif',
    ]);

    $user->update(['role' => 'petugas']);

    expect($user->fresh()->remember_token)->toBeNull();
});

it('clears remember token when account status changes', function () {
    $user = User::factory()->create([
        'remember_token' => 'token-lama',
        'account_status' => 'pending',
    ]);

    $user->update(['account_status' => 'ditolak']);

    expect($user->fresh()->remember_token)->toBeNull();
});

it('keeps remember token when unrelated fields change', function () {
    $user = User::factory()->create(['remember_token' => 'token-lama']);

    $user->update(['name' => 'Nama Baru']);

    expect($user->fresh()->remember_token)->toBe('token-lama');
});

it('deletes database sessions when role changes', function () {
    config()->set('session.driver', 'database');

    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    DB::table('sessions')->insert([
        'id' => 'sesi-uji-hapus',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'pest',
        'payload' => 'payload-uji',
        'last_activity' => now()->timestamp,
    ]);

    $user->update(['role' => 'petugas']);

    $this->assertDatabaseMissing('sessions', ['id' => 'sesi-uji-hapus']);
});

it('blocks admin from officer routes', function () {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);

    $this->actingAs($admin)->get('/petugas')->assertForbidden();
    $this->actingAs($admin)->get('/petugas/reservasi')->assertForbidden();
    $this->actingAs($admin)->get('/petugas/laporan')->assertForbidden();
});

it('blocks admin and petugas from user flows', function () {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    $petugas = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);

    $this->actingAs($admin)->get('/dashboard')->assertForbidden();
    $this->actingAs($admin)->get('/reservasi')->assertForbidden();
    $this->actingAs($admin)->get('/laporan')->assertForbidden();
    $this->actingAs($petugas)->get('/dashboard')->assertForbidden();
    $this->actingAs($petugas)->get('/reservasi')->assertForbidden();
    $this->actingAs($petugas)->get('/laporan')->assertForbidden();
});

it('blocks petugas from admin routes', function () {
    $petugas = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);

    $this->actingAs($petugas)->get('/admin')->assertForbidden();
    $this->actingAs($petugas)->get('/admin/pengguna')->assertForbidden();
});

it('blocks pengguna from officer and admin routes', function () {
    $pengguna = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    $this->actingAs($pengguna)->get('/petugas')->assertForbidden();
    $this->actingAs($pengguna)->get('/admin')->assertForbidden();
});

it('redirects guests to login on private routes', function () {
    $this->get('/dashboard')->assertRedirect(route('login'));
    $this->get('/reservasi')->assertRedirect(route('login'));
    $this->get('/petugas')->assertRedirect(route('login'));
    $this->get('/admin')->assertRedirect(route('login'));
});

it('lets an active admin create another admin without self-registration', function () {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);

    $this->actingAs($admin)->get('/admin/admin')->assertOk();
    $this->actingAs($admin)->get('/admin/admin/create')->assertOk();

    $response = $this->actingAs($admin)->post('/admin/admin', [
        'name' => 'Admin Kedua',
        'email' => 'admin-kedua@kampus.test',
        'password' => 'admin12345',
        'password_confirmation' => 'admin12345',
        'identity' => 'NIP-199202022000',
        'phone' => '081100002000',
    ]);

    $response->assertRedirect(route('admin.admin.index'));

    $second = User::where('email', 'admin-kedua@kampus.test')->firstOrFail();

    expect($second->role)->toBe('admin')->and($second->account_status)->toBe('aktif');

    $this->post('/logout');

    $this->post('/login', [
        'email' => 'admin-kedua@kampus.test',
        'password' => 'admin12345',
    ])->assertRedirect(route('admin.dashboard'));
});

it('forbids non-admin from the admin account pages', function () {
    foreach (['pengguna', 'petugas'] as $role) {
        $user = User::factory()->create(['role' => $role, 'account_status' => 'aktif']);

        $this->actingAs($user)->get('/admin/admin')->assertForbidden();
        $this->actingAs($user)->post('/admin/admin')->assertForbidden();
    }
});

it('lets admin restore a rejected account to pending', function () {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    $target = User::factory()->create([
        'role' => 'pengguna',
        'account_status' => 'ditolak',
        'rejected_by' => $admin->id,
        'rejected_at' => now(),
    ]);

    $this->actingAs($admin)
        ->patch("/admin/pengguna/{$target->id}/pulihkan")
        ->assertRedirect(route('admin.pengguna.index'));

    expect($target->fresh()->account_status)->toBe('pending');
});

it('rejects restoring an account that is not rejected', function () {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    $target = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    $this->actingAs($admin)
        ->patch("/admin/pengguna/{$target->id}/pulihkan")
        ->assertRedirect(route('admin.pengguna.index'))
        ->assertSessionHas('error');
});

it('rejects duplicate identity and phone on admin-created accounts', function () {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    User::factory()->create(['identity' => '2110512500', 'phone' => '+628120000500']);

    $this->actingAs($admin)->post('/admin/pengguna', [
        'name' => 'Duplikat Identitas',
        'email' => 'duplikat-id@kampus.test',
        'password' => 'user12345',
        'password_confirmation' => 'user12345',
        'identity' => '2110512500',
        'phone' => '081200002501',
    ])->assertSessionHasErrors('identity');

    $this->actingAs($admin)->post('/admin/pengguna', [
        'name' => 'Duplikat Telepon',
        'email' => 'duplikat-telp@kampus.test',
        'password' => 'user12345',
        'password_confirmation' => 'user12345',
        'identity' => '2110512501',
        'phone' => '08120000500',
    ])->assertSessionHasErrors('phone');
});
