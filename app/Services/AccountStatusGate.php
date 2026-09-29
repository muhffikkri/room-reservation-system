<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Satu-satunya pemilik aturan status akun (BR-14).
 *
 * Middleware active dan LoginController hanya menjadi adapter tipis:
 * keduanya bertanya ke sini, lalu membangun responsenya masing-masing
 * (redirect ke login vs back). Pesan penolakan hidup di satu tempat
 * sehingga tidak bisa basi sebelah.
 */
final class AccountStatusGate
{
    /**
     * Apakah akun ini boleh bertindak sebagai salah satu role yang diberikan.
     *
     * Inilah satu-satunya tempat yang menggabungkan syarat "status akun
     * aktif" (BR-14) dengan role, supaya 13 pemeriksa tidak masing-masing
     * mengulang pasangan `isX() && isActive()`. Role ditulis sebagai string
     * karena pemanggil sudah tahu role mana yang sedang diuji; accessor
     * isAdmin()/isPetugas()/isPengguna() tetap tersedia untuk pemeriksaan
     * satu-peran di luar gerbang ini.
     *
     * Argumentnya varies karena policy mengizinkan dua role ("pengguna atau
     * petugas") sementara FormRequest biasanya satu.
     */
    public static function mayActAs(?User $user, string ...$roles): bool
    {
        return $user !== null
            && $user->isActive()
            && in_array($user->role, $roles, true);
    }

    /**
     * Kembalikan pesan penolakan, atau null bila akun boleh lewat.
     */
    public static function denialMessage(User $user): ?string
    {
        if ($user->isActive()) {
            return null;
        }

        // Sistem membedakan pending (masih ada harapan) dari status lain
        // (selesai ditolak) agar pesannya jujur dan bisa ditindaklanjuti.
        return $user->account_status === 'pending'
            ? 'Akun Anda menunggu verifikasi admin.'
            : 'Akun Anda ditolak admin dan tidak dapat digunakan.';
    }

    /**
     * Akhiri sesi secara total: logout + buang data sesi + putar token.
     *
     * Tiga langkah ini tidak boleh setengah-setengah, karena sesi yang
     * tersisa membuat akun yang ditolak tetap bisa membuka halaman
     * pada request berikutnya.
     */
    public static function logout(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
