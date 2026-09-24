<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\Facility;
use App\Models\Reservation;
use App\Services\ReservationAvailability;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ReservationController extends Controller
{
    private const MAX_BOOKING_LOOKAHEAD_DAYS = 365;

    public function __construct(
        protected ReservationService $reservationService,
        protected ReservationAvailability $availability,
    ) {}

    /**
     * Menampilkan riwayat reservasi milik pengguna yang sedang login.
     */
    public function index(Request $request): View
    {
        $status = $request->string('status')->trim()->toString();

        $reservations = Reservation::with(['facility'])
            ->where('user_id', auth()->id())
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('reservasi.index', compact('reservations', 'status'));
    }

    /**
     * Menampilkan formulir pembuatan reservasi baru.
     */
    public function create(Request $request): View
    {
        $facilities = Facility::aktif()->orderBy('name')->get();

        $facilityId = $request->integer('facility_id');
        $selectedFacility = $facilities->firstWhere('id', $facilityId) ?? $facilities->first();

        $today = Carbon::now(config('app.timezone'))->startOfDay();
        $validated = $request->validate([
            'date' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:'.$today->toDateString(),
                'before_or_equal:'.$today->copy()->addDays(self::MAX_BOOKING_LOOKAHEAD_DAYS)->toDateString(),
            ],
        ]);

        $selectedDate = isset($validated['date'])
            ? Carbon::createFromFormat('!Y-m-d', $validated['date'], config('app.timezone'))
            : $today;

        $slots = $selectedFacility ? $this->bookingSlotsForDate($selectedFacility, $selectedDate) : [];

        return view('reservasi.create', [
            'facilities' => $facilities,
            'selectedFacility' => $selectedFacility,
            'selectedDate' => $selectedDate,
            'slots' => $slots,
            'timeOptions' => $this->availability->timeOptions(),
            'maxDurationSlots' => $this->availability->maxDurationSlots(),
        ]);
    }

    /**
     * Menyimpan reservasi baru berstatus pending.
     */
    public function store(StoreReservationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $facility = Facility::findOrFail($validated['facility_id']);

        $timezone = config('app.timezone', 'Asia/Jakarta');
        $start = Carbon::parse("{$validated['date']} {$validated['start_time']}", $timezone);
        $end = Carbon::parse("{$validated['date']} {$validated['end_time']}", $timezone);

        try {
            $reservation = $this->reservationService->create(
                $request->user(),
                $facility,
                $start,
                $end,
                $validated['purpose']
            );

            return redirect()->route('reservasi.show', $reservation)
                ->with('success', 'Reservasi berhasil diajukan dan sedang menunggu persetujuan petugas.');
        } catch (ConflictHttpException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }
    }

    /**
     * Menampilkan detail informasi reservasi milik pengguna.
     */
    public function show(Reservation $reservation): View
    {
        Gate::authorize('view', $reservation);

        $reservation->load(['facility', 'decidedBy']);

        return view('reservasi.show', compact('reservation'));
    }

    /**
     * Membatalkan reservasi oleh pengguna (BR-8).
     */
    public function destroy(Request $request, Reservation $reservation): RedirectResponse
    {
        if ($reservation->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk membatalkan reservasi ini.');
        }

        $validated = $request->validate([
            'cancel_reason' => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'cancel_reason.required' => 'Alasan pembatalan wajib diisi.',
            'cancel_reason.min' => 'Alasan pembatalan minimal 5 karakter.',
        ]);

        $cancelReason = strip_tags($validated['cancel_reason']);

        try {
            $this->reservationService->cancelByUser($reservation, auth()->user(), $cancelReason);

            return redirect()->route('reservasi.show', $reservation)
                ->with('success', 'Reservasi berhasil dibatalkan.');
        } catch (ConflictHttpException|AccessDeniedHttpException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Proyeksi slot untuk formulir pemesanan (BR-1..BR-3). Keputusan dimiliki
     * ReservationAvailability; controller hanya menyiapkan konteks tanggal.
     *
     * @return array<int, array{start: string, end: string, state: string}>
     */
    private function bookingSlotsForDate(Facility $facility, Carbon $selectedDate): array
    {
        return $this->availability->bookingSlots(
            $facility,
            $selectedDate,
            $this->availability->approvedForDay(
                $facility->id,
                $this->availability->dayStart($selectedDate),
                $this->availability->dayEnd($selectedDate),
            ),
        );
    }
}
