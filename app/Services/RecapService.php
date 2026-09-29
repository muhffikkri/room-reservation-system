<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class RecapService
{
    /**
     * Definisi kolom rekap okupansi — satu-satunya daftar urutan kolom.
     *
     * Header CSV, baris CSV, header PDF dan baris PDF semuanya diturunkan
     * dari sini; sebelumnya keempatnya ditulis manual dan bisa berbeda
     * urutan tanpa ada yang gagal.
     *
     * `label` dipakai CSV, `short` dipakai PDF/HTML bila labelnya terlalu
     * panjang untuk tabel, dan `suffix` ditambahkan hanya ke nilai di
     * PDF/HTML.
     *
     * @var list<array{key: string, label: string, short?: string, suffix?: string}>
     */
    private const OCCUPANCY_COLUMNS = [
        ['key' => 'facility_name', 'label' => 'Nama Fasilitas'],
        ['key' => 'facility_type', 'label' => 'Tipe'],
        ['key' => 'facility_location', 'label' => 'Lokasi'],
        ['key' => 'capacity', 'label' => 'Kapasitas'],
        ['key' => 'status', 'label' => 'Status'],
        ['key' => 'approved_count', 'label' => 'Disetujui'],
        ['key' => 'pending_count', 'label' => 'Pending'],
        ['key' => 'rejected_count', 'label' => 'Ditolak'],
        ['key' => 'cancelled_count', 'label' => 'Dibatalkan'],
        ['key' => 'total_reservations', 'label' => 'Total Reservasi', 'short' => 'Total'],
        ['key' => 'total_approved_hours', 'label' => 'Total Jam (Disetujui)', 'short' => 'Jam Disetujui'],
        ['key' => 'max_possible_hours', 'label' => 'Max Jam Operasional', 'short' => 'Max Jam'],
        ['key' => 'occupancy_rate', 'label' => 'Tingkat Okupansi (%)', 'short' => 'Okupansi (%)', 'suffix' => '%'],
    ];

    /**
     * Definisi kolom rekap kerusakan. Sama seperti okupansi: satu daftar
     * untuk CSV dan PDF/HTML.
     *
     * @var list<array{key: string, label: string, short?: string, suffix?: string}>
     */
    private const DAMAGE_COLUMNS = [
        ['key' => 'facility_name', 'label' => 'Nama Fasilitas'],
        ['key' => 'facility_type', 'label' => 'Tipe'],
        ['key' => 'facility_location', 'label' => 'Lokasi'],
        ['key' => 'status', 'label' => 'Status'],
        ['key' => 'baru_count', 'label' => 'Baru'],
        ['key' => 'diproses_count', 'label' => 'Diproses'],
        ['key' => 'selesai_count', 'label' => 'Selesai'],
        ['key' => 'ditolak_count', 'label' => 'Ditolak'],
        ['key' => 'total_reports', 'label' => 'Total Laporan', 'short' => 'Total'],
    ];

    public function __construct(
        protected int $defaultLookbackDays = 30,
    ) {}

    /**
     * Get occupancy recap data for all facilities within date range.
     */
    public function getOccupancyRecap(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->subDays($this->defaultLookbackDays)->startOfDay();
        $endDate = $endDate ?? Carbon::now()->endOfDay();

        return $this->computeOccupancyRecap($startDate, $endDate);
    }

    /**
     * Hitung rekap okupansi langsung dari database (tanpa cache).
     *
     * @return array{data: array<int, array<string, mixed>>, summary: array<string, int|float>, date_range: array<string, string>}
     */
    protected function computeOccupancyRecap(Carbon $startDate, Carbon $endDate): array
    {
        $queryStart = microtime(true);

        // Both ends are normalised to midnight before differencing: diffInDays
        // already counts the final calendar day when handed an end-of-day
        // timestamp, so the +1 would count it twice. 13 jam = 07.00-20.00.
        $operationalDays = $startDate->diffInDays($endDate->copy()->startOfDay()) + 1;
        $maxPossibleHours = $operationalDays * 13;

        // Calculate total approved hours per facility using subquery to avoid N+1
        $hoursSubquery = Reservation::approved()
            ->where('facility_id', DB::raw('facilities.id'))
            ->where('start_time', '>=', $startDate)
            ->where('start_time', '<=', $endDate)
            ->selectRaw('SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time) / 60) as total_hours');

        $facilities = Facility::query()
            ->select('facilities.*')
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
                $query->whereIn('status', Reservation::REJECTED)
                    ->where('start_time', '>=', $startDate)
                    ->where('start_time', '<=', $endDate);
            }])
            ->withCount(['reservations as cancelled_count' => function ($query) use ($startDate, $endDate) {
                $query->whereIn('status', Reservation::CANCELLED)
                    ->where('start_time', '>=', $startDate)
                    ->where('start_time', '<=', $endDate);
            }])
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

        // Rata-rata hanya menghitung fasilitas yang bisa dipakai. Fasilitas
        // nonaktif/perbaikan tidak punya kemungkinan okupansi, jadi
        // menyertakannya di penyebut menurunkan rata-rata tanpa alasan.
        $bookable = $data->where('status', 'aktif');
        $averageOccupancy = $bookable->avg('occupancy_rate');

        $summary = [
            'total_facilities' => $facilities->count(),
            'total_reservations' => $data->sum('total_reservations'),
            'total_approved' => $data->sum('approved_count'),
            'total_pending' => $data->sum('pending_count'),
            'total_rejected' => $data->sum('rejected_count'),
            'total_cancelled' => $data->sum('cancelled_count'),
            'total_approved_hours' => round($data->sum('total_approved_hours'), 2),
            'average_occupancy_rate' => $averageOccupancy ? round($averageOccupancy, 2) : 0,
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
     */
    public function getDamageRecap(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->subDays($this->defaultLookbackDays)->startOfDay();
        $endDate = $endDate ?? Carbon::now()->endOfDay();

        return $this->computeDamageRecap($startDate, $endDate);
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
     * Generate CSV for occupancy recap.
     */
    public function exportOccupancyCsv(array $recapData): string
    {
        $this->assertColumns($recapData, self::OCCUPANCY_COLUMNS, 'exportOccupancyCsv');

        $start = microtime(true);

        $headers = $this->csvLabels(self::OCCUPANCY_COLUMNS);

        $rows = [];
        foreach ($recapData['data'] as $item) {
            $rows[] = $this->csvRow($item, self::OCCUPANCY_COLUMNS);
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
        $this->assertColumns($recapData, self::DAMAGE_COLUMNS, 'exportDamageCsv');

        $start = microtime(true);

        $headers = $this->csvLabels(self::DAMAGE_COLUMNS);

        $rows = [];
        foreach ($recapData['data'] as $item) {
            $rows[] = $this->csvRow($item, self::DAMAGE_COLUMNS);
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
     * Netralkan sel yang akan dieksekusi sebagai rumus saat CSV dibuka.
     *
     * Sel yang diawali = + - @ diperlakukan sebagai rumus oleh Excel,
     * LibreOffice dan Google Sheets, dan nilainya berasal dari database
     * (nama fasilitas, lokasi, kategori kerusakan). Awalan kutip tunggal
     * memaksa sel dibaca sebagai teks.
     *
     * @param  array<int, mixed>  $row
     * @return array<int, mixed>
     */
    protected function neutraliseFormulas(array $row): array
    {
        return array_map(function ($value) {
            if (is_string($value) && preg_match('/^[=+\-@]/', $value) === 1) {
                return "'".$value;
            }

            return $value;
        }, $row);
    }

    /**
     * Pastikan rekap yang masuk sesuai dengan format yang diminta.
     *
     * Tanpa ini, `exportOccupancyCsv()` yang menerima rekap kerusakan lolos
     * secara tipe lalu menghasilkan baris kosong tanpa error: array tidak
     * membawa jenisnya, dan kedua laporan memang punya bentuk berbeda.
     *
     * Hanya baris pertama yang diperiksa karena semua baris dibangun oleh
     * kode yang sama, jadi cukup satu untuk menangkap ketidakcocokan.
     *
     * @param  array<string, mixed>  $recapData
     * @param  list<array{key: string, label: string, short?: string, suffix?: string}>  $columns
     */
    private function assertColumns(array $recapData, array $columns, string $formatter): void
    {
        $row = $recapData['data'][0] ?? null;

        if (! is_array($row)) {
            return;
        }

        foreach ($columns as $column) {
            if (! array_key_exists($column['key'], $row)) {
                throw new InvalidArgumentException(
                    "{$formatter} menerima rekap tanpa kolom '{$column['key']}'. "
                    .'Pastikan jenis rekap yang diminta benar.'
                );
            }
        }
    }

    /**
     * Label header CSV untuk satu definisi kolom.
     *
     * @param  list<array{key: string, label: string, short?: string, suffix?: string}>  $columns
     * @return list<string>
     */
    private function csvLabels(array $columns): array
    {
        return array_map(fn (array $column): string => $column['label'], $columns);
    }

    /**
     * Baris CSV untuk satu baris data, mengikuti urutan definisi kolom.
     *
     * @param  array<string, mixed>  $item
     * @param  list<array{key: string, label: string, short?: string, suffix?: string}>  $columns
     * @return list<mixed>
     */
    private function csvRow(array $item, array $columns): array
    {
        return array_map(fn (array $column): mixed => $item[$column['key']], $columns);
    }

    /**
     * Sel header PDF/HTML. Label dipendekkan bila `short` ada, supaya tabel
     * cetak tidak melebar.
     *
     * @param  list<array{key: string, label: string, short?: string, suffix?: string}>  $columns
     */
    private function htmlHeadCells(array $columns): string
    {
        $cells = '';

        foreach ($columns as $column) {
            $cells .= '<th>'.e($column['short'] ?? $column['label']).'</th>';
        }

        return $cells;
    }

    /**
     * Sel data PDF/HTML. Nilai dari database selalu di-escape di sini.
     *
     * @param  array<string, mixed>  $item
     * @param  list<array{key: string, label: string, short?: string, suffix?: string}>  $columns
     */
    private function htmlCells(array $item, array $columns): string
    {
        $cells = '';

        foreach ($columns as $column) {
            $cells .= '<td>'.e($item[$column['key']]).($column['suffix'] ?? '').'</td>';
        }

        return $cells;
    }

    /**
     * Build CSV string from headers and rows.
     *
     * Delimiter `;` sesuai spesifikasi §13: Excel dengan koma desimal
     * membaca koma sebagai pemisah kolom dan salah membelah angka, sedangkan
     * titik koma langsung terbaca sebagai kolom. BOM UTF-8 di depan
     * memastikan karakter non-ASCII terbaca di Excel.
     */
    protected function buildCsv(array $headers, array $rows): string
    {
        $handle = fopen('php://temp', 'r+');

        // Add BOM for UTF-8
        fwrite($handle, "\xEF\xBB\xBF");

        // escape wajib passed pada PHP 8.4+: tanpa itu fputcsv() memunculkan
        // deprecation pada setiap ekspor.
        fputcsv($handle, $headers, ';', '"', '');
        foreach ($rows as $row) {
            fputcsv($handle, $this->neutraliseFormulas($row), ';', '"', '');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    /**
     * Generate HTML for PDF export (occupancy).
     */
    public function exportOccupancyHtml(array $recapData): string
    {
        $this->assertColumns($recapData, self::OCCUPANCY_COLUMNS, 'exportOccupancyHtml');

        $start = microtime(true);

        $dateRange = "{$recapData['date_range']['start']} s/d {$recapData['date_range']['end']}";

        $rowsHtml = '';
        foreach ($recapData['data'] as $item) {
            $rowsHtml .= '<tr>'.$this->htmlCells($item, self::OCCUPANCY_COLUMNS).'</tr>';
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
            <tr>{$this->htmlHeadCells(self::OCCUPANCY_COLUMNS)}</tr>
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
        $this->assertColumns($recapData, self::DAMAGE_COLUMNS, 'exportDamageHtml');

        $start = microtime(true);

        $dateRange = "{$recapData['date_range']['start']} s/d {$recapData['date_range']['end']}";

        $rowsHtml = '';
        foreach ($recapData['data'] as $item) {
            $rowsHtml .= '<tr>'.$this->htmlCells($item, self::DAMAGE_COLUMNS).'</tr>';
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
                $categoryHtml .= '<tr><td>'.e($category).'</td><td>'.$count.'</td><td colspan="7"></td></tr>';
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
            <tr>{$this->htmlHeadCells(self::DAMAGE_COLUMNS)}</tr>
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
