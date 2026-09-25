<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecapService
{
    public function __construct(
        protected int $defaultLookbackDays = 30,
        protected int $cacheTtlSeconds = 300, // 5 minutes
    ) {}

    /**
     * Get occupancy recap data for all facilities within date range.
     *
     * Entri cache yang tidak berupa struktur array murni (mis. sisa
     * serialisasi lama berupa __PHP_Incomplete_Class) otomatis dibuang dan
     * dihitung ulang, sehingga count() tidak pernah menerima input tak valid.
     */
    public function getOccupancyRecap(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->subDays($this->defaultLookbackDays)->startOfDay();
        $endDate = $endDate ?? Carbon::now()->endOfDay();

        $cacheKey = $this->getCacheKey('occupancy', $startDate, $endDate);
        $recap = Cache::get($cacheKey);

        if (! $this->isValidRecap($recap)) {
            $recap = $this->computeOccupancyRecap($startDate, $endDate);
            Cache::put($cacheKey, $recap, $this->cacheTtlSeconds);
        }

        return $recap;
    }

    /**
     * Hitung rekap okupansi langsung dari database (tanpa cache).
     *
     * @return array{data: array<int, array<string, mixed>>, summary: array<string, int|float>, date_range: array<string, string>}
     */
    protected function computeOccupancyRecap(Carbon $startDate, Carbon $endDate): array
    {
        $queryStart = microtime(true);

        $operationalDays = $startDate->diffInDays($endDate) + 1;
        $maxPossibleHours = $operationalDays * 13; // 13 hours per day (07:00-20:00)

        // Calculate total approved hours per facility using subquery to avoid N+1
        $hoursSubquery = Reservation::approved()
            ->where('facility_id', DB::raw('facilities.id'))
            ->where('start_time', '>=', $startDate)
            ->where('start_time', '<=', $endDate)
            ->selectRaw('SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time) / 60) as total_hours');

        $facilities = Facility::query()
            ->withCount(['reservations as approved_count' => function ($query) use ($startDate, $endDate) {
                $query->where('status', 'approved')
                    ->where('start_time', '>=', $startDate)
                    ->where('start_time', '<=', $endDate);
            }])
            ->withCount(['reservations as pending_count' => function ($query) use ($startDate, $endDate) {
                $query->where('status', 'pending')
                    ->where('start_time', '>=', $startDate)
                    ->where('start_time', '<=', $endDate);
            }])
            ->withCount(['reservations as rejected_count' => function ($query) use ($startDate, $endDate) {
                $query->where('status', 'rejected')
                    ->where('start_time', '>=', $startDate)
                    ->where('start_time', '<=', $endDate);
            }])
            ->withCount(['reservations as cancelled_count' => function ($query) use ($startDate, $endDate) {
                $query->where('status', 'cancelled')
                    ->where('start_time', '>=', $startDate)
                    ->where('start_time', '<=', $endDate);
            }])
            ->select('facilities.*')
            ->selectSub($hoursSubquery, 'total_approved_hours')
            ->get();

        $queryDuration = microtime(true) - $queryStart;

        Log::info('RecapService: Occupancy query executed', [
            'date_range' => [$startDate->toDateString(), $endDate->toDateString()],
            'facilities_count' => $facilities->count(),
            'query_duration_ms' => round($queryDuration * 1000, 2),
        ]);

        $data = $facilities->map(function ($facility) use ($maxPossibleHours) {
            $totalHours = (float) ($facility->total_approved_hours ?? 0);

            return [
                'facility_id' => $facility->id,
                'facility_name' => $facility->name,
                'facility_type' => $facility->type,
                'facility_location' => $facility->location,
                'capacity' => $facility->capacity,
                'status' => $facility->status,
                'approved_count' => $facility->approved_count,
                'pending_count' => $facility->pending_count,
                'rejected_count' => $facility->rejected_count,
                'cancelled_count' => $facility->cancelled_count,
                'total_reservations' => $facility->approved_count + $facility->pending_count + $facility->rejected_count + $facility->cancelled_count,
                'total_approved_hours' => round($totalHours, 2),
                'max_possible_hours' => $maxPossibleHours,
                'occupancy_rate' => $maxPossibleHours > 0 ? round(($totalHours / $maxPossibleHours) * 100, 2) : 0,
            ];
        });

        $summary = [
            'total_facilities' => $facilities->count(),
            'total_reservations' => $data->sum('total_reservations'),
            'total_approved' => $data->sum('approved_count'),
            'total_pending' => $data->sum('pending_count'),
            'total_rejected' => $data->sum('rejected_count'),
            'total_cancelled' => $data->sum('cancelled_count'),
            'total_approved_hours' => round($data->sum('total_approved_hours'), 2),
            'average_occupancy_rate' => $data->avg('occupancy_rate') ? round($data->avg('occupancy_rate'), 2) : 0,
        ];

        return [
            'data' => $data->all(),
            'summary' => $summary,
            'date_range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
        ];
    }

    /**
     * Get damage frequency recap data for all facilities within date range.
     *
     * Entri cache yang tidak berupa struktur array murni otomatis dibuang dan
     * dihitung ulang, sehingga count() tidak pernah menerima input tak valid.
     */
    public function getDamageRecap(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->subDays($this->defaultLookbackDays)->startOfDay();
        $endDate = $endDate ?? Carbon::now()->endOfDay();

        $cacheKey = $this->getCacheKey('damage', $startDate, $endDate);
        $recap = Cache::get($cacheKey);

        if (! $this->isValidRecap($recap)) {
            $recap = $this->computeDamageRecap($startDate, $endDate);
            Cache::put($cacheKey, $recap, $this->cacheTtlSeconds);
        }

        return $recap;
    }

    /**
     * Hitung rekap kerusakan langsung dari database (tanpa cache).
     *
     * @return array{data: array<int, array<string, mixed>>, summary: array<string, mixed>, date_range: array<string, string>}
     */
    protected function computeDamageRecap(Carbon $startDate, Carbon $endDate): array
    {
        $queryStart = microtime(true);

        $facilities = Facility::query()
            ->withCount(['reports as baru_count' => function ($query) use ($startDate, $endDate) {
                $query->where('status', 'baru')
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<=', $endDate);
            }])
            ->withCount(['reports as diproses_count' => function ($query) use ($startDate, $endDate) {
                $query->where('status', 'diproses')
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<=', $endDate);
            }])
            ->withCount(['reports as selesai_count' => function ($query) use ($startDate, $endDate) {
                $query->where('status', 'selesai')
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<=', $endDate);
            }])
            ->withCount(['reports as ditolak_count' => function ($query) use ($startDate, $endDate) {
                $query->where('status', 'ditolak')
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<=', $endDate);
            }])
            ->get();

        $categoryData = Report::selectRaw('category, count(*) as count')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        $queryDuration = microtime(true) - $queryStart;

        Log::info('RecapService: Damage query executed', [
            'date_range' => [$startDate->toDateString(), $endDate->toDateString()],
            'facilities_count' => $facilities->count(),
            'categories_count' => count($categoryData),
            'query_duration_ms' => round($queryDuration * 1000, 2),
        ]);

        $data = $facilities->map(function ($facility) {
            return [
                'facility_id' => $facility->id,
                'facility_name' => $facility->name,
                'facility_type' => $facility->type,
                'facility_location' => $facility->location,
                'status' => $facility->status,
                'baru_count' => $facility->baru_count,
                'diproses_count' => $facility->diproses_count,
                'selesai_count' => $facility->selesai_count,
                'ditolak_count' => $facility->ditolak_count,
                'total_reports' => $facility->baru_count + $facility->diproses_count + $facility->selesai_count + $facility->ditolak_count,
            ];
        });

        $summary = [
            'total_facilities_with_reports' => $data->where('total_reports', '>', 0)->count(),
            'total_reports' => $data->sum('total_reports'),
            'total_baru' => $data->sum('baru_count'),
            'total_diproses' => $data->sum('diproses_count'),
            'total_selesai' => $data->sum('selesai_count'),
            'total_ditolak' => $data->sum('ditolak_count'),
            'by_category' => $categoryData,
        ];

        return [
            'data' => $data->all(),
            'summary' => $summary,
            'date_range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
        ];
    }

    /**
     * Validasi struktur hasil rekap: tiga bagian utama wajib array murni
     * (bukan objek hasil serialisasi rusak), termasuk rincian per kategori
     * bila ada.
     */
    protected function isValidRecap(mixed $recap): bool
    {
        return is_array($recap)
            && is_array($recap['data'] ?? null)
            && is_array($recap['summary'] ?? null)
            && is_array($recap['date_range'] ?? null)
            && is_array($recap['summary']['by_category'] ?? []);
    }

    /**
     * Generate cache key for recap data.
     */
    protected function getCacheKey(string $type, Carbon $startDate, Carbon $endDate): string
    {
        return "recap:{$type}:{$startDate->format('Ymd')}:{$endDate->format('Ymd')}";
    }

    /**
     * Invalidate all recap caches (call when reservations/reports change).
     */
    public function invalidateCache(): void
    {
        Cache::flush(); // Simple approach - could be optimized with tags if using Redis
        Log::info('RecapService: Cache invalidated');
    }

    /**
     * Generate CSV for occupancy recap.
     */
    public function exportOccupancyCsv(array $recapData): string
    {
        $start = microtime(true);

        $headers = [
            'Nama Fasilitas',
            'Tipe',
            'Lokasi',
            'Kapasitas',
            'Status',
            'Disetujui',
            'Pending',
            'Ditolak',
            'Dibatalkan',
            'Total Reservasi',
            'Total Jam (Disetujui)',
            'Max Jam Operasional',
            'Tingkat Okupansi (%)',
        ];

        $rows = [];
        foreach ($recapData['data'] as $item) {
            $rows[] = [
                $item['facility_name'],
                $item['facility_type'],
                $item['facility_location'],
                $item['capacity'],
                $item['status'],
                $item['approved_count'],
                $item['pending_count'],
                $item['rejected_count'],
                $item['cancelled_count'],
                $item['total_reservations'],
                $item['total_approved_hours'],
                $item['max_possible_hours'],
                $item['occupancy_rate'],
            ];
        }

        // Add summary row
        $rows[] = [
            'TOTAL',
            '',
            '',
            '',
            '',
            $recapData['summary']['total_approved'],
            $recapData['summary']['total_pending'],
            $recapData['summary']['total_rejected'],
            $recapData['summary']['total_cancelled'],
            $recapData['summary']['total_reservations'],
            $recapData['summary']['total_approved_hours'],
            '',
            $recapData['summary']['average_occupancy_rate'],
        ];

        $csv = $this->buildCsv($headers, $rows);

        Log::info('RecapService: Occupancy CSV exported', [
            'rows_count' => count($rows),
            'date_range' => $recapData['date_range'],
            'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            'bytes' => strlen($csv),
        ]);

        return $csv;
    }

    /**
     * Generate CSV for damage recap.
     */
    public function exportDamageCsv(array $recapData): string
    {
        $start = microtime(true);

        $headers = [
            'Nama Fasilitas',
            'Tipe',
            'Lokasi',
            'Status',
            'Baru',
            'Diproses',
            'Selesai',
            'Ditolak',
            'Total Laporan',
        ];

        $rows = [];
        foreach ($recapData['data'] as $item) {
            $rows[] = [
                $item['facility_name'],
                $item['facility_type'],
                $item['facility_location'],
                $item['status'],
                $item['baru_count'],
                $item['diproses_count'],
                $item['selesai_count'],
                $item['ditolak_count'],
                $item['total_reports'],
            ];
        }

        // Add summary row
        $rows[] = [
            'TOTAL',
            '',
            '',
            '',
            $recapData['summary']['total_baru'],
            $recapData['summary']['total_diproses'],
            $recapData['summary']['total_selesai'],
            $recapData['summary']['total_ditolak'],
            $recapData['summary']['total_reports'],
        ];

        // Add category breakdown
        $rows[] = ['', '', '', '', '', '', '', '', ''];
        $rows[] = ['Kategori Kerusakan', 'Jumlah', '', '', '', '', '', '', ''];
        foreach ($recapData['summary']['by_category'] as $category => $count) {
            $rows[] = [$category, $count, '', '', '', '', '', '', ''];
        }

        $csv = $this->buildCsv($headers, $rows);

        Log::info('RecapService: Damage CSV exported', [
            'rows_count' => count($rows),
            'date_range' => $recapData['date_range'],
            'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            'bytes' => strlen($csv),
        ]);

        return $csv;
    }

    /**
     * Build CSV string from headers and rows.
     */
    protected function buildCsv(array $headers, array $rows): string
    {
        $handle = fopen('php://temp', 'r+');

        // Add BOM for UTF-8
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    /**
     * Stream occupancy CSV directly to output (memory efficient for large datasets).
     */
    public function streamOccupancyCsv(?Carbon $startDate = null, ?Carbon $endDate = null): \Generator
    {
        $startDate = $startDate ?? Carbon::now()->subDays($this->defaultLookbackDays)->startOfDay();
        $endDate = $endDate ?? Carbon::now()->endOfDay();

        $recap = $this->getOccupancyRecap($startDate, $endDate);

        $headers = [
            'Nama Fasilitas',
            'Tipe',
            'Lokasi',
            'Kapasitas',
            'Status',
            'Disetujui',
            'Pending',
            'Ditolak',
            'Dibatalkan',
            'Total Reservasi',
            'Total Jam (Disetujui)',
            'Max Jam Operasional',
            'Tingkat Okupansi (%)',
        ];

        yield $headers;

        foreach ($recap['data'] as $item) {
            yield [
                $item['facility_name'],
                $item['facility_type'],
                $item['facility_location'],
                $item['capacity'],
                $item['status'],
                $item['approved_count'],
                $item['pending_count'],
                $item['rejected_count'],
                $item['cancelled_count'],
                $item['total_reservations'],
                $item['total_approved_hours'],
                $item['max_possible_hours'],
                $item['occupancy_rate'],
            ];
        }

        // Summary row
        yield [
            'TOTAL',
            '',
            '',
            '',
            '',
            $recap['summary']['total_approved'],
            $recap['summary']['total_pending'],
            $recap['summary']['total_rejected'],
            $recap['summary']['total_cancelled'],
            $recap['summary']['total_reservations'],
            $recap['summary']['total_approved_hours'],
            '',
            $recap['summary']['average_occupancy_rate'],
        ];
    }

    /**
     * Stream damage CSV directly to output (memory efficient for large datasets).
     */
    public function streamDamageCsv(?Carbon $startDate = null, ?Carbon $endDate = null): \Generator
    {
        $startDate = $startDate ?? Carbon::now()->subDays($this->defaultLookbackDays)->startOfDay();
        $endDate = $endDate ?? Carbon::now()->endOfDay();

        $recap = $this->getDamageRecap($startDate, $endDate);

        $headers = [
            'Nama Fasilitas',
            'Tipe',
            'Lokasi',
            'Status',
            'Baru',
            'Diproses',
            'Selesai',
            'Ditolak',
            'Total Laporan',
        ];

        yield $headers;

        foreach ($recap['data'] as $item) {
            yield [
                $item['facility_name'],
                $item['facility_type'],
                $item['facility_location'],
                $item['status'],
                $item['baru_count'],
                $item['diproses_count'],
                $item['selesai_count'],
                $item['ditolak_count'],
                $item['total_reports'],
            ];
        }

        // Summary row
        yield [
            'TOTAL',
            '',
            '',
            '',
            $recap['summary']['total_baru'],
            $recap['summary']['total_diproses'],
            $recap['summary']['total_selesai'],
            $recap['summary']['total_ditolak'],
            $recap['summary']['total_reports'],
        ];

        // Category breakdown
        yield ['', '', '', '', '', '', '', '', ''];
        yield ['Kategori Kerusakan', 'Jumlah', '', '', '', '', '', '', ''];
        foreach ($recap['summary']['by_category'] as $category => $count) {
            yield [$category, $count, '', '', '', '', '', '', ''];
        }
    }

    /**
     * Generate HTML for PDF export (occupancy).
     */
    public function exportOccupancyHtml(array $recapData): string
    {
        $start = microtime(true);

        $dateRange = "{$recapData['date_range']['start']} s/d {$recapData['date_range']['end']}";

        $rowsHtml = '';
        foreach ($recapData['data'] as $item) {
            $rowsHtml .= <<<HTML
<tr>
    <td>{$item['facility_name']}</td>
    <td>{$item['facility_type']}</td>
    <td>{$item['facility_location']}</td>
    <td>{$item['capacity']}</td>
    <td>{$item['status']}</td>
    <td>{$item['approved_count']}</td>
    <td>{$item['pending_count']}</td>
    <td>{$item['rejected_count']}</td>
    <td>{$item['cancelled_count']}</td>
    <td>{$item['total_reservations']}</td>
    <td>{$item['total_approved_hours']}</td>
    <td>{$item['max_possible_hours']}</td>
    <td>{$item['occupancy_rate']}%</td>
</tr>
HTML;
        }

        $summary = $recapData['summary'];
        $summaryHtml = <<<HTML
<tr style="font-weight: bold; background-color: #f3f4f6;">
    <td>TOTAL</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td>{$summary['total_approved']}</td>
    <td>{$summary['total_pending']}</td>
    <td>{$summary['total_rejected']}</td>
    <td>{$summary['total_cancelled']}</td>
    <td>{$summary['total_reservations']}</td>
    <td>{$summary['total_approved_hours']}</td>
    <td></td>
    <td>{$summary['average_occupancy_rate']}%</td>
</tr>
HTML;

        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Okupansi Fasilitas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 20px; }
        h1 { text-align: center; color: #1e3a5f; margin-bottom: 5px; }
        .subtitle { text-align: center; color: #6b7280; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: left; }
        th { background-color: #1e3a5f; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9fafb; }
    </style>
</head>
<body>
    <h1>Rekap Okupansi Fasilitas</h1>
    <p class="subtitle">Periode: {$dateRange}</p>
    <table>
        <thead>
            <tr>
                <th>Nama Fasilitas</th>
                <th>Tipe</th>
                <th>Lokasi</th>
                <th>Kapasitas</th>
                <th>Status</th>
                <th>Disetujui</th>
                <th>Pending</th>
                <th>Ditolak</th>
                <th>Dibatalkan</th>
                <th>Total</th>
                <th>Jam Disetujui</th>
                <th>Max Jam</th>
                <th>Okupansi (%)</th>
            </tr>
        </thead>
        <tbody>
            {$rowsHtml}
            {$summaryHtml}
        </tbody>
    </table>
</body>
</html>
HTML;

        Log::info('RecapService: Occupancy HTML exported', [
            'date_range' => $recapData['date_range'],
            'facilities_count' => is_countable($recapData['data'] ?? null) ? count($recapData['data']) : 0,
            'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            'bytes' => strlen($html),
        ]);

        return $html;
    }

    /**
     * Generate HTML for PDF export (damage).
     */
    public function exportDamageHtml(array $recapData): string
    {
        $start = microtime(true);

        $dateRange = "{$recapData['date_range']['start']} s/d {$recapData['date_range']['end']}";

        $rowsHtml = '';
        foreach ($recapData['data'] as $item) {
            $rowsHtml .= <<<HTML
<tr>
    <td>{$item['facility_name']}</td>
    <td>{$item['facility_type']}</td>
    <td>{$item['facility_location']}</td>
    <td>{$item['status']}</td>
    <td>{$item['baru_count']}</td>
    <td>{$item['diproses_count']}</td>
    <td>{$item['selesai_count']}</td>
    <td>{$item['ditolak_count']}</td>
    <td>{$item['total_reports']}</td>
</tr>
HTML;
        }

        $summary = $recapData['summary'];
        $summaryHtml = <<<HTML
<tr style="font-weight: bold; background-color: #f3f4f6;">
    <td>TOTAL</td>
    <td></td>
    <td></td>
    <td></td>
    <td>{$summary['total_baru']}</td>
    <td>{$summary['total_diproses']}</td>
    <td>{$summary['total_selesai']}</td>
    <td>{$summary['total_ditolak']}</td>
    <td>{$summary['total_reports']}</td>
</tr>
HTML;

        $categoryHtml = '';
        if (! empty($summary['by_category'])) {
            $categoryHtml = '<tr><td colspan="9" style="border: none; padding-top: 20px;"><strong>Rincian per Kategori Kerusakan:</strong></td></tr>';
            $categoryHtml .= '<tr><th>Kategori</th><th>Jumlah</th><th colspan="7"></th></tr>';
            foreach ($summary['by_category'] as $category => $count) {
                $categoryHtml .= "<tr><td>{$category}</td><td>{$count}</td><td colspan=\"7\"></td></tr>";
            }
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Kerusakan Fasilitas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 20px; }
        h1 { text-align: center; color: #1e3a5f; margin-bottom: 5px; }
        .subtitle { text-align: center; color: #6b7280; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: left; }
        th { background-color: #1e3a5f; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9fafb; }
    </style>
</head>
<body>
    <h1>Rekap Frekuensi Kerusakan Fasilitas</h1>
    <p class="subtitle">Periode: {$dateRange}</p>
    <table>
        <thead>
            <tr>
                <th>Nama Fasilitas</th>
                <th>Tipe</th>
                <th>Lokasi</th>
                <th>Status</th>
                <th>Baru</th>
                <th>Diproses</th>
                <th>Selesai</th>
                <th>Ditolak</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            {$rowsHtml}
            {$summaryHtml}
            {$categoryHtml}
        </tbody>
    </table>
</body>
</html>
HTML;

        Log::info('RecapService: Damage HTML exported', [
            'date_range' => $recapData['date_range'],
            'facilities_count' => is_countable($recapData['data'] ?? null) ? count($recapData['data']) : 0,
            'categories_count' => is_countable($recapData['summary']['by_category'] ?? null) ? count($recapData['summary']['by_category']) : 0,
            'duration_ms' => round((microtime(true) - $start) * 1000, 2),
            'bytes' => strlen($html),
        ]);

        return $html;
    }
}
