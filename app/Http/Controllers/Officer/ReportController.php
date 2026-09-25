<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateReportStatusRequest;
use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Menampilkan daftar antrian laporan kerusakan untuk petugas/admin.
     *
     * Tab "menunggu" memuat laporan aktif (baru/diproses) dan tab "selesai"
     * memuat laporan tertutup (selesai/ditolak); filter status menimpa tab.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $tab = $request->query('tab');
        $validStatuses = ['baru', 'diproses', 'selesai', 'ditolak'];
        $tabs = [
            'menunggu' => ['baru', 'diproses'],
            'selesai' => ['selesai', 'ditolak'],
        ];

        $query = Report::with(['facility', 'user']);

        if (! in_array($status, $validStatuses, true)) {
            $status = null;

            if (isset($tabs[$tab])) {
                $query->whereIn('status', $tabs[$tab]);
            } else {
                $tab = null;
            }
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        $reports = $query
            ->orderByRaw('CASE status WHEN "baru" THEN 0 WHEN "diproses" THEN 1 WHEN "selesai" THEN 2 WHEN "ditolak" THEN 3 ELSE 4 END')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $counts = [
            'total' => Report::count(),
            'baru' => Report::where('status', 'baru')->count(),
            'diproses' => Report::where('status', 'diproses')->count(),
            'selesai' => Report::where('status', 'selesai')->count(),
            'ditolak' => Report::where('status', 'ditolak')->count(),
            'menunggu' => Report::whereIn('status', ['baru', 'diproses'])->count(),
            'selesai_count' => Report::whereIn('status', ['selesai', 'ditolak'])->count(),
        ];

        return view('petugas.laporan.index', compact('reports', 'status', 'counts', 'tab'));
    }

    /**
     * Menampilkan detail laporan kerusakan dan form tindakan petugas.
     */
    public function show(Report $report): View
    {
        Gate::authorize('view', $report);

        $report->load(['facility', 'user', 'updates.user', 'handledBy']);

        $allowedTransitions = ReportService::allowedTransitions($report->status);

        return view('petugas.laporan.show', compact('report', 'allowedTransitions'));
    }

    /**
     * Memperbarui status laporan kerusakan (BR-10).
     */
    public function updateStatus(UpdateReportStatusRequest $request, Report $report): RedirectResponse
    {
        try {
            $newStatus = $request->validated('status');
            $note = $request->validated('resolution_note');

            $this->reportService->transition($report, $request->user(), $newStatus, $note);

            return redirect()->route('petugas.laporan.show', $report)
                ->with('success', "Status laporan berhasil diperbarui menjadi {$newStatus}.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Menandai atau mengembalikan status fasilitas perbaikan (BR-11).
     */
    public function toggleFacilityStatus(Request $request, Report $report): RedirectResponse
    {
        $action = $request->input('action');

        try {
            if ($action === 'perbaikan') {
                $this->reportService->markFacilityForRepair($report, $request->user());
                $message = 'Fasilitas berhasil ditandai sedang dalam perbaikan.';
            } elseif ($action === 'aktif') {
                $this->reportService->restoreFacilityToActive($report, $request->user());
                $message = 'Fasilitas berhasil dikembalikan ke status aktif.';
            } else {
                return back()->with('error', 'Aksi status fasilitas tidak valid.');
            }

            return redirect()->route('petugas.laporan.show', $report)->with('success', $message);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }
}
