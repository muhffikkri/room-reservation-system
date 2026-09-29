<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Reservation;
use App\Services\ReservationAvailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Landing page publik dan endpoint data fasilitas.
 *
 * Halaman utama menampilkan sejumlah fasilitas unggulan. Jadwal penuh ditampilkan
 * pada halaman fasilitas tersendiri.
 *
 * Fitur baru:
 * - Live search filter via AJAX (tanpa reload halaman)
 * - Grid fasilitas maksimal 9 kartu
 * - Filter kombinasi: query + jenis + lokasi + kapasitas
 */
class HomeController extends Controller
{
    private const CAPACITY_RANGES = [
        'lt_40' => [1, 39],
        '40_100' => [40, 100],
        'gt_100' => [101, PHP_INT_MAX],
    ];

    private const GRID_MAX_FACILITIES = 9;

    public function __construct(
        protected ReservationAvailability $availability,
    ) {}

    public function __invoke(Request $request): View
    {
        $filters = $request->only([
            'q',
            'type',
            'location',
            'capacity',
        ]);

        [$minCapacity, $maxCapacity] = self::CAPACITY_RANGES[$filters['capacity'] ?? ''] ?? [null, null];

        $facilitiesQuery = Facility::search(
            $filters['q'] ?? null,
            $filters['type'] ?? null,
            $filters['location'] ?? null,
            $minCapacity,
        )
            ->when($maxCapacity !== null, fn ($query) => $query->where('capacity', '<=', $maxCapacity))
            ->orderByRaw('CASE status WHEN "aktif" THEN 0 ELSE 1 END')
            ->orderBy('name');

        $totalFacilities = $facilitiesQuery->count();
        $facilities = $facilitiesQuery->limit(self::GRID_MAX_FACILITIES)->get();

        return view('landing.index', [
            'facilities' => $facilities,
            'filters' => $filters,
            'typeLabels' => Facility::TYPE_LABELS,
            'locationOptions' => Facility::query()->publicLocationOptions()->pluck('location'),
            'totalFacilities' => $totalFacilities,
        ]);
    }

    public function ajaxFacilities(Request $request): JsonResponse
    {
        // Jendela yang sama dengan jadwal publik di FacilityController::jadwal
        // (±365 hari), supaya kedua halaman tidak menerima rentang berbeda
        // untuk hal yang sama.
        $today = Carbon::now(config('app.timezone'))->startOfDay();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'string', Rule::in(array_keys(Facility::TYPE_LABELS))],
            'location' => ['nullable', 'string', 'max:120'],
            'capacity' => ['nullable', 'string', Rule::in(array_keys(self::CAPACITY_RANGES))],
            'facility_id' => ['nullable', 'integer'],
            'date' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:'.$today->copy()->subDays($this->availability->maxLookaheadDays())->toDateString(),
                'before_or_equal:'.$today->copy()->addDays($this->availability->maxLookaheadDays())->toDateString(),
            ],
        ]);

        [$minCapacity, $maxCapacity] = self::CAPACITY_RANGES[$filters['capacity'] ?? ''] ?? [null, null];

        if ($filters['facility_id'] ?? null) {
            $facility = Facility::find($filters['facility_id']);
            if (! $facility) {
                return response()->json(['error' => 'Facility not found'], 404);
            }

            $targetDate = $filters['date'] ? Carbon::parse($filters['date']) : now();
            $dayStart = $this->availability->dayStart($targetDate);
            $dayEnd = $this->availability->dayEnd($targetDate);

            $approvedByFacility = Reservation::approved()
                ->where('facility_id', $facility->id)
                ->where('start_time', '<', $dayEnd)
                ->where('end_time', '>', $dayStart)
                ->get(['facility_id', 'start_time', 'end_time'])
                ->groupBy('facility_id');

            $grids = [$facility->id => $this->availability->publicScheduleSlots(
                $facility,
                $targetDate,
                $approvedByFacility->get($facility->id, collect()),
            )];

            return response()->json([
                'facilities' => [$facility],
                'grids' => $grids,
                'total' => 1,
                'html' => $this->renderGrid(collect([$facility])),
            ]);
        }

        $facilitiesQuery = Facility::search(
            $filters['q'] ?? null,
            $filters['type'] ?? null,
            $filters['location'] ?? null,
            $minCapacity,
        )
            ->when($maxCapacity !== null, fn ($query) => $query->where('capacity', '<=', $maxCapacity))
            ->orderByRaw('CASE status WHEN "aktif" THEN 0 ELSE 1 END')
            ->orderBy('name')
            ->limit(self::GRID_MAX_FACILITIES);

        $facilities = $facilitiesQuery->get();

        $dayStart = $this->availability->dayStart(now());
        $dayEnd = $this->availability->dayEnd(now());
        $approvedByFacility = collect();

        if ($facilities->isNotEmpty()) {
            $approvedByFacility = Reservation::approved()
                ->whereIn('facility_id', $facilities->modelKeys())
                ->where('start_time', '<', $dayEnd)
                ->where('end_time', '>', $dayStart)
                ->get(['facility_id', 'start_time', 'end_time'])
                ->groupBy('facility_id');
        }

        $grids = $facilities->mapWithKeys(fn (Facility $facility) => [
            $facility->id => $this->availability->publicScheduleSlots(
                $facility,
                now(),
                $approvedByFacility->get($facility->id, collect()),
            ),
        ]);

        return response()->json([
            'facilities' => $facilities,
            'grids' => $grids,
            'total' => $facilities->count(),
            'html' => $this->renderGrid($facilities),
        ]);
    }

    /**
     * Render isi grid fasilitas memakai partial yang sama dengan render awal.
     *
     * Pencarian langsung menuliskan hasilnya lewat innerHTML, sehingga markup
     * card tidak boleh disusun ulang di JavaScript. Dengan HTML dari server,
     * batas kata deskripsi, tinggi card, dan pesan kosong tidak punya dua
     * pemilik.
     */
    private function renderGrid(Collection $facilities): string
    {
        return view('landing._facility-grid', ['facilities' => $facilities])->render();
    }
}
