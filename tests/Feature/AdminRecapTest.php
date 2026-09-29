<?php

use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use App\Models\User;
use App\Services\RecapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('renders the occupancy recap page with current facilities', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create();

    $this->actingAs($admin)->get(route('admin.rekap.occupancy', [
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-10',
    ]))->assertOk()->assertSee($facility->name);
});

it('groups every rejection and cancellation status in the occupancy recap', function (): void {
    $facility = Facility::factory()->create();
    $user = User::factory()->create();

    foreach ([
        'pending',
        'approved',
        'rejected',
        'rejected_by_system',
        'cancelled_by_user',
        'cancelled_by_officer',
        'cancelled_by_system',
    ] as $status) {
        Reservation::factory()->create([
            'facility_id' => $facility->id,
            'user_id' => $user->id,
            'status' => $status,
            'start_time' => '2026-08-10 09:00:00',
            'end_time' => '2026-08-10 10:00:00',
        ]);
    }

    $recap = app(RecapService::class)->getOccupancyRecap(
        Carbon::parse('2026-08-01')->startOfDay(),
        Carbon::parse('2026-08-31')->endOfDay(),
    );
    $facilityRecap = collect($recap['data'])->firstWhere('facility_id', $facility->id);

    expect($facilityRecap)
        ->not->toBeNull()
        ->and($facilityRecap['rejected_count'])->toBe(2)
        ->and($facilityRecap['cancelled_count'])->toBe(3)
        ->and($facilityRecap['total_reservations'])->toBe(7)
        ->and($recap['summary']['total_rejected'])->toBe(2)
        ->and($recap['summary']['total_cancelled'])->toBe(3)
        ->and($recap['summary']['total_reservations'])->toBe(7);
});

it('returns current recap data after reservations and reports change', function (): void {
    $facility = Facility::factory()->create();
    $user = User::factory()->create();
    $start = Carbon::parse('2026-09-01')->startOfDay();
    $end = Carbon::parse('2026-09-10')->endOfDay();
    $recaps = app(RecapService::class);

    expect($recaps->getOccupancyRecap($start, $end)['summary']['total_reservations'])->toBe(0)
        ->and($recaps->getDamageRecap($start, $end)['summary']['total_reports'])->toBe(0);

    Reservation::factory()->create([
        'facility_id' => $facility->id,
        'user_id' => $user->id,
        'start_time' => '2026-09-05 09:00:00',
        'end_time' => '2026-09-05 10:00:00',
    ]);
    Report::factory()->create([
        'facility_id' => $facility->id,
        'user_id' => $user->id,
        'created_at' => '2026-09-05 09:00:00',
    ]);

    expect($recaps->getOccupancyRecap($start, $end)['summary']['total_reservations'])->toBe(1)
        ->and($recaps->getDamageRecap($start, $end)['summary']['total_reports'])->toBe(1);
});

it('exports occupancy csv from array-backed recap data', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create();

    $response = $this->actingAs($admin)->get(route('admin.rekap.occupancy.export.csv', [
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-10',
    ]));

    $response->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())->toContain($facility->name);
});

it('serves the occupancy PDF export as a real PDF document', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    Facility::factory()->create();

    $response = $this->actingAs($admin)->get(route('admin.rekap.occupancy.export.pdf', [
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-10',
    ]));

    $response->assertOk()->assertHeader('Content-Type', 'application/pdf');

    // sebelumnya union return type tidak memuat Response, jadi TypeError
    // tertangkap fallback dan unduhan berisi HTML.
    expect($response->getContent())->toStartWith('%PDF');
});

it('serves the damage PDF export as a real PDF document', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    Facility::factory()->create();

    $response = $this->actingAs($admin)->get(route('admin.rekap.damage.export.pdf', [
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-10',
    ]));

    $response->assertOk()->assertHeader('Content-Type', 'application/pdf');

    expect($response->getContent())->toStartWith('%PDF');
});

it('keeps remote resource loading off while still producing a PDF', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    Facility::factory()->create();

    $response = $this->actingAs($admin)
        ->get(route('admin.rekap.occupancy.export.pdf', [
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-10',
        ]));

    // Config harus tetap menolak akses remote. Override di controller tidak
    // bisa diuji dari sini: dompdf.wrapper di-bind dengan bind(), bukan
    // singleton(), jadi mutasi option tidak terlihat dari container.
    // Yang diuji di sini: config-nya salah, dan PDF tetap berhasil dibuat.
    expect(config('dompdf.options.enable_remote'))->toBeFalse();

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    expect($response->getContent())->toStartWith('%PDF');
});

it('leaves PDF JavaScript and PHP execution disabled', function (): void {
    // Rekap tidak pernah menyertakan <script> atau PHP, jadi tidak ada
    // kebutuhan yang terbukti. Menyalakannya hanya menambah permukaan
    // eksekusi di pembaca PDF untuk dokumen yang dibangun dari nilai database.
    expect(config('dompdf.options.enable_javascript'))->toBeFalse()
        ->and(config('dompdf.options.enable_php'))->toBeFalse();
});

it('rejects a recap date range that is only half given', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);

    // end_date saja menghasilkan rentang terbalik: start 2026-09-29 s/d
    // 2026-09-10, dengan max_possible_hours negatif.
    $this->actingAs($admin)
        ->get(route('admin.rekap.occupancy', ['end_date' => '2026-09-10']))
        ->assertSessionHasErrors('start_date');

    $this->actingAs($admin)
        ->get(route('admin.rekap.occupancy', ['start_date' => '2026-09-10']))
        ->assertSessionHasErrors('end_date');
});

it('still accepts a complete range and no range at all', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    Facility::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.rekap.occupancy', ['start_date' => '2026-09-01', 'end_date' => '2026-09-10']))
        ->assertOk()
        ->assertSessionHasNoErrors();

    // Tanpa filter, rekap memakai rentang bawaan.
    $this->actingAs($admin)
        ->get(route('admin.rekap.occupancy'))
        ->assertOk()
        ->assertSessionHasNoErrors();
});
