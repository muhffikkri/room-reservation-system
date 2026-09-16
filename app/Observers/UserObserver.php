<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Membatalkan sesi saat role/status akun berubah (§5.2).
 *
 * Perubahan role atau account_status (verifikasi, penolakan, pergantian
 * role) mengakhiri seluruh session aktif akun tersebut: baris sesi di
 * database dihapus dan token ingat-saya dibersihkan agar cookie lama
 * tidak bisa membangun sesi baru. Request berikutnya wajib login ulang.
 */
class UserObserver
{
    public function updated(User $user): void
    {
        if (! $user->wasChanged(['role', 'account_status'])) {
            return;
        }

        // Sesi file/array tidak terdaftar di tabel mana pun, jadi hanya
        // driver database yang baris sesinya bisa dihapus langsung.
        if (config('session.driver') === 'database') {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        // Tanpa ini, cookie ingat-saya membangunkan sesi baru untuk akun
        // yang baru saja ditolak/diganti role-nya. saveQuietly agar tidak
        // memicu observer ini lagi tanpa henti.
        $user->forceFill(['remember_token' => null])->saveQuietly();
    }
}
