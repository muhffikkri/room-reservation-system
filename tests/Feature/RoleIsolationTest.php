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
