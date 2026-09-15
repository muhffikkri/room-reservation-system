<?php

use App\Models\Facility;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows report owner, petugas, and admin to view but forbids other pengguna', function () {
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $owner = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $other = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $petugas = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);

    $report = Report::factory()->create([
        'user_id' => $owner->id,
        'facility_id' => $facility->id,
    ]);

    $this->actingAs($owner)->get("/laporan/{$report->id}")->assertOk();
    $this->actingAs($petugas)->get("/petugas/laporan/{$report->id}")->assertOk();
    $this->actingAs($admin)->get("/petugas/laporan/{$report->id}")->assertOk();
    $this->actingAs($other)->get("/laporan/{$report->id}")->assertForbidden();
});
