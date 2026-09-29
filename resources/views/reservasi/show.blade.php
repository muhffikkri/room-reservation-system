@extends('layouts.app')

@use('App\Models\Reservation')

@section('title', 'Detail Reservasi | REKSA')

@section('content')
    @php
        $canCancel = auth()->user()->can('cancel', $reservation);
        $isTooLate = $reservation->isCancellable() && ! $canCancel && $reservation->user_id === auth()->id();
        $detailCard = 'flex min-h-24 items-center gap-4 rounded-2xl border border-white bg-gradient-to-br from-blue-50/80 to-white/80 p-4 shadow-[inset_2px_2px_6px_rgba(99,137,193,0.08),0_5px_14px_rgba(116,155,211,0.08)] sm:p-5';
        $detailIcon = 'flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-white bg-white text-blue-600 shadow-[0_5px_14px_rgba(59,130,246,0.14)]';
    @endphp

    <div class="space-y-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div><a href="{{ route('reservasi.index') }}" class="clay-pressable inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold text-blue-700">← Kembali ke riwayat reservasi</a><h1 class="mt-4 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">Detail Reservasi #{{ $reservation->id }}</h1></div>
            <x-ui.badge :status="$reservation->status">{{ $reservation->status === 'cancelled_by_system' ? 'Gagal' : Reservation::statusLabel($reservation->status) }}</x-ui.badge>
        </div>

        <article class="landing-panel space-y-7 rounded-[2rem] p-5 shadow-[0_16px_44px_rgba(53,103,175,0.12)] sm:p-7 lg:p-9">
            <section aria-labelledby="facility-heading">
                <h2 id="facility-heading" class="mb-4 flex items-center gap-3 text-xs font-extrabold uppercase tracking-[0.14em] text-blue-700"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-base">⌂</span> Informasi fasilitas</h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="{{ $detailCard }}"><span class="{{ $detailIcon }}">▤</span><div><p class="text-xs text-slate-500">Nama Fasilitas</p><p class="mt-1 font-bold text-slate-900">{{ $reservation->facility->name }}</p></div></div>
                    <div class="{{ $detailCard }}"><span class="{{ $detailIcon }}">⌖</span><div><p class="text-xs text-slate-500">Lokasi / Gedung</p><p class="mt-1 font-bold text-slate-900">{{ $reservation->facility->location }}</p></div></div>
                </div>
            </section>

            <section class="border-t border-blue-100/80 pt-6" aria-labelledby="schedule-heading">
                <h2 id="schedule-heading" class="mb-4 flex items-center gap-3 text-xs font-extrabold uppercase tracking-[0.14em] text-blue-700"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-base">▦</span> Jadwal penggunaan</h2>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="{{ $detailCard }}"><span class="{{ $detailIcon }}">▣</span><div><p class="text-xs text-slate-500">Tanggal</p><p class="mt-1 font-bold text-slate-900">{{ $reservation->start_time->translatedFormat('l, d F Y') }}</p></div></div>
                    <div class="{{ $detailCard }}"><span class="{{ $detailIcon }}">◷</span><div><p class="text-xs text-slate-500">Waktu Mulai & Selesai</p><p class="mt-1 font-bold text-slate-900">{{ $reservation->start_time->format('H:i') }} – {{ $reservation->end_time->format('H:i') }} WIB</p></div></div>
                    <div class="{{ $detailCard }}"><span class="{{ $detailIcon }}">⌛</span><div><p class="text-xs text-slate-500">Durasi</p><p class="mt-1 font-bold text-slate-900">{{ $reservation->start_time->diffInMinutes($reservation->end_time) / 60 }} Jam</p></div></div>
                </div>
            </section>

            <section class="border-t border-blue-100/80 pt-6" aria-labelledby="purpose-heading">
                <h2 id="purpose-heading" class="mb-4 flex items-center gap-3 text-xs font-extrabold uppercase tracking-[0.14em] text-blue-700"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-base">◎</span> Tujuan penggunaan</h2>
                <div class="flex items-start gap-4 rounded-2xl border border-white bg-gradient-to-br from-blue-50/80 to-white/80 p-4 sm:p-5"><span class="{{ $detailIcon }}">▤</span><p class="pt-2 text-sm leading-relaxed text-slate-700 whitespace-pre-line">{{ $reservation->purpose }}</p></div>
            </section>

            @if ($reservation->reject_reason)
                @php($rejectReasonStyle = match ($reservation->status) { 'rejected_by_system' => ['border-violet-200 bg-violet-50', 'text-violet-800', 'text-violet-700', 'Alasan Penolakan Otomatis oleh Sistem:'], default => ['border-rose-200 bg-rose-50', 'text-rose-800', 'text-rose-700', 'Alasan Penolakan:'] })
                <div class="rounded-2xl border {{ $rejectReasonStyle[0] }} p-4"><h3 class="text-sm font-bold {{ $rejectReasonStyle[1] }}">{{ $rejectReasonStyle[3] }}</h3><p class="mt-1 text-sm {{ $rejectReasonStyle[2] }}">{{ $reservation->reject_reason }}</p></div>
            @endif
            @if ($reservation->cancel_reason)
                @php($cancelReasonStyle = match ($reservation->status) { 'cancelled_by_officer' => ['border-amber-200 bg-amber-50', 'text-amber-800', 'text-amber-700', 'Alasan Pembatalan oleh Petugas:'], 'cancelled_by_system' => ['border-violet-200 bg-violet-50', 'text-violet-800', 'text-violet-700', 'Alasan Pembatalan Otomatis oleh Sistem:'], default => ['border-slate-200 bg-slate-50', 'text-slate-800', 'text-slate-700', 'Alasan Pembatalan:'] })
                <div class="rounded-2xl border {{ $cancelReasonStyle[0] }} p-4"><h3 class="text-sm font-bold {{ $cancelReasonStyle[1] }}">{{ $cancelReasonStyle[3] }}</h3><p class="mt-1 text-sm {{ $cancelReasonStyle[2] }}">{{ $reservation->cancel_reason }}</p></div>
            @endif

            <div class="flex flex-col-reverse items-stretch justify-between gap-3 border-t border-blue-100/80 pt-6 sm:flex-row sm:items-center">
                <a href="{{ route('reservasi.index') }}" class="clay-pressable inline-flex justify-center rounded-full px-5 py-3 text-sm font-semibold text-blue-700">← Kembali</a>
                @if ($canCancel)
                    <button type="button" data-open-dialog="cancel-modal" class="clay-button-danger inline-flex justify-center rounded-full px-5 py-3 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">Batalkan Reservasi</button>
                @elseif ($isTooLate)
                    <span class="text-xs italic text-slate-500">Pembatalan ditutup karena jadwal mulai kurang dari 1 jam lagi.</span>
                @endif
            </div>
        </article>
    </div>

    @if ($canCancel)
        <dialog id="cancel-modal" aria-labelledby="cancel-modal-title" class="w-full max-w-md rounded-3xl border border-blue-100 bg-white p-6 shadow-2xl backdrop:bg-slate-950/40">
            <h3 id="cancel-modal-title" class="text-lg font-extrabold text-[#10264a]">Batalkan Reservasi?</h3>
            <p class="mt-1 text-sm text-slate-500">{{ $reservation->facility->name }} · {{ $reservation->start_time->translatedFormat('d M Y') }}, {{ $reservation->start_time->format('H:i') }} – {{ $reservation->end_time->format('H:i') }} WIB</p>
            <form method="POST" action="{{ route('reservasi.destroy', $reservation) }}" class="mt-4 space-y-4">
                @csrf
                @method('DELETE')
                <div><label for="cancel_reason" class="block text-sm font-semibold text-slate-700">Alasan Pembatalan <span class="text-rose-500">*</span></label><textarea id="cancel_reason" name="cancel_reason" rows="3" required minlength="5" maxlength="255" autofocus placeholder="Contoh: Kegiatan dibatalkan karena ada perubahan jadwal mendadak..." class="landing-input mt-1 block w-full rounded-xl px-3 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-rose-400"></textarea><p class="mt-1 text-xs text-slate-400">Minimal 5 karakter.</p>@error('cancel_reason')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                <div class="flex items-center justify-end gap-3 pt-2"><button type="button" data-close-dialog class="clay-pressable rounded-full px-4 py-2.5 text-sm font-semibold text-blue-700">Kembali</button><button type="submit" data-submit-loading data-loading-label="Membatalkan..." class="clay-button-danger rounded-full px-4 py-2.5 text-sm font-bold">Konfirmasi Pembatalan</button></div>
            </form>
        </dialog>
    @endif
@endsection
