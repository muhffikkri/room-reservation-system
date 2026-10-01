<?php

use App\Models\Facility;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('maps every stored category value to its display label', function () {
    foreach (Report::CATEGORIES as $value => $label) {
        expect((new Report(['category' => $value]))->categoryLabel())->toBe($label);
    }

    expect(Report::CATEGORIES['listrik'])->toBe('Kelistrikan / Lampu / AC');
});

it('shows the submitted category label on the user list and detail pages', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    $response = $this->actingAs($user)->post(route('laporan.store'), [
        'facility_id' => $facility->id,
        'category' => 'listrik',
        'description' => 'Lampu ruangan mati total dan AC tidak dingin sama sekali.',
    ]);

    $report = Report::first();

    expect($report)->not->toBeNull()
        ->and($report->category)->toBe('listrik');

    $response->assertRedirect(route('laporan.show', $report));

    $this->actingAs($user)->get(route('laporan.show', $report))
        ->assertStatus(200)
        ->assertSee('Kelistrikan / Lampu / AC', false);

    $this->actingAs($user)->get(route('laporan.index'))
        ->assertStatus(200)
        ->assertSee('Kelistrikan / Lampu / AC', false);
});

it('shows the submitted category label on the officer queue and detail pages', function () {
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $report = Report::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'category' => 'listrik',
    ]);

    $this->actingAs($officer)->get(route('petugas.laporan.index'))
        ->assertStatus(200)
        ->assertSee('Kelistrikan / Lampu / AC', false);

    $this->actingAs($officer)->get(route('petugas.laporan.show', $report))
        ->assertStatus(200)
        ->assertSee('Kelistrikan / Lampu / AC', false);
});
