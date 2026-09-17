@extends('layouts.app')

@section('title', 'Jadwal ' . $facility->name . ' - Ketersediaan Slot')

@section('content')
<div class="space-y-8">
    <!-- Breadcrumb Navigasi -->
    <nav class="flex text-xs text-slate-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <a href="{{ route('home') }}" class="transition hover:text-[#00236f]">Beranda</a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="h-4 w-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                    <a href="{{ route('fasilitas.index') }}" class="ml-1 transition hover:text-[#00236f] md:ml-2">Katalog Fasilitas</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="h-4 w-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                    <a href="{{ route('fasilitas.show', $facility) }}" class="ml-1 max-w-xs truncate transition hover:text-[#00236f] md:ml-2">{{ $facility->name }}</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="h-4 w-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span class="ml-1 font-medium text-[#00236f] md:ml-2">Jadwal Slot</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header Ringkasan Fasilitas -->
    <div class="flex flex-col gap-4 rounded-2xl border border-[#E2E7FF] bg-white p-6 shadow-[0_14px_40px_rgba(15,23,42,0.06)] sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1.5">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-semibold tracking-tight text-[#00236f]">
                    Jadwal Ketersediaan Slot
                </h1>
                <x-ui.badge :status="$facility->status" :dot="true">
                    {{ ucfirst($facility->status) }}
                </x-ui.badge>
            </div>
            <p class="text-sm text-slate-600">
                Fasilitas: <strong class="text-[#00236f]">{{ $facility->name }}</strong> &bull; Lokasi: {{ $facility->location }} &bull; Kapasitas: {{ $facility->capacity }} orang
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a
                href="{{ route('fasilitas.show', $facility) }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-[#D6DDF8] bg-white px-3.5 py-2 text-xs font-semibold text-[#00236f] shadow-sm transition hover:bg-[#F2F3FF]"
            >
                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Detail Fasilitas
            </a>
        </div>
    </div>

    <!-- Banner Status Jika Fasilitas Tidak Aktif atau Sedang Perbaikan -->
    @if ($facility->status === 'perbaikan')
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800 sm:text-sm">
            <div class="flex items-center gap-2 font-semibold text-amber-900">
                <svg class="h-5 w-5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Fasilitas Sedang Dalam Perbaikan
            </div>
            <p class="mt-1 text-amber-700">
                Seluruh slot ditandai sebagai tidak aktif karena fasilitas dalam masa pemeliharaan. Reservasi tidak dapat diajukan.
            </p>
        </div>
    @elseif ($facility->status === 'nonaktif')
        <div class="rounded-xl border border-slate-200 bg-slate-100 p-4 text-xs text-slate-700 sm:text-sm">
            <div class="flex items-center gap-2 font-semibold text-slate-900">
                Fasilitas Non-aktif
            </div>
            <p class="mt-1 text-slate-600">
                Fasilitas ini sedang dinonaktifkan oleh administrator sehingga slot tidak dapat dipesan.
            </p>
        </div>
    @endif

    <!-- Pemilih Tanggal & Navigasi -->
    <x-ui.card>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" action="{{ route('fasilitas.jadwal', $facility) }}" class="flex flex-wrap items-center gap-3">
                <label for="date" class="text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Pilih Tanggal:
                </label>
                <input
                    type="date"
                    name="date"
                    id="date"
                    value="{{ $selectedDate->toDateString() }}"
                    class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 shadow-sm transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]"
                    data-submit-on-change
                >
                <button type="submit" class="rounded-lg bg-[#0051d5] px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-[#00236f]">
                    Lihat Jadwal
                </button>
            </form>

            <!-- Navigasi Cepat (Kemarin, Hari Ini, Besok) -->
            <div class="flex items-center gap-1.5 text-xs">
                <a
                    href="{{ route('fasilitas.jadwal', ['facility' => $facility, 'date' => $selectedDate->copy()->subDay()->toDateString()]) }}"
                    class="rounded-lg border border-[#D6DDF8] bg-white px-3 py-2 font-medium text-slate-600 transition hover:bg-[#F2F3FF] hover:text-[#00236f]"
                    title="Hari Sebelumnya"
                >
                    &larr; Kemarin
                </a>
                <a
                    href="{{ route('fasilitas.jadwal', ['facility' => $facility, 'date' => now()->toDateString()]) }}"
                    @class([
                        'rounded-lg px-3 py-2 font-medium transition',
                        'bg-[#00236f] text-white' => $selectedDate->isToday(),
                        'border border-[#D6DDF8] bg-white text-slate-600 hover:bg-[#F2F3FF] hover:text-[#00236f]' => ! $selectedDate->isToday(),
                    ])
                >
                    Hari Ini
                </a>
                <a
                    href="{{ route('fasilitas.jadwal', ['facility' => $facility, 'date' => $selectedDate->copy()->addDay()->toDateString()]) }}"
                    class="rounded-lg border border-[#D6DDF8] bg-white px-3 py-2 font-medium text-slate-600 transition hover:bg-[#F2F3FF] hover:text-[#00236f]"
                    title="Hari Berikutnya"
                >
                    Besok &rarr;
                </a>
            </div>
        </div>
    </x-ui.card>

    <!-- Tampilan Grid 26 Slot (07.00 - 20.00) Menggunakan Komponen Slot Picker -->
    <x-ui.card>
        <div class="space-y-4">
            <div class="flex flex-col gap-1 border-b border-slate-100 pb-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                        <h2 class="text-base font-semibold text-[#00236f]">
                        Ketersediaan Slot: {{ $selectedDate->translatedFormat('l, d F Y') }}
                    </h2>
                    <p class="text-xs text-slate-500">
                        Jam operasional 07.00 – 20.00 WIB &bull; Durasi 30 menit per slot (26 slot/hari)
                    </p>
                </div>

                @if ($facility->status === 'aktif')
                    @auth
                        @if (Route::has('reservasi.create'))
                            <a
                                href="{{ route('reservasi.create', ['facility_id' => $facility->id, 'date' => $selectedDate->toDateString()]) }}"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-[#0051d5] px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-[#00236f]"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Ajukan Reservasi di Tanggal Ini
                            </a>
                        @endif
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-[#D6DDF8] bg-white px-3.5 py-2 text-xs font-semibold text-[#00236f] shadow-sm transition hover:bg-[#F2F3FF]"
                        >
                            Login untuk Reservasi
                        </a>
                    @endauth
                @endif
            </div>

            <!-- Komponen Slot Picker (Mode Publik / Read-Only :selectable="false") -->
            <x-reservation.slot-picker
                :slots="$slots"
                :selectable="false"
            />

            <!-- Keterangan Privasi Sesuai Spesifikasi (BR-13) -->
            <div class="flex items-center gap-2 rounded-xl bg-[#F8FAFC] p-3 text-xs text-slate-500">
                <svg class="h-4 w-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>
                    Jadwal ketersediaan bersifat publik dan transparan. Demi privasi, nama pemohon dan tujuan reservasi tidak ditampilkan pada jadwal publik.
                </span>
            </div>
        </div>
    </x-ui.card>
</div>
@endsection
