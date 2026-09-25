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
            ->orderBy('created_at', 'desc')
            ->orderBy('start_time', 'asc')
            ->get();

        $newReportCount = Report::where('status', 'baru')->count();

        $queueReports = Report::query()
            ->with(['user', 'facility'])
            ->orderByRaw('CASE status WHEN "baru" THEN 0 WHEN "diproses" THEN 1 WHEN "selesai" THEN 2 WHEN "ditolak" THEN 3 ELSE 4 END')
            ->latest()
            ->get();

        $processedReports = Report::query()
            ->where('status', 'diproses')
            ->count();

        $repairFacilities = Facility::query()
            ->where('status', 'perbaikan')
            ->count();

        return view('petugas.dashboard', [
            'pendingReservationCount' => $pendingReservations->count(),
            'newReportCount' => $newReportCount,
            'processedReportCount' => $processedReports,
            'repairFacilityCount' => $repairFacilities,
            'pendingReservations' => $pendingReservations->take(5),
            'queueReports' => $queueReports->take(5),
        ]);
    }
}
