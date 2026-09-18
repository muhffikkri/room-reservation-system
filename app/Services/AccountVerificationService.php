<?php

namespace App\Services;

use App\Models\AccountVerificationAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
        $this->ensureActiveAdmin($admin);

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
            $this->recordAction($locked, $admin, AccountVerificationAction::VERIFIED);

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
        $this->ensureActiveAdmin($admin);

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
            $this->recordAction($locked, $admin, AccountVerificationAction::REJECTED);

            return $locked->refresh();
        });
    }

    /**
     * Kembalikan akun pengguna yang ditolak ke pending (BR-14).
     *
     * Jejak penolakan (rejected_by/at) dibersihkan karena status saat ini
     * kembali pending; kolom audit hanya satu slot per akun.
     *
     * @throws NotFoundHttpException saat target bukan pengguna.
     * @throws ConflictHttpException saat target tidak berstatus ditolak.
     */
    public function restore(User $target, User $admin): User
    {
        $this->ensureActiveAdmin($admin);

        return DB::transaction(function () use ($target, $admin): User {
            $locked = User::whereKey($target->id)->lockForUpdate()->firstOrFail();

            if ($locked->role !== 'pengguna') {
                throw new NotFoundHttpException;
            }

            if ($locked->account_status !== 'ditolak') {
                throw new ConflictHttpException('Hanya akun yang ditolak yang dapat dikembalikan ke pending.');
            }

            $locked->update([
                'account_status' => 'pending',
                'rejected_by' => null,
                'rejected_at' => null,
            ]);
            $this->recordAction($locked, $admin, AccountVerificationAction::RESTORED);

            return $locked->refresh();
        });
    }

    private function recordAction(User $target, User $admin, string $action): void
    {
        AccountVerificationAction::create([
            'target_user_id' => $target->id,
            'actor_id' => $admin->id,
            'action' => $action,
            'acted_at' => now(),
        ]);
    }

    private function ensureActiveAdmin(User $admin): void
    {
        if (! $admin->isAdmin() || ! $admin->isActive()) {
            throw new AccessDeniedHttpException('Akun tidak memiliki akses verifikasi akun.');
        }
    }
}
