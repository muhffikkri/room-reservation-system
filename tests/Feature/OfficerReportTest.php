<?php

use App\Models\Facility;
use App\Models\Report;
use App\Models\ReportUpdate;
use App\Models\Reservation;
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

it('opens the report queue on the baru status and keeps the menunggu approval tab filterable', function () {
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $activeFacility = Facility::factory()->create(['name' => 'Fasilitas Masih Aktif', 'status' => 'aktif']);
    $closedFacility = Facility::factory()->create(['name' => 'Fasilitas Sudah Tutup', 'status' => 'aktif']);

    Report::factory()->create(['user_id' => $user->id, 'facility_id' => $activeFacility->id, 'status' => 'baru']);
    Report::factory()->create(['user_id' => $user->id, 'facility_id' => $closedFacility->id, 'status' => 'selesai', 'resolution_note' => 'Sudah diperbaiki']);

    $this->actingAs($officer)
        ->get(route('petugas.laporan.index'))
        ->assertOk()
        ->assertSee('Fasilitas Masih Aktif')
        ->assertDontSee('Fasilitas Sudah Tutup');

    $this->actingAs($officer)
        ->get(route('petugas.laporan.index', ['tab' => 'menunggu']))
        ->assertOk()
        ->assertSee('Fasilitas Masih Aktif')
        ->assertDontSee('Fasilitas Sudah Tutup');
});

it('filters the report queue by the selesai tab', function () {
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $activeFacility = Facility::factory()->create(['name' => 'Fasilitas Masih Aktif', 'status' => 'aktif']);
    $doneFacility = Facility::factory()->create(['name' => 'Fasilitas Rampung', 'status' => 'aktif']);
    $rejectedFacility = Facility::factory()->create(['name' => 'Fasilitas Ditolak', 'status' => 'aktif']);

    Report::factory()->create(['user_id' => $user->id, 'facility_id' => $activeFacility->id, 'status' => 'diproses']);
    Report::factory()->create(['user_id' => $user->id, 'facility_id' => $doneFacility->id, 'status' => 'selesai', 'resolution_note' => 'Sudah diperbaiki']);
    Report::factory()->create(['user_id' => $user->id, 'facility_id' => $rejectedFacility->id, 'status' => 'ditolak', 'resolution_note' => 'Bukan fasilitas kampus']);

    $this->actingAs($officer)
        ->get(route('petugas.laporan.index', ['tab' => 'selesai']))
        ->assertOk()
        ->assertSee('Fasilitas Rampung')
        ->assertSee('Fasilitas Ditolak')
        ->assertDontSee('Fasilitas Masih Aktif');
});

it('surfaces unfinished reports first and finished reports behind the selesai filter', function () {
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $activeFacility = Facility::factory()->create(['name' => 'Fasilitas Masih Aktif', 'status' => 'aktif']);
    $closedFacility = Facility::factory()->create(['name' => 'Fasilitas Sudah Tutup', 'status' => 'aktif']);

    Report::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $closedFacility->id,
        'status' => 'selesai',
        'resolution_note' => 'Sudah diperbaiki',
        'created_at' => now()->addDay(),
    ]);
    Report::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $activeFacility->id,
        'status' => 'baru',
        'created_at' => now()->subDay(),
    ]);

    // Antrean terbuka pada laporan "baru"; laporan yang sudah selesai hanya
    // muncul lewat filter statusnya.
    $this->actingAs($officer)
        ->get(route('petugas.laporan.index'))
        ->assertOk()
        ->assertSee('Fasilitas Masih Aktif')
        ->assertDontSee('Fasilitas Sudah Tutup');

    $this->actingAs($officer)
        ->get(route('petugas.laporan.index', ['status' => 'selesai']))
        ->assertOk()
        ->assertDontSee('Fasilitas Masih Aktif')
        ->assertSee('Fasilitas Sudah Tutup');
});

it('shows the submitted at column on the report queue', function () {
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    Report::factory()->create(['user_id' => $user->id, 'facility_id' => $facility->id, 'status' => 'baru']);

    $this->actingAs($officer)
        ->get(route('petugas.laporan.index'))
        ->assertOk()
        ->assertSee('Waktu Diajukan');
});

it('surfaces approved reservations after marking facility for repair (BR-16)', function () {
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    $user = User::factory()->create(['name' => 'Budi Pemohon', 'role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $report = Report::factory()->create(['user_id' => $user->id, 'facility_id' => $facility->id, 'status' => 'diproses']);

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'status' => 'approved',
        'start_time' => now()->tomorrow()->setTime(9, 0),
        'end_time' => now()->tomorrow()->setTime(11, 0),
    ]);

    $response = $this->actingAs($officer)
        ->from(route('petugas.laporan.show', $report))
        ->patch(route('petugas.laporan.fasilitas-status', $report), [
            'action' => 'perbaikan',
        ]);

    $response->assertRedirect(route('petugas.laporan.show', $report));
    $response->assertSessionHas('affectedReservationIds', [$reservation->id]);

    $showResponse = $this->actingAs($officer)->get(route('petugas.laporan.show', $report));
    $showResponse->assertOk()
        ->assertSee('Reservasi yang Perlu Ditinjau')
        ->assertSee('Budi Pemohon')
        ->assertSee(route('petugas.reservasi.show', $reservation))
        ->assertSee(route('petugas.reservasi.index', ['facility_id' => $facility->id, 'status' => 'approved']));
});

it('shows no affected panel when there are no approved reservations on repair', function () {
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $report = Report::factory()->create(['facility_id' => $facility->id, 'status' => 'diproses']);

    $response = $this->actingAs($officer)->patch(route('petugas.laporan.fasilitas-status', $report), [
        'action' => 'perbaikan',
    ]);

    $response->assertRedirect(route('petugas.laporan.show', $report));
    $response->assertSessionHas('affectedReservationIds', []);

    $showResponse = $this->actingAs($officer)->get(route('petugas.laporan.show', $report));
    $showResponse->assertOk()
        ->assertDontSee('Reservasi yang Perlu Ditinjau');
});
