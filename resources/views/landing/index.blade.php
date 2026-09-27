@php
    $badgeClasses = [
        'aktif' => 'bg-green-50 text-green-700 ring-green-200',
        'perbaikan' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'nonaktif' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];
    $facilityFallbackImages = [
        'aula' => asset('images/aula.jpg'),
        'laboratorium' => asset('images/lab-komputer.jpg'),
        'lapangan' => asset('images/lapangan-futsal.jpg'),
        'ruang_kelas' => asset('images/ruang-kelas.jpg'),
        'alat' => asset('images/proyektor.jpg'),
    ];
    $slotClasses = [
        'available' => 'bg-white text-[#0F172A] shadow-sm hover:shadow',
        'booked' => 'bg-[#FFDAD6]/40 text-[#DC2626]',
        'past' => 'bg-[#EEF2FF] text-[#94A3B8]',
        'inactive' => 'bg-[#EEF2FF] text-[#94A3B8]',
    ];
    $slotLabels = [
        'available' => 'Tersedia',
        'booked' => 'Terpakai',
        'past' => 'Waktu Lewat',
        'inactive' => 'Tidak Aktif',
    ];
    $tabActiveClass = 'landing-button inline-flex items-center gap-2 rounded-full bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-md transition-all';
    $tabInactiveClass = 'landing-button inline-flex items-center gap-2 rounded-full border border-blue-100 bg-white/70 px-4 py-2 text-xs font-medium text-slate-600 transition-all hover:bg-blue-50 hover:text-blue-700';
    $navActiveClass = 'landing-button rounded-full bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-md transition-all';
    $navInactiveClass = 'landing-button rounded-full px-5 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-white/70 hover:text-blue-600';
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="REKSA — Reservasi dan Kerusakan Sarana Akademik. Temukan fasilitas kampus, cek jadwal, ajukan reservasi, dan laporkan kerusakan.">
    <title>REKSA — Reservasi dan Kerusakan Sarana Akademik</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-landing class="landing-page min-h-screen py-5 font-sans text-slate-800 antialiased selection:bg-blue-600 selection:text-white">

<a href="#content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-[#00236f] focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
    Lewati ke konten utama
</a>

<div class="mx-auto w-full max-w-7xl space-y-5 px-4 sm:px-6">
<header class="landing-panel sticky top-3 z-50 flex items-center justify-between gap-4 rounded-full px-4 py-3 shadow-[8px_8px_20px_rgba(166,195,235,0.35),-8px_-8px_20px_rgba(255,255,255,0.95)] sm:px-6">
    <div class="flex min-w-0 flex-1 items-center justify-between gap-3 sm:gap-6">
        <a href="#top" data-landing-nav="top" class="flex shrink-0 items-center gap-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2" aria-label="Kembali ke Beranda">
            <img src="{{ asset('images/reksa-logo.png') }}" alt="REKSA" class="h-11 w-11 shrink-0 rounded-xl object-contain" fetchpriority="high">
            <span class="flex min-w-0 flex-col">
                <span class="text-sm font-extrabold tracking-wide text-slate-900 sm:text-base">REKSA</span>
                <span class="hidden text-[10px] leading-tight text-slate-600 sm:inline">Reservasi dan Kerusakan Sarana Akademik</span>
            </span>
        </a>
        <nav class="hidden items-center rounded-full border border-blue-100/60 bg-slate-100/70 p-1.5 shadow-inner md:flex" aria-label="Navigasi halaman">
            <a href="#top" data-landing-nav="top" data-nav-active="{{ $navActiveClass }}" data-nav-inactive="{{ $navInactiveClass }}" class="{{ $navActiveClass }}" aria-current="page">Beranda</a>
            <a href="#panduan" data-landing-nav="panduan" data-nav-active="{{ $navActiveClass }}" data-nav-inactive="{{ $navInactiveClass }}" class="{{ $navInactiveClass }}">Panduan</a>
            <a href="#fasilitas" data-landing-nav="fasilitas" data-nav-active="{{ $navActiveClass }}" data-nav-inactive="{{ $navInactiveClass }}" class="{{ $navInactiveClass }}">Fasilitas</a>
            <a href="#jadwal-preview" data-landing-nav="jadwal-preview" data-nav-active="{{ $navActiveClass }}" data-nav-inactive="{{ $navInactiveClass }}" class="{{ $navInactiveClass }}">Jadwal</a>
        </nav>
        <div class="flex shrink-0 items-center gap-1 sm:gap-2">
            @auth
                <a href="{{ route('dashboard') }}" class="landing-button rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-4 py-2.5 text-xs font-semibold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)] transition hover:from-blue-700 hover:to-blue-600 sm:text-sm">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="landing-button rounded-full px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-white/60 hover:text-blue-600 sm:px-5 sm:text-sm">Masuk</a>
                <a href="{{ route('register') }}" class="landing-button inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-3 py-2.5 text-xs font-semibold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)] transition hover:from-blue-700 hover:to-blue-600 sm:px-5 sm:text-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="hidden h-4 w-4 sm:block" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Daftar Akun
                </a>
            @endauth
        </div>
    </div>
</header>

<main id="content" class="w-full">
    <div class="space-y-5 py-1 sm:space-y-6">

        {{-- SECTION 1: Hero --}}
        <section id="top" data-landing-section class="landing-panel relative grid scroll-mt-24 items-center gap-6 overflow-hidden rounded-3xl p-6 shadow-[0_10px_30px_-5px_rgba(186,215,248,0.45),0_0_0_1px_rgba(255,255,255,0.8)_inset] sm:p-8 lg:grid-cols-[1fr_0.9fr] lg:gap-10 lg:p-10">
            <div class="relative z-10 max-w-2xl space-y-4 sm:space-y-5">
                <h1 class="text-3xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-4xl lg:text-[42px] lg:leading-[1.2]">Kelola Penggunaan Fasilitas Kampus dengan Mudah</h1>
                <p class="max-w-2xl text-sm leading-relaxed text-slate-600 sm:text-[15px]">
                    Temukan fasilitas, lihat ketersediaan jadwal, dan lakukan reservasi secara mudah. Transparansi penuh untuk kegiatan akademik dan kemahasiswaan.
                </p>
                <div class="flex flex-wrap items-center gap-3 pt-1 sm:gap-4">
                    <a href="{{ route('login') }}" class="landing-button inline-flex min-h-11 items-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-3 text-sm font-semibold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)] transition hover:from-blue-700 hover:to-blue-600 sm:px-6">
                        Login untuk Reservasi
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-[18px] w-[18px]" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/>
                        </svg>
                    </a>
                    <a href="#jadwal-preview" class="landing-button inline-flex min-h-11 items-center gap-2 rounded-full border border-white bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-[8px_8px_20px_rgba(166,195,235,0.35),-8px_-8px_20px_rgba(255,255,255,0.95)] transition hover:bg-slate-50 sm:px-6">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px]" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4m8-4v4M3 10h18"/>
                        </svg>
                        Cek Ketersediaan Hari Ini
                    </a>
                </div>
            </div>
            <div class="landing-hero-art relative flex min-h-56 items-center justify-center lg:min-h-[330px]">
                <img src="{{ asset('images/building-calendar.png') }}" alt="Ilustrasi gedung dan kalender kampus" class="relative z-10 w-full max-h-[320px] object-contain drop-shadow-2xl sm:max-h-[380px]">
            </div>
        </section>

                {{-- SECTION 5: Panduan --}}
        <section id="panduan" data-landing-section class="landing-panel scroll-mt-24 space-y-5 rounded-3xl p-5 shadow-[0_10px_30px_-5px_rgba(186,215,248,0.45),0_0_0_1px_rgba(255,255,255,0.8)_inset] sm:p-7">
            <div>
                <h2 class="text-lg font-bold text-slate-900 sm:text-xl">Alur Peminjaman</h2>
                <p class="text-xs text-[#475569]">Tiga langkah sederhana untuk menggunakan fasilitas kampus.</p>
            </div>
            <div class="grid grid-cols-1 items-center gap-3 md:grid-cols-3 sm:gap-5">
                <div class="landing-step-card relative flex items-start gap-3 rounded-2xl border border-white bg-white/80 p-4 shadow-sm sm:gap-4 sm:p-5">
                    <div class="flex shrink-0 items-center gap-1.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white shadow-sm">1</div>
                    <span class="reksa-icon reksa-icon-calendar" aria-hidden="true"></span>
                    </div>
                    <div>
                    <h3 class="mt-3 text-sm font-semibold text-[#0F172A]">Pilih Fasilitas &amp; Jadwal</h3>
                    <p class="mt-1 text-xs leading-relaxed text-[#475569]">Cari fasilitas lalu cek slot waktu yang tersedia pada pratinjau jadwal.</p>
                    </div>
                    <span class="absolute -right-4 top-1/2 hidden -translate-y-1/2 text-blue-500 lg:block" aria-hidden="true">›</span>
                </div>
                <div class="landing-step-card relative flex items-start gap-3 rounded-2xl border border-white bg-white/80 p-4 shadow-sm sm:gap-4 sm:p-5">
                    <div class="flex shrink-0 items-center gap-1.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white shadow-sm">2</div>
                    <span class="reksa-icon reksa-icon-document" aria-hidden="true"></span>
                    </div>
                    <div>
                    <h3 class="mt-3 text-sm font-semibold text-[#0F172A]">Ajukan Reservasi</h3>
                    <p class="mt-1 text-xs leading-relaxed text-[#475569]">Login dan isi tujuan penggunaan; permohonan masuk antrian persetujuan.</p>
                    </div>
                    <span class="absolute -right-4 top-1/2 hidden -translate-y-1/2 text-blue-500 lg:block" aria-hidden="true">›</span>
                </div>
                <div class="landing-step-card flex items-start gap-3 rounded-2xl border border-white bg-white/80 p-4 shadow-sm sm:gap-4 sm:p-5">
                    <div class="flex shrink-0 items-center gap-1.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white shadow-sm">3</div>
                    <span class="reksa-icon reksa-icon-check" aria-hidden="true"></span>
                    </div>
                    <div>
                    <h3 class="mt-3 text-sm font-semibold text-[#0F172A]">Disetujui Petugas</h3>
                    <p class="mt-1 text-xs leading-relaxed text-[#475569]">Setelah disetujui petugas, slot terkunci dan fasilitas siap digunakan.</p>
                    </div>
                </div>
            </div>
        </section>
        
        {{-- SECTION 2: Pencarian --}}
        <section class="landing-panel rounded-2xl p-4 shadow-[0_10px_30px_-5px_rgba(186,215,248,0.45),0_0_0_1px_rgba(255,255,255,0.8)_inset] sm:p-5">
            <form id="landingFilterForm" class="grid grid-cols-1 items-end gap-4 md:grid-cols-2 lg:grid-cols-5">
                <div class="lg:col-span-1">
                    <label for="search-input" class="mb-1.5 block text-sm font-medium text-[#475569]">Pencarian</label>
                    <div class="relative">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="absolute left-3 top-2.5 h-[18px] w-[18px] text-[#94A3B8]" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M20 20l-3.5-3.5"/>
                        </svg>
                        <input id="search-input" type="text" placeholder="Cari fasilitas..." value=""
                               class="landing-input h-10 w-full rounded-xl pl-9 pr-3 text-xs text-[#0F172A] transition focus:outline-none focus:ring-2 focus:ring-blue-500/40"
                               data-debounce="300">
                    </div>
                </div>
                <div>
                    <label for="filter-type" class="mb-1.5 block text-sm font-medium text-[#475569]">Jenis Fasilitas</label>
                    <select id="filter-type" data-debounce="300"
                            class="landing-input h-10 w-full rounded-xl px-3 text-xs text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-blue-500/40">
                        <option value="">Semua Jenis</option>
                        @foreach ($typeLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter-location" class="mb-1.5 block text-sm font-medium text-[#475569]">Lokasi</label>
                    <select id="filter-location" data-debounce="300"
                            class="landing-input h-10 w-full rounded-xl px-3 text-xs text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-blue-500/40">
                        <option value="">Semua Gedung</option>
                        @foreach ($locationOptions as $location)
                            <option value="{{ $location }}">{{ $location }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter-capacity" class="mb-1.5 block text-sm font-medium text-[#475569]">Kapasitas</label>
                    <select id="filter-capacity" data-debounce="300"
                            class="landing-input h-10 w-full rounded-xl px-3 text-xs text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-blue-500/40">
                        <option value="">Semua Kapasitas</option>
                        <option value="lt_40">< 40 orang</option>
                        <option value="40_100">40 - 100 orang</option>
                        <option value="gt_100">> 100 orang</option>
                    </select>
                </div>
                <div>
                        <button type="button" id="landing-reset-filter"
                            class="landing-button flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 text-xs font-semibold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)] transition-all hover:from-blue-700 hover:to-blue-600">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px]" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16M8 6v-.5A1.5 1.5 0 0110 4v0A1.5 1.5 0 0111.5 6v0M12 12v-.5A1.5 1.5 0 0114 10v0A1.5 1.5 0 0115.5 12v0M8 18v-.5A1.5 1.5 0 0110 16v0A1.5 1.5 0 0111.5 18v0"/>
                        </svg>
                        Reset Filter
                    </button>
                </div>
            </form>
            <div id="landing-loading" class="mt-4 hidden">
                <div class="landing-panel rounded-xl p-8 text-center shadow-sm">
                    <svg class="animate-spin h-8 w-8 text-[#0051d5] mx-auto" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.37 0 0 5.37 0 12h4z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500">Memuat fasilitas...</p>
                </div>
            </div>
        </section>

        {{-- SECTION 3: Grid Kartu Fasilitas --}}
        <section id="fasilitas" data-landing-section class="scroll-mt-24 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 sm:text-xl">Fasilitas Kampus Unggulan</h2>
                    <p class="text-xs text-[#475569]">Fasilitas standar akademik dengan pratinjau ketersediaan jam secara real-time.</p>
                </div>
                <span class="rounded-full border border-white/80 bg-white/70 px-3 py-1.5 text-[11px] font-medium text-slate-600 shadow-sm" id="facility-count">
                    Menampilkan <span id="facility-count-num">{{ $facilities->count() }}</span> dari {{ $totalFacilities }} Fasilitas
                </span>
            </div>

            @if ($facilities->isEmpty())
                <div class="landing-panel rounded-2xl p-10 text-center shadow-sm">
                    <p class="text-base font-medium text-[#0F172A]">Fasilitas tidak ditemukan</p>
                    <p class="mt-1 text-sm text-[#475569]">Coba ubah kata kunci atau filter pencarian Anda.</p>
                    <a href="{{ route('home') }}" class="landing-button mt-4 inline-block rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-2.5 text-sm font-semibold text-white hover:from-blue-700 hover:to-blue-600">Reset Filter</a>
                </div>
            @endif

            <div id="landing-grid-container" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($facilities as $facility)
                    <div class="landing-card group facility-card flex flex-col overflow-hidden rounded-3xl border border-white/80 bg-white shadow-[0_10px_30px_-5px_rgba(186,215,248,0.45),0_0_0_1px_rgba(255,255,255,0.8)_inset] transition-all hover:-translate-y-0.5 hover:shadow-lg" data-facility-id="{{ $facility->id }}">
                        <div class="relative aspect-[16/9] w-full overflow-hidden bg-[#f2f3ff]">
                            @if ($facility->photo)
                                <img src="{{ Storage::disk('public')->url($facility->photo) }}" alt="{{ $facility->name }}" loading="lazy"
                                     class="img-fade h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                            @else
                                <img src="{{ $facilityFallbackImages[$facility->type] ?? asset('images/aula.jpg') }}" alt="{{ $facility->name }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                            @endif
                            <span class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-white/95 px-2.5 py-1 text-xs font-medium shadow-sm ring-1 {{ $badgeClasses[$facility->status] }}">
                                @if ($facility->status === 'aktif')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
                                @elseif ($facility->status === 'perbaikan')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a4 4 0 0 0-5.6 5.6L4 17l3 3 5.1-5.1a4 4 0 0 0 5.6-5.6L15 12l-3-3 2.7-2.7Z"/></svg>
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="m5.6 5.6 12.8 12.8"/></svg>
                                @endif
                                {{ ucfirst($facility->status) }}
                            </span>
                        </div>
                        <div class="flex flex-1 flex-col justify-between gap-4 p-4 sm:p-5">
                            <div class="space-y-2">
                                <h3 class="text-base font-bold text-slate-900">{{ $facility->name }}</h3>
                                <span class="inline-block rounded-full border border-blue-100 bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-600">{{ $typeLabels[$facility->type] }}</span>
                                <div class="space-y-1 pt-1">
                                    <div class="flex items-center gap-2 text-sm text-[#475569]">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 text-[#94A3B8]" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.5 7-11a7 7 0 10-14 0c0 5.5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>
                                        </svg>
                                        <span>{{ $facility->location }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-[#475569]">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 text-[#94A3B8]" aria-hidden="true">
                                            <circle cx="9" cy="8" r="3.5"/><path stroke-linecap="round" d="M3 20v-1a6 6 0 0112 0v1M16 8.5a3 3 0 010 5.5M17.5 15.2a6 6 0 013.5 4.8"/>
                                        </svg>
                                        <span>Kapasitas: {{ $facility->capacity }} orang</span>
                                    </div>
                                    @if ($facility->description)
                                    <p class="pt-1 text-xs leading-relaxed text-slate-500">{{ \Illuminate\Support\Str::limit($facility->description, 90) }}</p>
                                    @endif
                                </div>
                            </div>
                            <button type="button" data-landing-go="{{ $facility->id }}"
                                    class="landing-button flex h-10 w-full items-center justify-center gap-1.5 rounded-xl border border-blue-200 bg-white/80 text-xs font-semibold text-blue-700 transition-colors hover:bg-blue-50">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px]" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4m8-4v4M3 10h18"/>
                                </svg>
                                Lihat Jadwal
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($totalFacilities > 9)
            <div class="mt-4 text-center">
                <a href="{{ route('home') }}" class="landing-button inline-block rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-2.5 text-sm font-semibold text-white hover:from-blue-700 hover:to-blue-600">Lihat Semua Fasilitas ({{ $totalFacilities }})</a>
            </div>
            @endif
        </section>

        {{-- SECTION 4: Pratinjau Jadwal --}}
        <section id="jadwal-preview" data-landing-section class="landing-panel scroll-mt-24 space-y-5 rounded-3xl p-5 shadow-[0_10px_30px_-5px_rgba(186,215,248,0.45),0_0_0_1px_rgba(255,255,255,0.8)_inset] sm:space-y-6 sm:p-7">
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <div class="flex items-center gap-2">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6 text-[#00236f]" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4m8-4v4M3 10h18M9 15h6"/>
                        </svg>
                        <h2 class="text-base font-bold text-slate-900 sm:text-lg" id="schedule-title">Pratinjau Ketersediaan Jadwal</h2>
                    </div>
                    <p class="mt-1 text-xs text-[#475569]">Pilih fasilitas dan tanggal untuk melihat jam ketersediaan publik secara real-time.</p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="date" id="schedule-date" value="{{ $today->toDateString() }}" min="{{ $today->toDateString() }}"
                           class="landing-input h-10 rounded-full px-3 text-xs text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-blue-500/40">
                    <div class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50/80 px-3 py-1.5 text-xs text-blue-700">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 text-[#0891B2]" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/>
                        </svg>
                        Waktu Server: {{ $today->format('H:i') }} WIB
                    </div>
                </div>
            </div>

            {{-- Tab fasilitas --}}
            <div role="tablist" aria-label="Pilih fasilitas" class="flex flex-wrap gap-2 pt-1" id="schedule-tabs">
                @foreach ($facilities as $facility)
                    <button type="button" role="tab" id="tab-facility-{{ $facility->id }}"
                            aria-controls="facility-grid-{{ $facility->id }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                            tabindex="{{ $loop->first ? '0' : '-1' }}"
                            data-landing-tab="{{ $facility->id }}"
                            data-tab-active="{{ $tabActiveClass }}"
                            data-tab-inactive="{{ $tabInactiveClass }}"
                            class="{{ $loop->first ? $tabActiveClass : $tabInactiveClass }}">
                        {{ $facility->name }}
                    </button>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 rounded-2xl border border-blue-100 bg-blue-50/60 p-3.5">
                <div class="flex items-center gap-3">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-600" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg></span>
                    <span class="text-sm font-medium text-[#0F172A]" data-facility-indicator>
                        <span aria-hidden="true">Fasilitas: </span><span data-facility-name>{{ $facilities->first()?->name ?? '-' }}</span>
                    </span>
                </div>
                <span class="text-xs font-medium text-[#475569]">Zona Operasional: 07:00 - 20:00 WIB</span>
            </div>

            <div id="schedule-loading" class="hidden mt-6">
                <div class="landing-panel rounded-2xl p-8 text-center shadow-sm">
                    <svg class="animate-spin h-8 w-8 text-[#0051d5] mx-auto" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.37 0 0 5.37 0 12h4z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500">Memuat jadwal...</p>
                </div>
            </div>

            <div id="schedule-grid" class="space-y-3">
            @if ($facilities->isEmpty())
                <p class="rounded-xl border border-blue-100 bg-blue-50/60 p-6 text-center text-sm text-[#475569]">
                    Tidak ada fasilitas untuk ditampilkan. Reset filter untuk melihat pratinjau ketersediaan.
                </p>
            @endif

            @foreach ($facilities as $facility)
                <div role="tabpanel" aria-labelledby="tab-facility-{{ $facility->id }}" id="facility-grid-{{ $facility->id }}"
                     data-facility-grid="{{ $facility->id }}" data-facility-name="{{ $facility->name }}"
                     class="{{ $loop->first ? '' : 'hidden' }} space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium uppercase tracking-wider text-[#475569]">Slot Waktu Pemakaian (Interval 30 Menit)</span>
                        <span class="text-xs text-[#94A3B8]">Total 26 Slot</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                        @foreach ($grids[$facility->id] as $slot)
                            <div class="flex min-h-16 flex-col items-center justify-center rounded-xl border border-blue-100/70 p-3 text-center select-none {{ $slotClasses[$slot['state']] }}
                                @if ($slot['state'] === 'past' || $slot['state'] === 'booked' || $slot['state'] === 'inactive') cursor-not-allowed @endif">
                                <span class="text-sm font-medium">{{ $slot['start'] }} - {{ $slot['end'] }}</span>
                                <span class="mt-0.5 text-xs font-medium {{ in_array($slot['state'], ['booked', 'past', 'inactive'], true) ? '' : 'text-green-600' }}">{{ $slotLabels[$slot['state']] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-6 pb-1 pt-2">
                <div class="flex items-center gap-2">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="h-3 w-3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg></span>
                    <span class="text-xs text-[#475569]">Tersedia untuk Reservasi</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-rose-100 text-rose-600"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3 w-3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"/><path stroke-linecap="round" d="m9 16 2 2 4-4"/></svg></span>
                    <span class="text-xs text-[#475569]">Terpakai / Sedang Dipesan</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-slate-500"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3 w-3" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg></span>
                    <span class="text-xs text-[#475569]">Waktu Lewat / Tidak Aktif</span>
                </div>
            </div>

            {{-- Catatan Privasi (BR-13) --}}
            <div class="flex items-start gap-3.5 rounded-2xl border border-blue-100 bg-blue-50/75 p-4">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 h-[22px] w-[22px] flex-shrink-0 text-[#0891B2]" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6l7-3zM9 12l2 2 4-4"/>
                </svg>
                <div>
                    <h4 class="text-sm font-medium text-[#0F172A]">Kebijakan Privasi Peminjaman Publik</h4>
                    <p class="text-xs leading-relaxed text-[#475569]">
                        Pengunjung publik hanya dapat melihat status ketersediaan waktu. Identitas pemohon, nomor kontak, serta rincian spesifik kegiatan dilindungi privasinya. Untuk mengajukan permohonan reservasi, silakan login terlebih dahulu.
                    </p>
                </div>
            </div>
        </section>


    </div>
</main>

<footer class="landing-footer relative overflow-hidden rounded-3xl border border-blue-200/70 py-7 shadow-[0_12px_30px_rgba(53,103,175,0.12)] sm:py-10">
    <div class="relative z-10">
        <div class="mx-auto grid w-full max-w-7xl gap-8 px-5 sm:px-8 lg:grid-cols-[1.4fr_1fr_1fr] lg:gap-12">
            <div class="flex items-start gap-4">
                <img src="{{ asset('images/reksa-logo.png') }}" alt="Logo REKSA" class="h-14 w-14 shrink-0 rounded-2xl object-contain" loading="lazy">
                <div>
                    <p class="text-base font-extrabold tracking-wide text-slate-900">REKSA</p>
                    <p class="mt-1 text-xs font-semibold text-blue-900">Reservasi dan Kerusakan Sarana Akademik</p>
                    <p class="mt-2 max-w-md text-xs leading-relaxed text-slate-600">Layanan terpadu untuk peminjaman fasilitas kampus dan pelaporan kerusakan sarana akademik.</p>
                </div>
            </div>
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800">Layanan</h2>
                <ul class="mt-3 space-y-2 text-xs text-slate-600">
                    <li>Reservasi fasilitas kampus</li>
                    <li>Pratinjau ketersediaan jadwal</li>
                    <li>Pelaporan kerusakan sarana</li>
                </ul>
            </div>
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800">Informasi</h2>
                <p class="mt-3 text-xs leading-relaxed text-slate-600">Gunakan akun kampus untuk mengajukan reservasi dan memantau permohonan Anda.</p>
            </div>
            <div class="border-t border-blue-200/70 pt-4 text-xs text-slate-600 lg:col-span-3 lg:flex lg:items-center lg:justify-between">
                <span>&copy; {{ date('Y') }} REKSA. Seluruh hak cipta dilindungi.</span>
                <a href="#top" class="landing-button mt-2 inline-flex w-fit items-center gap-1 rounded-full px-3 py-2 font-semibold text-blue-800 hover:text-blue-950 lg:mt-0">Kembali ke atas <span aria-hidden="true">&uarr;</span></a>
            </div>
        </div>
    </div>
</footer>

</div>

</body>
</html>
