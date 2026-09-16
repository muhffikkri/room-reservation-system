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
     * Atribut akun dinormalisasi SEBELUM validasi agar unique menangkap
     * duplikat beda format. Pesan unique dibuat generik agar endpoint ini
     * tidak menjadi oracle keberadaan email, identitas, atau nomor telepon.
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
            'email' => ['required', 'string', 'email', 'max:254', 'unique:users,email'],
            'password' => ['required', 'string', 'max:255', Password::min(8)],
            'password_confirmation' => ['required', 'string', 'same:password'],
            'identity' => ['required', 'string', 'max:30', 'unique:users,identity'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
        ], $this->messages());

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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Data registrasi tidak dapat diproses.',
            'identity.unique' => 'Data registrasi tidak dapat diproses.',
            'phone.unique' => 'Data registrasi tidak dapat diproses.',
        ];
    }
}
