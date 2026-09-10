<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('displays public facility schedule page with 26 operational slots', function () {
    $facility = Facility::factory()->create([
        'name' => 'Aula Serbaguna Utama',
        'status' => 'aktif',
    ]);

    $tomorrow = now()->addDay()->toDateString();
    $response = $this->get("/fasilitas/{$facility->id}/jadwal?date={$tomorrow}");

    $response->assertOk()
        ->assertSee('Jadwal Ketersediaan Slot')
        ->assertSee('Aula Serbaguna Utama')
        ->assertSee('07:00')
        ->assertSee('19:30');
});

it('marks slots as booked when overlapping with approved reservations using scopeOverlap (BR-6)', function () {
    $user = User::factory()->create();
    $facility = Facility::factory()->create(['status' => 'aktif']);

    $date = now()->addDay()->startOfDay();

    // Approved reservation from 08:00 to 09:00 (blocks 08:00 and 08:30)
    Reservation::create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'purpose' => 'Seminar Teknologi',
        'start_time' => $date->copy()->setTime(8, 0),
        'end_time' => $date->copy()->setTime(9, 0),
        'status' => 'approved',
    ]);

    // Pending reservation from 10:00 to 11:00 (must NOT block slots - BR-6)
    Reservation::create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'purpose' => 'Rapat Organisasi Antrian',
        'start_time' => $date->copy()->setTime(10, 0),
        'end_time' => $date->copy()->setTime(11, 0),
        'status' => 'pending',
    ]);

    $response = $this->get("/fasilitas/{$facility->id}/jadwal?date={$date->toDateString()}");

    $response->assertOk();

    // The view should show 'booked' for 08:00 & 08:30, but not for 10:00
    $viewSlots = $response->viewData('slots');
    expect($viewSlots)->toBeArray()->toHaveCount(26);

    $slot0800 = collect($viewSlots)->firstWhere('start', '08:00');
    $slot0830 = collect($viewSlots)->firstWhere('start', '08:30');
    $slot1000 = collect($viewSlots)->firstWhere('start', '10:00');

    expect($slot0800['state'])->toBe('booked')
        ->and($slot0830['state'])->toBe('booked')
        ->and($slot1000['state'])->toBe('available');
});

it('marks all slots as inactive when facility status is not active (BR-12)', function () {
    $facility = Facility::factory()->create([
        'name' => 'Lab Fisika Rusak',
        'status' => 'perbaikan',
    ]);

    $tomorrow = now()->addDay()->toDateString();
    $response = $this->get("/fasilitas/{$facility->id}/jadwal?date={$tomorrow}");

    $response->assertOk()
        ->assertSee('Fasilitas Sedang Dalam Perbaikan');

    $viewSlots = $response->viewData('slots');
    foreach ($viewSlots as $slot) {
        expect($slot['state'])->toBe('inactive');
    }
});

it('protects applicant identity and purpose on the public schedule view (BR-13)', function () {
    $user = User::factory()->create([
        'name' => 'Ahmad Pemohon Sangat Rahasia',
        'email' => 'ahmad.rahasia@kampus.test',
    ]);

    $facility = Facility::factory()->create(['status' => 'aktif']);
    $date = now()->addDay()->startOfDay();

    Reservation::create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'purpose' => 'Tujuan Sangat Rahasia Organisasi',
        'start_time' => $date->copy()->setTime(9, 0),
        'end_time' => $date->copy()->setTime(10, 0),
        'status' => 'approved',
    ]);

    $response = $this->get("/fasilitas/{$facility->id}/jadwal?date={$date->toDateString()}");

    $response->assertOk()
        ->assertDontSee('Ahmad Pemohon Sangat Rahasia')
        ->assertDontSee('ahmad.rahasia@kampus.test')
        ->assertDontSee('Tujuan Sangat Rahasia Organisasi');
});
