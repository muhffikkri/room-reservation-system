<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RecapDateRangeRequest;
use App\Services\RecapService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecapController extends Controller
{
    public function __construct(protected RecapService $recapService) {}

    /**
     * Menampilkan halaman rekap okupansi.
     */
    public function occupancy(RecapDateRangeRequest $request): View
    {
        $start = microtime(true);
        [$startDate, $endDate] = $request->getValidatedDates();
        $dateStrings = $request->getDateStrings();

        $recap = $this->recapService->getOccupancyRecap($startDate, $endDate);

        Log::info('RecapController: Occupancy page loaded', [
            'user_id' => auth()->id(),
            'date_range' => $recap['date_range'],
            'facilities_count' => $this->facilitiesCount($recap),
            'duration_ms' => round((microtime(true) - $start) * 1000, 2),
        ]);

        return view('admin.rekap.occupancy', [
            'recap' => $recap,
            'startDate' => $dateStrings['startDate'],
            'endDate' => $dateStrings['endDate'],
        ]);
    }

    /**
     * Menampilkan halaman rekap kerusakan.
     */
    public function damage(RecapDateRangeRequest $request): View
    {
        $start = microtime(true);
        [$startDate, $endDate] = $request->getValidatedDates();
        $dateStrings = $request->getDateStrings();

        $recap = $this->recapService->getDamageRecap($startDate, $endDate);

        Log::info('RecapController: Damage page loaded', [
            'user_id' => auth()->id(),
            'date_range' => $recap['date_range'],
            'facilities_count' => $this->facilitiesCount($recap),
            'duration_ms' => round((microtime(true) - $start) * 1000, 2),
        ]);

        return view('admin.rekap.damage', [
            'recap' => $recap,
            'startDate' => $dateStrings['startDate'],
            'endDate' => $dateStrings['endDate'],
        ]);
    }

    /**
     * Ekspor rekap okupansi ke CSV.
     */
    public function exportOccupancyCsv(RecapDateRangeRequest $request): StreamedResponse
    {
        $start = microtime(true);
        [$startDate, $endDate] = $request->getValidatedDates();

        $recap = $this->recapService->getOccupancyRecap($startDate, $endDate);
        $csv = $this->recapService->exportOccupancyCsv($recap);

        $filename = 'rekap-okupansi-'.Carbon::now()->format('Y-m-d').'.csv';

        Log::info('RecapController: Occupancy CSV downloaded', [
            'user_id' => auth()->id(),
            'date_range' => $recap['date_range'],
            'facilities_count' => $this->facilitiesCount($recap),
            'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            'bytes' => strlen($csv),
        ]);

        return response()->streamDownload(
            fn () => print ($csv),
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    /**
     * Ekspor rekap kerusakan ke CSV.
     */
    public function exportDamageCsv(RecapDateRangeRequest $request): StreamedResponse
    {
        $start = microtime(true);
        [$startDate, $endDate] = $request->getValidatedDates();

        $recap = $this->recapService->getDamageRecap($startDate, $endDate);
        $csv = $this->recapService->exportDamageCsv($recap);

        $filename = 'rekap-kerusakan-'.Carbon::now()->format('Y-m-d').'.csv';

        Log::info('RecapController: Damage CSV downloaded', [
            'user_id' => auth()->id(),
            'date_range' => $recap['date_range'],
            'facilities_count' => $this->facilitiesCount($recap),
            'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            'bytes' => strlen($csv),
        ]);

        return response()->streamDownload(
            fn () => print ($csv),
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    /**
     * Ekspor rekap okupansi ke PDF (using dompdf).
     */
    public function exportOccupancyPdf(RecapDateRangeRequest $request): BinaryFileResponse|StreamedResponse
    {
        $start = microtime(true);
        [$startDate, $endDate] = $request->getValidatedDates();

        $recap = $this->recapService->getOccupancyRecap($startDate, $endDate);
        $html = $this->recapService->exportOccupancyHtml($recap);

        $filename = 'rekap-okupansi-'.Carbon::now()->format('Y-m-d').'.pdf';

        try {
            $pdf = Pdf::loadHTML($html)
                ->setPaper('A4', 'landscape')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true);

            Log::info('RecapController: Occupancy PDF generated', [
                'user_id' => auth()->id(),
                'date_range' => $recap['date_range'],
                'facilities_count' => $this->facilitiesCount($recap),
                'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            ]);

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            Log::error('RecapController: Occupancy PDF generation failed, falling back to HTML', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'date_range' => $recap['date_range'],
            ]);

            // Fallback to HTML download
            $htmlFilename = 'rekap-okupansi-'.Carbon::now()->format('Y-m-d').'.html';

            return response()->streamDownload(
                fn () => print ($html),
                $htmlFilename,
                ['Content-Type' => 'text/html; charset=UTF-8']
            );
        }
    }

    /**
     * Ekspor rekap kerusakan ke PDF (using dompdf).
     */
    public function exportDamagePdf(RecapDateRangeRequest $request): BinaryFileResponse|StreamedResponse
    {
        $start = microtime(true);
        [$startDate, $endDate] = $request->getValidatedDates();

        $recap = $this->recapService->getDamageRecap($startDate, $endDate);
        $html = $this->recapService->exportDamageHtml($recap);

        $filename = 'rekap-kerusakan-'.Carbon::now()->format('Y-m-d').'.pdf';

        try {
            $pdf = Pdf::loadHTML($html)
                ->setPaper('A4', 'landscape')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true);

            Log::info('RecapController: Damage PDF generated', [
                'user_id' => auth()->id(),
                'date_range' => $recap['date_range'],
                'facilities_count' => $this->facilitiesCount($recap),
                'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            ]);

            return $pdf->download($filename);
        } catch (\Throwable $e) {
            Log::error('RecapController: Damage PDF generation failed, falling back to HTML', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'date_range' => $recap['date_range'],
            ]);

            // Fallback to HTML download
            $htmlFilename = 'rekap-kerusakan-'.Carbon::now()->format('Y-m-d').'.html';

            return response()->streamDownload(
                fn () => print ($html),
                $htmlFilename,
                ['Content-Type' => 'text/html; charset=UTF-8']
            );
        }
    }

    /**
     * Jumlah baris rekap untuk log — aman walau cache pernah korup,
     * karena count() hanya menerima array atau Countable.
     */
    private function facilitiesCount(array $recap): int
    {
        return is_countable($recap['data'] ?? null) ? count($recap['data']) : 0;
    }
}
