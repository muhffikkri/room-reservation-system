@extends('layouts.app')

@use('App\Models\Reservation')

@section('title', 'Reservasi Saya | REKSA')

@section('content')
    <div class="space-y-5">
        <section class="landing-panel rounded-[1.8rem] p-5 shadow-[0_14px_38px_rgba(53,103,175,0.12)] sm:p-7 lg:p-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                <div><p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Aktivitas pengguna</p><h1 class="mt-1.5 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">Riwayat Reservasi Saya</h1><p class="mt-2 text-sm leading-relaxed text-slate-600">Kelola dan pantau status permohonan peminjaman fasilitas kampus Anda.</p></div>
                <a href="{{ route('reservasi.create') }}" class="landing-button inline-flex shrink-0 items-center justify-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-3 text-sm font-bold text-white"><span class="text-lg leading-none">+</span> Ajukan Reservasi Baru</a>
            </div>
            <nav class="mt-6 flex gap-2 overflow-x-auto pb-1" aria-label="Filter status reservasi">
                <a href="{{ route('reservasi.index') }}" @class(['shrink-0 rounded-full px-4 py-2 text-xs font-semibold', 'landing-button bg-gradient-to-r from-blue-600 to-blue-500 text-white' => (string) request('status') === '', 'clay-pressable text-slate-600' => (string) request('status') !== ''])>Semua</a>
                @foreach (Reservation::ORDERED_STATUSES as $status)
                    <a href="{{ route('reservasi.index', ['status' => $status]) }}" @class(['shrink-0 rounded-full px-4 py-2 text-xs font-semibold', 'landing-button bg-gradient-to-r from-blue-600 to-blue-500 text-white' => (string) request('status') === $status, 'clay-pressable text-slate-600' => (string) request('status') !== $status])>{{ $status === 'cancelled_by_system' ? 'Gagal' : Reservation::statusLabel($status) }}</a>
                @endforeach
            </nav>
        </section>

        <section class="landing-panel overflow-hidden rounded-[1.8rem] p-4 shadow-[0_14px_38px_rgba(53,103,175,0.1)] sm:p-6" aria-label="Daftar reservasi">
            @if ($reservations->isEmpty())
                <div class="px-4 py-14 text-center"><span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-2xl text-blue-600">▤</span><p class="mt-4 text-sm font-semibold text-slate-700">Belum ada data reservasi.</p><a href="{{ route('reservasi.create') }}" class="mt-2 inline-block text-sm font-bold text-blue-600 hover:text-blue-800">Ajukan reservasi sekarang →</a></div>
            @else
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full border-separate border-spacing-y-2 text-left text-sm text-slate-600">
                        <thead class="text-[11px] font-bold uppercase tracking-[0.1em] text-slate-500"><tr><th class="px-4 pb-2">Fasilitas</th><th class="px-4 pb-2">Waktu pelaksanaan</th><th class="px-4 pb-2">Tujuan</th><th class="px-4 pb-2">Status</th><th class="px-4 pb-2 text-right">Aksi</th></tr></thead>
                        <tbody>
                            @foreach ($reservations as $res)
                                <tr class="bg-white/80 transition hover:bg-white hover:shadow-sm">
                                    <td class="rounded-l-2xl px-4 py-4 font-bold text-slate-800">{{ $res->facility->name }}<span class="mt-1 block text-xs font-medium text-slate-500">{{ $res->facility->location }}</span></td>
                                    <td class="px-4 py-4 font-medium text-slate-700">{{ $res->start_time->translatedFormat('d M Y') }}<span class="mt-1 block text-xs text-slate-500">{{ $res->start_time->format('H:i') }} – {{ $res->end_time->format('H:i') }} WIB</span></td>
                                    <td class="max-w-xs truncate px-4 py-4" title="{{ $res->purpose }}">{{ $res->purpose }}</td>
                                    <td class="px-4 py-4"><x-ui.badge :status="$res->status">{{ $res->status === 'cancelled_by_system' ? 'Gagal' : Reservation::statusLabel($res->status) }}</x-ui.badge></td>
                                    <td class="rounded-r-2xl px-4 py-4 text-right"><a href="{{ route('reservasi.show', $res) }}" class="clay-pressable inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-bold text-blue-700">Lihat detail <span aria-hidden="true">→</span></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="space-y-3 md:hidden">
                    @foreach ($reservations as $res)
                        <article class="rounded-2xl border border-white bg-white/80 p-4 shadow-sm">
                            <div class="flex items-start justify-between gap-3"><div class="min-w-0"><h2 class="truncate text-sm font-bold text-slate-800">{{ $res->facility->name }}</h2><p class="mt-1 text-xs text-slate-500">{{ $res->facility->location }}</p></div><x-ui.badge :status="$res->status">{{ $res->status === 'cancelled_by_system' ? 'Gagal' : Reservation::statusLabel($res->status) }}</x-ui.badge></div>
                            <p class="mt-3 text-xs font-semibold text-slate-700">{{ $res->start_time->translatedFormat('d M Y') }} · {{ $res->start_time->format('H:i') }} – {{ $res->end_time->format('H:i') }} WIB</p><p class="mt-2 line-clamp-2 text-xs text-slate-500">{{ $res->purpose }}</p>
                            <a href="{{ route('reservasi.show', $res) }}" class="clay-pressable mt-4 inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-bold text-blue-700">Lihat detail <span aria-hidden="true">→</span></a>
                        </article>
                    @endforeach
                </div>
                @if ($reservations->hasPages())
                    <div class="mt-4 border-t border-blue-100/80 pt-4">{{ $reservations->links() }}</div>
                @endif
            @endif
        </section>
    </div>
@endsection
