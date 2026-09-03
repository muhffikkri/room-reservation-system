<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AccountStatusGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Memproses login dan memeriksa status akun (BR-14).
     *
     * Sistem memeriksa status akun SETELAH kredensial cocok. Middleware
     * hanya aktif saat user membuka halaman, jadi tanpa pemeriksaan ini
     * akun pending sempat memegang sesi yang valid selama satu request.
     * Akun pending menerima pesan verifikasi dan akun yang admin tolak
     * menerima pesan penolakan. Login juga dibatasi 5 percobaan per
     * menit untuk tiap kombinasi email dan IP.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $user = Auth::user();

        // Sistem bertanya ke satu pemilik aturan (BR-14); pesannya sama
        // dengan yang dipakai middleware agar user tidak menerima dua
        // versi cerita dari dua pintu berbeda.
        $denial = AccountStatusGate::denialMessage($user);

        if ($denial !== null) {
            AccountStatusGate::logout($request);
            RateLimiter::clear($throttleKey);

            return back()->with('error', $denial);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah keluar.');
    }

    private function throttleKey(Request $request): string
    {
        return strtolower((string) $request->input('email')).'|'.$request->ip();
    }
}
