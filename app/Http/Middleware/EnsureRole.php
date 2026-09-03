<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Membatasi route berdasarkan role, contoh role:admin atau
     * role:petugas,admin (salah satu role cukup).
     *
     * Middleware ini hanya memeriksa sumbu role. Kepemilikan data
     * (misal reservasi milik sendiri, BR-8) ditangani Policy di
     * controller, bukan di sini.
     *
     * @param  string  ...$roles  Daftar role yang diizinkan, mis. role:admin atau role:petugas,admin
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null || ! in_array($user->role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
