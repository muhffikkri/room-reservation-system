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
     * Detail laporan: pemilik pada alur pengguna ATAU petugas pada alur
     * operasional (§7.4, BR-13). Admin tidak termasuk: ia hanya menerima
     * agregat read-only di dashboard admin.
     */
    public function view(User $user, Report $report): bool
    {
        return $user->id === $report->user_id
            || $user->role === 'petugas';
    }

    /**
     * Buat laporan: akun aktif.
     */
    public function create(User $user): bool
    {
        return $user->account_status === 'aktif';
    }
}
