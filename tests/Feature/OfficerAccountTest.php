<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('forbids guests from the officer pages', function () {
    $this->get('/admin/petugas')->assertRedirect(route('login'));
    $this->get('/admin/petugas/create')->assertRedirect(route('login'));
    $this->post('/admin/petugas')->assertRedirect(route('login'));
});

it('forbids pengguna and petugas from the officer pages', function () {
    foreach (['pengguna', 'petugas'] as $role) {
        $user = User::factory()->create([
            'role' => $role,
            'account_status' => 'aktif',
        ]);

        $this->actingAs($user)->get('/admin/petugas')->assertForbidden();
        $this->actingAs($user)->get('/admin/petugas/create')->assertForbidden();
        $this->actingAs($user)->post('/admin/petugas')->assertForbidden();
    }
});

it('allows admin to open the officer index and create form', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $this->actingAs($admin)->get('/admin/petugas')->assertOk();
    $this->actingAs($admin)->get('/admin/petugas/create')->assertOk();
});

it('creates an active officer account by admin', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $response = $this->actingAs($admin)->post('/admin/petugas', [
        'name' => 'Petugas Baru',
        'email' => 'baru@petugas.kampus.test',
        'password' => 'petugas123',
        'password_confirmation' => 'petugas123',
        'identity' => '198001012010',
    ]);

    $response->assertRedirect(route('admin.petugas.index'));

    $officer = User::where('email', 'baru@petugas.kampus.test')->firstOrFail();

    expect($officer->role)->toBe('petugas')
        ->and($officer->account_status)->toBe('aktif')
        ->and(Hash::check('petugas123', $officer->password))->toBeTrue();
});

it('lets the new officer log in immediately without verification', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $this->actingAs($admin)->post('/admin/petugas', [
        'name' => 'Petugas Baru',
        'email' => 'baru@petugas.kampus.test',
        'password' => 'petugas123',
        'password_confirmation' => 'petugas123',
    ])->assertRedirect();

    $this->post('/login', [
        'email' => 'baru@petugas.kampus.test',
        'password' => 'petugas123',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();
});

it('ignores role and status smuggled through the form', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $this->actingAs($admin)->post('/admin/petugas', [
        'name' => 'Penyusup',
        'email' => 'sneaky@kampus.test',
        'password' => 'petugas123',
        'password_confirmation' => 'petugas123',
        'role' => 'admin',
        'account_status' => 'ditolak',
    ])->assertRedirect();

    $user = User::where('email', 'sneaky@kampus.test')->firstOrFail();

    expect($user->role)->toBe('petugas')
        ->and($user->account_status)->toBe('aktif');
});

it('rejects duplicate email and short password', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    User::factory()->create(['email' => 'taken@kampus.test']);

    $this->actingAs($admin)->post('/admin/petugas', [
        'name' => 'Duplikat',
        'email' => 'taken@kampus.test',
        'password' => 'petugas123',
        'password_confirmation' => 'petugas123',
    ])->assertSessionHasErrors('email');

    $this->actingAs($admin)->post('/admin/petugas', [
        'name' => 'Pendek',
        'email' => 'short@kampus.test',
        'password' => 'abc',
        'password_confirmation' => 'abc',
    ])->assertSessionHasErrors('password');

    expect(User::where('email', 'short@kampus.test')->exists())->toBeFalse();
});
