<?php

use App\Models\Reservation;

it('lists every status in queue order', function (): void {
    expect(Reservation::ORDERED_STATUSES)->toBe([
        'pending',
        'approved',
        'rejected',
        'rejected_by_system',
        'cancelled_by_user',
        'cancelled_by_officer',
        'cancelled_by_system',
    ]);
});

it('labels every known status', function (): void {
    expect(Reservation::statusLabel('pending'))->toBe('Menunggu Persetujuan')
        ->and(Reservation::statusLabel('approved'))->toBe('Disetujui')
        ->and(Reservation::statusLabel('rejected'))->toBe('Ditolak')
        ->and(Reservation::statusLabel('rejected_by_system'))->toBe('Ditolak oleh Sistem')
        ->and(Reservation::statusLabel('cancelled_by_user'))->toBe('Dibatalkan Pengguna')
        ->and(Reservation::statusLabel('cancelled_by_officer'))->toBe('Dibatalkan Petugas')
        ->and(Reservation::statusLabel('cancelled_by_system'))->toBe('Dibatalkan oleh Sistem');
});

it('has a label for every status in the ordered list', function (): void {
    foreach (Reservation::ORDERED_STATUSES as $status) {
        expect(Reservation::statusLabel($status))
            ->not->toBe(ucfirst(str_replace('_', ' ', $status)), "status {$status} fell through to the default label");
    }
});

it('falls back to a readable label for an unknown status', function (): void {
    expect(Reservation::statusLabel('some_future_status'))->toBe('Some future status');
});

it('groups the statuses used by the occupancy recap', function (): void {
    expect(Reservation::PENDING_APPROVED)->toBe(['pending', 'approved'])
        ->and(Reservation::REJECTED)->toBe(['rejected', 'rejected_by_system'])
        ->and(Reservation::CANCELLED)->toBe([
            'cancelled_by_user',
            'cancelled_by_officer',
            'cancelled_by_system',
        ]);
});

it('partitions every status across the recap groups', function (): void {
    $grouped = array_merge(Reservation::PENDING_APPROVED, Reservation::REJECTED, Reservation::CANCELLED);

    sort($grouped);
    $ordered = Reservation::ORDERED_STATUSES;
    sort($ordered);

    expect($grouped)->toBe($ordered)
        ->and(array_unique($grouped))->toHaveCount(count(Reservation::ORDERED_STATUSES));
});

it('reports whether a reservation is still pending or cancellable', function (): void {
    $pending = new Reservation(['status' => 'pending']);
    $approved = new Reservation(['status' => 'approved']);
    $rejected = new Reservation(['status' => 'rejected']);
    $cancelled = new Reservation(['status' => 'cancelled_by_system']);

    expect($pending->isPending())->toBeTrue()
        ->and($approved->isPending())->toBeFalse()
        ->and($pending->isCancellable())->toBeTrue()
        ->and($approved->isCancellable())->toBeTrue()
        ->and($rejected->isCancellable())->toBeFalse()
        ->and($cancelled->isCancellable())->toBeFalse();
});

it('agrees with the statuses the database enum accepts', function (): void {
    $accepted = ['pending', 'approved', 'rejected', 'cancelled_by_user', 'cancelled_by_officer'];

    foreach ($accepted as $status) {
        expect(Reservation::ORDERED_STATUSES)->toContain($status);
    }

    expect(Reservation::ORDERED_STATUSES)
        ->toContain('rejected_by_system', 'cancelled_by_system');
});
