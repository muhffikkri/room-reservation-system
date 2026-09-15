<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('records rejection and restore actions without deleting the rejection history', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);
    $target = User::factory()->create([
        'role' => 'pengguna',
        'account_status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->patch("/admin/pengguna/{$target->id}/tolak")
        ->assertRedirect();

    $this->actingAs($admin)
        ->patch("/admin/pengguna/{$target->id}/pulihkan")
        ->assertRedirect();

    expect($target->fresh()->account_status)->toBe('pending');

    $this->assertDatabaseHas('account_verification_actions', [
        'target_user_id' => $target->id,
        'actor_id' => $admin->id,
        'action' => 'rejected',
    ]);
    $this->assertDatabaseHas('account_verification_actions', [
        'target_user_id' => $target->id,
        'actor_id' => $admin->id,
        'action' => 'restored',
    ]);
    $this->assertDatabaseCount('account_verification_actions', 2);
});

it('records the verifying admin and action time', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);
    $target = User::factory()->create([
        'role' => 'pengguna',
        'account_status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->patch("/admin/pengguna/{$target->id}/verifikasi")
        ->assertRedirect();

    $this->assertDatabaseHas('account_verification_actions', [
        'target_user_id' => $target->id,
        'actor_id' => $admin->id,
        'action' => 'verified',
    ]);

    expect(
        DB::table('account_verification_actions')
            ->where('target_user_id', $target->id)
            ->value('acted_at')
    )->not->toBeNull();
});

it('does not record an action when the verification target is invalid', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);
    $target = User::factory()->create([
        'role' => 'petugas',
        'account_status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->patch("/admin/pengguna/{$target->id}/verifikasi")
        ->assertNotFound();

    $this->assertDatabaseCount('account_verification_actions', 0);
});
