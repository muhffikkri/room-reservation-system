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
 * Dashboard admin — ringkasan operasional (§6, §11.1).
 *
 * Strukturnya sengaja disetarakan dengan dashboard petugas agar admin
 * memantau antrean yang sama, ditambah satu kartu akun pending karena
 * verifikasi registrasi mandiri adalah tanggung jawab admin (BR-14).
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $pendingReservations = Reservation::pending()
            ->with(['user', 'facility'])
            ->orderBy('start_time')
            ->get();

        $newReports = Report::query()
            ->with(['user', 'facility'])
            ->where('status', 'baru')
            ->orderBy('created_at')
            ->get();

        $pendingAccounts = User::pendingAccount()
            ->orderBy('created_at')
            ->get();

        $repairFacilityCount = Facility::query()
            ->where('status', 'perbaikan')
            ->count();

        $processedReportCount = Report::query()
            ->where('status', 'diproses')
            ->count();

        return view('admin.dashboard.index', [
            'pendingReservationCount' => $pendingReservations->count(),
            'newReportCount' => $newReports->count(),
            'processedReportCount' => $processedReportCount,
            'repairFacilityCount' => $repairFacilityCount,
            'pendingAccountCount' => $pendingAccounts->count(),
            'pendingReservations' => $pendingReservations->take(5),
            'newReports' => $newReports->take(5),
            'pendingAccounts' => $pendingAccounts->take(5),
        ]);
    }
}
