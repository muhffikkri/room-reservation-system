<?php

namespace App\Http\Middleware;

use App\Services\AccountStatusGate;
use Closure;
use Illuminate\Http\Request;
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

        // Sistem bertanya ke satu pemilik aturan (BR-14); middleware hanya
        // membangun response, bukan memutuskan siapa yang boleh lewat.
        $denial = AccountStatusGate::denialMessage($user);

        if ($denial !== null) {
            AccountStatusGate::logout($request);

            return redirect()
                ->route('login')
                ->with('error', $denial);
        }

        return $next($request);
    }
}
