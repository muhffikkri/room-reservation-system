<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Facility;
use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Menampilkan daftar laporan kerusakan milik pengguna yang sedang login.
     */
    public function index(): View
    {
        $reports = Report::with('facility')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('laporan.index', compact('reports'));
    }

    /**
     * Menampilkan formulir pembuatan laporan kerusakan baru.
     */
    public function create(): View
    {
        $facilities = Facility::aktif()->orderBy('name')->get();

        return view('laporan.create', compact('facilities'));
    }

    /**
     * Menyimpan laporan kerusakan baru.
     */
    public function store(StoreReportRequest $request): RedirectResponse
    {
        $report = $this->reportService->createReport($request->user(), $request->validated());

        return redirect()->route('laporan.show', $report)
            ->with('success', 'Laporan kerusakan berhasil dibuat.');
    }

    /**
     * Menampilkan detail laporan kerusakan beserta riwayat pembaharuannya.
     */
    public function show(Report $report): View
    {
        if ($report->user_id !== auth()->id() && ! in_array(auth()->user()->role, ['petugas', 'admin'], true)) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }

        $report->load(['facility', 'updates.user', 'handledBy']);

        return view('laporan.show', compact('report'));
    }
}
