<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\ReservationOverlapRejected;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

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

it('rejects a new submission that touches an approved reservation (BR-6 interval tertutup)', function () {
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
        ->assertSessionHasErrors([
            'facility_id' => 'Maaf, fasilitas ini sudah dipesan pada jam yang sama (atau overlap). Permohonan Anda ditolak.',
        ]);

    $this->assertDatabaseCount('reservations', 1);
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

    $service->approve($winner, $officer);

    $fresh = $loser->fresh();

    expect($winner->fresh()->status)->toBe('approved')
        ->and($fresh->status)->toBe('rejected_by_system')
        ->and($fresh->reject_reason)->toContain('Otomatis ditolak sistem')
        ->and($fresh->decided_at)->not->toBeNull()
        ->and($fresh->decided_by)->toBeNull()
        ->and($service->autoRejectedOnApprove())->toBe(1);
});

it('auto-rejects an adjacent-time pending reservation when its neighbour is approved', function () {
    [$facility, $owner, $officer] = overlapActors();
    $rival = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $service = app(ReservationService::class);

    $winner = overlapPending($facility, $owner, '2030-05-07', '09:00', '11:00');
    $loser = overlapPending($facility, $rival, '2030-05-07', '11:00', '12:00');

    $service->approve($winner, $officer);

    expect($loser->fresh()->status)->toBe('rejected_by_system')
        ->and($service->autoRejectedOnApprove())->toBe(1);
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

it('leaves non-overlapping pendings untouched and sends no notification', function () {
    [$facility, $owner, $officer] = overlapActors();
    $rival = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $otherFacility = Facility::factory()->create(['status' => 'aktif']);
    $service = app(ReservationService::class);

    $winner = overlapPending($facility, $owner, '2030-05-12', '09:00', '11:00');
    $gapPendings = overlapPending($facility, $rival, '2030-05-12', '13:00', '14:00');
    $otherFacilityPending = overlapPending($otherFacility, $rival, '2030-05-12', '09:00', '11:00');

    $service->approve($winner, $officer);

    expect($service->autoRejectedOnApprove())->toBe(0)
        ->and($gapPendings->fresh()->status)->toBe('pending')
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

    $this->actingAs($rival)
        ->get(route('notifications.read', $notification->id))
        ->assertRedirect(route('reservasi.show', $loser));

    expect($rival->notifications()->first()->read_at)->not->toBeNull();
});

it('marks every notification as read via the bell action', function () {
    [$facility, $owner, $officer] = overlapActors();
    $rival = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    $winner = overlapPending($facility, $owner, '2030-05-14', '09:00', '11:00');
    overlapPending($facility, $rival, '2030-05-14', '10:00', '11:00');

    app(ReservationService::class)->approve($winner, $officer);

    expect($rival->unreadNotifications()->count())->toBe(1);

    $this->actingAs($rival)
        ->post(route('notifications.read-all'))
        ->assertRedirect();

    expect($rival->fresh()->unreadNotifications()->count())->toBe(0);
});
