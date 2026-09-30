<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Services\ReservationAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Controller publik untuk katalog dan jadwal fasilitas
 */
class FacilityController extends Controller
{
    private const MAX_SCHEDULE_LOOKAHEAD_DAYS = 365;

    /** @var array<string, array{int, int}> */
    private const CAPACITY_RANGES = [
        'lt_40' => [1, 39],
        '40_100' => [40, 100],
        'gt_100' => [101, PHP_INT_MAX],
    ];

    public function __construct(
        protected ReservationAvailability $availability,
    ) {}

    /**
     * Menampilkan daftar fasilitas publik dengan filter pencarian:
     * - q: kata kunci nama fasilitas
     * - tipe: jenis/tipe fasilitas
     * - lokasi: lokasi fasilitas
     * - kapasitas_min: batas minimal kapasitas (wajib integer >= 1)
     */
    public function index(Request $request): View
    {
        // Mendukung parameter 'tipe' atau 'type' dari URL
        if ($request->filled('type') && ! $request->filled('tipe')) {
            $request->merge(['tipe' => $request->input('type')]);
        }

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'tipe' => ['nullable', 'string', Rule::in(array_keys(Facility::TYPES))],
            'lokasi' => ['nullable', 'string', 'max:100'],
            'kapasitas' => ['nullable', 'string', Rule::in(array_keys(self::CAPACITY_RANGES))],
            'kapasitas_min' => ['nullable', 'integer', 'min:1'],
            'from' => ['nullable', 'string', Rule::in(['home', 'all'])],
        ], [
            'kapasitas_min.integer' => 'Kapasitas minimal harus berupa angka bulat positif.',
            'kapasitas_min.min' => 'Kapasitas minimal tidak boleh kurang dari 1.',
            'tipe.in' => 'Tipe fasilitas yang dipilih tidak valid.',
            'q.max' => 'Kata kunci pencarian maksimal 100 karakter.',
            'lokasi.max' => 'Lokasi maksimal 100 karakter.',
        ]);

        [$minimumCapacity, $maximumCapacity] = self::CAPACITY_RANGES[$validated['kapasitas'] ?? '']
            ?? [isset($validated['kapasitas_min']) ? (int) $validated['kapasitas_min'] : null, null];

        $facilities = Facility::query()
            ->search(
                keyword: $validated['q'] ?? null,
                type: $validated['tipe'] ?? null,
                location: $validated['lokasi'] ?? null,
                minCapacity: $minimumCapacity,
            )
            ->when($maximumCapacity !== null, fn ($query) => $query->where('capacity', '<=', $maximumCapacity))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $locations = Facility::query()
            ->publicLocationOptions()
            ->pluck('location');

        return view('fasilitas.index', [
            'facilities' => $facilities,
            'types' => Facility::TYPES,
            'locations' => $locations,
            'filters' => $validated,
            'from' => $validated['from'] ?? 'home',
        ]);
    }

    /**
     * Menampilkan detail fasilitas umum (tanpa data pemohon)
     */
    public function show(Facility $facility): View
    {
        return view('fasilitas.show', [
            'facility' => $facility,
            'types' => Facility::TYPES,
        ]);
    }

    /**
     * Menampilkan jadwal ketersediaan slot fasilitas publik (Poin 7 & BR-1, BR-6, BR-13).
     * Keputusan slot dan overlap dimiliki ReservationAvailability (BR-6, BR-12).
     */
    public function jadwal(Request $request, Facility $facility): View
    {
        $today = Carbon::now(config('app.timezone'))->startOfDay();
        $validated = $request->validate([
            'date' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:'.$today->toDateString(),
                'before_or_equal:'.$today->copy()->addDays(self::MAX_SCHEDULE_LOOKAHEAD_DAYS)->toDateString(),
            ],
            'from' => ['nullable', 'string', Rule::in(['home', 'all'])],
        ]);

        $selectedDate = isset($validated['date'])
            ? Carbon::createFromFormat('!Y-m-d', $validated['date'], config('app.timezone'))
            : $today;

        $slots = $this->availability->publicScheduleSlots(
            $facility,
            $selectedDate,
            $this->availability->approvedForDay(
                $facility->id,
                $this->availability->dayStart($selectedDate),
                $this->availability->dayEnd($selectedDate),
            ),
        );

        return view('fasilitas.jadwal', [
            'facility' => $facility,
            'selectedDate' => $selectedDate,
            'slots' => $slots,
            'types' => Facility::TYPES,
            'from' => $validated['from'] ?? 'all',
        ]);
    }
}
