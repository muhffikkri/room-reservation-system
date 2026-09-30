@extends('layouts.app')

@use('App\Models\Facility')
@use('App\Models\Report')

@section('title', 'Rekap Frekuensi Kerusakan Fasilitas')

@section('content')
    @php
        $clayTableHead = 'border-b border-blue-100/80 bg-blue-50/50 px-5 py-3.5 text-[11px] font-extrabold uppercase tracking-[0.14em] text-blue-700';
        $clayTableCell = 'px-5 py-4 align-middle';
        $period = \Carbon\Carbon::parse($recap['date_range']['start'])->isoFormat('D MMMM Y')
            .' s/d '. \Carbon\Carbon::parse($recap['date_range']['end'])->isoFormat('D MMMM Y');
    @endphp

    <div class="dashboard-page space-y-5">
        <section class="auth-clay-card rounded-[2rem] p-5 sm:p-7 lg:p-8" aria-labelledby="damage-recap-heading">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Laporan &amp; Rekap</p>
                    <h1 id="damage-recap-heading"
                        class="mt-0.5 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">Rekap Frekuensi
                        Kerusakan Fasilitas</h1>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600">Ringkasan laporan kerusakan per fasilitas dan
                        kategori kerusakan.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.rekap.damage.export.csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                        class="inline-flex min-h-11 items-center gap-2 rounded-full border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white px-5 py-2.5 text-sm font-bold text-emerald-700 transition hover:from-emerald-100 hover:to-white">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                        Ekspor CSV
                    </a>
                    <a href="{{ route('admin.rekap.damage.export.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                        class="clay-button-danger inline-flex min-h-11 items-center gap-2 rounded-full px-5 py-2.5 text-sm font-bold">
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

            <form method="GET" action="{{ route('admin.rekap.damage') }}"
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
                    Terapkan Filter
                </button>
                <a href="{{ route('admin.rekap.damage') }}"
                    class="clay-pressable inline-flex h-11 items-center justify-center rounded-full px-5 text-sm font-semibold text-blue-700">
                    Reset
                </a>
            </form>
        </section>

        <section class="dashboard-clay-card landing-panel rounded-[2rem] p-5 sm:p-6" aria-labelledby="damage-summary-heading">
            <h2 id="damage-summary-heading" class="text-lg font-extrabold tracking-tight text-[#10264a]">Ringkasan periode
                ini</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <article class="dashboard-clay-stat clay-inset flex items-center gap-4 rounded-2xl p-4">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-500 text-white shadow-[0_6px_12px_rgba(37,99,235,0.25),inset_0_1px_0_rgba(255,255,255,0.45)]">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-600">Fasilitas Dilaporkan</p>
                        <p class="mt-1 text-3xl font-extrabold leading-none tracking-tight text-slate-900">
                            {{ $recap['summary']['total_facilities_with_reports'] }}</p>
                    </div>
                </article>
                <article class="dashboard-clay-stat clay-inset flex items-center gap-4 rounded-2xl p-4">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-500 text-white shadow-[0_6px_12px_rgba(79,70,229,0.24),inset_0_1px_0_rgba(255,255,255,0.45)]">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7 3h7l5 5v13H5V3h2Zm6 1v5h5M8 13h8M8 17h8" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-600">Total Laporan</p>
                        <p class="mt-1 text-3xl font-extrabold leading-none tracking-tight text-slate-900">
                            {{ $recap['summary']['total_reports'] }}</p>
                    </div>
                </article>
                <article class="dashboard-clay-stat clay-inset flex items-center gap-4 rounded-2xl p-4">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-cyan-500 text-white shadow-[0_6px_12px_rgba(6,182,212,0.24),inset_0_1px_0_rgba(255,255,255,0.45)]">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" d="M12 7v5l3 2" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-600">Baru</p>
                        <p class="mt-1 text-3xl font-extrabold leading-none tracking-tight text-slate-900">
                            {{ $recap['summary']['total_baru'] }}</p>
                    </div>
                </article>
                <article class="dashboard-clay-stat clay-inset flex items-center gap-4 rounded-2xl p-4">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-[0_6px_12px_rgba(245,158,11,0.25),inset_0_1px_0_rgba(255,255,255,0.45)]">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.7 6.3a5 5 0 0 0-6.4 6.4L3 18l3 3 5.3-5.3a5 5 0 0 0 6.4-6.4L14 13l-3-3 3.7-3.7Z" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-600">Diproses</p>
                        <p class="mt-1 text-3xl font-extrabold leading-none tracking-tight text-slate-900">
                            {{ $recap['summary']['total_diproses'] }}</p>
                    </div>
                </article>
                <article class="dashboard-clay-stat clay-inset flex items-center gap-4 rounded-2xl p-4">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-[0_6px_12px_rgba(16,185,129,0.24),inset_0_1px_0_rgba(255,255,255,0.45)]">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 5 5L20 7" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-600">Selesai</p>
                        <p class="mt-1 text-3xl font-extrabold leading-none tracking-tight text-slate-900">
                            {{ $recap['summary']['total_selesai'] }}</p>
                    </div>
                </article>
            </div>
        </section>

        <section class="auth-clay-card rounded-[2rem] p-5 sm:p-7" aria-labelledby="damage-facility-table-heading">
            <h2 id="damage-facility-table-heading" class="text-lg font-extrabold tracking-tight text-[#10264a]">Laporan
                per fasilitas</h2>
            <div class="clay-inset mt-4 overflow-hidden rounded-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-full text-left text-sm text-slate-600">
                        <thead>
                            <tr>
                                <th scope="col" class="{{ $clayTableHead }}">Fasilitas</th>
                                <th scope="col" class="{{ $clayTableHead }}">Tipe</th>
                                <th scope="col" class="{{ $clayTableHead }}">Lokasi</th>
                                <th scope="col" class="{{ $clayTableHead }}">Status</th>
                                <th scope="col" class="{{ $clayTableHead }} text-right">Baru</th>
                                <th scope="col" class="{{ $clayTableHead }} text-right">Diproses</th>
                                <th scope="col" class="{{ $clayTableHead }} text-right">Selesai</th>
                                <th scope="col" class="{{ $clayTableHead }} text-right">Ditolak</th>
                                <th scope="col" class="{{ $clayTableHead }} text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-100/70">
                            @forelse ($recap['data'] as $item)
                                <tr class="transition-colors hover:bg-white/70">
                                    <td class="{{ $clayTableCell }} font-bold text-[#10264a]">
                                        {{ $item['facility_name'] }}</td>
                                    <td class="{{ $clayTableCell }} text-slate-700">
                                        {{ Facility::typeLabelFor($item['facility_type']) }}</td>
                                    <td class="{{ $clayTableCell }} text-slate-700">{{ $item['facility_location'] }}</td>
                                    <td class="{{ $clayTableCell }}">
                                        <x-ui.badge :status="$item['status']" dot />
                                    </td>
                                    <td class="{{ $clayTableCell }} text-right text-cyan-700">
                                        {{ $item['baru_count'] }}
                                    </td>
                                    <td class="{{ $clayTableCell }} text-right text-amber-700">
                                        {{ $item['diproses_count'] }}
                                    </td>
                                    <td class="{{ $clayTableCell }} text-right text-emerald-700">
                                        {{ $item['selesai_count'] }}
                                    </td>
                                    <td class="{{ $clayTableCell }} text-right text-rose-700">
                                        {{ $item['ditolak_count'] }}
                                    </td>
                                    <td class="{{ $clayTableCell }} text-right font-extrabold text-[#10264a]">
                                        {{ $item['total_reports'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-5 py-14 text-center">
                                        <span
                                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-white bg-white text-2xl text-blue-600 shadow-[0_4px_10px_rgba(59,130,246,0.15)]">
                                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </span>
                                        <p class="mt-4 text-base font-bold text-slate-800">Tidak ada data fasilitas dengan
                                            laporan kerusakan</p>
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
                                <td class="{{ $clayTableCell }} text-right">{{ $recap['summary']['total_baru'] }}</td>
                                <td class="{{ $clayTableCell }} text-right">{{ $recap['summary']['total_diproses'] }}</td>
                                <td class="{{ $clayTableCell }} text-right">{{ $recap['summary']['total_selesai'] }}</td>
                                <td class="{{ $clayTableCell }} text-right">{{ $recap['summary']['total_ditolak'] }}</td>
                                <td class="{{ $clayTableCell }} text-right">{{ $recap['summary']['total_reports'] }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </section>

        @if (! empty($recap['summary']['by_category']))
            <section class="auth-clay-card rounded-[2rem] p-5 sm:p-7" aria-labelledby="damage-category-heading">
                <h2 id="damage-category-heading" class="text-lg font-extrabold tracking-tight text-[#10264a]">Rincian per
                    Kategori Kerusakan</h2>
                <div class="clay-inset mt-4 overflow-hidden rounded-2xl">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-full text-left text-sm text-slate-600">
                            <thead>
                                <tr>
                                    <th scope="col" class="{{ $clayTableHead }}">Kategori</th>
                                    <th scope="col" class="{{ $clayTableHead }} text-right">Jumlah Laporan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-blue-100/70">
                                @foreach ($recap['summary']['by_category'] as $category => $count)
                                    <tr class="transition-colors hover:bg-white/70">
                                        <td class="{{ $clayTableCell }} font-bold text-[#10264a]">
                                            {{ Report::categoryLabelFor($category) }}
                                        </td>
                                        <td class="{{ $clayTableCell }} text-right">{{ $count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t-2 border-blue-200/80 bg-blue-50/60">
                                <tr class="font-extrabold text-[#10264a]">
                                    <th scope="row" class="{{ $clayTableCell }} text-left">TOTAL</th>
                                    <td class="{{ $clayTableCell }} text-right">{{ $recap['summary']['total_reports'] }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        <p class="text-center text-xs text-slate-500">Periode: {{ $period }}</p>
    </div>
@endsection
