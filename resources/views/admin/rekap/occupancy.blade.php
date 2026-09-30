@extends('layouts.app')

@use('App\Models\Facility')

@section('title', 'Rekap Okupansi Fasilitas')

@section('content')
    @php
        $clayTableHead = 'border-b border-blue-100/80 bg-blue-50/50 px-5 py-3.5 text-[11px] font-extrabold uppercase tracking-[0.14em] text-blue-700';
        $clayTableCell = 'px-5 py-4 align-middle';
        $period = \Carbon\Carbon::parse($recap['date_range']['start'])->isoFormat('D MMMM Y')
            .' s/d '. \Carbon\Carbon::parse($recap['date_range']['end'])->isoFormat('D MMMM Y');
    @endphp

    <div class="dashboard-page space-y-5">
        <section class="auth-clay-card rounded-[2rem] p-5 sm:p-7 lg:p-8" aria-labelledby="occupancy-recap-heading">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Laporan &amp; Rekap</p>
                    <h1 id="occupancy-recap-heading"
                        class="mt-1 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">Rekap Okupansi
                        Fasilitas</h1>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600">Ringkasan tingkat penggunaan fasilitas
                        berdasarkan reservasi yang disetujui.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.rekap.occupancy.export.csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                        class="clay-button-white inline-flex min-h-11 items-center gap-2 rounded-full px-5 py-2.5 text-sm font-bold text-slate-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                        Ekspor CSV
                    </a>
                    <a href="{{ route('admin.rekap.occupancy.export.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                        class="landing-button inline-flex min-h-11 items-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-2.5 text-sm font-bold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M14 2v6h6" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M16 13H8" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M16 17H8" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M10 9H8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Ekspor PDF
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.rekap.occupancy') }}"
                class="mt-6 flex flex-wrap items-end gap-3 rounded-2xl border border-blue-100/70 bg-blue-50/40 p-4">
                <div class="min-w-48 flex-1">
                    <label for="start_date" class="block text-xs font-bold uppercase tracking-[0.12em] text-slate-600">Tanggal
                        Mulai</label>
                    <input id="start_date" name="start_date" type="date" value="{{ $startDate }}"
                        class="landing-input mt-1.5 block h-11 w-full rounded-2xl px-4 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                </div>
                <div class="min-w-48 flex-1">
                    <label for="end_date" class="block text-xs font-bold uppercase tracking-[0.12em] text-slate-600">Tanggal
                        Akhir</label>
                    <input id="end_date" name="end_date" type="date" value="{{ $endDate }}"
                        class="landing-input mt-1.5 block h-11 w-full rounded-2xl px-4 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                </div>
                <button type="submit"
                    class="landing-button inline-flex h-11 items-center justify-center rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-6 text-sm font-bold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)]">
                    Filter
                </button>
                <a href="{{ route('admin.rekap.occupancy') }}"
                    class="clay-button-white inline-flex h-11 items-center justify-center rounded-full px-5 text-sm font-semibold text-blue-700">
                    Reset
                </a>
            </form>
        </section>

        <section class="dashboard-clay-card landing-panel rounded-[2rem] p-5 sm:p-6" aria-labelledby="occupancy-summary-heading">
            <h2 id="occupancy-summary-heading" class="sr-only">Ringkasan okupansi</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <article class="dashboard-clay-stat clay-inset flex flex-col gap-3 rounded-2xl p-4">
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-500 text-white shadow-[0_6px_12px_rgba(37,99,235,0.25),inset_0_1px_0_rgba(255,255,255,0.45)]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-semibold text-slate-600">Total Fasilitas</p>
                        <p class="mt-1 text-2xl font-extrabold leading-none tracking-tight text-slate-900">
                            {{ $recap['summary']['total_facilities'] }}</p>
                    </div>
                </article>
                <article class="dashboard-clay-stat clay-inset flex flex-col gap-3 rounded-2xl p-4">
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-500 text-white shadow-[0_6px_12px_rgba(79,70,229,0.24),inset_0_1px_0_rgba(255,255,255,0.45)]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7 3h7l5 5v13H5V3h2Zm6 1v5h5M8 13h8M8 17h8" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-semibold text-slate-600">Total Reservasi</p>
                        <p class="mt-1 text-2xl font-extrabold leading-none tracking-tight text-slate-900">
                            {{ $recap['summary']['total_reservations'] }}</p>
                    </div>
                </article>
                <article class="dashboard-clay-stat clay-inset flex flex-col gap-3 rounded-2xl p-4">
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-[0_6px_12px_rgba(16,185,129,0.24),inset_0_1px_0_rgba(255,255,255,0.45)]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 5 5L20 7" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-semibold text-slate-600">Disetujui</p>
                        <p class="mt-1 text-2xl font-extrabold leading-none tracking-tight text-slate-900">
                            {{ $recap['summary']['total_approved'] }}</p>
                    </div>
                </article>
                <article class="dashboard-clay-stat clay-inset flex flex-col gap-3 rounded-2xl p-4">
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-[0_6px_12px_rgba(245,158,11,0.25),inset_0_1px_0_rgba(255,255,255,0.45)]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v6l3.5 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-semibold text-slate-600">Pending</p>
                        <p class="mt-1 text-2xl font-extrabold leading-none tracking-tight text-slate-900">
                            {{ $recap['summary']['total_pending'] }}</p>
                    </div>
                </article>
                <article class="dashboard-clay-stat clay-inset flex flex-col gap-3 rounded-2xl p-4">
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-cyan-500 text-white shadow-[0_6px_12px_rgba(6,182,212,0.24),inset_0_1px_0_rgba(255,255,255,0.45)]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" d="M12 7v5l3 2" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-semibold text-slate-600">Jam Disetujui</p>
                        <p class="mt-1 text-2xl font-extrabold leading-none tracking-tight text-slate-900">
                            {{ number_format($recap['summary']['total_approved_hours'], 2) }}</p>
                    </div>
                </article>
                <article class="dashboard-clay-stat clay-inset flex flex-col gap-3 rounded-2xl p-4">
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-rose-500 text-white shadow-[0_6px_12px_rgba(244,63,94,0.24),inset_0_1px_0_rgba(255,255,255,0.45)]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 13h4l3 8 4-16 3 8h4" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-semibold text-slate-600">Rata-rata Okupansi</p>
                        <p class="mt-1 text-2xl font-extrabold leading-none tracking-tight text-slate-900">
                            {{ $recap['summary']['average_occupancy_rate'] }}%</p>
                    </div>
                </article>
            </div>
        </section>

        <section class="auth-clay-card rounded-[2rem] p-5 sm:p-7" aria-labelledby="occupancy-table-heading">
            <h2 id="occupancy-table-heading" class="text-lg font-extrabold tracking-tight text-[#10264a]">Okupansi per
                fasilitas</h2>
            <div class="clay-inset mt-4 overflow-hidden rounded-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-full text-left text-sm text-slate-600">
                        <thead>
                            <tr>
                                <th scope="col" class="{{ $clayTableHead }}">Fasilitas</th>
                                <th scope="col" class="{{ $clayTableHead }}">Tipe</th>
                                <th scope="col" class="{{ $clayTableHead }}">Lokasi</th>
                                <th scope="col" class="{{ $clayTableHead }} text-right">Kapasitas</th>
                                <th scope="col" class="{{ $clayTableHead }}">Status</th>
                                <th scope="col" class="{{ $clayTableHead }} text-right">Disetujui</th>
                                <th scope="col" class="{{ $clayTableHead }} text-right">Pending</th>
                                <th scope="col" class="{{ $clayTableHead }} text-right">Ditolak</th>
                                <th scope="col" class="{{ $clayTableHead }} text-right">Dibatalkan</th>
                                <th scope="col" class="{{ $clayTableHead }} text-right">Total</th>
                                <th scope="col" class="{{ $clayTableHead }} text-right">Jam Disetujui</th>
                                <th scope="col" class="{{ $clayTableHead }} text-right">Max Jam</th>
                                <th scope="col" class="{{ $clayTableHead }} text-right">Okupansi (%)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-100/70">
                            @forelse ($recap['data'] as $item)
                                <tr class="transition-colors hover:bg-white/70">
                                    <td class="{{ $clayTableCell }} font-bold text-[#10264a]">
                                        {{ $item['facility_name'] }}</td>
                                    <td class="{{ $clayTableCell }} text-slate-700">
                                        {{ \App\Models\Facility::TYPE_LABELS[$item['facility_type']] ?? $item['facility_type'] }}</td>
                                    <td class="{{ $clayTableCell }} text-slate-700">{{ $item['facility_location'] }}</td>
                                    <td class="{{ $clayTableCell }} text-right text-slate-700">{{ $item['capacity'] }}</td>
                                    <td class="{{ $clayTableCell }}">
                                        <x-ui.badge :status="$item['status']" dot />
                                    </td>
                                    <td class="{{ $clayTableCell }} text-right text-emerald-700">
                                        {{ $item['approved_count'] }}</td>
                                    <td class="{{ $clayTableCell }} text-right text-amber-700">
                                        {{ $item['pending_count'] }}</td>
                                    <td class="{{ $clayTableCell }} text-right text-rose-700">
                                        {{ $item['rejected_count'] }}</td>
                                    <td class="{{ $clayTableCell }} text-right text-slate-600">
                                        {{ $item['cancelled_count'] }}</td>
                                    <td class="{{ $clayTableCell }} text-right font-extrabold text-[#10264a]">
                                        {{ $item['total_reservations'] }}</td>
                                    <td class="{{ $clayTableCell }} text-right text-slate-700">
                                        {{ number_format($item['total_approved_hours'], 2) }}</td>
                                    <td class="{{ $clayTableCell }} text-right text-slate-700">
                                        {{ $item['max_possible_hours'] }}</td>
                                    <td class="{{ $clayTableCell }} text-right">
                                        <span
                                            class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700 ring-1 ring-inset ring-sky-200">
                                            {{ $item['occupancy_rate'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="px-5 py-14 text-center">
                                        <span
                                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-white bg-white text-2xl text-blue-600 shadow-[0_4px_10px_rgba(59,130,246,0.15)]">
                                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                            </svg>
                                        </span>
                                        <p class="mt-4 text-base font-bold text-slate-800">Tidak ada data fasilitas</p>
                                        <p class="mt-1 text-sm text-slate-500">Coba ubah rentang tanggal filter.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="border-t-2 border-blue-200/80 bg-blue-50/60">
                            <tr class="font-extrabold text-[#10264a]">
                                <th scope="row" class="{{ $clayTableCell }} text-left">TOTAL</th>
                                <td class="{{ $clayTableCell }}"></td>
                                <td class="{{ $clayTableCell }}"></td>
                                <td class="{{ $clayTableCell }}"></td>
                                <td class="{{ $clayTableCell }}"></td>
                                <td class="{{ $clayTableCell }} text-right">{{ $recap['summary']['total_approved'] }}</td>
                                <td class="{{ $clayTableCell }} text-right">{{ $recap['summary']['total_pending'] }}</td>
                                <td class="{{ $clayTableCell }} text-right">{{ $recap['summary']['total_rejected'] }}</td>
                                <td class="{{ $clayTableCell }} text-right">{{ $recap['summary']['total_cancelled'] }}</td>
                                <td class="{{ $clayTableCell }} text-right">{{ $recap['summary']['total_reservations'] }}</td>
                                <td class="{{ $clayTableCell }} text-right">
                                    {{ number_format($recap['summary']['total_approved_hours'], 2) }}</td>
                                <td class="{{ $clayTableCell }} text-right"></td>
                                <td class="{{ $clayTableCell }} text-right">
                                    <span
                                        class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700 ring-1 ring-inset ring-sky-200">
                                        {{ $recap['summary']['average_occupancy_rate'] }}%
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </section>

        <p class="text-center text-xs text-slate-500">Periode: {{ $period }}</p>
    </div>
@endsection
