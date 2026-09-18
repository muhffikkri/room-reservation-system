<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow(Carbon::parse('2026-09-09 10:00:00'));
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('menampilkan landing page publik dengan fasilitas dan grid 26 slot', function (): void {
    Facility::factory()->create([
        'name' => 'Aula Terpadu',
        'type' => 'aula',
        'location' => 'Gedung A',
        'capacity' => 300,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Aula Terpadu')
        ->assertSee('Fasilitas Kampus Unggulan')
        ->assertSee('07.00 - 07.30')
        ->assertSee('Total 26 Slot');
});

it('menandai Beranda sebagai navigasi aktif pada posisi awal', function (): void {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('data-landing-nav="top"')
        ->toContain('aria-current="page">Beranda</a>')
        ->not->toContain('aria-current="page">Jadwal</a>');
});

it('menandai slot lewat dan slot terpakai pada grid', function (): void {
    $facility = Facility::factory()->create(['name' => 'Lab Komputer 1']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    Reservation::factory()->approved()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'start_time' => '2026-09-09 11:00:00',
        'end_time' => '2026-09-09 12:00:00',
        'purpose' => 'Data uji rahasia internal',
    ]);

    $html = $this->get('/')->assertOk()->getContent();

    // With 1-hour lead time: now=10:00, past slots are <=11:00 (9 slots), but 11:00-12:00 is booked (2 slots)
    // Past: 07:00-10:30 = 8 slots | Booked: 11:00-11:30 = 2 slots | Available: 12:00-19:30 = 16 slots
    expect(substr_count($html, '>Terpakai</span>'))->toBe(2);
    expect(substr_count($html, '>Waktu Lewat</span>'))->toBe(8);
    expect(substr_count($html, '>Tersedia</span>'))->toBe(16);
});

it('tidak membocorkan identitas pemohon dan tujuan ke publik (BR-13)', function (): void {
    $facility = Facility::factory()->create();
    $user = User::factory()->create(['name' => 'Budi Raharjo', 'role' => 'pengguna', 'account_status' => 'aktif']);
    Reservation::factory()->approved()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'start_time' => '2026-09-10 08:00:00',
        'end_time' => '2026-09-10 09:00:00',
        'purpose' => 'Kegiatan organisasi mahasiswa rahasia',
    ]);

    $this->get('/')
        ->assertOk()
        ->assertDontSee('Budi Raharjo')
        ->assertDontSee('Kegiatan organisasi mahasiswa rahasia');
});

it('memfilter fasilitas berdasarkan tipe, lokasi, dan kapasitas', function (): void {
    Facility::factory()->create(['name' => 'Aula Besar', 'type' => 'aula', 'location' => 'Gedung A', 'capacity' => 200]);
    Facility::factory()->create(['name' => 'Lab Komputer Kecil', 'type' => 'laboratorium', 'location' => 'Gedung C', 'capacity' => 30]);

    $this->get('/?type=aula&location=Gedung%20A&capacity=gt_100')
        ->assertOk()
        ->assertSee('Aula Besar')
        ->assertDontSee('Lab Komputer Kecil');
});

it('menampilkan pesan ramah ketika tidak ada hasil pencarian', function (): void {
    Facility::factory()->create(['name' => 'Aula Terpadu']);

    $this->get('/?q=tidak-ada-fasilitas')
        ->assertOk()
        ->assertSee('Fasilitas tidak ditemukan')
        ->assertDontSee('Aula Terpadu');
});
