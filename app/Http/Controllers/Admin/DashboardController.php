<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dashboard admin — ringkasan operasional read-only (§6, §7.3, §10).
 *
 * Hanya angka agregat yang dimuat: tanpa nama pemohon, tanpa detail
 * antrean, tanpa aksi. Detail reservasi/laporan milik antrean petugas
 * (BR-13); daftar akun pending ada di halaman verifikasi admin sendiri.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('admin.dashboard.index', [
            'pendingReservationCount' => Reservation::pending()->count(),
            'newReportCount' => Report::where('status', 'baru')->count(),
            'processedReportCount' => Report::where('status', 'diproses')->count(),
            'repairFacilityCount' => Facility::where('status', 'perbaikan')->count(),
            'pendingAccountCount' => User::pendingPengguna()->count(),
        ]);
    }
}
