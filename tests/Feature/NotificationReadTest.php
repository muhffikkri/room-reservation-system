<?php

use App\Models\Facility;
use App\Models\User;
use App\Notifications\ReservationStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo(Carbon::parse('2030-01-01 07:00', config('app.timezone')));
});

/**
 * @return array{0: Facility, 1: User} [fasilitas, pemilik]
 */
function notificationReadActors(): array
{
    $facility = Facility::factory()->create(['status' => 'aktif']);
    $owner = User::factory()->create(['role' => 'pengguna', 'account_status' => 'aktif']);

    return [$facility, $owner];
}

/**
 * Sisipkan baris notifications dengan created_at yang pasti, supaya urutan
 * dropdown (yang diuji) tidak bergantung pada id auto-increment atau tie clock.
 */
function seedNotificationReadRows(User $user, int $count, string $prefix = 'Pesan'): void
{
    foreach (range(1, $count) as $i) {
        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => ReservationStatusChanged::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['message' => $prefix.' '.str_pad((string) $i, 2, '0', STR_PAD_LEFT), 'url' => '/']),
            'read_at' => null,
            'created_at' => now()->addSeconds($i),
            'updated_at' => now()->addSeconds($i),
        ]);
    }
}

it('marks only the visible notifications read when the dropdown opens', function () {
    [, $owner] = notificationReadActors();
    seedNotificationReadRows($owner, 7);

    $this->actingAs($owner)
        ->postJson(route('notifications.read-opened'))
        ->assertOk()
        ->assertJson(['unread' => 2]);

    $read = $owner->notifications()->whereNotNull('read_at')->pluck('data')
        ->map(fn (array $data): string => $data['message'])->sort()->values()->all();
    $unread = $owner->notifications()->whereNull('read_at')->pluck('data')
        ->map(fn (array $data): string => $data['message'])->sort()->values()->all();

    expect($read)->toBe(['Pesan 03', 'Pesan 04', 'Pesan 05', 'Pesan 06', 'Pesan 07'])
        ->and($unread)->toBe(['Pesan 01', 'Pesan 02']);
});

it('refuses to mark notifications read over GET', function () {
    [, $owner] = notificationReadActors();
    seedNotificationReadRows($owner, 1);

    $this->actingAs($owner)
        ->get(route('notifications.read-opened'))
        ->assertStatus(405);

    expect($owner->unreadNotifications()->count())->toBe(1);
});

it('does not let a pending account open the notification read endpoint', function () {
    $pending = User::factory()->create(['role' => 'pengguna', 'account_status' => 'pending']);
    seedNotificationReadRows($pending, 1);

    $this->actingAs($pending)
        ->postJson(route('notifications.read-opened'))
        ->assertRedirect(route('login'));

    expect($pending->unreadNotifications()->count())->toBe(1);
});

it('shows only the five latest notifications in the dropdown', function () {
    [, $owner] = notificationReadActors();
    seedNotificationReadRows($owner, 7);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Lihat semua notifikasi')
        ->assertSee('Pesan 03')
        ->assertSee('Pesan 07')
        ->assertDontSee('Pesan 01')
        ->assertDontSee('Pesan 02');
});

it('lists every notification across pages on the notifications page', function () {
    [, $owner] = notificationReadActors();
    seedNotificationReadRows($owner, 12);

    $this->actingAs($owner)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Pesan 12')
        ->assertSee('Pesan 03')
        ->assertDontSee('Pesan 02')
        ->assertDontSee('Pesan 01');
});

it('does not let a pending account view the notifications page', function () {
    $pending = User::factory()->create(['role' => 'pengguna', 'account_status' => 'pending']);

    $this->actingAs($pending)
        ->get(route('notifications.index'))
        ->assertRedirect(route('login'));
});
