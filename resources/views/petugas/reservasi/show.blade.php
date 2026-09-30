@extends('layouts.app')

@section('title', 'Detail Reservasi | REKSA')

@section('content')
    @php
        $detailCard =
            'flex min-h-20 items-center gap-3.5 rounded-2xl border border-white/90 bg-gradient-to-br from-blue-50/70 to-white/90 p-4 shadow-[inset_2px_2px_6px_rgba(99,137,193,0.08),0_4px_12px_rgba(116,155,211,0.08)]';
        $detailIcon =
            'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white bg-white text-blue-600 shadow-[0_4px_10px_rgba(59,130,246,0.15)]';
        $dialogShell =
            'w-full max-w-md rounded-[1.8rem] border border-white/90 bg-gradient-to-br from-white/98 to-blue-50/80 p-6 shadow-[0_24px_60px_rgba(16,38,74,0.24),inset_2px_2px_6px_rgba(255,255,255,0.9)] backdrop:bg-slate-950/40';
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">
        <div class="auth-clay-card rounded-[2rem] p-5 sm:p-7 lg:p-9">
            <div class="flex flex-col gap-4 border-b border-blue-100/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <a href="{{ route('petugas.reservasi.index') }}"
                        class="clay-button-white mb-3 inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold text-blue-700">
                        <span aria-hidden="true">&larr;</span> Kembali ke antrian
                    </a>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Pusat operasional · Antrian
                        Reservasi</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">Detail Reservasi
                        #{{ str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT) }}</h1>
                    <p class="mt-1 text-xs text-slate-500">
                        Diajukan pada {{ $reservation->created_at->locale('id')->translatedFormat('l, d F Y, H:i') }} WIB
                    </p>
                </div>
                <x-reservation.status-pill :status="$reservation->status" />
            </div>

            <section class="mt-7" aria-labelledby="reservation-facility-heading">
                <h2 id="reservation-facility-heading"
                    class="mb-4 flex items-center gap-2.5 text-xs font-extrabold uppercase tracking-[0.14em] text-blue-700">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100/70 text-blue-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </span>
                    Informasi Reservasi
                </h2>
                <div class="grid gap-3.5 sm:grid-cols-2">
                    <div class="{{ $detailCard }}">
                        <span class="{{ $detailIcon }}">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-slate-500">Fasilitas</p>
                            <p class="mt-0.5 text-sm font-extrabold text-[#10264a]">{{ $reservation->facility->name }}</p>
                            <p class="text-xs text-slate-500">{{ $reservation->facility->location }}</p>
                        </div>
                    </div>
                    <div class="{{ $detailCard }}">
                        <span class="{{ $detailIcon }}">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-slate-500">Jadwal Penggunaan</p>
                            <p class="mt-0.5 text-sm font-extrabold text-[#10264a]">
                                {{ $reservation->start_time->locale('id')->translatedFormat('l, d F Y') }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $reservation->start_time->format('H.i') }} –
                                {{ $reservation->end_time->format('H:i') }} WIB ·
                                {{ $reservation->start_time->diffInMinutes($reservation->end_time) / 60 }} jam</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mt-6 border-t border-blue-100/80 pt-6" aria-labelledby="reservation-purpose-heading">
                <h2 id="reservation-purpose-heading"
                    class="mb-4 flex items-center gap-2.5 text-xs font-extrabold uppercase tracking-[0.14em] text-blue-700">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100/70 text-blue-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                    Tujuan Penggunaan
                </h2>
                <div
                    class="flex items-start gap-3.5 rounded-2xl border border-white/90 bg-gradient-to-br from-blue-50/70 to-white/90 p-4 shadow-[inset_2px_2px_6px_rgba(99,137,193,0.08)] sm:p-5">
                    <p class="text-sm leading-relaxed whitespace-pre-line text-slate-700">{{ $reservation->purpose }}</p>
                </div>
            </section>

            <section class="mt-6 border-t border-blue-100/80 pt-6" aria-labelledby="reservation-requester-heading">
                <h2 id="reservation-requester-heading"
                    class="mb-4 flex items-center gap-2.5 text-xs font-extrabold uppercase tracking-[0.14em] text-blue-700">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100/70 text-blue-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                    Informasi Pemohon
                </h2>
                <div class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="clay-inset rounded-2xl p-4">
                        <p class="text-xs font-medium text-slate-500">Nama</p>
                        <p class="mt-0.5 text-sm font-extrabold text-[#10264a]">{{ $reservation->user->name }}</p>
                    </div>
                    <div class="clay-inset rounded-2xl p-4">
                        <p class="text-xs font-medium text-slate-500">Email</p>
                        <p class="mt-0.5 truncate text-sm font-extrabold text-[#10264a]" title="{{ $reservation->user->email }}">
                            {{ $reservation->user->email }}</p>
                    </div>
                    <div class="clay-inset rounded-2xl p-4">
                        <p class="text-xs font-medium text-slate-500">NIM/NIP</p>
                        <p class="mt-0.5 text-sm font-extrabold text-[#10264a]">{{ $reservation->user->identity ?? '-' }}</p>
                    </div>
                    <div class="clay-inset rounded-2xl p-4">
                        <p class="text-xs font-medium text-slate-500">No. HP</p>
                        <p class="mt-0.5 text-sm font-extrabold text-[#10264a]">{{ $reservation->user->phone ?? '-' }}</p>
                    </div>
                </div>
            </section>

            @if ($reservation->reject_reason || $reservation->cancel_reason || $reservation->decided_at !== null)
                <section class="mt-6 border-t border-blue-100/80 pt-6" aria-labelledby="reservation-decision-heading">
                    <h2 id="reservation-decision-heading"
                        class="mb-4 flex items-center gap-2.5 text-xs font-extrabold uppercase tracking-[0.14em] text-blue-700">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100/70 text-blue-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        Riwayat Keputusan
                    </h2>
                    <div class="space-y-3">
                        {{-- Judul alasan memakai label generik: pemilik varyasi
                             "Otomatis oleh Sistem" tetap di halaman pengguna
                             (reservasi/show), jadi tidak disalin di sini. --}}
                        @if ($reservation->reject_reason)
                            <div class="clay-inset rounded-2xl p-4">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-rose-700">Alasan Penolakan</h3>
                                <p class="mt-1 text-sm leading-relaxed text-slate-700">
                                    {{ $reservation->reject_reason }}</p>
                            </div>
                        @endif
                        @if ($reservation->cancel_reason)
                            <div class="clay-inset rounded-2xl p-4">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Alasan Pembatalan</h3>
                                <p class="mt-1 text-sm leading-relaxed text-slate-700">
                                    {{ $reservation->cancel_reason }}</p>
                            </div>
                        @endif
                        @if ($reservation->decided_at !== null)
                            <div class="clay-inset rounded-2xl p-4">
                                <p class="text-xs font-medium text-slate-500">Diputuskan oleh</p>
                                <p class="mt-0.5 text-sm font-extrabold text-[#10264a]">
                                    {{ $reservation->decidedBy?->name ?? '-' }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $reservation->decided_at->locale('id')->translatedFormat('l, d F Y, H:i') }} WIB</p>
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            <div
                class="mt-7 flex flex-col-reverse items-stretch gap-3 border-t border-blue-100/80 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('petugas.reservasi.index') }}"
                    class="clay-button-white inline-flex justify-center rounded-full px-6 py-3 text-sm font-semibold text-blue-700">
                    <span aria-hidden="true">&larr;</span> Kembali ke antrian
                </a>
                <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center">
                    @if ($reservation->isPending())
                        <button type="button" data-open-dialog="reject-detail"
                            class="clay-button-danger inline-flex w-full justify-center whitespace-nowrap rounded-full px-6 py-3 text-sm font-bold">
                            Tolak Reservasi
                        </button>
                        <form method="POST" action="{{ route('petugas.reservasi.approve', $reservation) }}">
                            @csrf
                            <button type="submit" data-submit-loading data-loading-label="Menyetujui..."
                                class="landing-button inline-flex w-full justify-center whitespace-nowrap rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-3 text-sm font-bold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)]">
                                Setujui Reservasi
                            </button>
                        </form>
                    @endif
                    @if ($reservation->isCancellable() && ! $reservation->isPending())
                        <button type="button" data-open-dialog="cancel-detail"
                            class="clay-button-danger inline-flex w-full justify-center whitespace-nowrap rounded-full px-6 py-3 text-sm font-bold">
                            Batalkan Reservasi
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <dialog id="reject-detail" class="{{ $dialogShell }}" aria-labelledby="reject-dialog-title">
        <h3 id="reject-dialog-title" class="text-lg font-extrabold tracking-tight text-[#10264a]">Tolak reservasi?</h3>
        <p class="mt-1 text-sm leading-relaxed text-slate-600">
            {{ $reservation->facility->name }} ·
            {{ $reservation->start_time->locale('id')->translatedFormat('d M Y') }},
            {{ $reservation->start_time->format('H.i') }} – {{ $reservation->end_time->format('H.i') }} WIB
        </p>
        <form method="POST" action="{{ route('petugas.reservasi.reject', $reservation) }}" class="mt-5">
            @csrf
            <label for="reject-reason-detail" class="block text-sm font-semibold text-slate-700">Alasan penolakan</label>
            <textarea id="reject-reason-detail" name="reason" rows="3" required minlength="10" maxlength="255"
                placeholder="Jelaskan alasan penolakan (min. 10 karakter)"
                class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-rose-500/15"></textarea>
            @error('reason')
                <p class="mt-1.5 text-xs text-[#B42318]">{{ $message }}</p>
            @enderror
            <div class="mt-5 flex items-center justify-end gap-2">
                <button type="button" data-close-dialog="reject-detail"
                    class="clay-button-white inline-flex h-10 items-center justify-center rounded-full px-5 text-sm font-semibold text-blue-700">
                    Kembali
                </button>
                <button type="submit" data-submit-loading data-loading-label="Menolak..."
                    class="clay-button-danger inline-flex h-10 items-center justify-center rounded-full px-5 text-sm font-bold">
                    Tolak Reservasi
                </button>
            </div>
        </form>
    </dialog>

    <dialog id="cancel-detail" class="{{ $dialogShell }}" aria-labelledby="cancel-dialog-title">
        <h3 id="cancel-dialog-title" class="text-lg font-extrabold tracking-tight text-[#10264a]">Batalkan reservasi?</h3>
        <p class="mt-1 text-sm leading-relaxed text-slate-600">
            {{ $reservation->facility->name }} ·
            {{ $reservation->start_time->locale('id')->translatedFormat('d M Y') }},
            {{ $reservation->start_time->format('H.i') }} – {{ $reservation->end_time->format('H.i') }} WIB
        </p>
        <form method="POST" action="{{ route('petugas.reservasi.cancel', $reservation) }}" class="mt-5">
            @csrf
            <label for="cancel-reason-detail" class="block text-sm font-semibold text-slate-700">Alasan pembatalan</label>
            <textarea id="cancel-reason-detail" name="cancel_reason" rows="3" required minlength="10" maxlength="255"
                placeholder="Jelaskan alasan pembatalan (min. 10 karakter)"
                class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-rose-500/15"></textarea>
            @error('cancel_reason')
                <p class="mt-1.5 text-xs text-[#B42318]">{{ $message }}</p>
            @enderror
            <div class="mt-5 flex items-center justify-end gap-2">
                <button type="button" data-close-dialog="cancel-detail"
                    class="clay-button-white inline-flex h-10 items-center justify-center rounded-full px-5 text-sm font-semibold text-blue-700">
                    Kembali
                </button>
                <button type="submit" data-submit-loading data-loading-label="Membatalkan..."
                    class="clay-button-danger inline-flex h-10 items-center justify-center rounded-full px-5 text-sm font-bold">
                    Batalkan Reservasi
                </button>
            </div>
        </form>
    </dialog>
@endsection
