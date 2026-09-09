<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AccountStatusGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * Ritual terminasi sesi tinggal di satu pemilik (AccountStatusGate);
     * controller ini hanya membangun response. Pesan dan tujuan redirect
     * tidak berubah dari sebelumnya (BR-14 tidak mengatur logout).
     */
    public function __invoke(Request $request): RedirectResponse
    {
        AccountStatusGate::logout($request);

        return redirect()->route('login')->with('success', 'Anda telah keluar.');
    }
}
