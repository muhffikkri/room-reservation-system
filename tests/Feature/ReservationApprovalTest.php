<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use App\Rules\SlotTimeValid;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

uses(RefreshDatabase::class);

function slotCarbon(string $date, string $time): Carbon
{
    return Carbon::parse("{$date} {$time}", config('app.timezone'));
}

function makeActors(): array
{
    $facility = Facility::factory()->create(['status' => 'aktif']);

    $user = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $officer = User::factory()->create(['role' => 'petugas', 'account_status' => 'aktif']);

    return [$facility, $user, $officer];
}

it('generates factory data that always passes slot validation', function () {
    [$facility, $user] = makeActors();

    $reservations = Reservation::factory()->count(10)->create([
        'user_id' => $user->id,
        'facility_id' => $facility->id,
    ]);

    expect($reservations)->not->toBeEmpty();

    foreach ($reservations as $reservation) {
        $failures = [];
        $rule = new SlotTimeValid($reservation->start_time, $reservation->end_time);
        $rule->validate('start_time', null, function (string $message) use (&$failures): void {
            $failures[] = $message;
        });

        expect($failures)->toBeEmpty();
    }
});

it('creates a pending reservation and approves it (kasus 3)', function () {
    [$facility, $user, $officer] = makeActors();
    $service = app(ReservationService::class);

    $reservation = $service->create(
        $user,
        $facility,
        slotCarbon('2030-01-07', '08:00'),
        slotCarbon('2030-01-07', '09:00'),
        'Rapat koordinasi panitia acara kampus',
    );

    expect($reservation->status)->toBe('pending');
    $this->assertDatabaseHas('reservations', ['id' => $reservation->id, 'status' => 'pending']);

    $approved = $service->approve($reservation, $officer);

    expect($approved->status)->toBe('approved')
        ->and($approved->decided_by)->toBe($officer->id)
        ->and($approved->decided_at)->not->toBeNull();
});

it('allows overlapping pending reservations in queue but rejects the second approval with 409 (kasus 4)', function () {
    [$facility, $user, $officer] = makeActors();
    $other = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);
    $service = app(ReservationService::class);

    $first = $service->create($user, $facility, slotCarbon('2030-01-08', '08:00'), slotCarbon('2030-01-08', '09:00'), 'Kegiatan pertama yang sudah terjadwal');
    $second = $service->create($other, $facility, slotCarbon('2030-01-08', '08:30'), slotCarbon('2030-01-08', '09:00'), 'Kegiatan kedua yang ikut mengantre slot sama');

    expect($second->status)->toBe('pending');

    $service->approve($first, $officer);

    try {
        $service->approve($second, $officer);
        $this->fail('Approve kedua seharusnya mengembalikan 409.');
    } catch (ConflictHttpException $exception) {
        expect($exception->getStatusCode())->toBe(409);
    }

    expect($second->fresh()->status)->toBe('pending');
});

it('rejects a new request overlapping an approved reservation (BR-6)', function () {
    [$facility, $user] = makeActors();
    $service = app(ReservationService::class);

    $service->create($user, $facility, slotCarbon('2030-01-09', '08:00'), slotCarbon('2030-01-09', '09:00'), 'Reservasi pertama yang langsung disetujui.')->update(['status' => 'approved']);

    expect(fn () => $service->create(
        $user,
        $facility,
        slotCarbon('2030-01-09', '08:30'),
        slotCarbon('2030-01-09', '09:00'),
        'Pengajuan kedua yang bentrok dengan approved',
    ))->toThrow(ValidationException::class);
});

it('rejects the third pending reservation on the same day (BR-4)', function () {
    [$facility, $user] = makeActors();
    $service = app(ReservationService::class);

    $service->create($user, $facility, slotCarbon('2030-01-10', '08:00'), slotCarbon('2030-01-10', '09:00'), 'Reservasi pagi untuk kegiatan pertama');
    $service->create($user, $facility, slotCarbon('2030-01-10', '10:00'), slotCarbon('2030-01-10', '11:00'), 'Reservasi siang untuk kegiatan kedua');

    expect(fn () => $service->create(
        $user,
        $facility,
        slotCarbon('2030-01-10', '13:00'),
        slotCarbon('2030-01-10', '14:00'),
        'Reservasi sore ketiga yang harus ditolak kuota',
    ))->toThrow(ValidationException::class);
});

it('rejects reservations on a non-aktif facility (BR-5)', function () {
    [$facility, $user] = makeActors();
    $facility->update(['status' => 'perbaikan']);
    $service = app(ReservationService::class);

    expect(fn () => $service->create(
        $user,
        $facility,
        slotCarbon('2030-01-11', '08:00'),
        slotCarbon('2030-01-11', '09:00'),
        'Pengajuan pada fasilitas yang sedang diperbaiki',
    ))->toThrow(ValidationException::class);
});

it('rejects approval when the facility is no longer aktif (BR-12)', function () {
    [$facility, $user, $officer] = makeActors();
    $service = app(ReservationService::class);

    $reservation = $service->create(
        $user,
        $facility,
        slotCarbon('2030-01-12', '08:00'),
        slotCarbon('2030-01-12', '09:00'),
        'Pengajuan saat fasilitas masih aktif dan layak pakai',
    );

    $facility->update(['status' => 'perbaikan']);

    try {
        $service->approve($reservation, $officer);
        $this->fail('Approve di fasilitas perbaikan seharusnya mengembalikan 409.');
    } catch (ConflictHttpException $exception) {
        expect($exception->getStatusCode())->toBe(409);
    }

    expect($reservation->fresh()->status)->toBe('pending');
});

it('rejects a start less than 30 minutes from now (BR-3)', function () {
    [$facility, $user] = makeActors();
    $service = app(ReservationService::class);

    $start = Carbon::now(config('app.timezone'))->addMinutes(10)->second(0);
    $start->minute((int) floor($start->minute / 30) * 30);
    $end = $start->copy()->addMinutes(30);

    expect(fn () => $service->create(
        $user,
        $facility,
        $start,
        $end,
        'Pengajuan mepet yang melanggar batas 30 menit',
    ))->toThrow(ValidationException::class);
});
