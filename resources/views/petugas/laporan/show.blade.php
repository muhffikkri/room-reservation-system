@extends('layouts.app')

@section('title', 'Penanganan Laporan #' . $report->id . ' | REKSA')

@section('content')
    @php
        $detailCard =
            'flex min-h-20 items-center gap-3.5 rounded-2xl border border-white/90 bg-gradient-to-br from-blue-50/70 to-white/90 p-4 shadow-[inset_2px_2px_6px_rgba(99,137,193,0.08),0_4px_12px_rgba(116,155,211,0.08)]';
        $detailIcon =
            'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white bg-white text-blue-600 shadow-[0_4px_10px_rgba(59,130,246,0.15)]';
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <section class="auth-clay-card rounded-[2rem] p-5 sm:p-7 lg:p-8" aria-labelledby="report-detail-heading">
                    <div
                        class="flex flex-col gap-4 border-b border-blue-100/80 pb-6 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <a href="{{ route('petugas.laporan.index') }}"
                                class="clay-button-white mb-3 inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold text-blue-700">
                                <span aria-hidden="true">&larr;</span> Kembali ke antrian laporan
                            </a>
                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Penanganan Laporan
                                Kerusakan</p>
                            <h1 id="report-detail-heading"
                                class="mt-1 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">Laporan
                                #{{ $report->id }}</h1>
                            <p class="mt-1 text-xs text-slate-500">
                                Dilaporkan oleh <span class="font-bold text-slate-700">{{ $report->user->name }}</span>
                                ({{ $report->user->email }}) pada
                                {{ $report->created_at->locale('id')->translatedFormat('d F Y, H:i') }} WIB
                            </p>
                        </div>
                        <x-ui.badge :status="$report->status" dot />
                    </div>

                    <div class="mt-6 grid gap-3.5 sm:grid-cols-2">
                        <div class="{{ $detailCard }}">
                            <span class="{{ $detailIcon }}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-slate-500">Fasilitas Kampus</p>
                                <p class="mt-0.5 text-sm font-extrabold text-[#10264a]">{{ $report->facility->name }}</p>
                                <p class="text-xs text-slate-500">{{ $report->facility->location }}</p>
                                <div class="mt-1.5"><x-ui.badge :status="$report->facility->status" /></div>
                            </div>
                        </div>
                        <div class="{{ $detailCard }}">
                            <span class="{{ $detailIcon }}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 3h7l5 5v13H5V3h2Zm6 1v5h5M8 13h8M8 17h8" />
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-slate-500">Kategori Kerusakan</p>
                                <p class="mt-0.5 text-sm font-extrabold text-[#10264a]">{{ $report->categoryLabel() }}</p>
                            </div>
                        </div>
                    </div>

                    <section class="mt-6 border-t border-blue-100/80 pt-6" aria-labelledby="report-description-heading">
                        <h2 id="report-description-heading"
                            class="mb-3 text-xs font-extrabold uppercase tracking-[0.14em] text-blue-700">Deskripsi
                            Kerusakan</h2>
                        <div
                            class="rounded-2xl border border-white/90 bg-gradient-to-br from-blue-50/70 to-white/90 p-4 shadow-[inset_2px_2px_6px_rgba(99,137,193,0.08)] sm:p-5">
                            <p class="text-sm leading-relaxed whitespace-pre-line text-slate-700">{{ $report->description }}</p>
                        </div>
                    </section>

                    @if ($report->photo)
                        <section class="mt-6 border-t border-blue-100/80 pt-6" aria-labelledby="report-photo-heading">
                            <h2 id="report-photo-heading"
                                class="mb-3 text-xs font-extrabold uppercase tracking-[0.14em] text-blue-700">Foto Bukti
                                Kerusakan</h2>
                            <div class="max-w-md overflow-hidden rounded-2xl border border-white/90 bg-white p-2 shadow-md">
                                <img src="{{ route('petugas.laporan.photo', $report) }}" alt="Foto laporan"
                                    loading="lazy" class="img-fade h-auto max-h-80 w-full rounded-xl object-cover">
                            </div>
                        </section>
                    @endif

                    @if ($report->resolution_note)
                        <div class="mt-6 rounded-2xl border border-blue-200 bg-blue-50/70 p-4 sm:p-5">
                            <h3 class="text-xs font-extrabold uppercase tracking-[0.14em] text-blue-800">Catatan
                                Resolusi / Penanganan Terakhir</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-800">{{ $report->resolution_note }}</p>
                            @if ($report->handledBy)
                                <p class="mt-2 text-xs text-slate-500">
                                    Ditangani oleh: <span class="font-bold text-slate-700">{{ $report->handledBy->name }}</span>
                                    @if ($report->handled_at)
                                        pada {{ $report->handled_at->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                                    @endif
                                </p>
                            @endif
                        </div>
                    @endif
                </section>

                <section class="auth-clay-card rounded-[2rem] p-5 sm:p-7" aria-labelledby="report-timeline-heading">
                    <h2 id="report-timeline-heading" class="text-base font-extrabold tracking-tight text-[#10264a]">Riwayat
                        Transisi Status</h2>
                    @if ($report->updates->isEmpty())
                        <p class="clay-inset mt-4 rounded-2xl p-4 text-xs italic text-slate-500">Belum ada riwayat
                            transisi status.</p>
                    @else
                        <ol class="relative ml-3 mt-5 space-y-4 border-l-2 border-blue-200">
                            @foreach ($report->updates as $update)
                                <li class="ml-6">
                                    <span
                                        class="absolute -left-3.5 flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white shadow-sm ring-4 ring-white">
                                        {{ $loop->iteration }}
                                    </span>
                                    <div
                                        class="rounded-2xl border border-white/90 bg-gradient-to-br from-blue-50/60 to-white/90 p-4">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <span class="text-xs font-semibold text-slate-800">
                                                Status diubah dari
                                                <span class="font-extrabold text-slate-600">{{ $update->old_status ?: 'Awal' }}</span>
                                                menjadi
                                                <span class="font-extrabold capitalize text-blue-600">{{ $update->new_status }}</span>
                                            </span>
                                            <time class="text-xs font-medium text-slate-500">
                                                {{ $update->created_at->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                                            </time>
                                        </div>
                                        @if ($update->note)
                                            <p
                                                class="mt-2 rounded-xl border border-blue-100/80 bg-white/90 p-2.5 text-xs leading-relaxed text-slate-600">
                                                {{ $update->note }}
                                            </p>
                                        @endif
                                        <p class="mt-1.5 text-[11px] font-medium text-slate-400">
                                            Petugas: {{ $update->user->name ?? '-' }}
                                        </p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>
            </div>

            <aside class="space-y-6">
                <section class="auth-clay-card rounded-[2rem] p-5 sm:p-6" aria-labelledby="report-actions-heading">
                    <h2 id="report-actions-heading" class="text-base font-extrabold tracking-tight text-[#10264a]">Tindakan
                        Petugas</h2>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">Status laporan hanya dapat diubah mengikuti
                        transisi yang diizinkan.</p>

                    @if (empty($allowedTransitions))
                        <p class="clay-inset mt-4 rounded-2xl p-4 text-xs leading-relaxed text-slate-600">
                            Laporan ini sudah ditutup dan tidak dapat diubah lagi.
                        </p>
                    @else
                        <form method="POST" action="{{ route('petugas.laporan.status', $report) }}"
                            class="mt-4 space-y-4">
                            @csrf
                            @method('PATCH')

                            <div>
                                <label for="status"
                                    class="block text-xs font-bold uppercase tracking-[0.12em] text-slate-600">Ubah Status
                                    Laporan</label>
                                <select id="status" name="status" required
                                    class="landing-input mt-1.5 block h-11 w-full appearance-none rounded-2xl px-4 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                                    <option value="" disabled selected>-- Pilih Transisi Status --</option>
                                    @foreach ($allowedTransitions as $targetStatus)
                                        <option value="{{ $targetStatus }}" {{ old('status') === $targetStatus ? 'selected' : '' }}>
                                            {{ ucfirst($targetStatus) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <p class="mt-1.5 text-xs text-[#B42318]">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="resolution_note"
                                    class="block text-xs font-bold uppercase tracking-[0.12em] text-slate-600">Catatan Resolusi /
                                    Tindakan</label>
                                <p class="mt-1 text-[11px] leading-relaxed text-slate-500">Wajib diisi minimal 10 karakter
                                    saat menutup laporan (Selesai/Ditolak).</p>
                                <textarea id="resolution_note" name="resolution_note" rows="3" minlength="10"
                                    placeholder="Jelaskan tindakan perbaikan atau alasan penolakan..."
                                    class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-blue-500/15">{{ old('resolution_note') }}</textarea>
                                @error('resolution_note')
                                    <p class="mt-1.5 text-xs text-[#B42318]">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" data-submit-loading data-loading-label="Menyimpan..."
                                class="landing-button inline-flex w-full justify-center rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-3 text-sm font-bold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)]">
                                Simpan Perubahan Status
                            </button>
                        </form>
                    @endif
                </section>

                <section class="auth-clay-card rounded-[2rem] p-5 sm:p-6" aria-labelledby="report-facility-heading">
                    <h2 id="report-facility-heading" class="text-base font-extrabold tracking-tight text-[#10264a]">Kelola
                        Fasilitas Terkait</h2>

                    @if ($report->status === 'diproses' && $report->facility->status !== 'perbaikan')
                        <p class="mt-2 text-xs leading-relaxed text-slate-600">Laporan sedang diproses. Anda dapat menandai
                            fasilitas ini sedang dalam perbaikan.</p>
                        <form method="POST" action="{{ route('petugas.laporan.fasilitas-status', $report) }}"
                            class="mt-4">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="perbaikan">
                            <button type="submit" data-submit-loading data-loading-label="Menandai..."
                                data-confirm-message="Tandai fasilitas sebagai PERBAIKAN? Fasilitas tidak dapat direservasi selama perbaikan."
                                class="inline-flex w-full justify-center rounded-full border border-amber-200 bg-gradient-to-br from-amber-50 to-white px-5 py-3 text-xs font-bold text-amber-700 transition hover:from-amber-100 hover:to-white">
                                Tandai Fasilitas PERBAIKAN
                            </button>
                        </form>
                    @elseif ($report->status === 'selesai' && $report->facility->status === 'perbaikan')
                        <p class="mt-2 text-xs leading-relaxed text-slate-600">Laporan sudah selesai. Anda dapat
                            mengembalikan fasilitas ini ke status AKTIF.</p>
                        <form method="POST" action="{{ route('petugas.laporan.fasilitas-status', $report) }}"
                            class="mt-4">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="aktif">
                            <button type="submit" data-submit-loading data-loading-label="Mengembalikan..."
                                data-confirm-message="Kembalikan fasilitas ke status AKTIF?"
                                class="inline-flex w-full justify-center rounded-full border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white px-5 py-3 text-xs font-bold text-emerald-700 transition hover:from-emerald-100 hover:to-white">
                                Kembalikan Fasilitas ke AKTIF
                            </button>
                        </form>
                    @else
                        <p class="clay-inset mt-4 rounded-2xl p-4 text-xs leading-relaxed text-slate-600">
                            @if ($report->facility->status === 'perbaikan')
                                Fasilitas sedang dalam status PERBAIKAN.
                            @else
                                Fasilitas sedang dalam status {{ strtoupper($report->facility->status) }}.
                            @endif
                        </p>
                    @endif
                </section>

                @if (isset($affectedReservations) && $affectedReservations->isNotEmpty())
                    <section class="auth-clay-card rounded-[2rem] p-5 sm:p-6"
                        aria-labelledby="affected-reservations-heading">
                        <h2 id="affected-reservations-heading"
                            class="text-base font-extrabold tracking-tight text-[#10264a]">
                            Reservasi yang Perlu Ditinjau
                        </h2>
                        <p class="mt-1 text-[11px] leading-relaxed text-slate-500">
                            Menampilkan maksimal 10 reservasi terdekat yang masih disetujui pada fasilitas yang sedang perbaikan. Batalkan jika jadwalnya bertabrakan dengan perbaikan.
                        </p>

                        <ul class="mt-4 space-y-2">
                            @foreach ($affectedReservations as $res)
                                <li class="clay-inset flex items-center justify-between gap-4 rounded-2xl p-3 text-xs">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $res->user->name }}</p>
                                        <p class="text-slate-500">
                                            {{ \Carbon\Carbon::parse($res->start_time)->isoFormat('D MMM YYYY, HH:mm') }}
                                            &ndash;
                                            {{ \Carbon\Carbon::parse($res->end_time)->format('H:i') }}
                                        </p>
                                    </div>
                                    <a href="{{ route('petugas.reservasi.show', $res) }}"
                                        class="clay-button-white shrink-0 rounded-full px-3 py-1.5 text-[11px] font-bold text-slate-700">
                                        Tinjau &amp; Batalkan
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-4 border-t border-blue-100/70 pt-3 text-right">
                            <a href="{{ route('petugas.reservasi.index', ['facility_id' => $report->facility_id, 'status' => 'approved']) }}"
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 transition">
                                <span>Buka seluruh antrean fasilitas ini</span>
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </div>
@endsection
