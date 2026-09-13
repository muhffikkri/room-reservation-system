<?php

use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan ringkasan aktivitas pengguna dan aktivitas terbaru miliknya', function (): void {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $otherUser = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['name' => 'Aula Utama']);

    Reservation::factory()->create(['user_id' => $user->id, 'facility_id' => $facility->id, 'status' => 'pending']);
    Reservation::factory()->approved()->create(['user_id' => $user->id, 'facility_id' => $facility->id]);
    Reservation::factory()->approved()->create([
        'user_id' => $otherUser->id,
        'facility_id' => $facility->id,
        'purpose' => 'Reservasi pengguna lain yang rahasia',
    ]);
    Report::factory()->create(['user_id' => $user->id, 'facility_id' => $facility->id, 'status' => 'baru']);
    Report::factory()->create(['user_id' => $user->id, 'facility_id' => $facility->id, 'status' => 'diproses']);
    Report::factory()->create([
        'user_id' => $otherUser->id,
        'facility_id' => $facility->id,
        'description' => 'Laporan pengguna lain yang rahasia',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Reservasi menunggu')
        ->assertSee('Reservasi disetujui')
        ->assertSee('Laporan baru')
        ->assertSee('Laporan diproses')
        ->assertSee('Aula Utama')
        ->assertDontSee('Reservasi pengguna lain yang rahasia')
        ->assertDontSee('Laporan pengguna lain yang rahasia');
});

it('mengharuskan pengguna aktif untuk membuka dashboard', function (): void {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'pending']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'));
});
