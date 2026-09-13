<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $reservationCounts = $user->reservations()
            ->select('status')
            ->selectRaw('count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $reportCounts = $user->reports()
            ->select('status')
            ->selectRaw('count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $recentReservations = $user->reservations()
            ->with('facility:id,name')
            ->latest('start_time')
            ->take(3)
            ->get();
        $recentReports = $user->reports()
            ->with('facility:id,name')
            ->latest()
            ->take(3)
            ->get();

        return view('dashboard.index', [
            'user' => $user,
            'reservationCounts' => $reservationCounts,
            'reportCounts' => $reportCounts,
            'recentReservations' => $recentReservations,
            'recentReports' => $recentReports,
        ]);
    }
}
