@extends('layouts.app')

@use('App\Models\Reservation')

@section('title', 'Antrian Reservasi | REKSA')

@section('content')
    @php
        $clayTableHead = 'border-b border-blue-100/80 bg-blue-50/50 px-5 py-3.5 text-[11px] font-extrabold uppercase tracking-[0.14em] text-blue-700';
        $clayTableCell = 'px-5 py-4 align-middle';
        $rowClass = 'transition-colors hover:bg-white/70';
        // Aksi di baris memakai token clay yang sama dengan tombol lain: Tolak
        // dan Batalkan merah seperti tombol Keluar, Setujui biru, Detail putih
        // dengan efek hover dan tekan, dan Detail selalu paling kanan.
        $chipLayout = 'inline-flex h-9 items-center justify-center whitespace-nowrap rounded-full px-4 text-xs font-bold';
        $detailChip = "clay-pressable {$chipLayout} text-slate-600";
        $approveChip = "landing-button {$chipLayout} bg-gradient-to-r from-blue-600 to-blue-500 text-white";
        $dangerChip = "clay-button-danger {$chipLayout}";
        $dialogShell = 'w-full max-w-md rounded-[1.8rem] border border-white/90 bg-gradient-to-br from-white/98 to-blue-50/80 p-6 shadow-[0_24px_60px_rgba(16,38,74,0.24),inset_2px_2px_6px_rgba(255,255,255,0.9)] backdrop:bg-slate-950/40';
    @endphp

    <div class="space-y-5">
        <section class="auth-clay-card rounded-[2rem] p-5 sm:p-7 lg:p-8" aria-labelledby="officer-queue-heading">
            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Pusat operasional</p>
            <h1 id="officer-queue-heading"
                class="mt-0.5 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">Antrian Reservasi</h1>
            <p class="mt-1 text-sm leading-relaxed text-slate-600">Setujui atau tolak reservasi yang menunggu,
                dan tinjau reservasi yang sudah selesai.</p>

            <nav class="mt-6 flex flex-wrap gap-2" aria-label="Tab antrean reservasi">
                <x-ui.filter-chip :href="route('petugas.reservasi.index', ['tab' => 'menunggu'])"
                    :label="Reservation::statusLabel('pending')" :active="$tab === 'menunggu'" tab="menunggu" />
                <x-ui.filter-chip :href="route('petugas.reservasi.index', ['tab' => 'selesai'])" label="Selesai"
                    :active="$tab === 'selesai'" tab="selesai" />
            </nav>

            <div id="loading-indicator" class="hidden clay-inset mt-6 rounded-2xl px-4 py-12 text-center">
                <svg class="mx-auto h-8 w-8 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.37 0 0 5.37 0 12h4z"></path>
                </svg>
                <p class="mt-3 text-sm text-slate-500">Memuat data...</p>
            </div>

            <div class="clay-inset mt-6 overflow-hidden rounded-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-full text-left text-sm" id="reservation-table">
                        <thead>
                            <tr>
                                <th scope="col" class="{{ $clayTableHead }}">Pemohon</th>
                                <th scope="col" class="{{ $clayTableHead }}">Fasilitas</th>
                                <th scope="col" class="{{ $clayTableHead }}">Jadwal</th>
                                <th scope="col" class="{{ $clayTableHead }}">Status</th>
                                <th scope="col" class="{{ $clayTableHead }}">Waktu Diajukan</th>
                                <th scope="col" class="{{ $clayTableHead }} text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-100/70" id="reservation-body">
                            @forelse ($reservations as $reservation)
                                @php
                                    $canCancel = $reservation->isCancellable() && ! $reservation->isPending();
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td class="{{ $clayTableCell }}">
                                        <p class="font-bold text-[#10264a]">{{ $reservation->user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $reservation->user->email }}</p>
                                    </td>
                                    <td class="{{ $clayTableCell }}">
                                        <p class="font-bold text-[#10264a]">{{ $reservation->facility->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $reservation->facility->location }}</p>
                                    </td>
                                    <td class="{{ $clayTableCell }} whitespace-nowrap text-slate-700">
                                        {{ $reservation->start_time->locale('id')->translatedFormat('d M Y') }}
                                        <span class="mt-0.5 block text-xs text-slate-500">
                                            {{ $reservation->start_time->format('H.i') }} –
                                            {{ $reservation->end_time->format('H.i') }} WIB
                                        </span>
                                    </td>
                                    <td class="{{ $clayTableCell }}">
                                        <x-reservation.status-pill :status="$reservation->status" plain />
                                    </td>
                                    <td class="{{ $clayTableCell }} whitespace-nowrap text-xs text-slate-500">
                                        {{ $reservation->created_at->locale('id')->translatedFormat('d M Y, H.i') }} WIB
                                    </td>
                                    <td class="{{ $clayTableCell }}">
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            @if ($reservation->isPending())
                                                <button type="button" data-open-dialog="reject-{{ $reservation->id }}"
                                                    class="{{ $dangerChip }}">Tolak</button>
                                                <button type="button" data-open-dialog="approve-{{ $reservation->id }}"
                                                    class="{{ $approveChip }}">Setujui</button>
                                            @endif
                                            @if ($canCancel)
                                                <button type="button" data-open-dialog="cancel-{{ $reservation->id }}"
                                                    class="{{ $dangerChip }}">Batalkan</button>
                                            @endif
                                            <a href="{{ route('petugas.reservasi.show', $reservation) }}"
                                                class="{{ $detailChip }}">Detail</a>
                                        </div>
                                    </td>
                                </tr>

                                @if ($reservation->isPending())
                                    <dialog id="approve-{{ $reservation->id }}" class="{{ $dialogShell }}"
                                        aria-labelledby="approve-{{ $reservation->id }}-title">
                                        <h3 id="approve-{{ $reservation->id }}-title"
                                            class="text-lg font-extrabold tracking-tight text-[#10264a]">Setujui reservasi?</h3>
                                        <p class="mt-1 text-sm leading-relaxed text-slate-600">
                                            {{ $reservation->facility->name }} ·
                                            {{ $reservation->start_time->locale('id')->translatedFormat('d M Y') }},
                                            {{ $reservation->start_time->format('H.i') }} –
                                            {{ $reservation->end_time->format('H.i') }} WIB
                                        </p>
                                        <form method="POST" action="{{ route('petugas.reservasi.approve', $reservation) }}"
                                            class="mt-5">
                                            @csrf
                                            <p class="clay-inset rounded-2xl p-3 text-xs leading-relaxed text-slate-600">
                                                Menyetujui mengunci slot dan menolak reservasi lain yang bertabrakan.
                                            </p>
                                            <div class="mt-4 flex items-center justify-end gap-2">
                                                <button type="button" data-close-dialog="approve-{{ $reservation->id }}"
                                                    class="clay-pressable inline-flex h-10 items-center justify-center rounded-full px-5 text-sm font-semibold text-blue-700">
                                                    Kembali
                                                </button>
                                                <button type="submit" data-submit-loading data-loading-label="Menyetujui..."
                                                    class="landing-button inline-flex h-10 items-center justify-center rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 text-sm font-bold text-white">
                                                    Konfirmasi Setujui
                                                </button>
                                            </div>
                                        </form>
                                    </dialog>

                                    <dialog id="reject-{{ $reservation->id }}" class="{{ $dialogShell }}"
                                        aria-labelledby="reject-{{ $reservation->id }}-title">
                                        <h3 id="reject-{{ $reservation->id }}-title"
                                            class="text-lg font-extrabold tracking-tight text-[#10264a]">Tolak reservasi?</h3>
                                        <p class="mt-1 text-sm leading-relaxed text-slate-600">
                                            {{ $reservation->facility->name }} ·
                                            {{ $reservation->start_time->locale('id')->translatedFormat('d M Y') }},
                                            {{ $reservation->start_time->format('H.i') }} –
                                            {{ $reservation->end_time->format('H.i') }} WIB
                                        </p>
                                        <form method="POST" action="{{ route('petugas.reservasi.reject', $reservation) }}"
                                            class="mt-5">
                                            @csrf
                                            <div>
                                                <label for="reject-reason-{{ $reservation->id }}"
                                                    class="block text-sm font-semibold text-slate-700">Alasan penolakan</label>
                                                <textarea id="reject-reason-{{ $reservation->id }}" name="reason" rows="3"
                                                    required minlength="10" maxlength="255"
                                                    placeholder="Jelaskan alasan penolakan (min. 10 karakter)"
                                                    class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-rose-500/15"></textarea>
                                                @error('reason')
                                                    <p class="mt-1.5 text-xs text-[#B42318]">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="mt-4 flex items-center justify-end gap-2">
                                                <button type="button" data-close-dialog="reject-{{ $reservation->id }}"
                                                    class="clay-pressable inline-flex h-10 items-center justify-center rounded-full px-5 text-sm font-semibold text-blue-700">
                                                    Kembali
                                                </button>
                                                <button type="submit" data-submit-loading data-loading-label="Menolak..."
                                                    class="clay-button-danger inline-flex h-10 items-center justify-center rounded-full px-5 text-sm font-bold">
                                                    Tolak Reservasi
                                                </button>
                                            </div>
                                        </form>
                                    </dialog>
                                @endif

                                @if ($canCancel)
                                    <dialog id="cancel-{{ $reservation->id }}" class="{{ $dialogShell }}"
                                        aria-labelledby="cancel-{{ $reservation->id }}-title">
                                        <h3 id="cancel-{{ $reservation->id }}-title"
                                            class="text-lg font-extrabold tracking-tight text-[#10264a]">Batalkan reservasi?</h3>
                                        <p class="mt-1 text-sm leading-relaxed text-slate-600">
                                            {{ $reservation->facility->name }} ·
                                            {{ $reservation->start_time->locale('id')->translatedFormat('d M Y') }},
                                            {{ $reservation->start_time->format('H.i') }} –
                                            {{ $reservation->end_time->format('H.i') }} WIB
                                        </p>
                                        <form method="POST" action="{{ route('petugas.reservasi.cancel', $reservation) }}"
                                            class="mt-5">
                                            @csrf
                                            <div>
                                                <label for="cancel-reason-{{ $reservation->id }}"
                                                    class="block text-sm font-semibold text-slate-700">Alasan pembatalan</label>
                                                <textarea id="cancel-reason-{{ $reservation->id }}" name="cancel_reason"
                                                    rows="3" required minlength="10" maxlength="255"
                                                    placeholder="Jelaskan alasan pembatalan (min. 10 karakter)"
                                                    class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-rose-500/15"></textarea>
                                                @error('cancel_reason')
                                                    <p class="mt-1.5 text-xs text-[#B42318]">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="mt-4 flex items-center justify-end gap-2">
                                                <button type="button" data-close-dialog="cancel-{{ $reservation->id }}"
                                                    class="clay-pressable inline-flex h-10 items-center justify-center rounded-full px-5 text-sm font-semibold text-blue-700">
                                                    Kembali
                                                </button>
                                                <button type="submit" data-submit-loading data-loading-label="Membatalkan..."
                                                    class="clay-button-danger inline-flex h-10 items-center justify-center rounded-full px-5 text-sm font-bold">
                                                    Batalkan Reservasi
                                                </button>
                                            </div>
                                        </form>
                                    </dialog>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-14 text-center">
                                        <span
                                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-white bg-white text-2xl text-blue-600 shadow-[0_4px_10px_rgba(59,130,246,0.15)]">
                                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M8 7V3m8 4V3M4 11h16M6 7h12a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V9a2 2 0 012-2z" />
                                            </svg>
                                        </span>
                                        <p class="mt-4 text-base font-bold text-slate-800">Tidak ada reservasi</p>
                                        <p class="mt-1 text-sm text-slate-500">Belum ada reservasi pada tab ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($reservations->hasPages())
                <div class="mt-5 border-t border-blue-100/80 pt-4" id="pagination-container">
                    {{ $reservations->links() }}
                </div>
            @else
                <div class="hidden" id="pagination-container"></div>
            @endif
        </section>
    </div>
@endsection
