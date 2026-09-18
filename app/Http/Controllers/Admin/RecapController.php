<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RecapService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecapController extends Controller
{
    public function __construct(protected RecapService $recapService) {}

    /**
     * Menampilkan halaman rekap okupansi.
     */
    public function occupancy(Request $request): View
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : null;
        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : null;

        $recap = $this->recapService->getOccupancyRecap($startDate, $endDate);

        return view('admin.rekap.occupancy', [
            'recap' => $recap,
            'startDate' => $startDate?->toDateString() ?? Carbon::now()->subDays(30)->toDateString(),
            'endDate' => $endDate?->toDateString() ?? Carbon::now()->toDateString(),
        ]);
    }

    /**
     * Menampilkan halaman rekap kerusakan.
     */
    public function damage(Request $request): View
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : null;
        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : null;

        $recap = $this->recapService->getDamageRecap($startDate, $endDate);

        return view('admin.rekap.damage', [
            'recap' => $recap,
            'startDate' => $startDate?->toDateString() ?? Carbon::now()->subDays(30)->toDateString(),
            'endDate' => $endDate?->toDateString() ?? Carbon::now()->toDateString(),
        ]);
    }

    /**
     * Ekspor rekap okupansi ke CSV.
     */
    public function exportOccupancyCsv(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : null;
        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : null;

        $recap = $this->recapService->getOccupancyRecap($startDate, $endDate);
        $csv = $this->recapService->exportOccupancyCsv($recap);

        $filename = 'rekap-okupansi-'.Carbon::now()->format('Y-m-d').'.csv';

        return response()->streamDownload(
            fn () => print ($csv),
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    /**
     * Ekspor rekap kerusakan ke CSV.
     */
    public function exportDamageCsv(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : null;
        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : null;

        $recap = $this->recapService->getDamageRecap($startDate, $endDate);
        $csv = $this->recapService->exportDamageCsv($recap);

        $filename = 'rekap-kerusakan-'.Carbon::now()->format('Y-m-d').'.csv';

        return response()->streamDownload(
            fn () => print ($csv),
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    /**
     * Ekspor rekap okupansi ke PDF (via HTML).
     */
    public function exportOccupancyPdf(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : null;
        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : null;

        $recap = $this->recapService->getOccupancyRecap($startDate, $endDate);
        $html = $this->recapService->exportOccupancyHtml($recap);

        $filename = 'rekap-okupansi-'.Carbon::now()->format('Y-m-d').'.pdf';

        return response()->streamDownload(
            fn () => print ($html),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Ekspor rekap kerusakan ke PDF (via HTML).
     */
    public function exportDamagePdf(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : null;
        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : null;

        $recap = $this->recapService->getDamageRecap($startDate, $endDate);
        $html = $this->recapService->exportDamageHtml($recap);

        $filename = 'rekap-kerusakan-'.Carbon::now()->format('Y-m-d').'.pdf';

        return response()->streamDownload(
            fn () => print ($html),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
