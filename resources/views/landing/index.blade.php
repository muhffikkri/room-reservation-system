@php
    $badgeClasses = [
        'aktif' => 'bg-green-50 text-green-700 ring-green-200',
        'perbaikan' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'nonaktif' => 'bg-slate-100 text-slate-600 ring-slate-200',
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
    $tabActiveClass = 'inline-flex items-center gap-2 rounded-lg bg-[#00236f] px-4 py-2 font-medium text-white shadow-sm transition-all';
    $tabInactiveClass = 'inline-flex items-center gap-2 rounded-lg bg-[#EEF2FF] px-4 py-2 font-medium text-[#475569] transition-all hover:bg-[#e2e7ff] hover:text-[#0F172A]';
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem Reservasi &amp; Pelaporan Fasilitas Kampus — temukan fasilitas, cek ketersediaan jadwal, dan ajukan reservasi.">
    <title>Sistem Reservasi Fasilitas Kampus</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-landing class="min-h-screen bg-[#F8FAFC] text-[#0F172A] antialiased">

<header class="fixed inset-x-0 top-0 z-50 bg-white/95 shadow-[0_1px_8px_rgba(0,0,0,0.04)] backdrop-blur-md">
    <div class="flex h-16 items-center justify-between px-6">
        <a href="#top" class="flex items-center gap-3">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-8 w-8 text-[#00236f]" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4M9 11h.01M15 11h.01M9 15h.01M15 15h.01"/>
            </svg>
            <span class="text-lg font-semibold text-[#00236f]">Sistem Reservasi Fasilitas Kampus</span>
        </a>
        <nav class="hidden items-center gap-4 md:flex">
            <a href="#top" class="rounded-lg px-3 py-2 text-sm font-medium text-[#475569] transition-colors hover:bg-[#f2f3ff] hover:text-[#0F172A]">Beranda</a>
            <a href="#fasilitas" class="rounded-lg px-3 py-2 text-sm font-medium text-[#475569] transition-colors hover:bg-[#f2f3ff] hover:text-[#0F172A]">Fasilitas</a>
            <a href="#jadwal-preview" class="rounded-lg bg-[#e2e7ff] px-3 py-2 text-sm font-medium text-[#0F172A] transition-colors">Jadwal</a>
            <a href="#panduan" class="rounded-lg px-3 py-2 text-sm font-medium text-[#475569] transition-colors hover:bg-[#f2f3ff] hover:text-[#0F172A]">Panduan</a>
        </nav>
        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="rounded-lg bg-[#0051d5] px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-[#00236f]">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-[#00236f] transition-colors hover:bg-[#f2f3ff]">Masuk</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-[#0051d5] px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-[#00236f]">Daftar Akun</a>
            @endauth
        </div>
    </div>
</header>

<main id="top" class="w-full pt-16">
    <div class="mx-auto w-full max-w-7xl space-y-10 px-6 py-8">

        {{-- SECTION 1: Hero --}}
        <section class="relative overflow-hidden rounded-xl bg-white p-8 shadow-sm md:p-12">
            <div class="pointer-events-none absolute -right-20 -top-20 h-96 w-96 rounded-full bg-[#00236f]/5 blur-3xl"></div>
            <div class="relative z-10 max-w-3xl space-y-4">
                <div class="inline-flex items-center gap-2 rounded-full bg-[#e2e7ff] px-3 py-1 text-xs font-medium text-[#00236f]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m-6-4.2V15a8 8 0 0012 0v-5.2"/>
                    </svg>
                    Portal Pelayanan Sarana &amp; Prasarana Kampus Terpadu
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-[#0F172A]">Kelola Penggunaan Fasilitas Kampus dengan Mudah</h1>
                <p class="max-w-2xl text-base leading-relaxed text-[#475569]">
                    Temukan fasilitas, lihat ketersediaan jadwal, dan lakukan reservasi secara mudah. Transparansi penuh untuk kegiatan akademik dan kemahasiswaan.
                </p>
                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <a href="{{ route('login') }}" class="inline-flex h-10 items-center gap-2 rounded-lg bg-[#00236f] px-5 text-sm font-medium text-white shadow-sm transition-all hover:bg-[#001a52]">
                        Login untuk Reservasi
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-[18px] w-[18px]" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/>
                        </svg>
                    </a>
                    <a href="#jadwal-preview" class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-[#f2f3ff] px-4 text-sm font-medium text-[#475569] transition-colors hover:bg-[#e2e7ff] hover:text-[#0F172A]">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px]" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4m8-4v4M3 10h18"/>
                        </svg>
                        Cek Ketersediaan Hari Ini
                    </a>
                </div>
            </div>
        </section>

        {{-- SECTION 2: Pencarian --}}
        <section class="rounded-xl bg-white p-6 shadow-sm">
            <form method="GET" action="{{ route('home') }}" class="grid grid-cols-1 items-end gap-4 md:grid-cols-2 lg:grid-cols-5">
                <div class="lg:col-span-1">
                    <label for="search-input" class="mb-1.5 block text-sm font-medium text-[#475569]">Pencarian</label>
                    <div class="relative">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="absolute left-3 top-2.5 h-[18px] w-[18px] text-[#94A3B8]" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M20 20l-3.5-3.5"/>
                        </svg>
                        <input id="search-input" name="q" value="{{ $filters['q'] ?? '' }}" type="text" placeholder="Search fasilitas..."
                               class="h-10 w-full rounded-lg bg-[#f2f3ff] pl-9 pr-3 text-sm text-[#0F172A] transition focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#00236f]/40">
                    </div>
                </div>
                <div>
                    <label for="filter-type" class="mb-1.5 block text-sm font-medium text-[#475569]">Jenis Fasilitas</label>
                    <select id="filter-type" name="type"
                            class="h-10 w-full rounded-lg bg-[#f2f3ff] px-3 text-sm text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#00236f]/40">
                        <option value="">Semua Jenis</option>
                        @foreach ($typeLabels as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? null) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter-location" class="mb-1.5 block text-sm font-medium text-[#475569]">Lokasi</label>
                    <select id="filter-location" name="location"
                            class="h-10 w-full rounded-lg bg-[#f2f3ff] px-3 text-sm text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#00236f]/40">
                        <option value="">Semua Gedung</option>
                        @foreach ($locationOptions as $location)
                            <option value="{{ $location }}" @selected(($filters['location'] ?? null) === $location)>{{ $location }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter-capacity" class="mb-1.5 block text-sm font-medium text-[#475569]">Kapasitas</label>
                    <select id="filter-capacity" name="capacity"
                            class="h-10 w-full rounded-lg bg-[#f2f3ff] px-3 text-sm text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#00236f]/40">
                        <option value="">Semua Kapasitas</option>
                        <option value="lt_40" @selected(($filters['capacity'] ?? null) === 'lt_40')>&lt; 40 orang</option>
                        <option value="40_100" @selected(($filters['capacity'] ?? null) === '40_100')>40 - 100 orang</option>
                        <option value="gt_100" @selected(($filters['capacity'] ?? null) === 'gt_100')>&gt; 100 orang</option>
                    </select>
                </div>
                <div>
                    <button type="submit"
                            class="flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-[#00236f] text-sm font-medium text-white shadow-sm transition-all hover:bg-[#001a52]">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px]" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16M8 6v-.5A1.5 1.5 0 0110 4v0A1.5 1.5 0 0111.5 6v0M12 12v-.5A1.5 1.5 0 0114 10v0A1.5 1.5 0 0115.5 12v0M8 18v-.5A1.5 1.5 0 0110 16v0A1.5 1.5 0 0111.5 18v0"/>
                        </svg>
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </section>

        {{-- SECTION 3: Grid Kartu Fasilitas --}}
        <section id="fasilitas" class="space-y-6 scroll-mt-24">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold text-[#0F172A]">Fasilitas Kampus Unggulan</h2>
                    <p class="text-xs text-[#475569]">Fasilitas standar akademik dengan pratinjau ketersediaan jam secara real-time.</p>
                </div>
                <span class="rounded-full bg-[#e2e7ff] px-3 py-1 text-xs font-medium text-[#475569]">
                    Menampilkan {{ $facilities->count() }} dari {{ $totalFacilities }} Fasilitas
                </span>
            </div>

            @if ($facilities->isEmpty())
                <div class="rounded-xl bg-white p-10 text-center shadow-sm">
                    <p class="text-base font-medium text-[#0F172A]">Fasilitas tidak ditemukan</p>
                    <p class="mt-1 text-sm text-[#475569]">Coba ubah kata kunci atau filter pencarian Anda.</p>
                    <a href="{{ route('home') }}" class="mt-4 inline-block rounded-lg bg-[#00236f] px-4 py-2 text-sm font-medium text-white hover:bg-[#001a52]">Reset Filter</a>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                @foreach ($facilities as $facility)
                    <div class="group flex flex-col overflow-hidden rounded-xl bg-white shadow-sm transition-all hover:shadow-md">
                        <div class="relative aspect-[16/9] w-full overflow-hidden bg-[#f2f3ff]">
                            @if ($facility->photo)
                                <img src="{{ Storage::disk('public')->url($facility->photo) }}" alt="{{ $facility->name }}"
                                     class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#00236f] to-[#0051d5]">
                                    <span class="text-5xl font-bold text-white/90">{{ strtoupper(substr($facility->name, 0, 2)) }}</span>
                                </div>
                            @endif
                            <span class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-white/95 px-2.5 py-1 text-xs font-medium shadow-sm ring-1 {{ $badgeClasses[$facility->status] }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $facility->status === 'aktif' ? 'bg-green-500' : ($facility->status === 'perbaikan' ? 'bg-amber-500' : 'bg-slate-400') }}"></span>
                                {{ ucfirst($facility->status) }}
                            </span>
                        </div>
                        <div class="flex flex-1 flex-col justify-between gap-4 p-6">
                            <div class="space-y-2">
                                <h3 class="text-lg font-semibold text-[#0F172A]">{{ $facility->name }}</h3>
                                <span class="inline-block rounded bg-[#e2e7ff] px-2 py-0.5 text-xs font-medium text-[#00236f]">{{ $typeLabels[$facility->type] }}</span>
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
                                        <p class="pt-1 text-xs leading-relaxed text-[#94A3B8]">{{ \Illuminate\Support\Str::limit($facility->description, 90) }}</p>
                                    @endif
                                </div>
                            </div>
                            <button type="button" data-landing-go="{{ $facility->id }}"
                                    class="flex h-10 w-full items-center justify-center gap-1.5 rounded-lg bg-[#f2f3ff] text-sm font-medium text-[#00236f] transition-colors hover:bg-[#e2e7ff]">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px]" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4m8-4v4M3 10h18"/>
                                </svg>
                                Lihat Jadwal
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- SECTION 4: Pratinjau Jadwal --}}
        <section id="jadwal-preview" class="scroll-mt-24 space-y-6 rounded-xl bg-white p-6 shadow-sm md:p-8">
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <div class="flex items-center gap-2">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6 text-[#00236f]" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4m8-4v4M3 10h18M9 15h6"/>
                        </svg>
                        <h2 class="text-lg font-semibold text-[#0F172A]">Pratinjau Ketersediaan Jadwal (Hari Ini: {{ $today->format('j F Y') }})</h2>
                    </div>
                    <p class="mt-1 text-xs text-[#475569]">Pilih fasilitas untuk melihat jam ketersediaan publik secara real-time.</p>
                </div>
                <div class="inline-flex items-center gap-2 self-start rounded-lg bg-[#f2f3ff] px-3 py-1.5 text-sm text-[#475569] md:self-auto">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 text-[#0891B2]" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/>
                    </svg>
                    Waktu Server: {{ $today->format('H:i') }} WIB
                </div>
            </div>

            {{-- Tab fasilitas --}}
            <div class="flex flex-wrap gap-2 pt-1">
                @foreach ($facilities as $facility)
                    <button type="button" data-landing-tab="{{ $facility->id }}"
                            data-tab-active="{{ $tabActiveClass }}"
                            data-tab-inactive="{{ $tabInactiveClass }}"
                            class="{{ $loop->first ? $tabActiveClass : $tabInactiveClass }}">
                        {{ $facility->name }}
                    </button>
                @endforeach
            </div>

            <div class="flex items-center justify-between rounded-lg bg-[#f2f3ff]/70 p-3.5">
                <div class="flex items-center gap-3">
                    <span class="h-3 w-3 animate-ping rounded-full bg-green-500"></span>
                    <span class="text-sm font-medium text-[#0F172A]" data-facility-indicator>
                        Fasilitas: {{ $facilities->first()?->name ?? '-' }}
                    </span>
                </div>
                <span class="text-xs font-medium text-[#475569]">Zona Operasional: 07.00 - 20.00 WIB</span>
            </div>

            @if ($facilities->isEmpty())
                <p class="rounded-lg bg-[#f2f3ff]/70 p-6 text-center text-sm text-[#475569]">
                    Tidak ada fasilitas untuk ditampilkan. Reset filter untuk melihat pratinjau ketersediaan.
                </p>
            @endif

            @foreach ($facilities as $facility)
                <div data-facility-grid="{{ $facility->id }}" data-facility-name="{{ $facility->name }}"
                     class="{{ $loop->first ? '' : 'hidden' }} space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium uppercase tracking-wider text-[#475569]">Slot Waktu Pemakaian (Interval 30 Menit)</span>
                        <span class="text-xs text-[#94A3B8]">Total 26 Slot</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                        @foreach ($grids[$facility->id] as $slot)
                            <div class="flex flex-col items-center justify-center rounded-lg p-3 text-center select-none {{ $slotClasses[$slot['state']] }}
                                @if ($slot['state'] === 'past' || $slot['state'] === 'booked' || $slot['state'] === 'inactive') cursor-not-allowed @endif">
                                <span class="text-sm font-medium">{{ $slot['start'] }} - {{ $slot['end'] }}</span>
                                <span class="mt-0.5 text-xs font-medium {{ in_array($slot['state'], ['booked', 'past', 'inactive'], true) ? '' : 'text-green-600' }}">{{ $slotLabels[$slot['state']] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Legend --}}
            <div class="flex flex-wrap items-center gap-6 pb-1 pt-2">
                <div class="flex items-center gap-2">
                    <span class="flex h-4 w-4 items-center justify-center rounded bg-white shadow-sm"><span class="h-2 w-2 rounded-full bg-green-500"></span></span>
                    <span class="text-xs text-[#475569]">Tersedia untuk Reservasi</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="flex h-4 w-4 items-center justify-center rounded bg-[#FFDAD6]/60"><span class="h-2 w-2 rounded-full bg-[#DC2626]"></span></span>
                    <span class="text-xs text-[#475569]">Terpakai / Sedang Dipesan</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="flex h-4 w-4 items-center justify-center rounded bg-[#EEF2FF]"><span class="h-2 w-2 rounded-full bg-[#94A3B8]"></span></span>
                    <span class="text-xs text-[#475569]">Waktu Lewat / Tidak Aktif</span>
                </div>
            </div>

            {{-- Catatan Privasi (BR-13) --}}
            <div class="flex items-start gap-3.5 rounded-xl bg-[#f2f3ff] p-4">
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

        {{-- SECTION 5: Panduan --}}
        <section id="panduan" class="scroll-mt-24 space-y-6 rounded-xl bg-white p-6 shadow-sm md:p-8">
            <div>
                <h2 class="text-xl font-semibold text-[#0F172A]">Alur Peminjaman</h2>
                <p class="text-xs text-[#475569]">Tiga langkah sederhana untuk menggunakan fasilitas kampus.</p>
            </div>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="rounded-xl bg-[#f2f3ff] p-5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#00236f] text-sm font-bold text-white">1</div>
                    <h3 class="mt-3 text-sm font-semibold text-[#0F172A]">Pilih Fasilitas &amp; Jadwal</h3>
                    <p class="mt-1 text-xs leading-relaxed text-[#475569]">Cari fasilitas lalu cek slot waktu yang tersedia pada pratinjau jadwal.</p>
                </div>
                <div class="rounded-xl bg-[#f2f3ff] p-5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#00236f] text-sm font-bold text-white">2</div>
                    <h3 class="mt-3 text-sm font-semibold text-[#0F172A]">Ajukan Reservasi</h3>
                    <p class="mt-1 text-xs leading-relaxed text-[#475569]">Login dan isi tujuan penggunaan; permohonan masuk antrian persetujuan.</p>
                </div>
                <div class="rounded-xl bg-[#f2f3ff] p-5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#00236f] text-sm font-bold text-white">3</div>
                    <h3 class="mt-3 text-sm font-semibold text-[#0F172A]">Disetujui Petugas</h3>
                    <p class="mt-1 text-xs leading-relaxed text-[#475569]">Setelah disetujui petugas, slot terkunci dan fasilitas siap digunakan.</p>
                </div>
            </div>
        </section>
    </div>
</main>

<footer class="w-full bg-white py-12 shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
    <div class="flex w-full flex-col items-center justify-between gap-4 px-6 md:flex-row">
        <div class="flex items-center gap-3">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7 text-[#00236f] opacity-90" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4M9 11h.01M15 11h.01M9 15h.01M15 15h.01"/>
            </svg>
            <div class="flex flex-col">
                <span class="text-sm font-medium text-[#00236f]">Biro Pengelolaan Fasilitas &amp; Logistik Kampus</span>
                <span class="text-xs text-[#475569]">Layanan peminjaman ruangan, laboratorium, dan pelaporan kerusakan sarana prasarana.</span>
            </div>
        </div>
        <span class="text-xs text-[#475569]">&copy; {{ date('Y') }} Kampus. Seluruh hak cipta dilindungi undang-undang.</span>
    </div>
</footer>

</body>
</html>