<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('locks each admin-created account to the route account type', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $accountTypes = [
        'pengguna' => 'admin.pengguna',
        'petugas' => 'admin.petugas',
        'admin' => 'admin.admin',
    ];

    foreach ($accountTypes as $role => $routePrefix) {
        $email = "{$role}@example.test";

        $this->actingAs($admin)
            ->post(route("{$routePrefix}.store"), [
                'name' => "Akun {$role}",
                'email' => $email,
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'identity' => "IDENTITY-{$role}",
                'phone' => '0812000000'.count(User::all()),
                'role' => $role === 'admin' ? 'pengguna' : 'admin',
                'account_status' => 'ditolak',
            ])
            ->assertRedirect(route("{$routePrefix}.index"));

        $account = User::where('email', $email)->firstOrFail();

        expect($account->role)->toBe($role)
            ->and($account->account_status)->toBe('aktif');
    }
});

it('renders the pattern and hint on the admin account creation forms', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    foreach (['admin.pengguna.create', 'admin.petugas.create', 'admin.admin.create'] as $routeName) {
        $this->actingAs($admin)
            ->get(route($routeName))
            ->assertStatus(200)
            ->assertSee('name="phone"', false)
            ->assertSee('type="tel"', false)
            ->assertSee('pattern="[\+0-9][\s\-().0-9]{7,19}"', false)
            ->assertSee('08xx')
            ->assertSee('+62xx');
    }
});
