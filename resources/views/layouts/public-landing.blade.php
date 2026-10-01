<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Jelajahi fasilitas kampus REKSA dan cek jadwal ketersediaannya.">
    <title>@yield('title', 'REKSA')</title>
    <x-ui.favicon />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="landing-page min-h-screen py-5 font-sans text-slate-800 antialiased">
<div class="mx-auto w-full max-w-public space-y-5 px-4 sm:px-6 lg:px-8">
    <header class="clay-nav sticky top-3 z-50 flex min-h-18 items-center justify-between gap-3 rounded-[2rem] px-4 sm:gap-4 sm:rounded-full sm:px-6">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2" aria-label="REKSA, kembali ke halaman utama">
            <img src="{{ asset('images/reksa-logo.webp') }}" alt="" class="h-10 w-10 rounded-xl object-contain">
            <span class="flex flex-col"><span class="text-sm font-extrabold tracking-wide text-slate-900 sm:text-base">REKSA</span><span class="hidden text-[10px] text-slate-600 lg:inline">Reservasi dan Kerusakan Sarana Akademik</span></span>
        </a>
        <div class="flex shrink-0 items-center gap-3">
            <a href="{{ route('home') }}" class="clay-button-white clay-nav-action rounded-full px-3.5 text-sm font-semibold text-blue-700">Beranda</a>
            @auth
<a href="{{ auth()->user()->homeRoute() }}" class="landing-button clay-nav-action rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-4 py-2.5 text-xs font-semibold text-white sm:text-sm">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="landing-button clay-nav-action rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-3.5 text-sm font-semibold text-white">Masuk</a>
            @endauth
        </div>
    </header>
    <main id="content" class="space-y-5 py-1">@yield('content')</main>
    <footer class="landing-footer relative overflow-hidden rounded-3xl border border-blue-200/70 px-6 py-6 text-xs text-slate-600 shadow-[0_12px_30px_rgba(53,103,175,0.12)]">
        <div class="relative z-10 flex flex-wrap items-center justify-between gap-3"><span>© {{ date('Y') }} REKSA · Reservasi dan Kerusakan Sarana Akademik</span><a href="{{ route('home') }}" class="landing-button rounded-full px-3 py-2 font-semibold text-blue-800">Kembali ke beranda</a></div>
    </footer>
</div>
</body>
</html>
