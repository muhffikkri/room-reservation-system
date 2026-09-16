<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AccountStatusGate;
use App\Support\AccountAttributes;
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
        $request->merge([
            'email' => AccountAttributes::normalizeEmail($request->input('email')),
        ]);

        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:254'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $throttleKey = $this->throttleKey($request);
        $accountThrottleKey = $this->accountThrottleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)
            || RateLimiter::tooManyAttempts($accountThrottleKey, 10)) {
            $seconds = max(
                RateLimiter::availableIn($throttleKey),
                RateLimiter::availableIn($accountThrottleKey),
            );

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey);
            RateLimiter::hit($accountThrottleKey);

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
            // Hapus token ingat-saya yang sempat ditulis Auth::attempt agar
            // akun pending/ditolak tidak meninggalkan token gantung di DB.
            $user->forceFill(['remember_token' => null])->save();
            AccountStatusGate::logout($request);

            return back()->with('error', $denial);
        }

        RateLimiter::clear($throttleKey);
        RateLimiter::clear($accountThrottleKey);
        $request->session()->regenerate();

        // Tiap role mendarat di dashboardnya sendiri (§14.2#10): pengguna ke
        // alur pengguna, petugas ke antrian operasional, admin ke ringkasan.
        // Tanpa ini admin/petugas nyasar ke halaman pengguna yang bukan haknya.
        $home = match ($user->role) {
            'petugas' => route('petugas.dashboard'),
            'admin' => route('admin.dashboard'),
            default => route('dashboard'),
        };

        return redirect()->intended($home);
    }

    private function throttleKey(Request $request): string
    {
        return strtolower((string) $request->input('email')).'|'.$request->ip();
    }

    private function accountThrottleKey(Request $request): string
    {
        return 'login-account:'.hash('sha256', strtolower((string) $request->input('email')));
    }
}
