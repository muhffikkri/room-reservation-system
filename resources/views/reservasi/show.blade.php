@extends('layouts.app')

@use('App\Models\Reservation')

@section('title', 'Detail Reservasi | REKSA')

@section('content')
    @php
        $canCancel = auth()->user()->can('cancel', $reservation);
        $isTooLate = $reservation->isCancellable() && !$canCancel && $reservation->user_id === auth()->id();
        $detailCard =
            'flex min-h-20 items-center gap-3.5 rounded-2xl border border-white/90 bg-gradient-to-br from-blue-50/70 to-white/90 p-4 shadow-[inset_2px_2px_6px_rgba(99,137,193,0.08),0_4px_12px_rgba(116,155,211,0.08)]';
        $detailIcon =
            'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white bg-white text-blue-600 shadow-[0_4px_10px_rgba(59,130,246,0.15)]';
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">
        <div class="auth-clay-card rounded-[2rem] p-5 sm:p-7 lg:p-9 space-y-7">
            {{-- Header Section Inside Single Card --}}
            <div class="flex flex-col gap-4 border-b border-blue-100/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <a href="{{ route('reservasi.index') }}"
                        class="clay-pressable mb-3 inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold text-blue-700">
                        &larr; Kembali ke riwayat reservasi
                    </a>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Informasi Pemesanan · REKSA
                    </p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">
                        Detail Reservasi #{{ $reservation->id }}
                    </h1>
                    <p class="mt-1 text-xs text-slate-500">
                        Diajukan pada {{ $reservation->created_at->locale('id')->translatedFormat('l, d F Y, H:i') }} WIB
                    </p>
                </div>
                <div>
                    <x-ui.badge :status="$reservation->status">
                        {{ $reservation->status === 'cancelled_by_system' ? 'Gagal' : Reservation::statusLabel($reservation->status) }}
                    </x-ui.badge>
                </div>
            </div>

            {{-- Informasi Fasilitas --}}
            <section aria-labelledby="facility-heading">
                <h2 id="facility-heading"
                    class="mb-4 flex items-center gap-2.5 text-xs font-extrabold uppercase tracking-[0.14em] text-blue-700">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100/70 text-blue-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </span>
                    Informasi Fasilitas
                </h2>
                <div class="grid gap-3.5 sm:grid-cols-2">
                    <div class="{{ $detailCard }}">
                        <span class="{{ $detailIcon }}">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-xs font-medium text-slate-500">Nama Fasilitas</p>
                            <p class="mt-0.5 text-sm font-extrabold text-[#10264a]">{{ $reservation->facility->name }}</p>
                        </div>
                    </div>
                    <div class="{{ $detailCard }}">
                        <span class="{{ $detailIcon }}">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-xs font-medium text-slate-500">Lokasi / Gedung</p>
                            <p class="mt-0.5 text-sm font-extrabold text-[#10264a]">{{ $reservation->facility->location }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Jadwal Penggunaan --}}
            <section class="border-t border-blue-100/80 pt-6" aria-labelledby="schedule-heading">
                <h2 id="schedule-heading"
                    class="mb-4 flex items-center gap-2.5 text-xs font-extrabold uppercase tracking-[0.14em] text-blue-700">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100/70 text-blue-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    Jadwal Penggunaan
                </h2>
                <div class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="{{ $detailCard }}">
                        <span class="{{ $detailIcon }}">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-xs font-medium text-slate-500">Tanggal Penggunaan</p>
                            <p class="mt-0.5 text-sm font-extrabold text-[#10264a]">
                                {{ $reservation->start_time->locale('id')->translatedFormat('l, d F Y') }}</p>
                        </div>
                    </div>
                    <div class="{{ $detailCard }}">
                        <span class="{{ $detailIcon }}">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-xs font-medium text-slate-500">Waktu Mulai & Selesai</p>
                            <p class="mt-0.5 text-sm font-extrabold text-[#10264a]">
                                {{ $reservation->start_time->format('H:i') }} –
                                {{ $reservation->end_time->format('H:i') }} WIB</p>
                        </div>
                    </div>
                    <div class="{{ $detailCard }}">
                        <span class="{{ $detailIcon }}">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-xs font-medium text-slate-500">Durasi Peminjaman</p>
                            <p class="mt-0.5 text-sm font-extrabold text-[#10264a]">
                                {{ $reservation->start_time->diffInMinutes($reservation->end_time) / 60 }} Jam</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Tujuan Penggunaan --}}
            <section class="border-t border-blue-100/80 pt-6" aria-labelledby="purpose-heading">
                <h2 id="purpose-heading"
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
                    class="flex items-start gap-3.5 rounded-2xl border border-white/90 bg-gradient-to-br from-blue-50/70 to-white/90 p-4 sm:p-5 shadow-[inset_2px_2px_6px_rgba(99,137,193,0.08)]">
                    <span class="{{ $detailIcon }}">
                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </span>
                    <p class="pt-2 text-sm leading-relaxed text-slate-700 whitespace-pre-line">{{ $reservation->purpose }}
                    </p>
                </div>
            </section>

            {{-- Catatan Penolakan / Pembatalan --}}
            @if ($reservation->reject_reason)
                @php(
    $rejectReasonStyle = match ($reservation->status) {
        'rejected_by_system' => ['border-violet-200 bg-violet-50/80', 'text-violet-800', 'text-violet-700', 'Alasan Penolakan Otomatis oleh Sistem:'],
        default => ['border-rose-200 bg-rose-50/80', 'text-rose-800', 'text-rose-700', 'Alasan Penolakan:']
    }
)
                <div class="rounded-2xl border {{ $rejectReasonStyle[0] }} p-4.5">
                    <h3 class="text-xs font-bold uppercase tracking-wider {{ $rejectReasonStyle[1] }}">
                        {{ $rejectReasonStyle[3] }}</h3>
                    <p class="mt-1 text-sm leading-relaxed {{ $rejectReasonStyle[2] }}">{{ $reservation->reject_reason }}
                    </p>
                </div>
            @endif
            @if ($reservation->cancel_reason)
                @php(
    $cancelReasonStyle = match ($reservation->status) {
        'cancelled_by_officer' => ['border-amber-200 bg-amber-50/80', 'text-amber-800', 'text-amber-700', 'Alasan Pembatalan oleh Petugas:'],
        'cancelled_by_system' => ['border-violet-200 bg-violet-50/80', 'text-violet-800', 'text-violet-700', 'Alasan Pembatalan Otomatis oleh Sistem:'],
        default => ['border-slate-200 bg-slate-50/80', 'text-slate-800', 'text-slate-700', 'Alasan Pembatalan:']
    }
)
                <div class="rounded-2xl border {{ $cancelReasonStyle[0] }} p-4.5">
                    <h3 class="text-xs font-bold uppercase tracking-wider {{ $cancelReasonStyle[1] }}">
                        {{ $cancelReasonStyle[3] }}</h3>
                    <p class="mt-1 text-sm leading-relaxed {{ $cancelReasonStyle[2] }}">{{ $reservation->cancel_reason }}
                    </p>
                </div>
            @endif

            {{-- Action Buttons --}}
            <div
                class="flex flex-col-reverse items-stretch justify-between gap-3 border-t border-blue-100/80 pt-6 sm:flex-row sm:items-center">
                <a href="{{ route('reservasi.index') }}"
                    class="clay-pressable inline-flex justify-center rounded-full px-6 py-3 text-sm font-semibold text-blue-700">
                    &larr; Kembali ke daftar
                </a>
                @if ($canCancel)
                    <button type="button" data-open-dialog="cancel-modal"
                        class="clay-button-danger inline-flex justify-center rounded-full px-6 py-3 text-sm font-bold">
                        Batalkan Reservasi
                    </button>
                @elseif ($isTooLate)
                    <span class="text-xs italic text-slate-500">Pembatalan ditutup karena jadwal mulai kurang dari 1 jam
                        lagi.</span>
                @endif
            </div>
        </div>
    </div>

    @if ($canCancel)
        <dialog id="cancel-modal" aria-labelledby="cancel-modal-title"
            class="w-full max-w-md rounded-3xl border border-blue-100 bg-white p-6 shadow-2xl backdrop:bg-slate-950/40">
            <h3 id="cancel-modal-title" class="text-lg font-extrabold text-[#10264a]">Batalkan Reservasi?</h3>
            <p class="mt-1 text-sm text-slate-500">{{ $reservation->facility->name }} ·
                {{ $reservation->start_time->locale('id')->translatedFormat('d M Y') }},
                {{ $reservation->start_time->format('H:i') }} – {{ $reservation->end_time->format('H:i') }} WIB</p>
            <form method="POST" action="{{ route('reservasi.destroy', $reservation) }}" class="mt-4 space-y-4">
                @csrf
                @method('DELETE')
                <div>
                    <label for="cancel_reason" class="block text-sm font-semibold text-slate-700">Alasan Pembatalan <span
                            class="text-rose-500">*</span></label>
                    <textarea id="cancel_reason" name="cancel_reason" rows="3" required minlength="5" maxlength="255" autofocus
                        placeholder="Contoh: Kegiatan dibatalkan karena ada perubahan jadwal mendadak..."
                        class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-rose-500/15"></textarea>
                    <p class="mt-1 text-xs text-slate-400">Minimal 5 karakter.</p>
                    @error('cancel_reason')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" data-close-dialog
                        class="clay-pressable rounded-full px-5 py-2.5 text-sm font-semibold text-blue-700">Kembali</button>
                    <button type="submit" data-submit-loading data-loading-label="Membatalkan..."
                        class="clay-button-danger rounded-full px-5 py-2.5 text-sm font-bold">Konfirmasi
                        Pembatalan</button>
                </div>
            </form>
        </dialog>
    @endif
@endsection
