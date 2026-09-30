@extends('layouts.app')

@section('title', 'Antrian Laporan Kerusakan | REKSA')

@section('content')
    @php
        $clayTableHead = 'border-b border-blue-100/80 bg-blue-50/50 px-5 py-3.5 text-[11px] font-extrabold uppercase tracking-[0.14em] text-blue-700';
        $clayTableCell = 'px-5 py-4 align-middle';
    @endphp

    <div class="space-y-5">
        <section class="auth-clay-card rounded-[2rem] p-5 sm:p-7 lg:p-8" aria-labelledby="report-queue-heading">
            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Pusat operasional</p>
            <h1 id="report-queue-heading"
                class="mt-0.5 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">Antrian Laporan
                Kerusakan</h1>
            <p class="mt-1 text-sm leading-relaxed text-slate-600">Kelola dan proses laporan kerusakan fasilitas
                kampus dari pengguna.</p>

            <nav class="mt-6 flex flex-wrap gap-2" aria-label="Filter status laporan">
                @foreach ($statusFilters as $statusKey)
                    <x-ui.filter-chip :href="route('petugas.laporan.index', ['status' => $statusKey])"
                        :active="(string) $status === $statusKey">
                        <x-ui.badge :status="$statusKey" plain />
                    </x-ui.filter-chip>
                @endforeach
            </nav>

            @if ($reports->isEmpty())
                <div class="clay-inset mt-6 rounded-2xl px-4 py-14 text-center">
                    <span
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-white bg-white text-2xl text-blue-600 shadow-[0_4px_10px_rgba(59,130,246,0.15)]">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                    <h3 class="mt-4 text-base font-bold text-slate-800">Tidak ada laporan</h3>
                    <p class="mt-1 text-sm text-slate-500">Tidak ditemukan laporan kerusakan dengan filter status ini.
                    </p>
                </div>
            @else
                <div class="clay-inset mt-6 overflow-hidden rounded-2xl">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-full text-left text-sm text-slate-600">
                            <thead>
                                <tr>
                                    <th scope="col" class="{{ $clayTableHead }}">Pelapor</th>
                                    <th scope="col" class="{{ $clayTableHead }}">Fasilitas</th>
                                    <th scope="col" class="{{ $clayTableHead }}">Kategori</th>
                                    <th scope="col" class="{{ $clayTableHead }}">Status Laporan</th>
                                    <th scope="col" class="{{ $clayTableHead }}">Waktu Diajukan</th>
                                    <th scope="col" class="{{ $clayTableHead }} text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-blue-100/70">
                                @foreach ($reports as $report)
                                    <tr class="transition-colors hover:bg-white/70">
                                        <td class="{{ $clayTableCell }}">
                                            <p class="font-bold text-[#10264a]">{{ $report->user->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $report->user->email }}</p>
                                        </td>
                                        <td class="{{ $clayTableCell }}">
                                            <p class="font-bold text-[#10264a]">{{ $report->facility->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $report->facility->location }}</p>
                                        </td>
                                        <td class="{{ $clayTableCell }} text-slate-700">
                                            {{ $report->categoryLabel() }}
                                        </td>
                                        <td class="{{ $clayTableCell }}">
                                            <x-ui.badge :status="$report->status" dot />
                                        </td>
                                        <td class="{{ $clayTableCell }} whitespace-nowrap text-xs text-slate-500">
                                            {{ $report->created_at->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                                        </td>
                                        <td class="{{ $clayTableCell }} text-right">
                                            <a href="{{ route('petugas.laporan.show', $report) }}"
                                                class="clay-pressable inline-flex h-9 items-center justify-center whitespace-nowrap rounded-full px-4 text-xs font-bold text-slate-600">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($reports->hasPages())
                    <div class="mt-5 border-t border-blue-100/80 pt-4">
                        {{ $reports->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection
