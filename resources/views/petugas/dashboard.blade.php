@extends('layouts.app')

@section('title', 'Dashboard Petugas')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Pusat operasional</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#00236f]">
                Dashboard Petugas
            </h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Pantau antrean reservasi dan laporan yang memerlukan tindakan.
            </p>
        </div>
        <a href="{{ route('petugas.reservasi.index') }}"
           class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-[#0051d5] px-4 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#00236f] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">
            Buka Antrian Reservasi
        </a>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-[#E2E7FF] bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-600">Reservasi Menunggu</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[#00236f]">{{ $pendingReservationCount }}</p>
            <p class="mt-1 text-xs text-slate-500">perlu disetujui / ditolak</p>
        </div>
        <div class="rounded-xl border border-[#E2E7FF] bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-600">Laporan Baru</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-sky-700">{{ $newReportCount }}</p>
            <p class="mt-1 text-xs text-slate-500">belum diambil petugas</p>
        </div>
        <div class="rounded-xl border border-[#E2E7FF] bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-600">Laporan Diproses</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-amber-700">{{ $processedReportCount }}</p>
            <p class="mt-1 text-xs text-slate-500">sedang ditangani</p>
        </div>
        <div class="rounded-xl border border-[#E2E7FF] bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-600">Fasilitas Dalam Perbaikan</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-rose-700">{{ $repairFacilityCount }}</p>
            <p class="mt-1 text-xs text-slate-500">menunggu selesai laporan</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-[#E2E7FF] bg-white shadow-sm">
            <div class="border-b border-[#EEF2FF] px-6 py-4">
                <h2 class="text-base font-semibold text-[#00236f]">Antrian Reservasi Terbaru</h2>
            </div>
            <div class="p-6">
                @forelse ($pendingReservations as $reservation)
                    <div class="flex items-center justify-between gap-4 border-b border-[#EEF2FF] py-3 last:border-b-0">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-[#00236f]">
                                {{ $reservation->facility->name }}
                            </p>
                            <p class="truncate text-xs text-slate-600">
                                {{ $reservation->user->name }} ·
                                {{ $reservation->start_time->format('d M Y H.i') }} – {{ $reservation->end_time->format('H.i') }}
                            </p>
                        </div>
                        <a href="{{ route('petugas.reservasi.show', $reservation) }}"
                           class="inline-flex h-9 shrink-0 items-center rounded-lg border border-[#D6DDF8] bg-white px-3 text-xs font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF]">
                            Tinjau
                        </a>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500">Tidak ada reservasi yang menunggu.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-[#E2E7FF] bg-white shadow-sm">
            <div class="border-b border-[#EEF2FF] px-6 py-4">
                <h2 class="text-base font-semibold text-[#00236f]">Antrian Laporan Terbaru</h2>
            </div>
            <div class="p-6">
                @forelse ($newReports as $report)
                    <div class="flex items-center justify-between gap-4 border-b border-[#EEF2FF] py-3 last:border-b-0">
                        <div class="min-w-0">
                            <a href="{{ route('petugas.laporan.show', $report) }}"
                               class="truncate text-sm font-semibold text-[#00236f] transition-colors hover:text-[#0051d5] hover:underline">
                                {{ $report->facility->name }}
                            </a>
                            <p class="truncate text-xs text-slate-600">
                                {{ $report->user->name }} ·
                                {{ $report->created_at->format('d M Y H.i') }}
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-cyan-50 px-2.5 py-1 text-xs font-semibold text-cyan-700 ring-1 ring-inset ring-cyan-200">
                                Baru
                            </span>
                            <a href="{{ route('petugas.laporan.show', $report) }}"
                               class="inline-flex h-9 items-center rounded-lg border border-[#D6DDF8] bg-white px-3 text-xs font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF]">
                                Tinjau
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500">Tidak ada laporan baru.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
