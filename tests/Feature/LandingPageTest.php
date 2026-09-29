<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow(Carbon::parse('2026-09-09 10:00:00'));
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('menampilkan landing page publik dengan kartu fasilitas dan gerbang ke grid jadwal', function (): void {
    $facility = Facility::factory()->create([
        'name' => 'Aula Terpadu',
        'type' => 'aula',
        'location' => 'Gedung A',
        'capacity' => 300,
    ]);

    $landing = $this->get('/')
        ->assertOk()
        ->assertSee('Aula Terpadu')
        ->assertSee('Fasilitas Kampus Unggulan')
        ->assertSee('Lihat Jadwal');

    expect($landing->viewData('facilities')->modelKeys())->toBe([$facility->id]);

    // Grid 26 slot tidak lagi dirender inline di kartu landing. Pengunjung
    // mencapainya lewat tombol "Lihat Jadwal" pada tiap kartu.
    $this->get(route('fasilitas.jadwal', ['facility' => $facility, 'from' => 'home']))
        ->assertOk()
        ->assertSee('Jadwal Ketersediaan')
        ->assertViewHas('slots', fn (array $slots): bool => count($slots) === 26);
});

it('membatasi kartu landing maksimal sembilan dan mengarahkan sisanya ke katalog', function (): void {
    Facility::factory()->count(15)->create(['status' => 'aktif']);

    $landing = $this->get('/')
        ->assertOk()
        ->assertSee('Lihat Semua Fasilitas');

    expect($landing->viewData('facilities')->count())->toBe(9)
        ->and($landing->viewData('totalFacilities'))->toBe(15);
});

it('menandai Beranda sebagai navigasi aktif pada posisi awal', function (): void {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('data-landing-nav="top"')
        ->toContain('aria-current="page">Beranda</a>')
        ->not->toContain('aria-current="page">Jadwal</a>');
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

it('melayani endpoint ajax landing page tanpa error', function (): void {
    $facility = Facility::factory()->create([
        'name' => 'Aula Terpadu',
        'type' => 'aula',
        'location' => 'Gedung A',
        'capacity' => 300,
    ]);

    // Tanpa filter: jalur yang dipanggil oleh pencarian langsung.
    $this->getJson(route('home.facilities.ajax'))
        ->assertOk()
        ->assertJsonPath('total', 1);

    // Semua filter yang dikirim app.js,termasuk date+facility_id yang
    // memanggil cabang jadwal dan langkah Carbon di dalam controller.
    $this->getJson(route('home.facilities.ajax', [
        'q' => 'aula',
        'type' => 'aula',
        'location' => 'Gedung A',
        'capacity' => 'gt_100',
    ]))->assertOk()->assertJsonPath('total', 1);

    $this->getJson(route('home.facilities.ajax', [
        'facility_id' => $facility->id,
        'date' => '2026-09-10',
    ]))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonStructure(['facilities', 'grids', 'total']);
});

it('menolak nilai filter yang tidak dikenal pada endpoint ajax', function (): void {
    Facility::factory()->create();

    $this->getJson(route('home.facilities.ajax', ['type' => 'bukan_tipe']))
        ->assertStatus(422)
        ->assertJsonValidationErrors('type');
});

it('mengirim url foto absolut pada endpoint ajax agar kartu hasil pencarian tidak rusak', function (): void {
    $withPhoto = Facility::factory()->create(['name' => 'Aula Terpadu']);
    $withPhoto->forceFill(['photo' => 'fasilitas/aula-terpadu.jpg'])->save();
    Facility::factory()->create(['name' => 'Ruang Tanpa Foto']);

    $facilities = $this->getJson(route('home.facilities.ajax'))->assertOk()->json('facilities');

    // Kolom photo tetap path relatif di disk, sementara photo_url sudah
    // resolved ke URL yang bisa langsung dipakai sebagai atribut src.
    expect($facilities[0]['photo'])->toBe('fasilitas/aula-terpadu.jpg');
    expect($facilities[0]['photo_url'])->toBe(url('/storage/fasilitas/aula-terpadu.jpg'));
    expect($facilities[0]['photo_url'])->not->toBe($facilities[0]['photo']);
    expect($facilities[1]['photo_url'])->toBe('');
});

it('memakai satu batas kata pada card grid dan menautkan ke halaman detail', function () {
    $words = array_map(
        fn (int $index): string => "kata{$index}",
        range(1, Facility::MAX_DESCRIPTION_WORDS + 5),
    );
    $facility = Facility::factory()->create(['description' => implode(' ', $words)]);

    // Accessor model adalah satu-satunya tempat pemotongan kata.
    expect(Str::wordCount($facility->short_description))->toBe(Facility::MAX_DESCRIPTION_WORDS);

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('Lihat Deskripsi Lengkap')
        ->toContain('href="'.route('fasilitas.show', $facility).'"')
        ->toContain('kata'.Facility::MAX_DESCRIPTION_WORDS)
        ->not->toContain('kata'.(Facility::MAX_DESCRIPTION_WORDS + 1));
});

it('menahan tinggi card dan tinggi grid agar hasil kosong tidak menggeser komponen', function () {
    Facility::factory()->create(['name' => 'Aula Terpadu']);

    $withResults = $this->get('/')->assertOk()->getContent();
    $empty = $this->get('/?q=tidak-ada-fasilitas')->assertOk()->getContent();

    // min-height mencegah grid menyusut jadi nol saat tidak ada hasil.
    expect($withResults)->toContain('min-h-[24rem]')->toContain('min-h-[5rem]');
    expect($empty)->toContain('min-h-[24rem]');

    // Pesan kosong harus berada DI DALAM container grid, bukan sibling di atasnya.
    $containerPosition = strpos($empty, 'id="landing-grid-container"');
    $emptyMessagePosition = strpos($empty, 'Fasilitas tidak ditemukan');

    expect($containerPosition)->toBeInt();
    expect($emptyMessagePosition)->toBeInt()
        ->toBeGreaterThan($containerPosition);
});

it('mengirim card ter-render dari server agar hasil pencarian sama dengan render awal', function () {
    $facility = Facility::factory()->create([
        'name' => 'Aula Terpadu',
        'description' => 'Aula untuk kegiatan besar.',
    ]);

    $html = $this->getJson(route('home.facilities.ajax'))->assertOk()->json('html');

    // app.js hanya menuliskan innerHTML, jadi markup card tidak boleh disusun
    // ulang di JavaScript.
    expect($html)
        ->toContain($facility->name)
        ->toContain('Lihat Deskripsi Lengkap')
        ->toContain('Lihat Jadwal')
        ->toContain(route('fasilitas.show', $facility));
});

it('mengirim pesan kosong dari server agar bentuknya sama dengan render awal', function () {
    Facility::factory()->create(['name' => 'Aula Terpadu']);

    $html = $this->getJson(route('home.facilities.ajax', ['q' => 'tidak-ada-fasilitas']))
        ->assertOk()
        ->json('html');

    expect($html)->toContain('Fasilitas tidak ditemukan')->toContain('col-span-full');
});
