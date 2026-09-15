<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Satu-satunya pemilik aturan verifikasi akun (BR-14).
 *
 * Controller hanya memanggil verify()/reject() lalu redirect; seluruh
 * cek (role pengguna, status pending) dan tulis audit hidup di sini dan
 * berjalan di dalam lock agar dua admin tidak saling menimpa.
 */
class AccountVerificationService
{
    /**
     * Setujui akun pengguna pending (1 admin cukup).
     *
     * @throws NotFoundHttpException saat target bukan pengguna (sembunyikan eksistensi).
     * @throws ConflictHttpException saat target sudah diproses (termasuk kalah balapan lock).
     */
    public function verify(User $target, User $admin): User
    {
        return DB::transaction(function () use ($target, $admin): User {
            $locked = User::whereKey($target->id)->lockForUpdate()->firstOrFail();

            if ($locked->role !== 'pengguna') {
                throw new NotFoundHttpException;
            }

            if ($locked->account_status !== 'pending') {
                throw new ConflictHttpException('Akun tersebut sudah diproses admin lain.');
            }

            $locked->update([
                'account_status' => 'aktif',
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]);

            return $locked->refresh();
        });
    }

    /**
     * Tolak akun pengguna pending (1 admin cukup).
     *
     * @throws NotFoundHttpException saat target bukan pengguna.
     * @throws ConflictHttpException saat target sudah diproses.
     */
    public function reject(User $target, User $admin): User
    {
        return DB::transaction(function () use ($target, $admin): User {
            $locked = User::whereKey($target->id)->lockForUpdate()->firstOrFail();

            if ($locked->role !== 'pengguna') {
                throw new NotFoundHttpException;
            }

            if ($locked->account_status !== 'pending') {
                throw new ConflictHttpException('Akun tersebut sudah diproses admin lain.');
            }

            $locked->update([
                'account_status' => 'ditolak',
                'rejected_by' => $admin->id,
                'rejected_at' => now(),
            ]);

            return $locked->refresh();
        });
    }
}
