@extends('layouts.app')

@section('title', 'Ringkasan REKSA')

@section('content')
    @php
        $reservationStatuses = [
            'pending' => ['label' => 'Menunggu', 'class' => 'bg-amber-50 text-amber-700'],
            'approved' => ['label' => 'Disetujui', 'class' => 'bg-emerald-50 text-emerald-700'],
            'rejected' => ['label' => 'Ditolak', 'class' => 'bg-rose-50 text-rose-700'],
            'rejected_by_system' => ['label' => 'Ditolak oleh Sistem', 'class' => 'bg-violet-50 text-violet-700'],
            'cancelled_by_user' => ['label' => 'Dibatalkan Pengguna', 'class' => 'bg-slate-100 text-slate-600'],
            'cancelled_by_officer' => ['label' => 'Dibatalkan Petugas', 'class' => 'bg-slate-100 text-slate-600'],
            'cancelled_by_system' => ['label' => 'Gagal', 'class' => 'bg-rose-50 text-rose-700'],
        ];
        $reportStatuses = [
            'baru' => ['label' => 'Baru', 'class' => 'bg-sky-50 text-sky-700'],
            'diproses' => ['label' => 'Diproses', 'class' => 'bg-amber-50 text-amber-700'],
            'selesai' => ['label' => 'Selesai', 'class' => 'bg-emerald-50 text-emerald-700'],
            'ditolak' => ['label' => 'Ditolak', 'class' => 'bg-rose-50 text-rose-700'],
        ];
    @endphp

    <div class="space-y-7">
        <section class="landing-panel relative grid min-h-64 overflow-hidden rounded-[2rem] px-6 py-7 shadow-[8px_10px_24px_rgba(137,171,216,0.2),-7px_-7px_20px_rgba(255,255,255,0.85),inset_1px_1px_2px_rgba(255,255,255,0.95)] sm:px-9 lg:grid-cols-[1fr_18rem] lg:items-center lg:px-11 lg:py-9" aria-labelledby="welcome-heading">
            <div class="relative z-10 max-w-3xl pr-32 sm:pr-52 lg:pr-0">
                <h1 id="welcome-heading" class="text-3xl font-extrabold leading-tight tracking-tight text-[#10264a] sm:text-4xl">Selamat datang, {{ $user->name }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-600 sm:text-base">Pantau reservasi fasilitas dan laporan kerusakan Anda dari satu tempat.</p>
                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <a href="{{ route('reservasi.create') }}" class="landing-button inline-flex min-h-11 items-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-3 text-sm font-semibold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)] transition hover:from-blue-700 hover:to-blue-600 sm:px-6">Buat reservasi <span aria-hidden="true">→</span></a>
                    <a href="{{ route('laporan.create') }}" class="landing-button inline-flex min-h-11 items-center gap-2 rounded-full border border-white bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-[8px_8px_20px_rgba(166,195,235,0.35),-8px_-8px_20px_rgba(255,255,255,0.95)] transition hover:bg-slate-50 sm:px-6">Laporkan kerusakan</a>
                </div>
            </div>
            <div class="dashboard-mascot pointer-events-none absolute inset-y-0 right-0 z-0 flex w-32 items-end justify-center sm:w-52 lg:w-72">
                <img src="{{ asset('images/maskot-greetings.png') }}" alt="Maskot REKSA menyapa pengguna" class="relative z-10 h-full w-full object-contain object-bottom drop-shadow-[0_14px_18px_rgba(37,99,235,0.2)]">
            </div>
        </section>

        <section class="landing-panel rounded-[2rem] p-5 shadow-[8px_10px_24px_rgba(137,171,216,0.2),-7px_-7px_20px_rgba(255,255,255,0.85),inset_1px_1px_2px_rgba(255,255,255,0.95)] sm:p-6" aria-labelledby="summary-heading">
            <div class="mb-5"><p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Aktivitas Anda</p><h2 id="summary-heading" class="mt-1 text-xl font-extrabold tracking-tight text-[#10264a]">Ringkasan saat ini</h2></div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="clay-inset flex items-center gap-4 rounded-2xl p-4 sm:p-5">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-500 text-white shadow-[0_6px_12px_rgba(37,99,235,0.25),inset_0_1px_0_rgba(255,255,255,0.45)]"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg></span>
                    <div class="min-w-0"><p class="text-xs font-semibold text-slate-600">Reservasi menunggu</p><p class="mt-1 text-3xl font-extrabold leading-none tracking-tight text-slate-900">{{ $reservationCounts->get('pending', 0) }}</p><p class="mt-2 text-[11px] text-slate-500">Menanti keputusan petugas</p></div>
                </article>
                <article class="clay-inset flex items-center gap-4 rounded-2xl p-4 sm:p-5">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-[0_6px_12px_rgba(16,185,129,0.24),inset_0_1px_0_rgba(255,255,255,0.45)]"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="m8 12 2.5 2.5L16 9"/></svg></span>
                    <div class="min-w-0"><p class="text-xs font-semibold text-slate-600">Reservasi disetujui</p><p class="mt-1 text-3xl font-extrabold leading-none tracking-tight text-slate-900">{{ $reservationCounts->get('approved', 0) }}</p><p class="mt-2 text-[11px] text-slate-500">Jadwal sudah dikonfirmasi</p></div>
                </article>
                <article class="clay-inset flex items-center gap-4 rounded-2xl p-4 sm:p-5">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-sky-500 text-white shadow-[0_6px_12px_rgba(14,165,233,0.24),inset_0_1px_0_rgba(255,255,255,0.45)]"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3h7l5 5v13H5V3h2Zm6 1v5h5M8 13h8M8 17h8"/></svg></span>
                    <div class="min-w-0"><p class="text-xs font-semibold text-slate-600">Laporan baru</p><p class="mt-1 text-3xl font-extrabold leading-none tracking-tight text-slate-900">{{ $reportCounts->get('baru', 0) }}</p><p class="mt-2 text-[11px] text-slate-500">Belum ditangani petugas</p></div>
                </article>
                <article class="clay-inset flex items-center gap-4 rounded-2xl p-4 sm:p-5">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-[0_6px_12px_rgba(245,158,11,0.25),inset_0_1px_0_rgba(255,255,255,0.45)]"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a5 5 0 0 0-6.4 6.4L3 18l3 3 5.3-5.3a5 5 0 0 0 6.4-6.4L14 13l-3-3 3.7-3.7Z"/></svg></span>
                    <div class="min-w-0"><p class="text-xs font-semibold text-slate-600">Laporan diproses</p><p class="mt-1 text-3xl font-extrabold leading-none tracking-tight text-slate-900">{{ $reportCounts->get('diproses', 0) }}</p><p class="mt-2 text-[11px] text-slate-500">Sedang dalam penanganan</p></div>
                </article>
            </div>
        </section>

        <div class="grid gap-5 lg:grid-cols-2">
            <section class="landing-panel rounded-[1.8rem] p-5 shadow-[0_12px_32px_rgba(53,103,175,0.1)] sm:p-6" aria-labelledby="reservations-heading">
                <div class="flex items-center justify-between gap-3"><div><p class="text-[11px] font-bold uppercase tracking-[0.14em] text-blue-600">Jadwal</p><h2 id="reservations-heading" class="mt-1 text-lg font-extrabold text-[#10264a]">Reservasi terbaru</h2></div><a href="{{ route('reservasi.index') }}" class="landing-button inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-2.5 text-sm font-semibold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)] transition hover:from-blue-700 hover:to-blue-600">Lihat semua <span aria-hidden="true">›</span></a></div>
                <div class="mt-5 space-y-3">
                    @forelse ($recentReservations as $reservation)
                        @php($status = $reservationStatuses[$reservation->status] ?? ['label' => ucfirst($reservation->status), 'class' => 'bg-slate-100 text-slate-600'])
                        <a href="{{ route('reservasi.show', $reservation) }}" class="clay-pressable flex items-center justify-between gap-3 rounded-2xl p-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="flex min-w-0 items-center gap-3"><span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-[10px] font-bold text-blue-700">{{ $reservation->facility?->location ? \Illuminate\Support\Str::limit($reservation->facility->location, 7, '') : 'REKSA' }}</span><span class="min-w-0"><span class="block truncate text-sm font-bold text-slate-800">{{ $reservation->facility?->name ?? 'Fasilitas dihapus' }}</span><span class="mt-1 block text-xs text-slate-500">{{ $reservation->start_time->format('d M Y, H.i') }}–{{ $reservation->end_time->format('H.i') }}</span></span></span>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold {{ $status['class'] }}">{{ $status['label'] }}</span>
                        </a>
                    @empty
                        <div class="rounded-2xl bg-white/65 px-4 py-5 text-sm leading-6 text-slate-600">Belum ada reservasi. Pilih fasilitas yang sesuai dan ajukan jadwal pertama Anda.</div>
                    @endforelse
                </div>
            </section>

            <section class="landing-panel rounded-[1.8rem] p-5 shadow-[0_12px_32px_rgba(53,103,175,0.1)] sm:p-6" aria-labelledby="reports-heading">
                <div class="flex items-center justify-between gap-3"><div><p class="text-[11px] font-bold uppercase tracking-[0.14em] text-blue-600">Pelaporan</p><h2 id="reports-heading" class="mt-1 text-lg font-extrabold text-[#10264a]">Laporan terbaru</h2></div><a href="{{ route('laporan.index') }}" class="landing-button inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-2.5 text-sm font-semibold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)] transition hover:from-blue-700 hover:to-blue-600">Lihat semua <span aria-hidden="true">›</span></a></div>
                <div class="mt-5 space-y-3">
                    @forelse ($recentReports as $report)
                        @php($status = $reportStatuses[$report->status] ?? ['label' => ucfirst($report->status), 'class' => 'bg-slate-100 text-slate-600'])
                        <a href="{{ route('laporan.show', $report) }}" class="clay-pressable flex items-center justify-between gap-3 rounded-2xl p-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="flex min-w-0 items-center gap-3"><span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-lg font-bold text-sky-600">▤</span><span class="min-w-0"><span class="block truncate text-sm font-bold text-slate-800">{{ $report->facility?->name ?? 'Fasilitas dihapus' }}</span><span class="mt-1 block truncate text-xs text-slate-500">{{ $report->categoryLabel() }} · {{ $report->created_at->format('d M Y') }}</span></span></span>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold {{ $status['class'] }}">{{ $status['label'] }}</span>
                        </a>
                    @empty
                        <div class="rounded-2xl bg-white/65 px-4 py-5 text-sm leading-6 text-slate-600">Belum ada laporan kerusakan. Bantu petugas menjaga fasilitas tetap siap digunakan.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection
