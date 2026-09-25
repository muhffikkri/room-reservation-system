@extends('layouts.app')

@section('title', 'Detail Reservasi')

@section('content')
    <div class="mx-auto max-w-3xl space-y-8">
        <div>
            <a href="{{ route('reservasi.index') }}"
                class="inline-flex items-center text-sm font-medium text-slate-500 transition hover:text-[#00236f]">
                &larr; Kembali ke riwayat reservasi
            </a>
            <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-2xl font-semibold tracking-tight text-[#00236f]">Detail Reservasi #{{ $reservation->id }}</h1>
                <div>
                    <x-ui.badge :status="$reservation->status">
                        {{ match ($reservation->status) {
                            'pending' => 'Menunggu Persetujuan',
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                            'cancelled_by_user' => 'Dibatalkan Pengguna',
                            'cancelled_by_officer' => 'Dibatalkan Petugas',
                            'cancelled_by_system' => 'Gagal',
                            default => ucfirst($reservation->status),
                        } }}
                    </x-ui.badge>
                </div>
            </div>
        </div>

        <div class="space-y-6 rounded-2xl border border-[#E2E7FF] bg-white p-6 shadow-[0_14px_40px_rgba(15,23,42,0.06)] sm:p-8">
            {{-- Info Fasilitas --}}
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-[0.16em] text-[#0051d5]">Informasi Fasilitas</h2>
                <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-[#EEF2FF] bg-[#F8FAFC] p-4">
                        <p class="text-xs text-slate-500">Nama Fasilitas</p>
                        <p class="mt-1 text-base font-semibold text-[#00236f]">{{ $reservation->facility->name }}</p>
                    </div>
                    <div class="rounded-xl border border-[#EEF2FF] bg-[#F8FAFC] p-4">
                        <p class="text-xs text-slate-500">Lokasi / Gedung</p>
                        <p class="mt-1 text-base font-semibold text-[#00236f]">{{ $reservation->facility->location }}</p>
                    </div>
                </div>
            </div>

            {{-- Waktu & Jadwal --}}
            <div class="border-t border-[#EEF2FF] pt-5">
                <h2 class="text-xs font-semibold uppercase tracking-[0.16em] text-[#0051d5]">Jadwal Penggunaan</h2>
                <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-xl border border-[#EEF2FF] bg-[#F8FAFC] p-4">
                        <p class="text-xs text-slate-500">Tanggal</p>
                        <p class="mt-1 text-sm font-medium text-[#00236f]">
                            {{ $reservation->start_time->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <div class="rounded-xl border border-[#EEF2FF] bg-[#F8FAFC] p-4">
                        <p class="text-xs text-slate-500">Waktu Mulai & Selesai</p>
                        <p class="mt-1 text-sm font-medium text-[#00236f]">
                            {{ $reservation->start_time->format('H:i') }} - {{ $reservation->end_time->format('H:i') }} WIB
                        </p>
                    </div>
                    <div class="rounded-xl border border-[#EEF2FF] bg-[#F8FAFC] p-4">
                        <p class="text-xs text-slate-500">Durasi</p>
                        <p class="mt-1 text-sm font-medium text-[#00236f]">
                            {{ $reservation->start_time->diffInMinutes($reservation->end_time) / 60 }} Jam
                        </p>
                    </div>
                </div>
            </div>

            {{-- Tujuan Peminjaman --}}
            <div class="border-t border-[#EEF2FF] pt-5">
                <h2 class="text-xs font-semibold uppercase tracking-[0.16em] text-[#0051d5]">Tujuan Penggunaan</h2>
                <div class="mt-2 rounded-xl border border-[#EEF2FF] bg-[#F8FAFC] p-4">
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $reservation->purpose }}</p>
                </div>
            </div>

            {{-- Catatan Penolakan / Pembatalan --}}
            @if ($reservation->reject_reason)
                <div class="rounded-lg border border-rose-200 bg-rose-50 p-4">
                    <h3 class="text-sm font-semibold text-rose-800">Alasan Penolakan:</h3>
                    <p class="mt-1 text-sm text-rose-700">{{ $reservation->reject_reason }}</p>
                </div>
            @endif

            @if ($reservation->cancel_reason)
                @php
                    $cancelReasonStyle = match ($reservation->status) {
                        'cancelled_by_officer' => ['border-amber-200 bg-amber-50', 'text-amber-800', 'text-amber-700', 'Alasan Pembatalan oleh Petugas:'],
                        'cancelled_by_system' => ['border-violet-200 bg-violet-50', 'text-violet-800', 'text-violet-700', 'Alasan Pembatalan Otomatis oleh Sistem:'],
                        default => ['border-slate-200 bg-slate-50', 'text-slate-800', 'text-slate-700', 'Alasan Pembatalan:'],
                    };
                @endphp
                <div class="rounded-lg border {{ $cancelReasonStyle[0] }} p-4">
                    <h3 class="text-sm font-semibold {{ $cancelReasonStyle[1] }}">
                        {{ $cancelReasonStyle[3] }}
                    </h3>
                    <p class="mt-1 text-sm {{ $cancelReasonStyle[2] }}">
                        {{ $reservation->cancel_reason }}
                    </p>
                </div>
            @endif

            {{-- Tombol Batal untuk Pengguna (BR-8) --}}
            @php
                $canCancel =
                    in_array($reservation->status, ['pending', 'approved'], true) &&
                    $reservation->start_time->isAfter(now()->addHour()) &&
                    $reservation->user_id === auth()->id();
                $isTooLate =
                    in_array($reservation->status, ['pending', 'approved'], true) &&
                    !$reservation->start_time->isAfter(now()->addHour()) &&
                    $reservation->user_id === auth()->id();
            @endphp

            <div class="flex flex-col items-center justify-between gap-4 border-t border-[#EEF2FF] pt-5 sm:flex-row">
                <a href="{{ route('reservasi.index') }}"
                    class="rounded-lg border border-[#D6DDF8] bg-white px-4 py-2 text-sm font-medium text-[#00236f] shadow-sm transition hover:bg-[#F2F3FF] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">
                    &larr; Kembali
                </a>

                @if ($canCancel)
                    <button type="button" data-open-dialog="cancel-modal"
                        class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                        Batalkan Reservasi
                    </button>
                @elseif ($isTooLate)
                    <span class="text-xs text-slate-400 italic">
                        Pembatalan sudah ditutup karena jadwal mulai kurang dari 1 jam lagi.
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Dialog Modal Pembatalan Reservasi --}}
    @if ($canCancel)
        <dialog id="cancel-modal" aria-labelledby="cancel-modal-title" class="w-full max-w-md rounded-2xl border border-[#E2E7FF] bg-white p-6 shadow-xl backdrop:bg-slate-950/40">
            <h3 id="cancel-modal-title" class="text-lg font-semibold text-[#00236f]">Batalkan Reservasi?</h3>
            <p class="mt-1 text-sm text-slate-500">
                {{ $reservation->facility->name }} &bull; {{ $reservation->start_time->translatedFormat('d M Y') }},
                {{ $reservation->start_time->format('H:i') }} – {{ $reservation->end_time->format('H:i') }} WIB
            </p>

            <form method="POST" action="{{ route('reservasi.destroy', $reservation) }}" class="mt-4 space-y-4">
                @csrf
                @method('DELETE')

                <div>
                    <label for="cancel_reason" class="block text-sm font-medium text-slate-700">
                        Alasan Pembatalan <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="cancel_reason" name="cancel_reason" rows="3" required minlength="5" maxlength="255" autofocus
                        placeholder="Contoh: Kegiatan dibatalkan karena ada perubahan jadwal mendadak..."
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-1 focus:ring-rose-500"></textarea>
                    <p class="mt-1 text-xs text-slate-400">Minimal 5 karakter.</p>
                    @error('cancel_reason')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" data-close-dialog
                        class="rounded-lg border border-[#D6DDF8] bg-white px-4 py-2 text-sm font-medium text-[#00236f] transition hover:bg-[#F2F3FF]">
                        Kembali
                    </button>
                    <button type="submit" data-submit-loading data-loading-label="Membatalkan..."
                        class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                        Konfirmasi Pembatalan
                    </button>
                </div>
            </form>
        </dialog>
    @endif
@endsection
