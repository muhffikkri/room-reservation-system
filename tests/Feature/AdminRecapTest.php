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

it('excludes inactive facilities from the average occupancy rate', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);

    $bookable = Facility::factory()->create(['status' => 'aktif']);
    Facility::factory()->create(['status' => 'perbaikan']);

    // 13 jam operasional per hari, jadi 3 jam = 3/13 = 23.08% untuk satu hari.
    Reservation::factory()->approved()->create([
        'user_id' => $admin->id,
        'facility_id' => $bookable->id,
        'start_time' => Carbon::parse('2026-09-01 08:00', config('app.timezone')),
        'end_time' => Carbon::parse('2026-09-01 11:00', config('app.timezone')),
    ]);

    $recap = app(RecapService::class)->getOccupancyRecap(
        Carbon::parse('2026-09-01', config('app.timezone'))->startOfDay(),
        Carbon::parse('2026-09-01', config('app.timezone'))->endOfDay(),
    );

    // Fasilitas yang bisa dipakai: 23.08%. Jika nonaktif ikut dihitung,
    // rata-rata turun jadi 11.54% karena penyebut bertambah tanpa
    // kemungkinan okupansi.
    expect($recap['summary']['average_occupancy_rate'])->toBe(23.08)
        ->and($recap['summary']['total_facilities'])->toBe(2);
});

it('still reports every facility in the per-facility rows', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    $bookable = Facility::factory()->create(['status' => 'aktif', 'name' => 'Ruang Aktif']);
    Facility::factory()->create(['status' => 'perbaikan', 'name' => 'Ruang Perbaikan']);

    $recap = app(RecapService::class)->getOccupancyRecap(
        Carbon::parse('2026-09-01', config('app.timezone'))->startOfDay(),
        Carbon::parse('2026-09-01', config('app.timezone'))->endOfDay(),
    );

    $names = array_column($recap['data'], 'facility_name');

    expect($names)->toContain('Ruang Aktif')
        ->and($names)->toContain('Ruang Perbaikan')
        ->and($recap['data'])->toHaveCount(2);
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

it('escapes database values in the exported HTML', function (): void {
    Facility::factory()->create([
        'name' => '<b>Gedung</b> <script>alert(1)</script>',
        'type' => 'ruang_kelas',
        'location' => 'Lantai 3 "A" & B',
    ]);

    $service = app(RecapService::class);
    $recap = $service->getOccupancyRecap(
        Carbon::parse('2026-09-01', config('app.timezone'))->startOfDay(),
        Carbon::parse('2026-09-01', config('app.timezone'))->endOfDay(),
    );

    $html = $service->exportOccupancyHtml($recap);

    // Nama dan lokasi fasilitas berasal dari database, jadi harus keluar
    // sebagai teks, bukan markup yang lolos ke dokumen.
    expect($html)->not->toContain('<script>alert(1)</script>')
        ->and($html)->not->toContain('<b>Gedung</b>')
        ->and($html)->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')
        ->and($html)->toContain('&amp;');
});

it('neutralises spreadsheet formulas in a CSV export', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    Facility::factory()->create(['name' => '=cmd|\' /C calc\'!A1']);

    $response = $this->actingAs($admin)->get(route('admin.rekap.occupancy.export.csv', [
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-10',
    ]));

    $response->assertOk();

    $csv = $response->streamedContent();

    // Sel yang diawali operator rumus harus dibaca sebagai teks, bukan
    // dieksekusi saat CSV dibuka di Excel/LibreOffice/Sheets. Yang penting
    // adalah sel tidak diawali '='; teks sel tetap ada setelah kutip
    // tunggal, jadi '=cmd|' sendiri masih muncul sebagai substring.
    expect($csv)->not->toContain('"=cmd|')
        ->and($csv)->toContain("\"'=cmd|");
});

it('emits a CSV without the fputcsv deprecation', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    Facility::factory()->create();

    $deprecations = [];
    set_error_handler(function (int $level, string $message) use (&$deprecations): bool {
        $deprecations[] = $message;

        return true;
    }, E_DEPRECATED | E_USER_DEPRECATED);

    try {
        $csv = $this->actingAs($admin)
            ->get(route('admin.rekap.occupancy.export.csv', [
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-10',
            ]))
            ->streamedContent();
    } finally {
        restore_error_handler();
    }

    expect($csv)->toContain('Nama Fasilitas')
        ->and(array_filter($deprecations, fn ($m) => str_contains($m, 'fputcsv')))->toBeEmpty();
});

it('rate limits recap exports per admin', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    Facility::factory()->create();

    $query = ['start_date' => '2026-09-01', 'end_date' => '2026-09-10'];

    // Enam ekspor per menit lolos, ketujuh ditolak.
    foreach (range(1, 6) as $ignored) {
        $this->actingAs($admin)
            ->get(route('admin.rekap.occupancy.export.pdf', $query))
            ->assertOk();
    }

    $this->actingAs($admin)
        ->get(route('admin.rekap.occupancy.export.pdf', $query))
        ->assertStatus(429);
});

it('still serves the recap pages themselves without the export limit', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    Facility::factory()->create();

    // Halaman rekap murah dan harus tetap bisa dibuka berulang; yang dibatasi
    // hanya ekspor.
    foreach (range(1, 8) as $ignored) {
        $this->actingAs($admin)->get(route('admin.rekap.occupancy'))->assertOk();
    }
});

it('names an export after the selected range rather than today', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    Facility::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.rekap.occupancy.export.csv', [
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-10',
        ]))
        ->assertHeader(
            'content-disposition',
            'attachment; filename=rekap-okupansi-2026-09-01-sd-2026-09-10.csv'
        );

    $this->actingAs($admin)
        ->get(route('admin.rekap.damage.export.pdf', [
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-10',
        ]))
        ->assertHeader(
            'content-disposition',
            'attachment; filename=rekap-kerusakan-2026-09-01-sd-2026-09-10.pdf'
        );
});

it('gives the two report types different filenames for the same range', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    Facility::factory()->create();

    $query = ['start_date' => '2026-09-01', 'end_date' => '2026-09-10'];

    $occupancy = $this->actingAs($admin)
        ->get(route('admin.rekap.occupancy.export.csv', $query))
        ->headers->get('content-disposition');

    $damage = $this->actingAs($admin)
        ->get(route('admin.rekap.damage.export.csv', $query))
        ->headers->get('content-disposition');

    // Tanpa segmen jenis keduanya bernama sama dan unduhan kedua menimpa
    // yang pertama.
    expect($occupancy)->not->toBe($damage);
});
