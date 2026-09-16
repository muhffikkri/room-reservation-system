<?php

use App\Models\Facility;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('allows active authenticated users to view their report list', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $report = Report::factory()->create(['user_id' => $user->id, 'facility_id' => $facility->id]);

    $response = $this->actingAs($user)->get(route('laporan.index'));

    $response->assertStatus(200)
        ->assertSee($facility->name)
        ->assertSee($report->status);
});

it('renders report creation form with active facilities', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['name' => 'Lab Komputer 1', 'status' => 'aktif']);
    Facility::factory()->create(['name' => 'Ruang Rusak', 'status' => 'nonaktif']);

    $response = $this->actingAs($user)->get(route('laporan.create'));

    $response->assertStatus(200)
        ->assertSee('Lab Komputer 1')
        ->assertDontSee('Ruang Rusak');
});

it('stores a report with photo upload successfully', function () {
    Storage::fake('public');

    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $photo = UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg');

    $response = $this->actingAs($user)->post(route('laporan.store'), [
        'facility_id' => $facility->id,
        'category' => 'kerusakan_alat',
        'description' => 'Proyektor mati total dan kabel VGA putus.',
        'photo' => $photo,
    ]);

    $report = Report::first();

    $response->assertRedirect(route('laporan.show', $report));
    expect($report)->not->toBeNull()
        ->and($report->user_id)->toBe($user->id)
        ->and($report->facility_id)->toBe($facility->id)
        ->and($report->category)->toBe('kerusakan_alat')
        ->and($report->status)->toBe('baru');

    Storage::disk('public')->assertExists($report->photo);
});

it('prevents user from viewing reports owned by other users', function () {
    $user1 = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $user2 = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $report = Report::factory()->create(['user_id' => $user1->id, 'facility_id' => $facility->id]);

    $response = $this->actingAs($user2)->get(route('laporan.show', $report));

    $response->assertStatus(403);
});
