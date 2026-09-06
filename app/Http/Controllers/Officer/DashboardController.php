<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dashboard petugas — ringkasan antrian (§11.1, US-*).
 *
 * Menampilkan jumlah reservasi pending, laporan baru, laporan diproses,
 * dan fasilitas dalam perbaikan, plus daftar antrian yang perlu tindakan.
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

        $processedReports = Report::query()
            ->where('status', 'diproses')
            ->count();

        $repairFacilities = Facility::query()
            ->where('status', 'perbaikan')
            ->count();

        return view('petugas.dashboard', [
            'pendingReservationCount' => $pendingReservations->count(),
            'newReportCount' => $newReports->count(),
            'processedReportCount' => $processedReports,
            'repairFacilityCount' => $repairFacilities,
            'pendingReservations' => $pendingReservations->take(5),
            'newReports' => $newReports->take(5),
        ]);
    }
}
