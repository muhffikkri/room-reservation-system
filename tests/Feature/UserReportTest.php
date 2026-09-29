<?php

use App\Models\Facility;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
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
    Storage::fake('local');

    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $photo = UploadedFile::fake()->image('bukti.jpg', 100, 100);

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

    Storage::disk('local')->assertExists($report->photo);

    expect($report->photo)->toStartWith('reports/')
        ->and(Storage::disk('local')->size($report->photo))->toBeLessThan(500 * 1024);
});

it('rejects a report description made only of markup', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    // 28 karakter, semuanya tag: lolos min:15 apa adanya, lalu kosong
    // setelah strip_tags.
    $this->actingAs($user)->post(route('laporan.store'), [
        'facility_id' => $facility->id,
        'category' => 'kerusakan_alat',
        'description' => '<b></b><i></i><u></u><s></s>',
    ])->assertSessionHasErrors('description');

    expect(Report::count())->toBe(0);
});

it('stores a report description that carries text alongside markup', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    $this->actingAs($user)->post(route('laporan.store'), [
        'facility_id' => $facility->id,
        'category' => 'kerusakan_alat',
        'description' => '<b>Proyektor</b> mati total dan <i>kabel</i> VGA putus.',
    ])->assertSessionHasNoErrors();

    expect(Report::first()->description)->toBe('Proyektor mati total dan kabel VGA putus.');
});

it('prevents user from viewing reports owned by other users', function () {
    $user1 = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $user2 = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $report = Report::factory()->create(['user_id' => $user1->id, 'facility_id' => $facility->id]);

    $response = $this->actingAs($user2)->get(route('laporan.show', $report));

    $response->assertStatus(403);
});

it('lets a report through after midnight once the calendar day rolls over', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    // 23.50, sehingga 20 laporan masuk pada 23.50-23.53 — semua masih hari
    // yang sama, dan seluruhnya di bawah batas limiter 5 per menit.
    $this->travelTo(Carbon::parse('2030-03-01 23:50:00', config('app.timezone')));

    foreach (range(1, 4) as $batch) {
        foreach (range(1, 5) as $attempt) {
            $this->actingAs($user)->post(route('laporan.store'), [
                'facility_id' => $facility->id,
                'category' => 'kerusakan_alat',
                'description' => 'Proyektor mati total dan kabel VGA putus.',
            ])->assertSessionHasNoErrors();
        }

        $this->travel(1)->minutes();
    }

    expect(Report::where('user_id', $user->id)->count())->toBe(20);

    // 00.05 keesokan harinya: jendela per menit sudah kedaluwarsa, tetapi
    // jendela 24 jam berputar masih menyimpan 20 percobaan. Hari kalender
    // sudah berganti, jadi ReportService mengizinkan laporan baru.
    $this->travelTo(Carbon::parse('2030-03-02 00:05:00', config('app.timezone')));

    $response = $this->actingAs($user)->post(route('laporan.store'), [
        'facility_id' => $facility->id,
        'category' => 'kerusakan_alat',
        'description' => 'Lampu proyektor berkedip setelah menyala.',
    ]);

    $response->assertSessionHasNoErrors();
    expect(Report::where('user_id', $user->id)->count())->toBe(21);
});

it('still stops a report once the calendar day quota is used up', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    $this->travelTo(Carbon::parse('2030-03-01 08:00:00', config('app.timezone')));

    foreach (range(1, 4) as $batch) {
        foreach (range(1, 5) as $attempt) {
            $this->actingAs($user)->post(route('laporan.store'), [
                'facility_id' => $facility->id,
                'category' => 'kerusakan_alat',
                'description' => 'Proyektor mati total dan kabel VGA putus.',
            ])->assertSessionHasNoErrors();
        }

        $this->travel(1)->minutes();
    }

    $this->travel(30)->minutes();

    $response = $this->actingAs($user)->post(route('laporan.store'), [
        'facility_id' => $facility->id,
        'category' => 'kerusakan_alat',
        'description' => 'Lampu proyektor berkedip setelah menyala.',
    ]);

    $response->assertStatus(429);
    expect(Report::where('user_id', $user->id)->count())->toBe(20);
});
