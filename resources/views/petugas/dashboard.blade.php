@extends('layouts.app')

@section('title', 'Dashboard Petugas')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                Dashboard Petugas
            </h1>
            <p class="mt-1 text-sm text-slate-600">
                Pantau antrean reservasi dan laporan yang memerlukan tindakan.
            </p>
        </div>
        <a href="{{ route('petugas.reservasi.index') }}"
           class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-900 px-4 text-sm font-semibold text-white transition-colors hover:bg-blue-800">
            Buka Antrian Reservasi
        </a>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-600">Reservasi Menunggu</p>
            <p class="mt-1 text-3xl font-bold text-slate-900">{{ $pendingReservationCount }}</p>
            <p class="mt-1 text-xs text-slate-500">perlu disetujui / ditolak</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-600">Laporan Baru</p>
            <p class="mt-1 text-3xl font-bold text-slate-900">{{ $newReportCount }}</p>
            <p class="mt-1 text-xs text-slate-500">belum diambil petugas</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-600">Laporan Diproses</p>
            <p class="mt-1 text-3xl font-bold text-slate-900">{{ $processedReportCount }}</p>
            <p class="mt-1 text-xs text-slate-500">sedang ditangani</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-600">Fasilitas Dalam Perbaikan</p>
            <p class="mt-1 text-3xl font-bold text-slate-900">{{ $repairFacilityCount }}</p>
            <p class="mt-1 text-xs text-slate-500">menunggu selesai laporan</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4">
                <h2 class="text-base font-semibold text-slate-900">Antrian Reservasi Terbaru</h2>
            </div>
            <div class="p-6">
                @forelse ($pendingReservations as $reservation)
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 py-3 last:border-b-0">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">
                                {{ $reservation->facility->name }}
                            </p>
                            <p class="truncate text-xs text-slate-600">
                                {{ $reservation->user->name }} ·
                                {{ $reservation->start_time->format('d M Y H.i') }} – {{ $reservation->end_time->format('H.i') }}
                            </p>
                        </div>
                        <a href="{{ route('petugas.reservasi.show', $reservation) }}"
                           class="shrink-0 inline-flex h-9 items-center rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                            Tinjau
                        </a>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500">Tidak ada reservasi yang menunggu.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4">
                <h2 class="text-base font-semibold text-slate-900">Antrian Laporan Terbaru</h2>
            </div>
            <div class="p-6">
                @forelse ($newReports as $report)
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 py-3 last:border-b-0">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">
                                {{ $report->facility->name }}
                            </p>
                            <p class="truncate text-xs text-slate-600">
                                {{ $report->user->name }} ·
                                {{ $report->created_at->format('d M Y H.i') }}
                            </p>
                        </div>
                        <span class="inline-flex shrink-0 items-center rounded-full bg-cyan-50 px-2.5 py-1 text-xs font-semibold text-cyan-700 ring-1 ring-inset ring-cyan-200">
                            Baru
                        </span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500">Tidak ada laporan baru.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection