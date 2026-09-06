<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelReservationOfficerRequest;
use App\Http\Requests\RejectReservationRequest;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Antrian reservasi petugas (§11.1, US-*).
 *
 * index menampilkan antrean beserta filter status/tanggal; approve,
 * reject, dan cancel mendelegasikan mutasi status ke ReservationService
 * yang memegang seluruh aturan slot, bentrok, dan kunci transaksi.
 */
class ReservationController extends Controller
{
    public const STATUSES = [
        'pending',
        'approved',
        'rejected',
        'cancelled_by_user',
        'cancelled_by_officer',
    ];

    public function __construct(private readonly ReservationService $reservations) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', 'in:'.implode(',', self::STATUSES)],
            'date' => ['nullable', 'date'],
        ]);

        $reservations = Reservation::query()
            ->with(['user', 'facility', 'decidedBy'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['date'] ?? null, fn ($query, string $date) => $query->whereDate('start_time', $date))
            ->orderByRaw('CASE status WHEN "pending" THEN 0 ELSE 1 END')
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        return view('petugas.reservasi.index', [
            'reservations' => $reservations,
            'filters' => $filters,
        ]);
    }

    public function show(Reservation $reservation): View
    {
        $reservation->load(['user', 'facility', 'decidedBy']);

        return view('petugas.reservasi.show', ['reservation' => $reservation]);
    }

    /**
     * Setujui reservasi pending (BR-7).
     *
     * Cek bentrok dan kunci transaksi tinggal di ReservationService;
     * bentrok balapan dikembalikan sebagai 409 dan diterjemahkan ke
     * flash error di sini.
     */
    public function approve(Request $request, Reservation $reservation): RedirectResponse
    {
        try {
            $this->reservations->approve($reservation->fresh() ?? $reservation, $request->user());
        } catch (ConflictHttpException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Reservasi disetujui dan slot terkunci.');
    }

    public function reject(RejectReservationRequest $request, Reservation $reservation): RedirectResponse
    {
        try {
            $this->reservations->reject($reservation->fresh() ?? $reservation, $request->user(), $request->validated('reason'));
        } catch (ConflictHttpException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Reservasi ditolak.');
    }

    public function cancel(CancelReservationOfficerRequest $request, Reservation $reservation): RedirectResponse
    {
        try {
            $this->reservations->cancel($reservation->fresh() ?? $reservation, $request->user(), $request->validated('cancel_reason'));
        } catch (ConflictHttpException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Reservasi dibatalkan.');
    }
}
