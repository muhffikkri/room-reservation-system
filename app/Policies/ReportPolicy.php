<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use App\Services\AccountStatusGate;

class ReportPolicy
{
    /**
     * Daftar laporan milik sendiri selalu boleh (dipakai index milik sendiri).
     */
    public function viewAny(User $user): bool
    {
        return AccountStatusGate::mayActAs($user, 'pengguna', 'petugas');
    }

    /**
     * Detail laporan: pemilik pada alur pengguna ATAU petugas pada alur
     * operasional (§7.4, BR-13). Admin tidak termasuk: ia hanya menerima
     * agregat read-only di dashboard admin.
     */
    public function view(User $user, Report $report): bool
    {
        // Petugas boleh membuka laporan siapa pun; pengguna hanya laporan
        // miliknya sendiri. Syarat "aktif" sudah ditangani mayActAs().
        return AccountStatusGate::mayActAs($user, 'pengguna', 'petugas')
            && ($user->isPetugas() || $user->id === $report->user_id);
    }

    /**
     * Buat laporan: akun aktif.
     */
    public function create(User $user): bool
    {
        return AccountStatusGate::mayActAs($user, 'pengguna');
    }
}
