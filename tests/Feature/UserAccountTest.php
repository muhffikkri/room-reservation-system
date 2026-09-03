<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('forbids guests from the user account pages', function () {
    $this->get('/admin/pengguna')->assertRedirect(route('login'));
    $this->get('/admin/pengguna/create')->assertRedirect(route('login'));
    $this->post('/admin/pengguna')->assertRedirect(route('login'));
});

it('forbids pengguna and petugas from the user account pages', function () {
    foreach (['pengguna', 'petugas'] as $role) {
        $user = User::factory()->create([
            'role' => $role,
            'account_status' => 'aktif',
        ]);

        $this->actingAs($user)->get('/admin/pengguna')->assertForbidden();
        $this->actingAs($user)->get('/admin/pengguna/create')->assertForbidden();
        $this->actingAs($user)->post('/admin/pengguna')->assertForbidden();
    }
});

it('allows admin to open the user index and create form', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $this->actingAs($admin)->get('/admin/pengguna')->assertOk();
    $this->actingAs($admin)->get('/admin/pengguna/create')->assertOk();
});

it('creates an active pengguna account by admin', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $response = $this->actingAs($admin)->post('/admin/pengguna', [
        'name' => 'Mahasiswa Baru',
        'email' => 'baru@student.kampus.test',
        'password' => 'user12345',
        'password_confirmation' => 'user12345',
        'identity' => '2110512100',
    ]);

    $response->assertRedirect(route('admin.pengguna.index'));

    $user = User::where('email', 'baru@student.kampus.test')->firstOrFail();

    expect($user->role)->toBe('pengguna')
        ->and($user->account_status)->toBe('aktif')
        ->and(Hash::check('user12345', $user->password))->toBeTrue();
});

it('lets the new pengguna log in immediately without verification', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $this->actingAs($admin)->post('/admin/pengguna', [
        'name' => 'Mahasiswa Baru',
        'email' => 'baru@student.kampus.test',
        'password' => 'user12345',
        'password_confirmation' => 'user12345',
    ])->assertRedirect();

    $this->post('/login', [
        'email' => 'baru@student.kampus.test',
        'password' => 'user12345',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();
});

it('ignores role and status smuggled through the form', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $this->actingAs($admin)->post('/admin/pengguna', [
        'name' => 'Penyusup',
        'email' => 'sneaky@kampus.test',
        'password' => 'user12345',
        'password_confirmation' => 'user12345',
        'role' => 'admin',
        'account_status' => 'ditolak',
    ])->assertRedirect();

    $user = User::where('email', 'sneaky@kampus.test')->firstOrFail();

    expect($user->role)->toBe('pengguna')
        ->and($user->account_status)->toBe('aktif');
});

it('rejects duplicate email and short password', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    User::factory()->create(['email' => 'taken@kampus.test']);

    $this->actingAs($admin)->post('/admin/pengguna', [
        'name' => 'Duplikat',
        'email' => 'taken@kampus.test',
        'password' => 'user12345',
        'password_confirmation' => 'user12345',
    ])->assertSessionHasErrors('email');

    $this->actingAs($admin)->post('/admin/pengguna', [
        'name' => 'Pendek',
        'email' => 'short@kampus.test',
        'password' => 'abc',
        'password_confirmation' => 'abc',
    ])->assertSessionHasErrors('password');

    expect(User::where('email', 'short@kampus.test')->exists())->toBeFalse();
});
