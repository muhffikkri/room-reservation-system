@extends('layouts.app')

@use('App\Models\Reservation')

@section('title', 'Reservasi Saya | REKSA')

@section('content')
    <div class="space-y-5">
        <section class="auth-clay-card rounded-[2rem] p-5 sm:p-7 lg:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Aktivitas pengguna</p>
                    <h1 class="mt-0.5 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">Riwayat Reservasi Saya</h1>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600">Kelola dan pantau status permohonan peminjaman fasilitas kampus Anda.</p>
                </div>
                <a href="{{ route('reservasi.create') }}" class="landing-button inline-flex shrink-0 items-center justify-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-3 text-sm font-bold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)]">
                    <span class="text-lg leading-none">+</span> Ajukan Reservasi Baru
                </a>
            </div>
            <nav class="mt-6 flex gap-2 overflow-x-auto pb-1" aria-label="Filter status reservasi">
                <a href="{{ route('reservasi.index') }}" @class(['shrink-0 rounded-full px-4 py-2 text-xs font-semibold', 'landing-button bg-gradient-to-r from-blue-600 to-blue-500 text-white' => (string) request('status') === '', 'clay-button-white text-slate-600' => (string) request('status') !== ''])>Semua</a>
                @foreach (Reservation::ORDERED_STATUSES as $status)
                    <a href="{{ route('reservasi.index', ['status' => $status]) }}" @class(['shrink-0 rounded-full px-4 py-2 text-xs font-semibold', 'landing-button bg-gradient-to-r from-blue-600 to-blue-500 text-white' => (string) request('status') === $status, 'clay-button-white text-slate-600' => (string) request('status') !== $status])><x-reservation.status-pill :status="$status" plain :colored="false" user-facing /></a>
                @endforeach
            </nav>
        </section>

        <section class="auth-clay-card rounded-[2rem] p-5 sm:p-7" aria-label="Daftar reservasi">
            @if ($reservations->isEmpty())
                <div class="px-4 py-14 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-2xl text-blue-600 shadow-inner">▤</span>
                    <p class="mt-4 text-base font-bold text-slate-800">Belum ada data reservasi.</p>
                    <p class="mt-1 text-xs text-slate-500">Ajukan permohonan peminjaman fasilitas pertama Anda.</p>
                    <a href="{{ route('reservasi.create') }}" class="landing-button mt-4 inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-2.5 text-xs font-bold text-white shadow-sm">Ajukan reservasi sekarang →</a>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($reservations as $res)
                        <a href="{{ route('reservasi.show', $res) }}" class="dashboard-clay-list clay-pressable flex items-center justify-between gap-4 rounded-2xl p-4.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="flex min-w-0 items-center gap-3.5">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-blue-100 bg-white p-1 shadow-sm">
                                    <img src="{{ $res->facility?->photo_url ?: $res->facility?->display_image_url }}" alt="" class="h-full w-full rounded-lg object-cover">
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-bold text-slate-800 sm:text-base">{{ $res->facility?->name ?? 'Fasilitas dihapus' }}</span>
                                    <span class="mt-0.5 block truncate text-xs text-slate-500">{{ $res->start_time->translatedFormat('d M Y') }} · {{ $res->start_time->format('H.i') }}–{{ $res->end_time->format('H.i') }} WIB</span>
                                    <span class="mt-0.5 block truncate text-xs text-slate-400 max-w-md hidden sm:block">{{ $res->purpose }}</span>
                                </span>
                            </span>
                            <span class="shrink-0 flex items-center gap-3">
                                <x-reservation.status-pill :status="$res->status" user-facing />
                                <span class="hidden text-xs font-bold text-blue-700 sm:inline">&rarr;</span>
                            </span>
                        </a>
                    @endforeach
                </div>
                @if ($reservations->hasPages())
                    <div class="mt-5 border-t border-blue-100/80 pt-4">{{ $reservations->links() }}</div>
                @endif
            @endif
        </section>
    </div>
@endsection
