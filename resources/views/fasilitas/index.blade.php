@extends('layouts.app')

@section('title', 'Katalog Fasilitas - Sistem Reservasi')

@section('content')
<div class="space-y-8">
    <!-- Header Halaman -->
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Eksplorasi kampus</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#00236f] sm:text-3xl">Katalog Fasilitas Kampus</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Jelajahi dan temukan fasilitas ruangan, laboratorium, serta peralatan kampus yang tersedia.
            </p>
        </div>
    </div>

    <!-- Alert Validasi Filter jika ada kesalahan input -->
    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <div class="flex items-center gap-2 font-semibold text-rose-900">
                <svg class="h-5 w-5 shrink-0 text-rose-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                </svg>
                <span>Filter tidak valid:</span>
            </div>
            <ul class="mt-2 list-disc list-inside space-y-1 text-xs sm:text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Card Filter Pencarian -->
    <x-ui.card>
        <form method="GET" action="{{ route('fasilitas.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Filter Kata Kunci (Nama) -->
                <div>
                    <label for="q" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Pencarian
                    </label>
                    <input
                        type="text"
                        name="q"
                        id="q"
                        value="{{ request('q') }}"
                        placeholder="Nama fasilitas..."
                        class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]"
                    >
                </div>

                <!-- Filter Tipe Fasilitas -->
                <div>
                    <label for="tipe" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Tipe Fasilitas
                    </label>
                    <x-ui.select
                        name="tipe"
                        id="tipe"
                        placeholder="Semua Tipe"
                        :options="$types"
                        :selected="request('tipe')"
                    />
                </div>

                <!-- Filter Lokasi -->
                <div>
                    <label for="lokasi" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Lokasi / Gedung
                    </label>
                    <input
                        type="text"
                        name="lokasi"
                        id="lokasi"
                        value="{{ request('lokasi') }}"
                        placeholder="Contoh: Gedung A..."
                        class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]"
                    >
                </div>

                <!-- Filter Kapasitas Minimal -->
                <div>
                    <label for="kapasitas_min" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Min. Kapasitas (Orang)
                    </label>
                    <input
                        type="number"
                        name="kapasitas_min"
                        id="kapasitas_min"
                        min="1"
                        value="{{ request('kapasitas_min') }}"
                        placeholder="Contoh: 30"
                        class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF] @error('kapasitas_min') border-rose-500 @enderror"
                    >
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-[#EEF2FF] pt-4">
                <span class="text-xs text-slate-500">
                    Menampilkan <strong>{{ $facilities->count() }}</strong> fasilitas
                </span>
                <div class="flex items-center gap-2">
                    @if (request()->hasAny(['q', 'tipe', 'lokasi', 'kapasitas_min', 'type']))
                        <a href="{{ route('fasilitas.index') }}" class="rounded-lg px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-[#F2F3FF] hover:text-[#00236f]">
                            Reset Filter
                        </a>
                    @endif
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-[#0051d5] px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-[#00236f] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0051d5]">
                        <svg class="mr-1.5 h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </x-ui.card>

    <!-- Grid Hasil Daftar Fasilitas -->
    @if ($facilities->isEmpty())
        <!-- State Kosong: Ramah Pengguna Sesuai Spesifikasi -->
        <div class="rounded-2xl border border-dashed border-[#D6DDF8] bg-white p-12 text-center shadow-sm">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#F2F3FF] text-[#0051d5]">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <h3 class="mt-4 text-base font-semibold text-[#00236f]">Fasilitas tidak ditemukan</h3>
            <p class="mx-auto mt-1 max-w-sm text-sm text-slate-500">
                Tidak ada fasilitas yang cocok dengan kriteria pencarian Anda. Silakan coba kata kunci lain atau reset filter.
            </p>
            <div class="mt-6">
                <a href="{{ route('fasilitas.index') }}" class="inline-flex items-center rounded-lg border border-[#D6DDF8] bg-white px-4 py-2 text-xs font-semibold text-[#00236f] shadow-sm transition hover:bg-[#F2F3FF]">
                    Lihat Semua Fasilitas
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($facilities as $facility)
                <div class="flex flex-col overflow-hidden rounded-2xl border border-[#E2E7FF] bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-[0_14px_30px_rgba(15,23,42,0.08)]">
                    <!-- Foto Fasilitas / Placeholder -->
                    <div class="relative h-48 w-full overflow-hidden bg-[#F2F3FF]">
                        @if ($facility->photo)
                            <img
                                src="{{ asset('storage/' . $facility->photo) }}"
                                alt="{{ $facility->name }}"
                                class="h-full w-full object-cover"
                            >
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#F2F3FF] to-[#E2E7FF] text-[#0051d5]/50">
                                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                        @endif

                        <!-- Badge Status & Tipe di atas foto -->
                        <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                            <x-ui.badge :status="$facility->status" :dot="true">
                                {{ ucfirst($facility->status) }}
                            </x-ui.badge>
                        </div>
                        <div class="absolute top-3 right-3">
                            <span class="inline-flex items-center rounded-md bg-[#00236f]/85 px-2 py-1 text-xs font-medium text-white backdrop-blur">
                                {{ $types[$facility->type] ?? ucfirst($facility->type) }}
                            </span>
                        </div>
                    </div>

                    <!-- Informasi Fasilitas -->
                    <div class="flex flex-1 flex-col justify-between p-5">
                        <div class="space-y-2.5">
                            <h2 class="line-clamp-1 text-lg font-semibold text-[#00236f]" title="{{ $facility->name }}">
                                {{ $facility->name }}
                            </h2>

                            <div class="space-y-1.5 text-xs text-slate-600">
                                <div class="flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="truncate">{{ $facility->location }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <span>Kapasitas: <strong>{{ $facility->capacity }}</strong> orang</span>
                                </div>
                            </div>

                            @if ($facility->description)
                                <p class="text-xs text-slate-500 line-clamp-2 pt-1">
                                    {{ $facility->description }}
                                </p>
                            @endif
                        </div>

                        <!-- Aksi -->
                        <div class="mt-5 flex items-center gap-2 border-t border-[#EEF2FF] pt-4">
                            <a
                                href="{{ route('fasilitas.show', $facility) }}"
                                class="flex-1 rounded-lg border border-[#D6DDF8] bg-white py-2 text-center text-xs font-semibold text-[#00236f] transition hover:bg-[#F2F3FF]"
                            >
                                Detail
                            </a>
                            <a
                                href="{{ route('fasilitas.jadwal', $facility) }}"
                                class="flex-1 rounded-lg bg-[#F2F3FF] py-2 text-center text-xs font-semibold text-[#0051d5] transition hover:bg-[#E2E7FF]"
                            >
                                Cek Jadwal
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
