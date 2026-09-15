<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat daftar reservasi.
     */
    public function viewAny(User $user): bool
    {
        return $user->account_status === 'aktif';
    }

    /**
     * Tentukan apakah pengguna dapat melihat detail reservasi.
     * Pemilik pada alur pengguna ATAU petugas pada alur operasional
     * (§7.4, BR-13). Admin tidak termasuk: ia hanya menerima agregat
     * read-only di dashboard admin.
     */
    public function view(User $user, Reservation $reservation): bool
    {
        return $user->id === $reservation->user_id
            || $user->role === 'petugas';
    }

    /**
     * Tentukan apakah pengguna dapat membuat reservasi.
     */
    public function create(User $user): bool
    {
        return $user->account_status === 'aktif';
    }

    /**
     * Tentukan apakah pengguna dapat membatalkan reservasi (BR-8).
     * Hanya pemilik yang dapat membatalkan reservasi miliknya yang berstatus
     * pending atau approved, dan minimal 1 jam sebelum waktu mulai.
     */
    public function cancel(User $user, Reservation $reservation): bool
    {
        if ($user->id !== $reservation->user_id) {
            return false;
        }

        if (! in_array($reservation->status, ['pending', 'approved'], true)) {
            return false;
        }

        return $reservation->start_time->isAfter(now()->addHour());
    }

    /**
     * Alias delete untuk route model binding / resource controller.
     */
    public function delete(User $user, Reservation $reservation): bool
    {
        return $this->cancel($user, $reservation);
    }
}
