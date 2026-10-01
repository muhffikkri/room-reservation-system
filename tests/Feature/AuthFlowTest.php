<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

it('rejects array-shaped account fields during registration', function () {
    $response = $this->post('/register', [
        'name' => 'Malformed User',
        'email' => ['malformed@student.kampus.test'],
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'identity' => ['2110512199'],
        'phone' => ['081200000199'],
    ]);

    $response->assertSessionHasErrors(['email', 'identity', 'phone']);
    $this->assertDatabaseCount('users', 0);
});

it('rejects an array-shaped email during login', function () {
    $response = $this->post('/login', [
        'email' => ['user@student.kampus.test'],
        'password' => 'rahasia123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
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

it('shares the account throttle bucket across accent variants of the same email', function () {
    // cleanRateLimiterKey() milik Laravel merapikan aksen untuk kunci
    // email+IP, jadi bucket lima percobaan tidak bocor. Yang bocor adalah
    // bucket per-akun: kuncinya di-hash dari email mentah SEBELUM
    // perapian, jadi ejaan lain mendapat bucket sendiri. Bucket itu baru
    // terlihat saat IP diputar, sebab limiter email+IP biasanya lebih dulu
    // menahan.
    $accented = "b\u{00FA}d\u{00ED}@student.kampus.test";

    User::factory()->create([
        'email' => 'budi@student.kampus.test',
        'password' => Hash::make('rahasia-kampus-123'),
        'role' => 'pengguna',
        'account_status' => 'aktif',
    ]);

    // 10 percobaan, dua per IP, sehingga caps email+IP tidak menyentuh
    // bucket per-akun.
    foreach (['10.0.0.1', '10.0.0.2', '10.0.0.3', '10.0.0.4', '10.0.0.5'] as $ip) {
        foreach (range(1, 2) as $attempt) {
            $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->post('/login', [
                    'email' => 'budi@student.kampus.test',
                    'password' => 'password-salah',
                ]);
        }
    }

    $response = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.6'])
        ->post('/login', [
            'email' => $accented,
            'password' => 'password-salah',
        ]);

    $response->assertSessionHasErrors('email');
    expect(collect(session('errors')->get('email'))->implode(' '))
        ->toContain('Terlalu banyak percobaan login');
    $this->assertGuest();
});

it('sends an already authenticated account to its own dashboard from the login page', function (string $role, string $homeRoute) {
    $user = User::factory()->create(['role' => $role, 'account_status' => 'aktif']);

    $this->actingAs($user)
        ->get(route('login'))
        ->assertRedirect(route($homeRoute));

    // Halaman tujuan harus benar-benar bisa dibuka, bukan 403.
    $this->actingAs($user)->get(route($homeRoute))->assertOk();
})->with([
    ['pengguna', 'dashboard'],
    ['petugas', 'petugas.dashboard'],
    ['admin', 'admin.dashboard'],
]);

it('rejects a registration whose phone is not a number', function () {
    $response = $this->post('/register', [
        'name' => 'Budi Ngawur',
        'email' => 'budi-ngawur@student.kampus.test',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'identity' => '2110512199',
        'phone' => 'not-a-phone',
    ]);

    $response->assertSessionHasErrors('phone');
    expect(User::where('email', 'budi-ngawur@student.kampus.test')->exists())->toBeFalse();
});

it('accepts the documented Indonesian phone formats at registration', function (string $submitted, string $stored) {
    $this->post('/register', [
        'name' => 'Budi Sah',
        'email' => 'budi-sah@student.kampus.test',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'identity' => '2110512188',
        'phone' => $submitted,
    ])->assertSessionHasNoErrors();

    expect(User::where('email', 'budi-sah@student.kampus.test')->value('phone'))->toBe($stored);
})->with([
    ['081234567890', '+6281234567890'],
    ['62 812 3456 7890', '+6281234567890'],
    ['+6281234567890', '+6281234567890'],
    ['0812 3456 789', '+628123456789'],
]);

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

it('renders the pattern and hint on the registration phone input', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200)
        ->assertSee('id="phone"', false)
        ->assertSee('type="tel"', false)
        ->assertSee('pattern="[\+0-9][\s\-\(\).0-9]{7,19}"', false)
        ->assertSee('08xx')
        ->assertSee('+62xx');
});
