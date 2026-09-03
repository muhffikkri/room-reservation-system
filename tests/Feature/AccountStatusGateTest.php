<?php

use App\Models\User;
use App\Services\AccountStatusGate;

it('lets active accounts pass the gate', function () {
    $user = User::factory()->make(['account_status' => 'aktif']);

    expect(AccountStatusGate::denialMessage($user))->toBeNull();
});

it('denies pending accounts with the verification message', function () {
    $user = User::factory()->make(['account_status' => 'pending']);

    expect(AccountStatusGate::denialMessage($user))->toBe('Akun Anda menunggu verifikasi admin.');
});

it('denies rejected accounts with the rejection message', function () {
    $user = User::factory()->make(['account_status' => 'ditolak']);

    expect(AccountStatusGate::denialMessage($user))->toBe('Akun Anda ditolak admin dan tidak dapat digunakan.');
});
