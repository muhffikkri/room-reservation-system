<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function show(): \Illuminate\View\View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        // Sengaja TIDAK menerima 'role' dan 'account_status' dari input (BR-14, BR-15):
        // registrasi mandiri hanya menghasilkan akun pengguna berstatus pending.
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
            'password_confirmation' => ['required', 'string', 'same:password'],
            'identity' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:20'],
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
