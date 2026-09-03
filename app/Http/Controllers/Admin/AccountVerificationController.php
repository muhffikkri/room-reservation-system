<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Verifikasi akun registrasi mandiri oleh admin (BR-14).
 *
 * Hanya akun pending yang tampil di daftar dan dapat berubah status.
 * Sistem mengembalikan 404 untuk akun aktif atau yang admin tolak agar
 * URL verifikasi tidak dapat dipakai ulang.
 */
class AccountVerificationController extends Controller
{
    public function index(): View
    {
        $pendingUsers = User::pendingAccount()
            ->orderBy('created_at')
            ->get();

        return view('admin.pengguna.verifikasi', ['pendingUsers' => $pendingUsers]);
    }

    public function verify(User $user): RedirectResponse
    {
        abort_if($user->account_status !== 'pending', 404);

        $user->update(['account_status' => 'aktif']);

        return redirect()
            ->route('admin.pengguna.verifikasi')
            ->with('success', "Akun {$user->email} berhasil diverifikasi.");
    }

    public function reject(User $user): RedirectResponse
    {
        abort_if($user->account_status !== 'pending', 404);

        $user->update(['account_status' => 'ditolak']);

        return redirect()
            ->route('admin.pengguna.verifikasi')
            ->with('success', "Akun {$user->email} ditolak.");
    }
}
