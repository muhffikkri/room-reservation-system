@php
    $navActiveClass = 'landing-button clay-nav-item rounded-full bg-blue-600 text-sm font-semibold text-white shadow-md transition-all';
    $navInactiveClass = 'landing-button landing-nav-muted clay-nav-item rounded-full text-sm font-medium transition-colors';
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="REKSA — Reservasi dan Kerusakan Sarana Akademik. Temukan fasilitas kampus, cek jadwal, ajukan reservasi, dan laporkan kerusakan.">
    <title>REKSA — Reservasi dan Kerusakan Sarana Akademik</title>
    <x-ui.favicon />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-landing class="landing-page min-h-screen py-5 font-sans text-slate-800 antialiased selection:bg-blue-600 selection:text-white">

<a href="#content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-[#00236f] focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
    Lewati ke konten utama
</a>

<div class="mx-auto w-full max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
<header class="clay-nav sticky top-3 z-50 rounded-[2rem] px-4 sm:rounded-full sm:px-6">
    <div class="flex min-h-18 items-center gap-3 sm:gap-4">
        <a href="#top" data-landing-nav="top" class="flex shrink-0 items-center gap-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2" aria-label="Kembali ke Beranda">
            <img src="{{ asset('images/reksa-logo.webp') }}" alt="REKSA" class="h-10 w-10 shrink-0 rounded-xl object-contain" fetchpriority="high">
            <span class="flex min-w-0 flex-col">
                <span class="text-sm font-extrabold tracking-wide text-slate-900 sm:text-base">REKSA</span>
                <span class="hidden text-[10px] text-slate-600 lg:inline">Reservasi dan Kerusakan Sarana Akademik</span>
            </span>
        </a>
        <div class="flex min-w-0 flex-1 justify-center">
            <nav class="clay-nav-group hidden max-w-full items-center gap-1 rounded-full lg:flex" aria-label="Navigasi halaman">
                <a href="#top" data-landing-nav="top" data-nav-active="{{ $navActiveClass }}" data-nav-inactive="{{ $navInactiveClass }}" class="{{ $navActiveClass }}" aria-current="page">Beranda</a>
                <a href="#panduan" data-landing-nav="panduan" data-nav-active="{{ $navActiveClass }}" data-nav-inactive="{{ $navInactiveClass }}" class="{{ $navInactiveClass }}">Panduan</a>
                <a href="#fasilitas" data-landing-nav="fasilitas" data-nav-active="{{ $navActiveClass }}" data-nav-inactive="{{ $navInactiveClass }}" class="{{ $navInactiveClass }}">Fasilitas</a>
            </nav>
        </div>
        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            @auth
                <a href="{{ auth()->user()->homeRoute() }}" class="landing-button clay-nav-action rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-3.5 text-sm font-semibold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)] transition hover:from-blue-700 hover:to-blue-600">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="clay-button-white clay-nav-action rounded-full px-3.5 text-sm font-semibold text-blue-700">Masuk</a>
                <a href="{{ route('register') }}" class="landing-button clay-nav-action rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-3.5 text-sm font-semibold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)] transition hover:from-blue-700 hover:to-blue-600">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="hidden h-4 w-4 sm:block" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Daftar Akun
                </a>
            @endauth
        </div>
    </div>
    <div class="border-t border-blue-100/80 py-3 lg:hidden">
        <div class="app-mobile-nav-links text-sm font-medium">
            <a href="#top" data-landing-nav="top" data-nav-active="{{ $navActiveClass }}" data-nav-inactive="{{ $navInactiveClass }}" class="{{ $navActiveClass }}" aria-current="page">Beranda</a>
            <a href="#panduan" data-landing-nav="panduan" data-nav-active="{{ $navActiveClass }}" data-nav-inactive="{{ $navInactiveClass }}" class="{{ $navInactiveClass }}">Panduan</a>
            <a href="#fasilitas" data-landing-nav="fasilitas" data-nav-active="{{ $navActiveClass }}" data-nav-inactive="{{ $navInactiveClass }}" class="{{ $navInactiveClass }}">Fasilitas</a>
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
                    <a href="{{ route('fasilitas.index', ['from' => 'home']) }}" class="clay-button-white inline-flex min-h-11 items-center gap-2 rounded-full px-4 py-3 text-sm font-semibold text-slate-700 sm:px-6">
                        Cek Fasilitas
                    </a>
                </div>
            </div>
            <div class="landing-hero-art relative flex min-h-56 items-center justify-center lg:min-h-[330px]">
                <img src="{{ asset('images/building-calendar.webp') }}" alt="Ilustrasi gedung dan kalender kampus" class="relative z-10 w-full max-h-[320px] object-contain drop-shadow-2xl sm:max-h-[380px]">
            </div>
        </section>

                {{-- SECTION 5: Panduan --}}
        <section id="panduan" data-landing-section class="landing-panel scroll-mt-24 space-y-5 rounded-3xl p-5 shadow-[0_10px_30px_-5px_rgba(186,215,248,0.45),0_0_0_1px_rgba(255,255,255,0.8)_inset] sm:p-7">
            <div>
                <h2 class="text-lg font-bold text-slate-900 sm:text-xl">Alur Peminjaman</h2>
                <p class="text-xs text-[#475569]">Ikuti panduan peminjaman fasilitas atau pelaporan kerusakan.</p>
            </div>
            <div class="grid grid-cols-1 items-center gap-3 md:grid-cols-3 sm:gap-5">
                <div class="landing-step-card relative flex items-start gap-3 rounded-2xl border border-white bg-white/80 p-4 shadow-sm sm:gap-4 sm:p-5">
                    <div class="flex shrink-0 items-center gap-1.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white shadow-sm">1</div>
                    </div>
                    <div>
                    <h3 class="mt-3 text-sm font-semibold text-[#0F172A]">Pilih Fasilitas &amp; Jadwal</h3>
                    <p class="mt-1 text-xs leading-relaxed text-[#475569]">Cari fasilitas lalu cek slot waktu yang tersedia pada pratinjau jadwal.</p>
                    </div>
                </div>
                <div class="landing-step-card relative flex items-start gap-3 rounded-2xl border border-white bg-white/80 p-4 shadow-sm sm:gap-4 sm:p-5">
                    <div class="flex shrink-0 items-center gap-1.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white shadow-sm">2</div>
                    </div>
                    <div>
                    <h3 class="mt-3 text-sm font-semibold text-[#0F172A]">Ajukan Reservasi</h3>
                    <p class="mt-1 text-xs leading-relaxed text-[#475569]">Login dan isi tujuan penggunaan; permohonan masuk antrian persetujuan.</p>
                    </div>
                </div>
                <div class="landing-step-card flex items-start gap-3 rounded-2xl border border-white bg-white/80 p-4 shadow-sm sm:gap-4 sm:p-5">
                    <div class="flex shrink-0 items-center gap-1.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white shadow-sm">3</div>
                    </div>
                    <div>
                    <h3 class="mt-3 text-sm font-semibold text-[#0F172A]">Disetujui Petugas</h3>
                    <p class="mt-1 text-xs leading-relaxed text-[#475569]">Setelah disetujui petugas, slot terkunci dan fasilitas siap digunakan.</p>
                    </div>
                </div>
            </div>
            <div class="border-t border-blue-100 pt-5">
                <h3 class="text-base font-bold text-slate-900">Alur Pelaporan Fasilitas Rusak</h3>
                <p class="text-xs text-[#475569]">Bantu kampus menangani kerusakan fasilitas dengan cepat.</p>
                <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3 sm:gap-5">
                    <div class="landing-step-card flex items-start gap-3 rounded-2xl border border-white bg-white/80 p-4 shadow-sm sm:p-5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white shadow-sm">1</span>
                        <div><h4 class="text-sm font-semibold text-[#0F172A]">Pilih Fasilitas</h4><p class="mt-1 text-xs leading-relaxed text-[#475569]">Temukan fasilitas kampus yang mengalami kerusakan.</p></div>
                    </div>
                    <div class="landing-step-card flex items-start gap-3 rounded-2xl border border-white bg-white/80 p-4 shadow-sm sm:p-5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white shadow-sm">2</span>
                        <div><h4 class="text-sm font-semibold text-[#0F172A]">Kirim Laporan</h4><p class="mt-1 text-xs leading-relaxed text-[#475569]">Login, jelaskan kerusakan, dan sertakan foto jika ada.</p></div>
                    </div>
                    <div class="landing-step-card flex items-start gap-3 rounded-2xl border border-white bg-white/80 p-4 shadow-sm sm:p-5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white shadow-sm">3</span>
                        <div><h4 class="text-sm font-semibold text-[#0F172A]">Pantau Tindak Lanjut</h4><p class="mt-1 text-xs leading-relaxed text-[#475569]">Petugas akan memeriksa laporan dan memperbarui status penanganannya.</p></div>
                    </div>
                </div>
            </div>
        </section>
        
        {{-- SECTION 2: Pencarian --}}
        <section id="fasilitas" data-landing-section class="landing-panel scroll-mt-24 space-y-5 rounded-3xl p-5 shadow-[0_10px_30px_-5px_rgba(186,215,248,0.45),0_0_0_1px_rgba(255,255,255,0.8)_inset] sm:p-7">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 sm:text-xl">Fasilitas Kampus Unggulan</h2>
                    <p class="text-xs text-[#475569]">Temukan fasilitas kampus dan cek jadwal penggunaannya.</p>
                </div>
                <a href="{{ route('fasilitas.index', ['from' => 'home']) }}" class="landing-button rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-2.5 text-sm font-semibold text-white hover:from-blue-700 hover:to-blue-600">Lihat Semua Fasilitas</a>
            </div>
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
                            class="clay-button-white flex h-10 w-full items-center justify-center gap-2 rounded-xl text-xs font-semibold text-slate-700">
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
        <div class="flex justify-end">
            <span class="rounded-full border border-white/80 bg-white/70 px-3 py-1.5 text-[11px] font-medium text-slate-600 shadow-sm" id="facility-count">
                    Menampilkan <span id="facility-count-num">{{ $facilities->count() }}</span> fasilitas pilihan
            </span>
        </div>

            <div id="landing-grid-container" class="grid min-h-[24rem] grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @include('landing._facility-grid', ['facilities' => $facilities])
            </div>

        </section>




    </div>
</main>

<footer class="landing-footer relative overflow-hidden rounded-3xl border border-blue-200/70 py-7 shadow-[0_12px_30px_rgba(53,103,175,0.12)] sm:py-10">
    <div class="relative z-10">
        <div class="mx-auto grid w-full max-w-7xl gap-8 px-5 sm:px-8 lg:grid-cols-[1.4fr_1fr_1fr] lg:gap-12">
            <div class="flex items-start gap-4">
                <img src="{{ asset('images/reksa-logo.webp') }}" alt="Logo REKSA" class="h-14 w-14 shrink-0 rounded-2xl object-contain" loading="lazy">
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
