<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'REKSA')</title>
    <x-ui.favicon />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="landing-page min-h-screen p-3 font-sans text-slate-800 antialiased selection:bg-blue-600 selection:text-white sm:p-5 lg:p-6">
    <a href="#content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-[#00236f] focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">Lewati ke konten utama</a>
    <main id="content" class="landing-panel relative mx-auto min-h-[calc(100vh-2rem)] w-full max-w-7xl overflow-hidden rounded-[2rem] p-5 shadow-[0_24px_60px_-20px_rgba(54,111,194,0.24),0_0_0_1px_rgba(255,255,255,0.8)_inset] sm:p-7 lg:min-h-[calc(100vh-3rem)] lg:p-9 xl:p-11">
        <header class="relative z-10 flex flex-wrap items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" aria-label="REKSA, kembali ke beranda">
                <img src="{{ asset('images/reksa-logo.webp') }}" alt="" class="h-10 w-10 rounded-xl object-contain">
                <span class="flex flex-col"><span class="text-sm font-extrabold tracking-wide text-slate-900 sm:text-base">REKSA</span><span class="hidden text-[10px] text-slate-600 lg:inline">Reservasi dan Kerusakan Sarana Akademik</span></span>
            </a>
            <a href="{{ route('home') }}" class="clay-button-white clay-nav-action gap-2 rounded-full px-3.5 text-sm font-semibold text-blue-700">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m10 19-7-7 7-7M3 12h18"/></svg>
                Kembali ke Beranda
            </a>
        </header>

        <div class="relative z-10 mt-6 grid items-start gap-7 lg:mt-8 lg:grid-cols-12 lg:gap-8 xl:gap-10">
            <section class="flex flex-col lg:col-span-6" aria-label="Tentang REKSA">
                <div class="max-w-2xl">
                    <h1 class="text-3xl font-extrabold leading-tight tracking-tight text-[#10264a] sm:text-4xl xl:text-[44px]">Satu akses untuk fasilitas kampus.</h1>
                    <p class="mt-3 max-w-xl text-sm leading-relaxed text-slate-600 sm:text-base">Cek jadwal, ajukan reservasi, dan laporkan kerusakan dalam satu tempat yang mudah digunakan.</p>
                </div>

                <div class="landing-hero-art relative mx-auto flex min-h-64 w-full flex-1 items-center justify-center sm:min-h-80 lg:min-h-[min(58vh,580px)]">
                    <img src="{{ asset('images/reksa-mascot.webp') }}" alt="Maskot REKSA menyambut pengguna" class="relative z-10 h-64 w-full object-contain drop-shadow-[0_20px_24px_rgba(37,99,235,0.18)] transition-transform duration-300 hover:-translate-y-1 sm:h-80 lg:h-[min(58vh,580px)]">
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <article class="landing-card flex items-start gap-3 rounded-2xl border border-white/90 bg-white/75 p-4 backdrop-blur-md">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-white bg-blue-50 text-blue-600 shadow-[0_4px_10px_rgba(59,130,246,0.1),inset_0_1px_2px_rgba(255,255,255,0.9)]"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 10h16M5 5h14a1 1 0 0 1 1 1v13H4V6a1 1 0 0 1 1-1Z"/><path stroke-linecap="round" d="M8 14h3m-3 3h6"/></svg></span>
                        <div><h2 class="text-sm font-bold text-slate-800">Jadwal transparan</h2><p class="mt-1 text-xs leading-relaxed text-slate-500">Lihat slot yang tersedia sebelum mengajukan.</p></div>
                    </article>
                    <article class="landing-card flex items-start gap-3 rounded-2xl border border-white/90 bg-white/75 p-4 backdrop-blur-md">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-white bg-blue-50 text-blue-600 shadow-[0_4px_10px_rgba(59,130,246,0.1),inset_0_1px_2px_rgba(255,255,255,0.9)]"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg></span>
                        <div><h2 class="text-sm font-bold text-slate-800">Status terarah</h2><p class="mt-1 text-xs leading-relaxed text-slate-500">Pantau reservasi dan laporan Anda.</p></div>
                    </article>
                </div>
            </section>

            <section class="lg:col-span-6" aria-label="Formulir akun">
                @if (session('success'))
                    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50/90 px-4 py-3 text-sm leading-relaxed text-emerald-800" role="status">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50/90 px-4 py-3 text-sm leading-relaxed text-rose-800" role="alert">{{ session('error') }}</div>
                @endif
                @yield('content')
            </section>
        </div>
    </main>
</body>
</html>
