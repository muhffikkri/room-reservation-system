<?php

use App\Models\Facility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function fakeFacilityPhoto(string $name): UploadedFile
{
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==', true);

    return UploadedFile::fake()->createWithContent($name, $png);
}

it('forbids guests from the facility management pages', function () {
    $facility = Facility::factory()->create();

    $this->get('/admin/fasilitas')->assertRedirect(route('login'));
    $this->get('/admin/fasilitas/create')->assertRedirect(route('login'));
    $this->post('/admin/fasilitas')->assertRedirect(route('login'));
    $this->get("/admin/fasilitas/{$facility->id}/edit")->assertRedirect(route('login'));
    $this->put("/admin/fasilitas/{$facility->id}")->assertRedirect(route('login'));
    $this->patch("/admin/fasilitas/{$facility->id}/nonaktifkan")->assertRedirect(route('login'));
    $this->patch("/admin/fasilitas/{$facility->id}/aktifkan")->assertRedirect(route('login'));
});

it('forbids pengguna and petugas from the facility management pages', function () {
    foreach (['pengguna', 'petugas'] as $role) {
        $user = User::factory()->create([
            'role' => $role,
            'account_status' => 'aktif',
        ]);

        $this->actingAs($user)->get('/admin/fasilitas')->assertForbidden();
        $this->actingAs($user)->get('/admin/fasilitas/create')->assertForbidden();
        $this->actingAs($user)->post('/admin/fasilitas')->assertForbidden();
    }
});

it('allows admin to open the facility index and create form', function () {
    Facility::factory()->create([
        'name' => 'Aula Utama',
        'type' => 'aula',
        'status' => 'aktif',
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $this->actingAs($admin)->get('/admin/fasilitas')->assertOk()->assertSee('Aula Utama');
    $this->actingAs($admin)->get('/admin/fasilitas/create')->assertOk();
});

it('creates an active facility and stores its photo under facilities/', function () {
    Storage::fake('public');

    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $this->actingAs($admin)->post('/admin/fasilitas', [
        'name' => 'Aula Serbaguna Utama',
        'type' => 'aula',
        'location' => 'Gedung Serbaguna Lantai 1',
        'capacity' => 200,
        'description' => 'Aula untuk kegiatan besar.',
        'photo' => fakeFacilityPhoto('aula.png'),
    ])->assertRedirect(route('admin.fasilitas.index'));

    $facility = Facility::where('name', 'Aula Serbaguna Utama')->firstOrFail();

    expect($facility->type)->toBe('aula')
        ->and($facility->capacity)->toBe(200)
        ->and($facility->status)->toBe('aktif');

    Storage::disk('public')->assertExists($facility->photo);
    expect($facility->photo)->toStartWith('facilities/');
});

it('requires name, type, location, and capacity when creating', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $this->actingAs($admin)->post('/admin/fasilitas', [])
        ->assertSessionHasErrors(['name', 'type', 'location', 'capacity']);

    expect(Facility::query()->count())->toBe(0);
});

it('rejects an unknown type and a non-image photo', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $this->actingAs($admin)->post('/admin/fasilitas', [
        'name' => 'Ruang VIP',
        'type' => 'kamar_mandi',
        'location' => 'Gedung Utama',
        'capacity' => 5,
        'photo' => UploadedFile::fake()->create('dokumen.txt', 100),
    ])->assertSessionHasErrors('type');

    $this->actingAs($admin)->post('/admin/fasilitas', [
        'name' => 'Ruang VIP',
        'type' => 'ruang_kelas',
        'location' => 'Gedung Utama',
        'capacity' => 5,
        'photo' => UploadedFile::fake()->create('dokumen.txt', 100),
    ])->assertSessionHasErrors('photo');

    expect(Facility::query()->count())->toBe(0);
});

it('updates a facility and replaces its photo', function () {
    Storage::fake('public');

    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $facility = Facility::factory()->create([
        'name' => 'Ruang Kelas Lama',
        'photo' => 'facilities/lama.jpg',
    ]);

    Storage::disk('public')->put('facilities/lama.jpg', 'lama');

    $this->actingAs($admin)->put("/admin/fasilitas/{$facility->id}", [
        'name' => 'Ruang Kelas Baru',
        'type' => 'ruang_kelas',
        'location' => 'Gedung Utama Lantai 2',
        'capacity' => 40,
        'description' => 'Deskripsi baru.',
        'photo' => fakeFacilityPhoto('baru.png'),
    ])->assertRedirect(route('admin.fasilitas.index'));

    $facility->refresh();

    expect($facility->name)->toBe('Ruang Kelas Baru')
        ->and($facility->location)->toBe('Gedung Utama Lantai 2')
        ->and($facility->status)->toBe('aktif');

    Storage::disk('public')->assertExists($facility->photo);
    Storage::disk('public')->assertMissing('facilities/lama.jpg');
});

it('keeps the existing photo when none is uploaded on update', function () {
    Storage::fake('public');

    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $facility = Facility::factory()->create([
        'photo' => 'facilities/tetap.jpg',
    ]);

    Storage::disk('public')->put('facilities/tetap.jpg', 'tetap');

    $this->actingAs($admin)->put("/admin/fasilitas/{$facility->id}", [
        'name' => 'Nama Diubah Saja',
        'type' => $facility->type,
        'location' => $facility->location,
        'capacity' => $facility->capacity,
        'description' => $facility->description,
    ])->assertRedirect(route('admin.fasilitas.index'));

    Storage::disk('public')->assertExists('facilities/tetap.jpg');

    expect($facility->refresh()->photo)->toBe('facilities/tetap.jpg');
});

it('deactivates and reactivates a facility', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    $facility = Facility::factory()->create(['status' => 'aktif']);

    $this->actingAs($admin)
        ->patch("/admin/fasilitas/{$facility->id}/nonaktifkan")
        ->assertRedirect();

    expect($facility->refresh()->status)->toBe('nonaktif');

    $this->actingAs($admin)
        ->patch("/admin/fasilitas/{$facility->id}/aktifkan")
        ->assertRedirect();

    expect($facility->refresh()->status)->toBe('aktif');
});

it('filters the facility list by keyword', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'account_status' => 'aktif',
    ]);

    Facility::factory()->create(['name' => 'Aula Besar']);
    Facility::factory()->create(['name' => 'Laboratorium Fisika']);

    $this->actingAs($admin)
        ->get('/admin/fasilitas?q=Aula')
        ->assertOk()
        ->assertSee('Aula Besar')
        ->assertDontSee('Laboratorium Fisika');
});
