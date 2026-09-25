<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AccountStatusGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogoutController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * Ritual terminasi sesi tinggal di satu pemilik (AccountStatusGate);
     * controller ini hanya membangun response. Pesan dan tujuan redirect
     * tidak berubah dari sebelumnya (BR-14 tidak mengatur logout).
     * Kegagalan teknis saat ritual tetap dicatat lalu diabaikan agar
     * pemakai selalu berhasil mencapai halaman login.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        try {
            AccountStatusGate::logout($request);
        } catch (\Throwable $e) {
            Log::warning('Logout: terminasi sesi gagal, tetap diarahkan ke login', [
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('login')->with('success', 'Anda telah keluar.');
    }
}
