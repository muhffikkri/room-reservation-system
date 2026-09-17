<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistem Reservasi Fasilitas Kampus')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#F8FAFC] text-[#0F172A] antialiased">
    <a href="#content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-[#00236f] focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
        Lewati ke konten utama
    </a>
    <main id="content" class="min-h-screen lg:grid lg:grid-cols-[minmax(0,1fr)_minmax(28rem,0.92fr)]">
        <section class="relative hidden overflow-hidden bg-[#EEF2FF] lg:flex lg:min-h-screen lg:flex-col lg:justify-between">
            <div class="pointer-events-none absolute -left-36 -top-32 h-[34rem] w-[34rem] rounded-full bg-[#00236f]/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-44 -right-24 h-[30rem] w-[30rem] rounded-full bg-[#0051d5]/10 blur-3xl"></div>

            <div class="relative z-10 px-10 pt-10 xl:px-16 xl:pt-12">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 text-[#00236f]" aria-label="Kembali ke beranda">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-[#00236f]/10">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4M9 11h.01M15 11h.01M9 15h.01M15 15h.01" />
                        </svg>
                    </span>
                    <span class="max-w-[14rem] text-sm font-semibold leading-tight">Sistem Reservasi Fasilitas Kampus</span>
                </a>
            </div>

            <div class="relative z-10 px-10 pb-16 xl:px-16">
                <p class="mb-4 inline-flex rounded-full bg-white px-3 py-1 text-xs font-semibold text-[#00236f] shadow-sm ring-1 ring-[#00236f]/10">
                    Portal Sarana &amp; Prasarana Kampus
                </p>
                <h1 class="max-w-xl text-4xl font-bold tracking-tight text-[#00236f] xl:text-5xl">
                    Satu akses untuk fasilitas kampus.
                </h1>
                <p class="mt-5 max-w-lg text-base leading-relaxed text-[#475569]">
                    Cek jadwal, ajukan reservasi, dan laporkan kerusakan dalam satu tempat yang mudah digunakan.
                </p>

                <div class="mt-8 grid max-w-lg grid-cols-2 gap-3">
                    <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-[#00236f]/10">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#E2E7FF] text-sm font-bold text-[#00236f]">01</span>
                        <p class="mt-3 text-sm font-semibold text-[#0F172A]">Jadwal transparan</p>
                        <p class="mt-1 text-xs leading-relaxed text-[#64748B]">Lihat slot yang tersedia sebelum mengajukan.</p>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-[#00236f]/10">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#E2E7FF] text-sm font-bold text-[#00236f]">02</span>
                        <p class="mt-3 text-sm font-semibold text-[#0F172A]">Status terarah</p>
                        <p class="mt-1 text-xs leading-relaxed text-[#64748B]">Pantau reservasi dan laporan Anda.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="flex min-h-screen flex-col">
            <header class="flex items-center justify-between px-5 py-5 sm:px-8 lg:justify-end lg:px-12 lg:py-8">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#00236f] lg:hidden">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#E2E7FF]">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4M9 11h.01M15 11h.01M9 15h.01M15 15h.01" />
                        </svg>
                    </span>
                    Beranda
                </a>
                <p class="text-xs font-medium text-[#64748B]">Layanan fasilitas kampus</p>
            </header>

            <div class="flex flex-1 items-start justify-center px-5 pb-10 pt-6 sm:px-8 sm:pt-10 lg:items-center lg:px-12 lg:pb-16 lg:pt-0">
                <div class="w-full max-w-md">
                    @if (session('success'))
                        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm leading-relaxed text-emerald-800" role="status">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm leading-relaxed text-rose-800" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </section>
    </main>
</body>

</html>
