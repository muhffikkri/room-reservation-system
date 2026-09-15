<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AccountAttributes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register');
    }

    /**
     * Mendaftarkan akun baru sebagai pengguna pending (BR-14, BR-15).
     *
     * Registrasi mandiri mengabaikan field role dan account_status dari
     * input, walau penyerang mengirimnya manual. Sistem selalu menulis
     * role pengguna dan status pending. Hanya admin yang dapat membuat
     * akun petugas atau mengaktifkan akun (§5.3).
     *
     * Email dinormalisasi SEBELUM validasi agar unique:users,email
     * menangkap duplikat beda kapitalisasi; email akun yang sudah
     * ditolak admin tetap ada di DB sehingga pendaftarannya otomatis
     * tertolak unique (BR-14) dengan pesan field yang jelas.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => AccountAttributes::normalizeEmail($request->input('email')),
            'identity' => AccountAttributes::normalizeIdentity($request->input('identity')),
            'phone' => AccountAttributes::normalizePhone($request->input('phone')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
            'password_confirmation' => ['required', 'string', 'same:password'],
            'identity' => ['required', 'string', 'max:30', 'unique:users,identity'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'identity' => $validated['identity'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'role' => 'pengguna',
            'account_status' => 'pending',
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Registrasi berhasil. Akun Anda menunggu verifikasi admin sebelum dapat digunakan.');
    }
}
