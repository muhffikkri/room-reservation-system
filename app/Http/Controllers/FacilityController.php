<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Controller publik untuk katalog dan jadwal fasilitas
 */
class FacilityController extends Controller
{
    /**
     * Jam buka operasional (07:00).
     */
    public const OPERATIONAL_START_HOUR = 7;

    /**
     * Jumlah slot 30 menit per hari (07.00 - 20.00 = 26 slot).
     */
    public const SLOT_COUNT = 26;

    /**
     * Tipe fasilitas valid beserta label yang ramah pengguna.
     *
     * @var array<string, string>
     */
    public const FACILITY_TYPES = [
        'ruang_kelas' => 'Ruang Kelas',
        'aula' => 'Aula',
        'laboratorium' => 'Laboratorium',
        'alat' => 'Alat',
        'lapangan' => 'Lapangan',
    ];

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
            'tipe' => ['nullable', 'string', Rule::in(array_keys(self::FACILITY_TYPES))],
            'lokasi' => ['nullable', 'string', 'max:100'],
            'kapasitas_min' => ['nullable', 'integer', 'min:1'],
        ], [
            'kapasitas_min.integer' => 'Kapasitas minimal harus berupa angka bulat positif.',
            'kapasitas_min.min' => 'Kapasitas minimal tidak boleh kurang dari 1.',
            'tipe.in' => 'Tipe fasilitas yang dipilih tidak valid.',
            'q.max' => 'Kata kunci pencarian maksimal 100 karakter.',
            'lokasi.max' => 'Lokasi maksimal 100 karakter.',
        ]);

        $facilities = Facility::query()
            ->search(
                keyword: $validated['q'] ?? null,
                type: $validated['tipe'] ?? null,
                location: $validated['lokasi'] ?? null,
                minCapacity: isset($validated['kapasitas_min']) ? (int) $validated['kapasitas_min'] : null,
            )
            ->orderBy('name')
            ->get();

        $locations = Facility::query()
            ->select('location')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        return view('fasilitas.index', [
            'facilities' => $facilities,
            'types' => self::FACILITY_TYPES,
            'locations' => $locations,
            'filters' => $validated,
        ]);
    }

    /**
     * Menampilkan detail fasilitas umum (tanpa data pemohon)
     */
    public function show(Facility $facility): View
    {
        return view('fasilitas.show', [
            'facility' => $facility,
            'types' => self::FACILITY_TYPES,
        ]);
    }

    /**
     * Menampilkan jadwal ketersediaan slot fasilitas publik (Poin 7 & BR-1, BR-6, BR-13).
     * Memanfaatkan scopeOverlap pada model Reservation (Poin 3).
     */
    public function jadwal(Request $request, Facility $facility): View
    {
        $dateInput = $request->string('date')->trim()->toString();
        $selectedDate = $dateInput !== '' && strtotime($dateInput) !== false
            ? Carbon::parse($dateInput)->startOfDay()
            : now()->startOfDay();

        $slots = $this->buildSlotsForDate($facility, $selectedDate);

        return view('fasilitas.jadwal', [
            'facility' => $facility,
            'selectedDate' => $selectedDate,
            'slots' => $slots,
            'types' => self::FACILITY_TYPES,
        ]);
    }

    /**
     * Menghitung status 26 slot 30 menit pada tanggal yang dipilih.
     * Menggunakan scopeOverlap pada model Reservation (Poin 3).
     *
     * @return array<int, array{start: string, end: string, state: string}>
     */
    private function buildSlotsForDate(Facility $facility, Carbon $selectedDate): array
    {
        $dayStart = $selectedDate->copy()->setTime(self::OPERATIONAL_START_HOUR, 0);
        $dayEnd = $dayStart->copy()->addMinutes(self::SLOT_COUNT * 30);

        // Hanya reservasi approved yang memblokir slot (BR-6) menggunakan scopeOverlap (Poin 3)
        $approvedReservations = Reservation::approved()
            ->overlap($facility->id, $dayStart, $dayEnd)
            ->get(['start_time', 'end_time']);

        $slots = [];

        for ($i = 0; $i < self::SLOT_COUNT; $i++) {
            $slotStart = $dayStart->copy()->addMinutes($i * 30);
            $slotEnd = $slotStart->copy()->addMinutes(30);

            $isBooked = $approvedReservations->contains(
                fn (Reservation $reservation): bool => $reservation->start_time->lt($slotEnd) && $reservation->end_time->gt($slotStart),
            );

            $slots[] = [
                'start' => $slotStart->format('H:i'),
                'end' => $slotEnd->format('H:i'),
                'state' => $this->determineSlotState($facility, $slotStart, $isBooked),
            ];
        }

        return $slots;
    }

    /**
     * Menentukan state suatu slot: inactive, past, booked, atau available.
     */
    private function determineSlotState(Facility $facility, Carbon $slotStart, bool $isBooked): string
    {
        if ($facility->status !== 'aktif') {
            return 'inactive';
        }

        if ($slotStart->isPast()) {
            return 'past';
        }

        return $isBooked ? 'booked' : 'available';
    }
}
