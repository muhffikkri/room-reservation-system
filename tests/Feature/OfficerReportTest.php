<?php

use App\Models\Facility;
use App\Models\Report;
use App\Models\ReportUpdate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows petugas to view report queue and filter by status', function () {
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility1 = Facility::factory()->create(['name' => 'Fasilitas Baru', 'status' => 'aktif']);
    $facility2 = Facility::factory()->create(['name' => 'Fasilitas Selesai', 'status' => 'aktif']);

    $reportBaru = Report::factory()->create(['user_id' => $user->id, 'facility_id' => $facility1->id, 'status' => 'baru']);
    $reportSelesai = Report::factory()->create(['user_id' => $user->id, 'facility_id' => $facility2->id, 'status' => 'selesai', 'resolution_note' => 'Sudah diperbaiki dengan baik']);

    $response = $this->actingAs($officer)->get(route('petugas.laporan.index', ['status' => 'baru']));

    $response->assertStatus(200)
        ->assertSee('Fasilitas Baru')
        ->assertDontSee('Fasilitas Selesai');
});

it('allows officer to transition report status from baru to diproses', function () {
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $report = Report::factory()->create(['user_id' => $user->id, 'facility_id' => $facility->id, 'status' => 'baru']);

    $response = $this->actingAs($officer)->patch(route('petugas.laporan.status', $report), [
        'status' => 'diproses',
        'resolution_note' => 'Sedang dalam penanganan petugas teknis.',
    ]);

    $response->assertRedirect(route('petugas.laporan.show', $report));

    expect($report->fresh()->status)->toBe('diproses')
        ->and(ReportUpdate::where('report_id', $report->id)->count())->toBe(1);
});

it('allows officer to mark facility for repair during processing and restore to active when done', function () {
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $report = Report::factory()->create(['user_id' => $user->id, 'facility_id' => $facility->id, 'status' => 'baru']);

    // 1. Transition to diproses
    $this->actingAs($officer)->patch(route('petugas.laporan.status', $report), [
        'status' => 'diproses',
    ]);

    // 2. Mark facility as perbaikan
    $this->actingAs($officer)->patch(route('petugas.laporan.fasilitas-status', $report), [
        'action' => 'perbaikan',
    ]);

    expect($facility->fresh()->status)->toBe('perbaikan');

    // 3. Transition to selesai with resolution note
    $this->actingAs($officer)->patch(route('petugas.laporan.status', $report), [
        'status' => 'selesai',
        'resolution_note' => 'AC sudah diperbaiki dan dingin kembali.',
    ]);

    // 4. Restore facility to aktif
    $this->actingAs($officer)->patch(route('petugas.laporan.fasilitas-status', $report), [
        'action' => 'aktif',
    ]);

    expect($facility->fresh()->status)->toBe('aktif');
});

it('prevents regular pengguna from accessing officer report queue', function () {
    $pengguna = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    $response = $this->actingAs($pengguna)->get(route('petugas.laporan.index'));

    $response->assertStatus(403);
});
