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
