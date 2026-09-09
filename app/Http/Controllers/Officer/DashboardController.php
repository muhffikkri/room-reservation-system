<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard ringkasan antrian petugas.
     */
    public function __invoke(): View
    {
        $pendingReservationsCount = Reservation::pending()->count();
        $newReportsCount = Report::where('status', 'baru')->count();
        $processingReportsCount = Report::where('status', 'diproses')->count();
        $repairFacilitiesCount = Facility::where('status', 'perbaikan')->count();

        $recentReports = Report::with(['facility', 'user'])
            ->whereIn('status', ['baru', 'diproses'])
            ->latest()
            ->take(5)
            ->get();

        return view('petugas.dashboard', compact(
            'pendingReservationsCount',
            'newReportsCount',
            'processingReportsCount',
            'repairFacilitiesCount',
            'recentReports'
        ));
    }
}
