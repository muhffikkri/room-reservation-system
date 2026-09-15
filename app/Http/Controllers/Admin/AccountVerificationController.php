<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Verifikasi akun registrasi mandiri oleh admin (BR-14).
 *
 * Hanya akun pengguna pending yang tampil dan dapat berubah status.
 * Mutasi tinggal di AccountVerificationService (transaksi + lock + audit);
 * controller hanya redirect dengan flash message.
 */
class AccountVerificationController extends Controller
{
    public function __construct(private readonly AccountVerificationService $verifications) {}

    public function index(): View
    {
        $pendingUsers = User::pendingPengguna()
            ->orderBy('created_at')
            ->get();

        return view('admin.pengguna.verifikasi', ['pendingUsers' => $pendingUsers]);
    }

    public function verify(User $user): RedirectResponse
    {
        try {
            $verified = $this->verifications->verify($user, auth()->user());
        } catch (ConflictHttpException $exception) {
            return redirect()
                ->route('admin.pengguna.verifikasi')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.pengguna.verifikasi')
            ->with('success', "Akun {$verified->email} berhasil diverifikasi.");
    }

    public function reject(User $user): RedirectResponse
    {
        try {
            $rejected = $this->verifications->reject($user, auth()->user());
        } catch (ConflictHttpException $exception) {
            return redirect()
                ->route('admin.pengguna.verifikasi')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.pengguna.verifikasi')
            ->with('success', "Akun {$rejected->email} ditolak.");
    }

    public function restore(User $user): RedirectResponse
    {
        try {
            $restored = $this->verifications->restore($user, auth()->user());
        } catch (ConflictHttpException $exception) {
            return redirect()
                ->route('admin.pengguna.index')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.pengguna.index')
            ->with('success', "Akun {$restored->email} dikembalikan ke pending.");
    }
}
