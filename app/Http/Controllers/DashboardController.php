<?php

namespace App\Http\Controllers;

use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly ReservationService $reservations) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $expired = $this->reservations->expireStale($user);

        if ($expired > 0) {
            $request->session()->flash(
                'info',
                "{$expired} reservasi Anda otomatis dibatalkan sistem karena melewati batas persetujuan."
            );
        }

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
