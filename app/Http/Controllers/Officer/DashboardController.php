<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use App\Services\ReservationService;
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
    /**
     * Jumlah baris antrean yang ditampilkan di ringkasan. Batasnya dibawa ke
     * SQL: sebelumnya seluruh antrean dimuat lalu dipotong di memori, jadi
     * halaman ini men.transfer seluruh tabel hanya untuk menampilkannya lima
     * baris.
     */
    private const QUEUE_PREVIEW = 5;

    public function __construct(private readonly ReservationService $reservations) {}

    public function __invoke(Request $request): View
    {
        $expired = $this->reservations->expireStale();

        if ($expired > 0) {
            $request->session()->flash(
                'info',
                "{$expired} reservasi otomatis dibatalkan sistem karena melewati batas persetujuan."
            );
        }

        $pendingReservations = Reservation::pending()
            ->with(['user', 'facility'])
            ->orderBy('created_at', 'desc')
            ->orderBy('start_time', 'asc')
            ->take(self::QUEUE_PREVIEW)
            ->get();

        $newReportCount = Report::where('status', 'baru')->count();

        $queueReports = Report::query()
            ->with(['user', 'facility'])
            ->orderByRaw('CASE status WHEN "baru" THEN 0 WHEN "diproses" THEN 1 WHEN "selesai" THEN 2 WHEN "ditolak" THEN 3 ELSE 4 END')
            ->latest()
            ->take(self::QUEUE_PREVIEW)
            ->get();

        $processedReports = Report::query()
            ->where('status', 'diproses')
            ->count();

        $repairFacilities = Facility::query()
            ->where('status', 'perbaikan')
            ->count();

        return view('petugas.dashboard', [
            'pendingReservationCount' => Reservation::pending()->count(),
            'newReportCount' => $newReportCount,
            'processedReportCount' => $processedReports,
            'repairFacilityCount' => $repairFacilities,
            'pendingReservations' => $pendingReservations,
            'queueReports' => $queueReports,
        ]);
    }
}
