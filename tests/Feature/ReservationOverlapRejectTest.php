<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\ReservationOverlapRejected;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * Fixture di file ini memakai tanggal 2030 sebagai "masa depan yang sah".
 * Karena BR-3 menolak pemesanan lebih dari 365 hari ke depan, jam sistem
 * dipindahkan ke awal 2030 agar rentang fixture tetap berada di dalam
 * jendela pemesanan.
 */
beforeEach(function () {
    $this->travelTo(Carbon::parse('2030-01-01 07:00', config('app.timezone')));
});

function overlapSlot(string $date, string $time): Carbon
{
    return Carbon::parse("{$date} {$time}", config('app.timezone'));
}

/**
 * @return array{0: Facility, 1: User, 2: User} [fasilitas, pemohon, petugas]
 */
function overlapActors(): array
{
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $owner = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);

    return [$facility, $owner, $officer];
}

function overlapPending(Facility $facility, User $owner, string $date, string $start, string $end): Reservation
{
    return app(ReservationService::class)->create(
        $owner,
        $facility,
        overlapSlot($date, $start),
        overlapSlot($date, $end),
        'Kegiatan organisasi membutuhkan ruang rapat besar.',
    );
}

it('accepts a new submission that starts when an approved reservation ends', function () {
    [$facility, $owner] = overlapActors();
    $day = now()->addDays(2)->toDateString();

    Reservation::factory()->approved()->create([
        'user_id' => $owner->id,
        'facility_id' => $facility->id,
        'start_time' => "{$day} 09:00:00",
        'end_time' => "{$day} 11:00:00",
        'purpose' => 'Reservasi yang sudah disetujui petugas.',
    ]);

    $other = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    $this->actingAs($other)
        ->post(route('reservasi.store'), [
            'facility_id' => $facility->id,
            'date' => $day,
            'start_time' => '11:00',
            'end_time' => '12:00',
            'purpose' => 'Pengajuan kedua yang bersinggungan dengan reservasi 09.00-11.00.',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('reservations', [
        'facility_id' => $facility->id,
        'user_id' => $other->id,
        'status' => 'pending',
        'start_time' => "{$day} 11:00:00",
        'end_time' => "{$day} 12:00:00",
    ]);
});

it('still accepts a reservation with a gap after an approved one', function () {
    [$facility, $owner] = overlapActors();

    Reservation::factory()->approved()->create([
        'user_id' => $owner->id,
        'facility_id' => $facility->id,
        'start_time' => overlapSlot('2030-05-05', '09:00'),
        'end_time' => overlapSlot('2030-05-05', '11:00'),
        'purpose' => 'Reservasi pagi yang sudah disetujui terlebih dulu.',
    ]);

    $reservation = overlapPending($facility, $owner, '2030-05-05', '11:30', '12:30');

    expect($reservation->status)->toBe('pending');
});

it('auto-rejects identical-time pending reservations when one is approved', function () {
    [$facility, $owner, $officer] = overlapActors();
    $rival = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $service = app(ReservationService::class);

    $winner = overlapPending($facility, $owner, '2030-05-06', '09:00', '11:00');
    $loser = overlapPending($facility, $rival, '2030-05-06', '09:00', '11:00');

    $autoRejected = $service->approve($winner, $officer);

    $fresh = $loser->fresh();

    expect($winner->fresh()->status)->toBe('approved')
        ->and($fresh->status)->toBe('rejected_by_system')
        ->and($fresh->reject_reason)->toContain('Otomatis ditolak sistem')
        ->and($fresh->decided_at)->not->toBeNull()
        ->and($fresh->decided_by)->toBeNull()
        ->and($autoRejected)->toBe(1);
});

it('keeps an adjacent-time pending reservation when its neighbour is approved', function () {
    [$facility, $owner, $officer] = overlapActors();
    $rival = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $service = app(ReservationService::class);

    $winner = overlapPending($facility, $owner, '2030-05-07', '09:00', '11:00');
    $loser = overlapPending($facility, $rival, '2030-05-07', '11:00', '12:00');

    $autoRejected = $service->approve($winner, $officer);

    expect($loser->fresh()->status)->toBe('pending')
        ->and($autoRejected)->toBe(0);
});

it('sends an in-app database notification with the required feedback message', function () {
    [$facility, $owner, $officer] = overlapActors();
    $rival = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    $winner = overlapPending($facility, $owner, '2030-05-08', '09:00', '11:00');
    $loser = overlapPending($facility, $rival, '2030-05-08', '09:30', '10:30');

    app(ReservationService::class)->approve($winner, $officer);

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $rival->id,
        'notifiable_type' => User::class,
        'type' => ReservationOverlapRejected::class,
    ]);

    $notification = $rival->notifications()->first();

    expect($notification->data['message'])
        ->toBe('Maaf, fasilitas ini sudah dipesan pada jam yang sama (atau overlap). Permohonan Anda ditolak.')
        ->and($notification->data['reservation_id'])->toBe($loser->id)
        ->and($notification->read_at)->toBeNull();
});

it('shows the Ditolak oleh Sistem label and the notification on the owner pages', function () {
    [$facility, $owner, $officer] = overlapActors();
    $rival = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    $winner = overlapPending($facility, $owner, '2030-05-09', '09:00', '11:00');
    $loser = overlapPending($facility, $rival, '2030-05-09', '10:00', '11:00');

    app(ReservationService::class)->approve($winner, $officer);

    $this->actingAs($rival)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Ditolak oleh Sistem')
        ->assertSee('Maaf, fasilitas ini sudah dipesan pada jam yang sama (atau overlap). Permohonan Anda ditolak.');

    $this->actingAs($rival)
        ->get(route('reservasi.show', $loser))
        ->assertOk()
        ->assertSee('Ditolak oleh Sistem')
        ->assertSee('Alasan Penolakan Otomatis oleh Sistem:', false)
        ->assertDontSee('Gagal');
});

it('shows Ditolak oleh Sistem instead of Gagal on the officer queue and detail', function () {
    [$facility, $owner, $officer] = overlapActors();
    $rival = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    $winner = overlapPending($facility, $owner, '2030-05-10', '09:00', '11:00');
    $loser = overlapPending($facility, $rival, '2030-05-10', '10:00', '11:00');

    app(ReservationService::class)->approve($winner, $officer);

    $this->actingAs($officer)
        ->get(route('petugas.reservasi.index', ['status' => 'rejected_by_system']))
        ->assertOk()
        ->assertSee('Ditolak oleh Sistem');

    $this->actingAs($officer)
        ->get(route('petugas.reservasi.show', $loser))
        ->assertOk()
        ->assertSee('>Ditolak oleh Sistem</span>', false)
        ->assertDontSee('Gagal');
});

it('flashes the auto-rejected count to the officer after approving', function () {
    [$facility, $owner, $officer] = overlapActors();
    $rival = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    $winner = overlapPending($facility, $owner, '2030-05-11', '09:00', '11:00');
    overlapPending($facility, $rival, '2030-05-11', '10:00', '11:00');
    overlapPending($facility, $owner, '2030-05-11', '14:00', '15:00');

    $this->actingAs($officer)
        ->post(route('petugas.reservasi.approve', $winner))
        ->assertRedirect()
        ->assertSessionHas(
            'success',
            'Reservasi disetujui dan slot terkunci. 1 reservasi lain otomatis ditolak karena overlap.'
        );
});

it('leaves adjacent and other-facility pendings untouched without notifications', function () {
    [$facility, $owner, $officer] = overlapActors();
    $rival = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $otherFacility = Facility::factory()->create(['status' => 'aktif']);
    $service = app(ReservationService::class);

    $winner = overlapPending($facility, $owner, '2030-05-12', '09:00', '11:00');
    $adjacentPending = overlapPending($facility, $rival, '2030-05-12', '11:00', '12:00');
    $otherFacilityPending = overlapPending($otherFacility, $rival, '2030-05-12', '09:00', '11:00');

    $autoRejected = $service->approve($winner, $officer);

    expect($autoRejected)->toBe(0)
        ->and($adjacentPending->fresh()->status)->toBe('pending')
        ->and($otherFacilityPending->fresh()->status)->toBe('pending');

    $this->assertDatabaseCount('notifications', 0);
});

it('marks a notification as read and redirects to its reservation', function () {
    [$facility, $owner, $officer] = overlapActors();
    $rival = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    $winner = overlapPending($facility, $owner, '2030-05-13', '09:00', '11:00');
    $loser = overlapPending($facility, $rival, '2030-05-13', '10:00', '11:00');

    app(ReservationService::class)->approve($winner, $officer);

    $notification = $rival->notifications()->first();

    // Panel lonceng harus Offered lewat form ber-CSRF, bukan tautan GET.
    $this->actingAs($rival)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('action="'.route('notifications.read', $notification->id).'"', false)
        ->assertSee('name="_token"', false);

    $this->actingAs($rival)
        ->post(route('notifications.read', $notification->id))
        ->assertRedirect(route('reservasi.show', $loser));

    expect($rival->notifications()->first()->read_at)->not->toBeNull();
});

it('refuses to mark a notification read over GET', function () {
    [$facility, $owner, $officer] = overlapActors();
    $rival = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    $winner = overlapPending($facility, $owner, '2030-05-13', '09:00', '11:00');
    overlapPending($facility, $rival, '2030-05-13', '10:00', '11:00');

    app(ReservationService::class)->approve($winner, $officer);

    $notification = $rival->notifications()->first();

    // Menandai dibaca adalah perubahan state, jadi tidak boleh dipicu GET:
    // GET bisa dipicu diam-diam oleh pihak ketiga tanpa token CSRF.
    $this->actingAs($rival)
        ->get(route('notifications.read', $notification->id))
        ->assertStatus(405);

    expect($rival->notifications()->first()->read_at)->toBeNull();
});

it('does not let a pending account mark a notification read', function () {
    [$facility, $owner, $officer] = overlapActors();
    $pending = User::factory()->create(['role' => 'pengguna', 'account_status' => 'pending']);

    $winner = overlapPending($facility, $owner, '2030-05-13', '09:00', '11:00');

    // Disisipkan lewat factory, bukan service: create() sendiri menolak akun
    // yang belum aktif, jadi antrean untuk pending dibuat di luar service.
    Reservation::factory()->create([
        'user_id' => $pending->id,
        'facility_id' => $facility->id,
        'status' => 'pending',
        'start_time' => overlapSlot('2030-05-13', '10:00'),
        'end_time' => overlapSlot('2030-05-13', '11:00'),
    ]);

    app(ReservationService::class)->approve($winner, $officer);

    $notification = $pending->notifications()->first();

    $this->actingAs($pending)
        ->post(route('notifications.read', $notification->id))
        ->assertRedirect(route('login'))
        ->assertSessionHas('error', 'Akun Anda menunggu verifikasi admin.');

    expect($pending->notifications()->first()->read_at)->toBeNull();
});

it('still lets an active account mark a notification read', function () {
    [$facility, $owner, $officer] = overlapActors();
    $rival = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    $winner = overlapPending($facility, $owner, '2030-05-13', '09:00', '11:00');
    overlapPending($facility, $rival, '2030-05-13', '10:00', '11:00');

    app(ReservationService::class)->approve($winner, $officer);

    $this->actingAs($rival)
        ->post(route('notifications.read', $rival->notifications()->first()->id))
        ->assertRedirect();

    expect($rival->notifications()->first()->read_at)->not->toBeNull();
});

it('keeps logout usable for an account that is no longer active', function () {
    // `active` sengaja tidak dipasang pada logout; kalau tidak, akun yang
    // dinonaktifkan akan terkunci tanpa jalan keluar.
    $pending = User::factory()->create(['role' => 'pengguna', 'account_status' => 'pending']);

    $this->actingAs($pending)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('marks every notification as read in a single update', function () {
    [$facility, $owner] = overlapActors();
    $rival = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $reservation = overlapPending($facility, $owner, '2030-05-14', '09:00', '11:00');

    // Notifikasi dibuat langsung: yang diuji adalah penandaan, bukan alur
    // penolakan otomatis yang kebetulan hanya bisa menghasilkan dua.
    foreach (range(1, 3) as $ignored) {
        $rival->notify(new ReservationOverlapRejected($reservation));
    }

    expect($rival->unreadNotifications()->count())->toBe(3);

    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $this->actingAs($rival)
        ->post(route('notifications.read-all'))
        ->assertRedirect();

    expect($rival->fresh()->unreadNotifications()->count())->toBe(0);

    // Satu update untuk semua baris. Collection markAsRead() menulis satu
    // baris satu per notifikasi, jadi mahanya ikut jumlah notifikasi.
    $updates = array_values(array_filter(
        $queries,
        fn (string $sql) => str_starts_with($sql, 'update `notifications`')
    ));

    expect($updates)->toHaveCount(1);
});
