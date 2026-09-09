<?php

use App\Models\Facility;
use App\Models\Report;
use App\Models\ReportUpdate;
use App\Models\User;
use App\Services\ReportService;
use Database\Seeders\ReportSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function makeReportActors(): array
{
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $reporter = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);

    $report = Report::factory()->create([
        'user_id' => $reporter->id,
        'facility_id' => $facility->id,
    ]);

    return [$report, $officer];
}

it('walks baru to diproses to selesai with audit trail and facility restore (kasus 7)', function () {
    [$report, $officer] = makeReportActors();
    $service = app(ReportService::class);

    expect($report->status)->toBe('baru');

    $processing = $service->transition($report, $officer, 'diproses', 'Petugas mulai memeriksa proyektor yang mati.');

    expect($processing->status)->toBe('diproses')
        ->and($processing->handled_by)->toBe($officer->id)
        ->and($processing->handled_at)->not->toBeNull();

    $service->markFacilityForRepair($processing, $officer);
    expect($processing->fresh()->facility->status)->toBe('perbaikan');

    $done = $service->transition($processing, $officer, 'selesai', 'Lampu proyektor diganti dan sudah menyala normal.');

    expect($done->status)->toBe('selesai')
        ->and($done->resolution_note)->toBe('Lampu proyektor diganti dan sudah menyala normal.');

    $service->restoreFacilityToActive($done, $officer);
    expect($done->fresh()->facility->status)->toBe('aktif');

    $trail = ReportUpdate::where('report_id', $report->id)->orderBy('id')->get();
    expect($trail)->toHaveCount(2);
    expect([$trail[0]->old_status, $trail[0]->new_status])->toBe(['baru', 'diproses']);
    expect([$trail[1]->old_status, $trail[1]->new_status])->toBe(['diproses', 'selesai']);
    expect($trail->pluck('user_id')->unique()->all())->toBe([$officer->id]);
});

it('rejects jumping straight from baru to selesai', function () {
    [$report, $officer] = makeReportActors();
    $service = app(ReportService::class);

    expect(fn () => $service->transition($report, $officer, 'selesai', 'Langsung selesai tanpa pernah diproses.'))
        ->toThrow(ValidationException::class);

    expect($report->fresh()->status)->toBe('baru');
    expect(ReportUpdate::where('report_id', $report->id)->count())->toBe(0);
});

it('rejects closing a report without a resolution note', function () {
    [$report, $officer] = makeReportActors();
    $service = app(ReportService::class);

    $processing = $service->transition($report, $officer, 'diproses', 'Petugas mulai memeriksa proyektor yang mati.');

    expect(fn () => $service->transition($processing, $officer, 'selesai'))
        ->toThrow(ValidationException::class);

    expect(fn () => $service->transition($processing, $officer, 'ditolak', 'Singkat'))
        ->toThrow(ValidationException::class);

    expect($processing->fresh()->status)->toBe('diproses');
});

it('rejects facility actions outside their allowed states', function () {
    [$report, $officer] = makeReportActors();
    $service = app(ReportService::class);

    expect(fn () => $service->markFacilityForRepair($report, $officer))
        ->toThrow(ValidationException::class);

    $processing = $service->transition($report, $officer, 'diproses', 'Petugas mulai memeriksa proyektor yang mati.');

    expect(fn () => $service->restoreFacilityToActive($processing, $officer))
        ->toThrow(ValidationException::class);
});

it('seeds the diproses report with an audit row', function () {
    User::factory()->create(['email' => 'budi@student.kampus.test', 'role' => 'pengguna', 'account_status' => 'aktif']);
    User::factory()->create(['email' => 'petugas@kampus.test', 'role' => 'petugas', 'account_status' => 'aktif']);
    Facility::factory()->create(['name' => 'Lab Komputer 1']);
    Facility::factory()->create(['name' => 'Ruang Kelas B-201']);

    (new ReportSeeder)->run();

    $processing = Report::where('status', 'diproses')->firstOrFail();
    $trail = ReportUpdate::where('report_id', $processing->id)->get();

    expect($trail)->toHaveCount(1);
    expect([$trail->first()->old_status, $trail->first()->new_status])->toBe(['baru', 'diproses']);
});
