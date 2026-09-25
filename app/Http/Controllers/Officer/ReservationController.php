<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelReservationOfficerRequest;
use App\Http\Requests\RejectReservationRequest;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
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
        'rejected_by_system',
        'cancelled_by_user',
        'cancelled_by_officer',
        'cancelled_by_system',
    ];

    public const TABS = [
        'menunggu' => ['pending'],
        'selesai' => ['approved', 'rejected', 'rejected_by_system', 'cancelled_by_user', 'cancelled_by_officer', 'cancelled_by_system'],
    ];

    public const STATUS_ORDER = 'CASE status WHEN "pending" THEN 0 WHEN "approved" THEN 1 WHEN "rejected" THEN 2 WHEN "rejected_by_system" THEN 3 WHEN "cancelled_by_user" THEN 4 WHEN "cancelled_by_officer" THEN 5 WHEN "cancelled_by_system" THEN 6 ELSE 7 END';

    public function __construct(private readonly ReservationService $reservations) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', 'in:'.implode(',', self::STATUSES)],
            'date' => ['nullable', 'date'],
            'tab' => ['nullable', 'string', 'in:'.implode(',', array_keys(self::TABS))],
        ]);

        $expired = $this->reservations->expireStale();

        if ($expired > 0) {
            $request->session()->flash(
                'info',
                "{$expired} reservasi otomatis dibatalkan sistem karena melewati batas persetujuan."
            );
        }

        $reservations = $this->filteredQuery($filters)
            ->orderBy('created_at', 'desc')
            ->orderBy('start_time', 'asc')
            ->orderByRaw(self::STATUS_ORDER)
            ->paginate(15)
            ->withQueryString();

        return view('petugas.reservasi.index', [
            'reservations' => $reservations,
            'filters' => $filters,
            'tab' => $filters['tab'] ?? null,
        ]);
    }

    public function ajaxIndex(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', 'in:'.implode(',', self::STATUSES)],
            'date' => ['nullable', 'date'],
            'tab' => ['nullable', 'string', 'in:'.implode(',', array_keys(self::TABS))],
        ]);

        $this->reservations->expireStale();

        $reservations = $this->filteredQuery($filters)
            ->orderBy('created_at', 'desc')
            ->orderBy('start_time', 'asc')
            ->orderByRaw(self::STATUS_ORDER)
            ->paginate(15);

        return response()->json([
            'reservations' => $reservations->items(),
            'pagination' => [
                'current_page' => $reservations->currentPage(),
                'last_page' => $reservations->lastPage(),
                'per_page' => $reservations->perPage(),
                'total' => $reservations->total(),
                'from' => $reservations->firstItem(),
                'to' => $reservations->lastItem(),
            ],
        ]);
    }

    /**
     * Query reservasi dengan filter status, tanggal, dan tab.
     * Filter status bersifat menimpa tab agar tidak menghasilkan irisan kosong.
     */
    private function filteredQuery(array $filters): Builder
    {
        $tab = ($filters['status'] ?? null) === null ? ($filters['tab'] ?? null) : null;

        return Reservation::query()
            ->with(['user', 'facility', 'decidedBy'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($tab !== null, fn ($query) => $query->whereIn('status', self::TABS[$tab]))
            ->when($filters['date'] ?? null, fn ($query, string $date) => $query->whereDate('start_time', $date));
    }

    public function show(Request $request, Reservation $reservation): View
    {
        $expired = $this->reservations->expireStale();

        if ($expired > 0) {
            $request->session()->flash(
                'info',
                "{$expired} reservasi otomatis dibatalkan sistem karena melewati batas persetujuan."
            );
        }

        $reservation->refresh();
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

        $autoRejected = $this->reservations->autoRejectedOnApprove();
        $message = $autoRejected > 0
            ? "Reservasi disetujui dan slot terkunci. {$autoRejected} reservasi lain otomatis ditolak karena overlap."
            : 'Reservasi disetujui dan slot terkunci.';

        return back()->with('success', $message);
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
            $cancelReason = strip_tags($request->validated('cancel_reason'));
            $this->reservations->cancel($reservation->fresh() ?? $reservation, $request->user(), $cancelReason);
        } catch (ConflictHttpException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Reservasi dibatalkan.');
    }
}
