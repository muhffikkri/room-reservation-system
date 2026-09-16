<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    /**
     * Daftar laporan milik sendiri selalu boleh (dipakai index milik sendiri).
     */
    public function viewAny(User $user): bool
    {
        return $user->isActive() && ($user->isPengguna() || $user->isPetugas());
    }

    /**
     * Detail laporan: pemilik pada alur pengguna ATAU petugas pada alur
     * operasional (§7.4, BR-13). Admin tidak termasuk: ia hanya menerima
     * agregat read-only di dashboard admin.
     */
    public function view(User $user, Report $report): bool
    {
        return $user->isActive()
            && (($user->isPengguna() && $user->id === $report->user_id)
                || $user->isPetugas());
    }

    /**
     * Buat laporan: akun aktif.
     */
    public function create(User $user): bool
    {
        return $user->isActive() && $user->isPengguna();
    }
}
