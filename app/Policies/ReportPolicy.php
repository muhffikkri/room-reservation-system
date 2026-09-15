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
        return $user->account_status === 'aktif';
    }

    /**
     * Detail laporan: pemilik ATAU petugas/admin (spec §7.4).
     */
    public function view(User $user, Report $report): bool
    {
        return $user->id === $report->user_id
            || in_array($user->role, ['petugas', 'admin'], true);
    }

    /**
     * Buat laporan: akun aktif.
     */
    public function create(User $user): bool
    {
        return $user->account_status === 'aktif';
    }
}
