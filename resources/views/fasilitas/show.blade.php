@extends('layouts.app')

@section('title', $facility->name . ' - Detail Fasilitas')

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
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="h-4 w-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span class="ml-1 max-w-xs truncate font-medium text-[#00236f] md:ml-2">{{ $facility->name }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Tombol Kembali & Judul Singkat -->
    <div class="flex items-center justify-between">
        <a href="{{ route('fasilitas.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 transition hover:text-[#00236f]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Katalog
        </a>

        <div class="flex items-center gap-2">
            <x-ui.badge :status="$facility->status" :dot="true">
                Status: {{ ucfirst($facility->status) }}
            </x-ui.badge>
        </div>
    </div>

    <!-- Layout Utama: 2 Kolom -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Kolom Kiri: Detail & Foto (2 Kolom) -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Foto Utama -->
            <div class="overflow-hidden rounded-2xl border border-[#E2E7FF] bg-white shadow-[0_14px_40px_rgba(15,23,42,0.06)]">
                <div class="relative h-72 w-full bg-[#F2F3FF] sm:h-96">
                    @if ($facility->photo)
                        <img
                            src="{{ asset('storage/' . $facility->photo) }}"
                            alt="{{ $facility->name }}"
                            loading="lazy"
                            class="img-fade h-full w-full object-cover"
                        >
                    @else
                        <div class="flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-[#F2F3FF] to-[#E2E7FF] text-[#0051d5]/50">
                            <svg class="h-20 w-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="mt-2 text-xs font-medium text-slate-500">Tidak ada foto pratinjau</span>
                        </div>
                    @endif

                    <div class="absolute top-4 left-4">
                        <span class="inline-flex items-center rounded-lg bg-[#00236f]/85 px-3 py-1.5 text-xs font-semibold text-white shadow backdrop-blur">
                            {{ $types[$facility->type] ?? ucfirst($facility->type) }}
                        </span>
                    </div>
                </div>

                <div class="p-6 sm:p-8 space-y-6">
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight text-[#00236f] sm:text-3xl">
                            {{ $facility->name }}
                        </h1>
                        <p class="mt-2 flex items-center gap-2 text-sm text-slate-600">
                            <svg class="h-4 w-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>{{ $facility->location }}</span>
                        </p>
                    </div>

                    <!-- Spesifikasi Fasilitas -->
                    <div class="grid grid-cols-2 gap-4 rounded-xl border border-[#EEF2FF] bg-[#F8FAFC] p-4 sm:grid-cols-3">
                        <div>
                            <span class="block text-xs font-medium text-slate-500">Tipe</span>
                            <span class="mt-1 block text-sm font-semibold text-[#00236f]">
                                {{ $types[$facility->type] ?? ucfirst($facility->type) }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs font-medium text-slate-500">Kapasitas Maksimal</span>
                            <span class="mt-1 block text-sm font-semibold text-[#00236f]">
                                {{ $facility->capacity }} Orang
                            </span>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <span class="block text-xs font-medium text-slate-500">Status Operasional</span>
                            <span class="mt-1 inline-block">
                                <x-ui.badge :status="$facility->status" :dot="true">
                                    {{ ucfirst($facility->status) }}
                                </x-ui.badge>
                            </span>
                        </div>
                    </div>

                    <!-- Deskripsi Lengkap -->
                    <div class="space-y-2">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-[#00236f]">Deskripsi & Fasilitas</h2>
                        <div class="prose prose-slate max-w-none text-sm leading-relaxed text-slate-600">
                            @if ($facility->description)
                                <p class="whitespace-pre-line">{{ $facility->description }}</p>
                            @else
                                <p class="italic text-slate-400">Belum ada deskripsi tambahan untuk fasilitas ini.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Aksi & Informasi Reservasi (1 Kolom) -->
        <div class="space-y-6">
            <!-- Card Aksi Reservasi & Jadwal -->
            <x-ui.card>
                <div class="space-y-5">
                    <h2 class="text-base font-bold text-slate-900">Aksi & Ketersediaan</h2>

                    @if ($facility->status === 'aktif')
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3.5 text-xs text-emerald-800">
                            <div class="flex items-center gap-2 font-semibold">
                                <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Fasilitas Aktif
                            </div>
                            <p class="mt-1 text-emerald-700">
                                Fasilitas ini siap digunakan dan dapat direservasi pada jam operasional kampus.
                            </p>
                        </div>
                    @elseif ($facility->status === 'perbaikan')
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-3.5 text-xs text-amber-800">
                            <div class="flex items-center gap-2 font-semibold">
                                <svg class="h-4 w-4 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                Sedang Dalam Perbaikan
                            </div>
                            <p class="mt-1 text-amber-700">
                                Fasilitas ini sedang dalam masa perawatan/perbaikan sehingga tidak dapat dipesan sementara waktu.
                            </p>
                        </div>
                    @else
                        <div class="rounded-lg bg-slate-100 border border-slate-200 p-3.5 text-xs text-slate-700">
                            <div class="flex items-center gap-2 font-semibold text-slate-900">
                                Fasilitas Non-aktif
                            </div>
                            <p class="mt-1 text-slate-600">
                                Fasilitas ini sedang dinonaktifkan oleh administrator sistem.
                            </p>
                        </div>
                    @endif

                    <div class="space-y-3 pt-2">
                        <!-- Tombol Cek Jadwal Slot -->
                        <a
                            href="{{ route('fasilitas.jadwal', $facility) }}"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#0051d5] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#00236f] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0051d5]"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Lihat Jadwal Slot Ketersediaan
                        </a>

                        <!-- Tombol Reservasi -->
                        @if ($facility->status === 'aktif')
                            @auth
                                @if (Route::has('reservasi.create'))
                                    <a
                                        href="{{ route('reservasi.create', ['facility_id' => $facility->id]) }}"
                                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-[#D6DDF8] bg-white px-4 py-2.5 text-sm font-semibold text-[#00236f] shadow-sm transition hover:bg-[#F2F3FF]"
                                    >
                                        Ajukan Reservasi
                                    </a>
                                @else
                                    <span class="block text-center text-xs text-slate-500 pt-1">
                                        Untuk memesan, silakan cek slot di halaman jadwal.
                                    </span>
                                @endif
                            @else
                                <a
                                    href="{{ route('login') }}"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-[#D6DDF8] bg-white px-4 py-2.5 text-sm font-semibold text-[#00236f] shadow-sm transition hover:bg-[#F2F3FF]"
                                >
                                    Login untuk Mengajukan Reservasi
                                </a>
                            @endauth
                        @else
                            <button
                                disabled
                                class="flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-400"
                            >
                                Reservasi Tidak Tersedia
                            </button>
                        @endif
                    </div>
                </div>
            </x-ui.card>

            <!-- Card Aturan & Jam Operasional -->
            <x-ui.card>
                <div class="space-y-3.5 text-xs text-slate-600">
                    <h3 class="text-sm font-semibold text-[#00236f]">Ketentuan Penggunaan</h3>
                    <ul class="space-y-2.5 list-disc list-inside">
                        <li>
                            <strong class="text-slate-800">Jam Operasional:</strong> Pukul 07.00 – 20.00 WIB (26 slot/hari, 30 menit per slot).
                        </li>
                        <li>
                            <strong class="text-slate-800">Batas Pengajuan:</strong> Reservasi diajukan minimal 1 jam sebelum waktu mulai.
                        </li>
                        <li>
                            <strong class="text-slate-800">Privasi Publik:</strong> Jadwal dan ketersediaan dapat dilihat secara transparan tanpa menampilkan identitas pemohon.
                        </li>
                    </ul>
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
@endsection
