<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menolak user yang status akunnya pending atau admin tolak (BR-14).
 *
 * Middleware ini berjalan pada setiap request terotentikasi, sehingga
 * akun yang admin tolak di tengah sesi langsung kehilangan akses pada
 * request berikutnya. Sistem mengeluarkan user lalu mengarahkannya ke
 * halaman login dengan pesan yang sesuai statusnya.
 */
class EnsureAccountActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($user->account_status === 'pending') {
            $this->logout($request);

            return redirect()
                ->route('login')
                ->with('error', 'Akun Anda menunggu verifikasi admin.');
        }

        if ($user->account_status === 'ditolak') {
            $this->logout($request);

            return redirect()
                ->route('login')
                ->with('error', 'Akun Anda ditolak admin dan tidak dapat digunakan.');
        }

        return $next($request);
    }

    private function logout(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
