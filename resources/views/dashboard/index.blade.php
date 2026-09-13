@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $reservationStatuses = [
            'pending' => ['label' => 'Menunggu', 'class' => 'bg-amber-50 text-amber-700'],
            'approved' => ['label' => 'Disetujui', 'class' => 'bg-emerald-50 text-emerald-700'],
            'rejected' => ['label' => 'Ditolak', 'class' => 'bg-rose-50 text-rose-700'],
            'cancelled' => ['label' => 'Dibatalkan', 'class' => 'bg-slate-100 text-slate-600'],
        ];
        $reportStatuses = [
            'baru' => ['label' => 'Baru', 'class' => 'bg-sky-50 text-sky-700'],
            'diproses' => ['label' => 'Diproses', 'class' => 'bg-amber-50 text-amber-700'],
            'selesai' => ['label' => 'Selesai', 'class' => 'bg-emerald-50 text-emerald-700'],
            'ditolak' => ['label' => 'Ditolak', 'class' => 'bg-rose-50 text-rose-700'],
        ];
    @endphp

    <div class="space-y-8">
        <section class="overflow-hidden rounded-2xl border border-[#E2E7FF] bg-white shadow-[0_14px_40px_rgba(15,23,42,0.06)]">
            <div class="grid gap-8 px-6 py-7 sm:px-8 lg:grid-cols-[1fr_auto] lg:items-center lg:px-10 lg:py-9">
                <div>
                    <p class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Portal pengguna</p>
                    <h1 class="max-w-2xl text-3xl font-semibold tracking-tight text-[#00236f] sm:text-4xl">Selamat datang, {{ $user->name }}</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">Pantau reservasi fasilitas dan laporan kerusakan Anda dari satu tempat.</p>
                    <div class="mt-5 flex flex-wrap items-center gap-3 text-sm text-slate-600">
                        <span class="rounded-full bg-[#F2F3FF] px-3 py-1.5 font-medium text-[#00236f]">Akun {{ ucfirst($user->account_status) }}</span>
                        <span>{{ ucfirst($user->role) }}</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3 lg:justify-end">
                    <a href="{{ route('reservasi.create') }}" class="inline-flex items-center justify-center rounded-lg bg-[#0051d5] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#00236f] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">Buat reservasi</a>
                    <a href="{{ route('laporan.create') }}" class="inline-flex items-center justify-center rounded-lg border border-[#D6DDF8] bg-white px-4 py-2.5 text-sm font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">Laporkan kerusakan</a>
                </div>
            </div>
        </section>

        <section aria-labelledby="summary-heading">
            <div class="mb-4 flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Aktivitas Anda</p>
                    <h2 id="summary-heading" class="mt-1 text-xl font-semibold tracking-tight text-[#00236f]">Ringkasan saat ini</h2>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-[#E2E7FF] bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-600">Reservasi menunggu</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-[#00236f]">{{ $reservationCounts->get('pending', 0) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Menanti keputusan petugas</p>
                </div>
                <div class="rounded-xl border border-[#E2E7FF] bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-600">Reservasi disetujui</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-emerald-700">{{ $reservationCounts->get('approved', 0) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Jadwal yang sudah dikonfirmasi</p>
                </div>
                <div class="rounded-xl border border-[#E2E7FF] bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-600">Laporan baru</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-sky-700">{{ $reportCounts->get('baru', 0) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Belum ditangani petugas</p>
                </div>
                <div class="rounded-xl border border-[#E2E7FF] bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-600">Laporan diproses</p>
                    <p class="mt-3 text-3xl font-semibold tracking-tight text-amber-700">{{ $reportCounts->get('diproses', 0) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Sedang dalam penanganan</p>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-[#E2E7FF] bg-white p-6 shadow-sm" aria-labelledby="reservations-heading">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Jadwal</p>
                        <h2 id="reservations-heading" class="mt-1 text-xl font-semibold tracking-tight text-[#00236f]">Reservasi terbaru</h2>
                    </div>
                    <a href="{{ route('reservasi.index') }}" class="text-sm font-semibold text-[#0051d5] hover:text-[#00236f]">Lihat semua</a>
                </div>
                <div class="mt-5 divide-y divide-[#EEF2FF]">
                    @forelse ($recentReservations as $reservation)
                        @php($status = $reservationStatuses[$reservation->status] ?? ['label' => ucfirst($reservation->status), 'class' => 'bg-slate-100 text-slate-600'])
                        <a href="{{ route('reservasi.show', $reservation) }}" class="block py-4 first:pt-0 last:pb-0 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#0051d5]">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ $reservation->facility?->name ?? 'Fasilitas dihapus' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $reservation->start_time->format('d M Y, H.i') }}–{{ $reservation->end_time->format('H.i') }}</p>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $status['class'] }}">{{ $status['label'] }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-xl bg-[#F8FAFC] px-4 py-5 text-sm leading-6 text-slate-600">Belum ada reservasi. Pilih fasilitas yang sesuai dan ajukan jadwal pertama Anda.</div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-[#E2E7FF] bg-white p-6 shadow-sm" aria-labelledby="reports-heading">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Pelaporan</p>
                        <h2 id="reports-heading" class="mt-1 text-xl font-semibold tracking-tight text-[#00236f]">Laporan terbaru</h2>
                    </div>
                    <a href="{{ route('laporan.index') }}" class="text-sm font-semibold text-[#0051d5] hover:text-[#00236f]">Lihat semua</a>
                </div>
                <div class="mt-5 divide-y divide-[#EEF2FF]">
                    @forelse ($recentReports as $report)
                        @php($status = $reportStatuses[$report->status] ?? ['label' => ucfirst($report->status), 'class' => 'bg-slate-100 text-slate-600'])
                        <a href="{{ route('laporan.show', $report) }}" class="block py-4 first:pt-0 last:pb-0 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#0051d5]">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ $report->facility?->name ?? 'Fasilitas dihapus' }}</p>
                                    <p class="mt-1 truncate text-xs text-slate-500">{{ str_replace('_', ' ', ucfirst($report->category)) }} · {{ $report->created_at->format('d M Y') }}</p>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $status['class'] }}">{{ $status['label'] }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-xl bg-[#F8FAFC] px-4 py-5 text-sm leading-6 text-slate-600">Belum ada laporan kerusakan. Bantu petugas menjaga fasilitas tetap siap digunakan.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection
