<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

it('creates a pending pengguna account and ignores role input', function () {
    $response = $this->post('/register', [
        'name' => 'Budi Baru',
        'email' => 'budi-baru@student.kampus.test',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'identity' => '2110512100',
        'phone' => '081200000100',
        'role' => 'petugas',
        'account_status' => 'aktif',
    ]);

    $response->assertRedirect(route('login'));

    $this->assertDatabaseHas('users', [
        'email' => 'budi-baru@student.kampus.test',
        'role' => 'pengguna',
        'account_status' => 'pending',
        'identity' => '2110512100',
        'phone' => '+6281200000100',
    ]);
});

it('allows active pengguna to access dashboard', function () {
    $user = User::factory()->create([
        'account_status' => 'aktif',
        'role' => 'pengguna',
    ]);

    $this->actingAs($user)->get('/dashboard')->assertOk();
});

it('blocks pending account from dashboard via active middleware', function () {
    $user = User::factory()->create([
        'account_status' => 'pending',
        'role' => 'pengguna',
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'Akun Anda menunggu verifikasi admin.');
    $this->assertGuest();
});

it('blocks rejected account from dashboard via active middleware', function () {
    $user = User::factory()->create([
        'account_status' => 'ditolak',
        'role' => 'pengguna',
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'Akun Anda ditolak admin dan tidak dapat digunakan.');
    $this->assertGuest();
});

it('rejects login for a pending account with a verification message', function () {
    User::factory()->create([
        'email' => 'pending-flow@student.kampus.test',
        'password' => 'rahasia123',
        'account_status' => 'pending',
    ]);

    $response = $this->post('/login', [
        'email' => 'pending-flow@student.kampus.test',
        'password' => 'rahasia123',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Akun Anda menunggu verifikasi admin.');
    $this->assertGuest();
});

it('rejects login for a rejected account', function () {
    User::factory()->create([
        'email' => 'rejected-flow@student.kampus.test',
        'password' => 'rahasia123',
        'account_status' => 'ditolak',
    ]);

    $response = $this->post('/login', [
        'email' => 'rejected-flow@student.kampus.test',
        'password' => 'rahasia123',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Akun Anda ditolak admin dan tidak dapat digunakan.');
    $this->assertGuest();
});

it('allows login after admin verification', function () {
    User::factory()->create([
        'email' => 'admin-verify@kampus.test',
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $user = User::factory()->create([
        'email' => 'verified-flow@student.kampus.test',
        'password' => 'rahasia123',
        'account_status' => 'pending',
    ]);

    $this->actingAs(User::where('email', 'admin-verify@kampus.test')->first())
        ->patch("/admin/pengguna/{$user->id}/verifikasi")
        ->assertRedirect();

    expect($user->fresh()->account_status)->toBe('aktif');

    // Keluar sebagai admin agar middleware guest tidak menghalangi proses login.
    $this->post('/logout');

    $response = $this->post('/login', [
        'email' => 'verified-flow@student.kampus.test',
        'password' => 'rahasia123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user->fresh());
});

it('throttles repeated failed login attempts', function () {
    $throttleKey = 'throttled-flow@student.kampus.test|127.0.0.1';

    foreach (range(1, 5) as $i) {
        $this->post('/login', [
            'email' => 'throttled-flow@student.kampus.test',
            'password' => 'password-salah',
        ]);
    }

    expect(RateLimiter::tooManyAttempts($throttleKey, 5))->toBeTrue();

    $response = $this->post('/login', [
        'email' => 'throttled-flow@student.kampus.test',
        'password' => 'password-salah',
    ]);

    $response->assertSessionHasErrors('email');
    expect(collect(session('errors')->get('email'))->implode(' '))
        ->toContain('Terlalu banyak percobaan login');
    $this->assertGuest();
});

it('logs out an authenticated user', function () {
    $user = User::factory()->create([
        'account_status' => 'aktif',
    ]);

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

it('clears remember token when login is denied for a pending account', function () {
    User::factory()->create([
        'email' => 'pending-remember@student.kampus.test',
        'password' => 'rahasia123',
        'account_status' => 'pending',
        'remember_token' => 'token-lama',
    ]);

    $this->post('/login', [
        'email' => 'pending-remember@student.kampus.test',
        'password' => 'rahasia123',
        'remember' => true,
    ]);

    $this->assertGuest();
    expect(User::where('email', 'pending-remember@student.kampus.test')->first()->remember_token)->toBeNull();
});

it('throttles repeated register attempts', function () {
    foreach (range(1, 11) as $i) {
        $response = $this->post('/register', [
            'name' => 'Spam',
            'email' => "spam-throttle-{$i}@student.kampus.test",
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'identity' => "2199000{$i}",
            'phone' => "0812999000{$i}",
        ]);

        if ($i <= 10) {
            $response->assertRedirect(route('login'));
        }
    }

    $response->assertStatus(429);
});

it('keeps the post-logout back button off the dashboard', function () {
    $user = User::factory()->create([
        'account_status' => 'aktif',
        'role' => 'pengguna',
    ]);

    $this->actingAs($user)->get(route('dashboard'))->assertOk();

    $this->post(route('logout'))->assertRedirect(route('login'));

    $this->assertGuest();

    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('stays error-free when logout is submitted repeatedly', function () {
    $user = User::factory()->create([
        'account_status' => 'aktif',
        'role' => 'pengguna',
    ]);

    $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));

    $this->post(route('logout'))->assertRedirect(route('login'));

    $this->assertGuest();
});
