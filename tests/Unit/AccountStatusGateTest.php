<?php

use App\Models\User;
use App\Services\AccountStatusGate;

/**
 * Gate hanya membaca role dan account_status, jadi cukup instantiate model
 * tanpa database.
 */
function gateUser(?string $role, string $status): ?User
{
    if ($role === null) {
        return null;
    }

    $user = new User;
    $user->role = $role;
    $user->account_status = $status;

    return $user;
}

it('refuses every role for a null, pending or rejected account', function (): void {
    expect(AccountStatusGate::mayActAs(null, 'admin'))->toBeFalse()
        ->and(AccountStatusGate::mayActAs(gateUser('petugas', 'pending'), 'petugas', 'admin'))->toBeFalse()
        ->and(AccountStatusGate::mayActAs(gateUser('admin', 'ditolak'), 'admin'))->toBeFalse();
});

it('lets an active account act as any one of the roles it is listed with', function (): void {
    $petugas = gateUser('petugas', 'aktif');

    // Satu role, dan dua role di mana akun hanya cocok dengan yang kedua.
    expect(AccountStatusGate::mayActAs($petugas, 'petugas'))->toBeTrue()
        ->and(AccountStatusGate::mayActAs($petugas, 'pengguna', 'petugas'))->toBeTrue();

    // Role yang tidak disebut, dan role yang tidak ada sama sekali.
    expect(AccountStatusGate::mayActAs($petugas, 'admin'))->toBeFalse()
        ->and(AccountStatusGate::mayActAs($petugas, 'pengguna'))->toBeFalse()
        ->and(AccountStatusGate::mayActAs($petugas, 'tidak-ada'))->toBeFalse();
});

it('never treats an empty role list as a pass', function (): void {
    expect(AccountStatusGate::mayActAs(gateUser('admin', 'aktif')))->toBeFalse();
});
