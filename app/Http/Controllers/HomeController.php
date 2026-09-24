<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Reservation;
use App\Services\ReservationAvailability;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Landing page publik (home) — daftar fasilitas + pratinjau ketersediaan slot.
 *
 * Menampilkan katalog fasilitas dengan filter pencarian dan grid slot 30 menit
 * (BR-1). Hanya reservasi approved yang memblokir slot (BR-6, BR-12), dan
 * identitas pemohon/tujuan tidak pernah dikirim ke halaman publik (BR-13).
 * Grid pratinjau memakai proyeksi booking dari ReservationAvailability agar
 * lewatnya lead time (BR-3) konsisten dengan formulir pemesanan.
 */
class HomeController extends Controller
{
    private const TYPE_LABELS = [
        'ruang_kelas' => 'Ruang Kelas',
        'aula' => 'Aula',
        'laboratorium' => 'Laboratorium',
        'alat' => 'Alat',
        'lapangan' => 'Lapangan',
    ];

    private const CAPACITY_RANGES = [
        'lt_40' => [1, 39],
        '40_100' => [40, 100],
        'gt_100' => [101, PHP_INT_MAX],
    ];

    private const MAX_PUBLIC_FACILITIES = 50;

    public function __construct(
        protected ReservationAvailability $availability,
    ) {}

    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'string', Rule::in(array_keys(self::TYPE_LABELS))],
            'location' => ['nullable', 'string', 'max:120'],
            'capacity' => ['nullable', 'string', Rule::in(array_keys(self::CAPACITY_RANGES))],
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
        $facilities = $facilitiesQuery->limit(self::MAX_PUBLIC_FACILITIES)->get();

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

        return view('landing.index', [
            'facilities' => $facilities,
            'grids' => $facilities->mapWithKeys(fn (Facility $facility) => [
                $facility->id => $this->availability->bookingSlots(
                    $facility,
                    now(),
                    $approvedByFacility->get($facility->id, collect()),
                ),
            ]),
            'filters' => $filters,
            'typeLabels' => self::TYPE_LABELS,
            'locationOptions' => Facility::query()->orderBy('location')->distinct()->limit(self::MAX_PUBLIC_FACILITIES)->pluck('location'),
            'totalFacilities' => $totalFacilities,
            'today' => now(),
        ]);
    }
}
