<?php

use App\Models\Facility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
