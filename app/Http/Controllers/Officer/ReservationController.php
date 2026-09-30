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
    public function __construct(private readonly ReservationService $reservations) {}

    public function index(Request $request): View
    {
        $filters = $this->filters($request);

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
            ->orderByRaw($this->statusOrderSql())
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
        $filters = $this->filters($request);

        $this->reservations->expireStale();

        $reservations = $this->filteredQuery($filters)
            ->orderBy('created_at', 'desc')
            ->orderBy('start_time', 'asc')
            ->orderByRaw($this->statusOrderSql())
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
     * Filter yang sah untuk antrean: status dari kosakata Reservation dan tab
     * rekapitulasi. Tanpa status, antrean dibuka pada tab "menunggu".
     *
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', 'in:'.implode(',', Reservation::ORDERED_STATUSES)],
            'date' => ['nullable', 'date'],
            'tab' => ['nullable', 'string', 'in:'.implode(',', array_keys($this->tabs()))],
        ]);

        if (($filters['status'] ?? null) === null) {
            $filters['tab'] ??= 'menunggu';
        }

        return $filters;
    }

    /**
     * Tab antrean: "menunggu" hanya yang pending, "selesai" seluruh status
     * lain mengikuti urutan kosakata.
     *
     * @return array<string, list<string>>
     */
    private function tabs(): array
    {
        return [
            'menunggu' => ['pending'],
            'selesai' => array_values(array_diff(Reservation::ORDERED_STATUSES, ['pending'])),
        ];
    }

    /**
     * Urutan antrean sebagai SQL, diturunkan dari kosakata Reservation
     * supaya daftar status tidak pernah ditulis dua kali.
     */
    private function statusOrderSql(): string
    {
        $arms = [];

        foreach (Reservation::ORDERED_STATUSES as $position => $status) {
            $arms[] = 'WHEN "'.$status.'" THEN '.$position;
        }

        return 'CASE status '.implode(' ', $arms).' ELSE '.count(Reservation::ORDERED_STATUSES).' END';
    }

    /**
     * Query reservasi dengan filter status, tanggal, dan tab.
     * Filter status bersifat menimpa tab agar tidak menghasilkan irisan kosong.
     */
    private function filteredQuery(array $filters): Builder
    {
        $tabs = $this->tabs();
        $tab = ($filters['status'] ?? null) === null ? ($filters['tab'] ?? null) : null;

        return Reservation::query()
            ->with(['user', 'facility', 'decidedBy'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($tab !== null, fn ($query) => $query->whereIn('status', $tabs[$tab]))
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
            $autoRejected = $this->reservations->approve($reservation->fresh() ?? $reservation, $request->user());
        } catch (ConflictHttpException $exception) {
            return back()->with('error', $exception->getMessage());
        }

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
