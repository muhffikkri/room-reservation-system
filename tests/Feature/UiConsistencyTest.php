<?php

use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;

uses(RefreshDatabase::class);

it('preserves a public slot through login and preselects it on the booking form', function () {
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $selection = ['facility_id' => $facility->id, 'date' => now()->addDay()->toDateString(), 'start_time' => '08:00', 'end_time' => '08:30'];
    $url = route('reservasi.create', $selection);
    $this->get(route('fasilitas.jadwal', ['facility' => $facility, 'date' => $selection['date']]))->assertOk()->assertSee($url);
    $this->get($url)->assertRedirect(route('login'));
    $intended = session('url.intended');
    parse_str(parse_url($intended, PHP_URL_QUERY), $intendedSelection);
    expect($intendedSelection)->toEqual(array_map('strval', $selection));
    $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])->assertRedirect($intended);
    $this->get($url)->assertOk()->assertSee('data-old-start-time="08:00"', false)->assertSee('data-old-end-time="08:30"', false);
});

it('does not preselect a slot that has become unavailable', function () {
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    Reservation::factory()->create(['facility_id' => $facility->id, 'user_id' => $user->id, 'status' => 'approved', 'start_time' => now()->addDay()->setTime(8, 0), 'end_time' => now()->addDay()->setTime(8, 30)]);
    $this->actingAs($user)->get(route('reservasi.create', ['facility_id' => $facility->id, 'date' => now()->addDay()->toDateString(), 'start_time' => '08:00', 'end_time' => '08:30']))->assertOk()->assertSee('data-old-start-time=""', false);
});

it('rejects malformed selection times before rendering the form', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $this->actingAs($user)->get(route('reservasi.create', ['start_time' => '<script>', 'end_time' => '25:00']))
        ->assertSessionHasErrors(['start_time', 'end_time']);
});

it('does not carry a selection to a different facility when the original is inactive', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    Facility::factory()->create(['status' => 'aktif']);
    $inactive = Facility::factory()->create(['status' => 'nonaktif']);
    $this->actingAs($user)->get(route('reservasi.create', ['facility_id' => $inactive->id, 'date' => now()->addDay()->toDateString(), 'start_time' => '08:00', 'end_time' => '08:30']))
        ->assertOk()->assertSee('data-old-start-time=""', false);
});

it('limits admin account lists and exposes the remaining page', function (string $role) {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    User::factory()->count(16)->create(['role' => $role, 'account_status' => 'aktif']);
    $variable = ['pengguna' => 'users', 'petugas' => 'officers', 'admin' => 'admins'][$role];
    $this->actingAs($admin)->get(route("admin.{$role}.index"))->assertOk()
        ->assertViewHas($variable, fn ($accounts) => $accounts instanceof LengthAwarePaginator && $accounts->count() === 15)->assertSee('page=2', false);
})->with(['pengguna', 'petugas', 'admin']);

it('paginates verification and facility search without dropping the filter', function () {
    $admin = User::factory()->create(['role' => 'admin', 'account_status' => 'aktif']);
    User::factory()->count(16)->create(['role' => 'pengguna', 'account_status' => 'pending']);
    Facility::factory()->count(16)->create(['name' => 'Aula Kampus']);
    $this->actingAs($admin)->get(route('admin.pengguna.verifikasi'))->assertOk()
        ->assertViewHas('pendingUsers', fn ($accounts) => $accounts instanceof LengthAwarePaginator && $accounts->count() === 15)->assertSee('page=2', false);
    $this->get(route('admin.fasilitas.index', ['q' => 'Aula']))->assertOk()
        ->assertViewHas('facilities', fn ($facilities) => $facilities instanceof LengthAwarePaginator && $facilities->count() === 15)->assertSee('q=Aula', false)->assertSee('page=2', false);
});

it('uses bounded clay pagination even with a thousand pages', function () {
    $paginator = new LengthAwarePaginator([], 15000, 15, 500, ['path' => '/petugas/reservasi']);
    $html = (string) $paginator->links();
    expect(substr_count($html, '<a '))->toBeLessThanOrEqual(13);
    expect($html)->toContain('clay-button-white', 'aria-current="page"', '…');
});

it('returns the same pagination and status presentation for AJAX queue updates', function () {
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    Reservation::factory()->count(16)->create(['user_id' => User::factory()->create()->id, 'facility_id' => Facility::factory()->create()->id, 'status' => 'approved', 'start_time' => now()->addDay()->setTime(8, 0), 'end_time' => now()->addDay()->setTime(9, 0)]);
    $this->actingAs($officer);
    $page = $this->get(route('petugas.reservasi.index', ['status' => 'approved']))->assertOk();
    $ajax = $this->getJson(route('petugas.reservasi.ajax', ['status' => 'approved']))->assertOk();
    expect($ajax->json('pagination_html'))->toBeString();
    $page->assertSee($ajax->json('pagination_html'), false);
    $page->assertSee($ajax->json('reservations.0.status_html'), false);
});

it('keeps facility detail in the public shell and shares status colors', function () {
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $this->get(route('fasilitas.show', $facility))->assertOk()->assertSee('max-w-public', false)->assertSee('landing-panel', false);
    expect(trim(Blade::render('<x-reservation.status-pill status="cancelled_by_system" />')))
        ->toBe(trim(Blade::render('<x-ui.badge status="cancelled_by_system" />')));
});

it('shows the same processing label and blue status across user and officer report pages', function () {
    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);
    $report = Report::factory()->create(['user_id' => $user->id, 'facility_id' => Facility::factory()->create()->id, 'status' => 'diproses']);
    $this->actingAs($user);
    foreach ([route('dashboard'), route('laporan.index'), route('laporan.show', $report)] as $url) {
        $this->get($url)->assertOk()->assertSee('text-blue-700 ring-blue-200', false)->assertSee('Sedang Diproses');
    }
    $this->actingAs($officer)->get(route('petugas.laporan.index', ['status' => 'diproses']))->assertOk()
        ->assertSee('text-blue-700 ring-blue-200', false)->assertSee('Sedang Diproses');
});
