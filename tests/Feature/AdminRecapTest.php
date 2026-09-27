<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use App\Services\RecapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

it('renders the occupancy recap page and caches plain arrays', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create();

    $this->actingAs($admin)->get(route('admin.rekap.occupancy', [
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-10',
    ]))->assertOk()->assertSee($facility->name);

    $cached = Cache::get('recap:occupancy:20260901:20260910');

    expect(is_array($cached))->toBeTrue()
        ->and(is_array($cached['data'] ?? null))->toBeTrue()
        ->and(is_array($cached['summary'] ?? null))->toBeTrue()
        ->and(is_array($cached['date_range'] ?? null))->toBeTrue();

    foreach ($cached['data'] as $row) {
        expect(is_array($row))->toBeTrue();
    }
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

it('heals a corrupted occupancy recap cache instead of failing on count()', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    Facility::factory()->create();

    $key = 'recap:occupancy:20260901:20260910';
    Cache::put($key, [
        'data' => unserialize('O:8:"NotAReal":0:{}'),
        'summary' => [],
        'date_range' => ['start' => '2026-09-01', 'end' => '2026-09-10'],
    ], 300);

    $this->actingAs($admin)->get(route('admin.rekap.occupancy', [
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-10',
    ]))->assertOk();

    $healed = Cache::get($key);

    expect(is_array($healed['data']))->toBeTrue()
        ->and($healed['data'])->not->toBeEmpty();
});

it('heals a corrupted damage recap cache instead of failing on count()', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    Facility::factory()->create();

    $key = 'recap:damage:20260901:20260910';
    Cache::put($key, unserialize('O:8:"NotAReal":0:{}'), 300);

    $this->actingAs($admin)->get(route('admin.rekap.damage', [
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-10',
    ]))->assertOk();

    $healed = Cache::get($key);

    expect(is_array($healed['data']))->toBeTrue()
        ->and(is_array($healed['summary']['by_category'] ?? null))->toBeTrue();
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
