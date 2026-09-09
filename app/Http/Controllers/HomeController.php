<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Landing page publik (home) — daftar fasilitas + pratinjau ketersediaan slot.
 *
 * Menampilkan katalog fasilitas dengan filter pencarian dan grid slot 30 menit
 * (BR-1). Hanya reservasi approved yang memblokir slot (BR-6, BR-12), dan
 * identitas pemohon/tujuan tidak pernah dikirim ke halaman publik (BR-13).
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

    private const OPERATIONAL_START_HOUR = 7;

    private const SLOT_COUNT = 26;

    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'string', Rule::in(array_keys(self::TYPE_LABELS))],
            'location' => ['nullable', 'string', 'max:120'],
            'capacity' => ['nullable', 'string', Rule::in(array_keys(self::CAPACITY_RANGES))],
        ]);

        [$minCapacity, $maxCapacity] = self::CAPACITY_RANGES[$filters['capacity'] ?? ''] ?? [null, null];

        $facilities = Facility::search(
            $filters['q'] ?? null,
            $filters['type'] ?? null,
            $filters['location'] ?? null,
            $minCapacity,
        )
            ->when($maxCapacity !== null, fn ($query) => $query->where('capacity', '<=', $maxCapacity))
            ->orderByRaw('CASE status WHEN "aktif" THEN 0 ELSE 1 END')
            ->orderBy('name')
            ->get();

        return view('landing.index', [
            'facilities' => $facilities,
            'grids' => $facilities->mapWithKeys(fn (Facility $facility) => [$facility->id => $this->todaySlots($facility)]),
            'filters' => $filters,
            'typeLabels' => self::TYPE_LABELS,
            'locationOptions' => Facility::query()->orderBy('location')->distinct()->pluck('location'),
            'totalFacilities' => Facility::aktif()->count(),
            'today' => now(),
        ]);
    }

    /**
     * Grid 26 slot 07.00–20.00 (BR-1) untuk tanggal hari ini dengan status:
     * past (mulai sudah lewat), booked (ada reservasi approved yang overlap),
     * available, atau inactive (fasilitas tidak aktif — BR-12).
     */
    private function todaySlots(Facility $facility): array
    {
        $dayStart = now()->copy()->startOfDay()->setTime(self::OPERATIONAL_START_HOUR, 0);
        $dayEnd = $dayStart->copy()->addMinutes(self::SLOT_COUNT * 30);

        $approved = Reservation::approved()
            ->overlap($facility->id, $dayStart, $dayEnd)
            ->get(['start_time', 'end_time']);

        $slots = [];

        for ($i = 0; $i < self::SLOT_COUNT; $i++) {
            $start = $dayStart->copy()->addMinutes($i * 30);
            $end = $start->copy()->addMinutes(30);

            $booked = $approved->contains(
                fn ($reservation): bool => $reservation->start_time->lt($end) && $reservation->end_time->gt($start),
            );

            $slots[] = [
                'start' => $start->format('H.i'),
                'end' => $end->format('H.i'),
                'state' => $this->slotState($facility, $start, $booked),
            ];
        }

        return $slots;
    }

    private function slotState(Facility $facility, Carbon $start, bool $booked): string
    {
        if ($facility->status !== 'aktif') {
            return 'inactive';
        }

        if ($start->lte(now())) {
            return 'past';
        }

        return $booked ? 'booked' : 'available';
    }
}
