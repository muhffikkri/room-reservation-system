<?php

use App\Models\Facility;
use App\Models\Report;
use App\Models\User;
use App\Services\ReportService;
use App\Services\ReservationService;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use LogicException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

uses(RefreshDatabase::class);

it('refuses demo seeding in staging', function (): void {
    $originalEnvironment = (string) app()->environment();
    app()->detectEnvironment(static fn (): string => 'staging');

    try {
        expect(fn () => (new UserSeeder)->run())
            ->toThrow(LogicException::class);
    } finally {
        app()->detectEnvironment(static fn (): string => $originalEnvironment);
    }
});

it('adds baseline security headers to public responses', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
});

it('adds a strict content security policy outside local and testing environments', function (): void {
    $originalEnvironment = (string) app()->environment();
    app()->detectEnvironment(static fn (): string => 'production');

    try {
        $this->get('/')->assertHeaderContains('Content-Security-Policy', "script-src 'self'");
    } finally {
        app()->detectEnvironment(static fn () => $originalEnvironment);
    }
});

it('serves report photos only through authorized private routes', function (): void {
    Storage::fake('local');
    Storage::fake('public');

    $owner = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $otherUser = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $path = 'reports/private-photo.jpg';

    Storage::disk('local')->put($path, 'private-photo');
    $report = Report::factory()->create([
        'user_id' => $owner->id,
        'facility_id' => $facility->id,
        'photo' => $path,
    ]);

    $ownerResponse = $this->actingAs($owner)->get(route('laporan.photo', $report));
    $ownerResponse->assertOk()->assertHeaderContains('Content-Disposition', 'inline');
    expect($ownerResponse->streamedContent())->toBe('private-photo');

    $this->actingAs($otherUser)
        ->get(route('laporan.photo', $report))
        ->assertForbidden();

    $this->actingAs($officer)
        ->get(route('petugas.laporan.photo', $report))
        ->assertOk();

    Storage::disk('public')->assertMissing($path);
});

it('rejects reports for inactive facilities', function (): void {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'nonaktif']);

    $this->actingAs($user)
        ->post(route('laporan.store'), [
            'facility_id' => $facility->id,
            'category' => 'listrik',
            'description' => 'Panel listrik fasilitas perlu pemeriksaan segera.',
        ])
        ->assertSessionHasErrors('facility_id');

    expect(Report::query()->count())->toBe(0);
});

it('rejects oversized image dimensions before storage', function (): void {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    $this->actingAs($user)
        ->post(route('laporan.store'), [
            'facility_id' => $facility->id,
            'category' => 'kerusakan_alat',
            'description' => 'Gambar bukti ini sengaja memiliki dimensi terlalu besar.',
            'photo' => UploadedFile::fake()->image('terlalu-besar.jpg', 6001, 1),
        ])
        ->assertSessionHasErrors('photo');
});

it('uses a generic message for duplicate registration attributes', function (): void {
    $existing = User::factory()->create([
        'email' => 'sudah-terdaftar@student.kampus.test',
        'identity' => '2199000001',
        'phone' => '+628120000001',
    ]);

    $response = $this->post('/register', [
        'name' => 'Pendaftar Baru',
        'email' => $existing->email,
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'identity' => '2199000002',
        'phone' => '081200000002',
    ]);

    $response->assertSessionHasErrors('email');
    expect(collect(session('errors')->get('email'))->implode(' '))
        ->toBe('Data registrasi tidak dapat diproses.');
});

it('enforces the daily report quota in the service boundary', function (): void {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);

    Report::factory()->count(ReportService::DAILY_REPORT_QUOTA)->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'created_at' => now(),
    ]);

    expect(fn () => app(ReportService::class)->createReport($user, [
        'facility_id' => $facility->id,
        'category' => 'lainnya',
        'description' => 'Laporan ke dua puluh satu harus ditolak oleh quota.',
    ]))->toThrow(TooManyRequestsHttpException::class);
});

it('rejects service calls made by the wrong role', function (): void {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    $pengguna = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $start = Carbon::tomorrow()->setTime(8, 0);
    $end = $start->copy()->addHour();

    expect(fn () => app(ReservationService::class)->create(
        $admin,
        $facility,
        $start,
        $end,
        'Percobaan reservasi dari role admin',
    ))->toThrow(AccessDeniedHttpException::class);

    $report = Report::factory()->create([
        'user_id' => $pengguna->id,
        'facility_id' => $facility->id,
    ]);

    expect(fn () => app(ReportService::class)->transition(
        $report,
        $admin,
        'diproses',
        'Percobaan perubahan status oleh admin.',
    ))->toThrow(AccessDeniedHttpException::class);
});

it('binds repair status to the report that caused it', function (): void {
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $first = Report::factory()->create(['user_id' => $user->id, 'facility_id' => $facility->id]);
    $second = Report::factory()->create(['user_id' => $user->id, 'facility_id' => $facility->id]);
    $service = app(ReportService::class);

    $firstProcessing = $service->transition($first, $officer, 'diproses', 'Petugas mulai memeriksa laporan pertama.');
    $service->markFacilityForRepair($firstProcessing, $officer);

    $secondProcessing = $service->transition($second, $officer, 'diproses', 'Petugas memeriksa laporan kedua.');
    $secondDone = $service->transition($secondProcessing, $officer, 'selesai', 'Laporan kedua selesai diperiksa.');

    expect(fn () => $service->restoreFacilityToActive($secondDone, $officer))
        ->toThrow(ValidationException::class);

    expect($facility->refresh()->status)->toBe('perbaikan')
        ->and((int) $facility->repair_report_id)->toBe($first->id);
});

it('migrates legacy public report photos to private storage', function (): void {
    Storage::fake('public');
    Storage::fake('local');

    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $path = 'reports/legacy-photo.jpg';

    Storage::disk('public')->put($path, 'legacy-photo');
    Report::factory()->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
        'photo' => $path,
    ]);

    $this->artisan('reports:protect-photos')->assertExitCode(0);
    Storage::disk('local')->assertExists($path);
    Storage::disk('public')->assertExists($path);

    $this->artisan('reports:protect-photos', ['--delete-public' => true])->assertExitCode(0);
    Storage::disk('local')->assertExists($path);
    Storage::disk('public')->assertMissing($path);
});

it('caps public catalog results and rejects unbounded schedule dates', function (): void {
    Facility::factory()->count(51)->create();
    $facility = Facility::query()->firstOrFail();

    expect($this->get('/')->assertOk()->viewData('facilities')->count())->toBe(50)
        ->and($this->get('/fasilitas')->assertOk()->viewData('facilities')->count())->toBe(50);

    $this->get(route('fasilitas.jadwal', [
        'facility' => $facility,
        'date' => 'not-a-date',
    ]))->assertSessionHasErrors('date');

    $this->get(route('fasilitas.jadwal', [
        'facility' => $facility,
        'date' => Carbon::now()->addDays(366)->toDateString(),
    ]))->assertSessionHasErrors('date');
});

it('disables browser caching for authenticated responses only', function (): void {
    $guestResponse = $this->get('/');

    $guestResponse->assertOk();
    expect($guestResponse->baseResponse->headers->get('Cache-Control') ?? '')->not->toContain('no-store');

    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertHeaderContains('Cache-Control', 'no-store');
});
