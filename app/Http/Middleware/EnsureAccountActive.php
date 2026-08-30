<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

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
