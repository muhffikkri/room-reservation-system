<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistem Reservasi Fasilitas Kampus')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="landing-page min-h-screen py-4 font-sans text-slate-800 antialiased sm:py-5">
    <a href="#content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-[#00236f] focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
        Lewati ke konten utama
    </a>
    <nav class="sticky top-3 z-50 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" aria-label="Navigasi utama">
        <div class="landing-panel mx-auto rounded-[2rem] px-4 shadow-[8px_8px_20px_rgba(166,195,235,0.35),-8px_-8px_20px_rgba(255,255,255,0.95)] sm:rounded-full sm:px-6">
            <div class="flex min-h-18 items-center justify-between gap-4">
                <a href="{{ url('/') }}" class="flex shrink-0 items-center gap-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2" aria-label="REKSA, kembali ke halaman utama">
                    <img src="{{ asset('images/reksa-logo.webp') }}" alt="" class="h-10 w-10 rounded-xl object-contain">
                    <span class="flex flex-col"><span class="text-sm font-extrabold tracking-wide text-slate-900 sm:text-base">REKSA</span><span class="hidden text-[10px] text-slate-600 xl:inline">Reservasi dan Kerusakan Sarana Akademik</span></span>
                </a>

                @auth
                    @php($role = auth()->user()->role)
                    <div class="hidden items-center gap-1 rounded-full border border-blue-100/60 bg-slate-100/70 p-1.5 shadow-inner lg:flex">
                        @if ($role === 'pengguna')
                            <a href="{{ route('dashboard') }}" @class(['landing-button rounded-full px-5 py-2 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('dashboard'), 'landing-nav-muted font-medium' => ! request()->routeIs('dashboard')])>Ringkasan</a>
                            <a href="{{ route('reservasi.index') }}" @class(['landing-button rounded-full px-5 py-2 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('reservasi.*'), 'landing-nav-muted font-medium' => ! request()->routeIs('reservasi.*')])>Reservasi Saya</a>
                            <a href="{{ route('laporan.index') }}" @class(['landing-button rounded-full px-5 py-2 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('laporan.*'), 'landing-nav-muted font-medium' => ! request()->routeIs('laporan.*')])>Laporan Kerusakan</a>
                        @elseif ($role === 'petugas')
                            <a href="{{ route('petugas.dashboard') }}" @class(['landing-button rounded-full px-5 py-2 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('petugas.dashboard'), 'landing-nav-muted font-medium' => ! request()->routeIs('petugas.dashboard')])>Operasional</a>
                            <a href="{{ route('petugas.reservasi.index') }}" @class(['landing-button rounded-full px-5 py-2 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('petugas.reservasi.*'), 'landing-nav-muted font-medium' => ! request()->routeIs('petugas.reservasi.*')])>Antrian Reservasi</a>
                            <a href="{{ route('petugas.laporan.index') }}" @class(['landing-button rounded-full px-5 py-2 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('petugas.laporan.*'), 'landing-nav-muted font-medium' => ! request()->routeIs('petugas.laporan.*')])>Laporan</a>
                        @else
                            <a href="{{ route('admin.dashboard') }}" @class(['landing-button rounded-full px-5 py-2 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('admin.dashboard'), 'landing-nav-muted font-medium' => ! request()->routeIs('admin.dashboard')])>Administrasi</a>
                            <a href="{{ route('admin.pengguna.verifikasi') }}" @class(['landing-button rounded-full px-5 py-2 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('admin.pengguna.verifikasi'), 'landing-nav-muted font-medium' => ! request()->routeIs('admin.pengguna.verifikasi')])>Verifikasi Akun</a>
                            <a href="{{ route('admin.fasilitas.index') }}" @class(['landing-button rounded-full px-5 py-2 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('admin.fasilitas.*'), 'landing-nav-muted font-medium' => ! request()->routeIs('admin.fasilitas.*')])>Fasilitas</a>
                            <a href="{{ route('admin.rekap.occupancy') }}" @class(['landing-button rounded-full px-5 py-2 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('admin.rekap.occupancy*'), 'landing-nav-muted font-medium' => ! request()->routeIs('admin.rekap.occupancy*')])>Rekap Okupansi</a>
                            <a href="{{ route('admin.rekap.damage') }}" @class(['landing-button rounded-full px-5 py-2 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('admin.rekap.damage*'), 'landing-nav-muted font-medium' => ! request()->routeIs('admin.rekap.damage*')])>Rekap Kerusakan</a>
                        @endif
                    </div>

                    <div class="flex items-center gap-3 sm:gap-4">
                        @php($unreadNotifications = auth()->user()->unreadNotifications()->count())
                        <div class="relative">
                        <button type="button" data-notification-toggle aria-expanded="false" aria-controls="notification-panel"
                                class="clay-button-white relative inline-flex h-10 w-10 items-center justify-center rounded-full text-[#00236f] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2"
                                aria-label="Notifikasi{{ $unreadNotifications > 0 ? " ({$unreadNotifications} belum dibaca)" : '' }}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4-5.7V5a2 2 0 1 0-4 0v.3A6 6 0 0 0 6 11v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0m6 0H9" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                @if ($unreadNotifications > 0)
                                    <span class="absolute -right-1 -top-1 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-bold leading-none text-white">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                                @endif
                            </button>
                            <div id="notification-panel" data-notification-panel class="hidden absolute right-0 z-50 mt-2 w-80 rounded-xl border border-[#E2E7FF] bg-white p-3 shadow-[0_14px_40px_rgba(15,23,42,0.12)]">
                                <div class="flex items-center justify-between border-b border-[#EEF2FF] pb-2">
                                    <p class="text-sm font-semibold text-[#00236f]">Notifikasi</p>
                                    @if ($unreadNotifications > 0)
                                        <form method="POST" action="{{ route('notifications.read-all') }}">
                                            @csrf
                                            <button type="submit" class="text-xs font-medium text-[#0051d5] transition-colors hover:text-[#00236f]">Tandai semua dibaca</button>
                                        </form>
                                    @endif
                                </div>
                                <ul class="mt-2 max-h-80 space-y-1 overflow-y-auto">
                                    @forelse (auth()->user()->notifications()->latest()->limit(10)->get() as $notification)
                                        <li>
                                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="block w-full rounded-lg px-3 py-2 text-left text-sm transition hover:bg-[#F8FAFC] {{ $notification->read_at === null ? 'bg-[#F2F3FF]' : '' }}">
                                                    <span class="{{ $notification->read_at === null ? 'font-semibold text-[#00236f]' : 'text-slate-600' }}">{{ $notification->data['message'] ?? '' }}</span>
                                                    <span class="mt-0.5 block text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                                                </button>
                                            </form>
                                        </li>
                                    @empty
                                        <li class="px-3 py-4 text-center text-sm text-slate-500">Belum ada notifikasi.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                        <span class="flex min-w-0 items-center gap-2 sm:gap-2.5">
                            <img src="{{ asset('images/reksa-mascot.webp') }}" alt="" class="hidden h-9 w-9 shrink-0 rounded-full border border-white bg-blue-50 object-contain shadow-[3px_3px_8px_rgba(135,166,207,0.2),-2px_-2px_6px_rgba(255,255,255,0.9)] sm:inline">
                            <span class="hidden max-w-36 truncate text-sm font-semibold text-slate-700 sm:block">{{ auth()->user()->name }}</span>
                        </span>
                        <form method="POST" action="{{ route('logout') }}" class="hidden sm:block sm:ml-1">
                            @csrf
                            <button type="submit" class="clay-button-danger rounded-full px-4 py-2.5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">Keluar</button>
                        </form>
                    </div>
                @endauth
            </div>

            @auth
                <div id="mobile-navigation" class="border-t border-blue-100/80 py-3 lg:hidden" data-mobile-menu>
                    <div class="app-mobile-nav-links text-sm font-medium">
                        @if ($role === 'pengguna')
                            <a href="{{ route('dashboard') }}" @class(['landing-button rounded-full px-5 py-2.5 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('dashboard'), 'landing-nav-muted bg-white/50 font-medium' => ! request()->routeIs('dashboard')])>Ringkasan</a>
                            <a href="{{ route('reservasi.index') }}" @class(['landing-button rounded-full px-5 py-2.5 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('reservasi.*'), 'landing-nav-muted bg-white/50 font-medium' => ! request()->routeIs('reservasi.*')])>Reservasi Saya</a>
                            <a href="{{ route('laporan.index') }}" @class(['landing-button rounded-full px-5 py-2.5 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('laporan.*'), 'landing-nav-muted bg-white/50 font-medium' => ! request()->routeIs('laporan.*')])>Laporan Kerusakan</a>
                        @elseif ($role === 'petugas')
                            <a href="{{ route('petugas.dashboard') }}" @class(['landing-button rounded-full px-5 py-2.5 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('petugas.dashboard'), 'landing-button-secondary font-medium' => ! request()->routeIs('petugas.dashboard')])>Operasional</a>
                            <a href="{{ route('petugas.reservasi.index') }}" @class(['landing-button rounded-full px-5 py-2.5 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('petugas.reservasi.*'), 'landing-button-secondary font-medium' => ! request()->routeIs('petugas.reservasi.*')])>Antrian Reservasi</a>
                            <a href="{{ route('petugas.laporan.index') }}" @class(['landing-button rounded-full px-5 py-2.5 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('petugas.laporan.*'), 'landing-button-secondary font-medium' => ! request()->routeIs('petugas.laporan.*')])>Laporan</a>
                        @else
                            <a href="{{ route('admin.dashboard') }}" @class(['landing-button rounded-full px-5 py-2.5 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('admin.dashboard'), 'landing-button-secondary font-medium' => ! request()->routeIs('admin.dashboard')])>Administrasi</a>
                            <a href="{{ route('admin.pengguna.verifikasi') }}" @class(['landing-button rounded-full px-5 py-2.5 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('admin.pengguna.verifikasi'), 'landing-button-secondary font-medium' => ! request()->routeIs('admin.pengguna.verifikasi')])>Verifikasi Akun</a>
                            <a href="{{ route('admin.fasilitas.index') }}" @class(['landing-button rounded-full px-5 py-2.5 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('admin.fasilitas.*'), 'landing-button-secondary font-medium' => ! request()->routeIs('admin.fasilitas.*')])>Fasilitas</a>
                            <a href="{{ route('admin.rekap.occupancy') }}" @class(['landing-button rounded-full px-5 py-2.5 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('admin.rekap.occupancy*'), 'landing-button-secondary font-medium' => ! request()->routeIs('admin.rekap.occupancy*')])>Rekap Okupansi</a>
                            <a href="{{ route('admin.rekap.damage') }}" @class(['landing-button rounded-full px-5 py-2.5 text-sm transition-all', 'bg-blue-600 font-semibold text-white shadow-md' => request()->routeIs('admin.rekap.damage*'), 'landing-button-secondary font-medium' => ! request()->routeIs('admin.rekap.damage*')])>Rekap Kerusakan</a>
                        @endif
                    </div>
                <div class="mt-4 flex items-center justify-between gap-4 border-t border-blue-100/80 px-3 pt-4 text-sm sm:hidden">
                        <span class="flex min-w-0 items-center gap-2.5 text-slate-700"><img src="{{ asset('images/reksa-mascot.webp') }}" alt="" class="h-9 w-9 shrink-0 rounded-full border border-white bg-blue-50 object-contain shadow-sm"><span class="max-w-52 truncate font-semibold">{{ auth()->user()->name }}</span></span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="clay-button-danger rounded-full px-4 py-2 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">Keluar</button>
                        </form>
                    </div>
                </div>
            @endauth
        </div>
    </nav>
    <main id="content" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        @if (session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm leading-relaxed text-emerald-800" role="status">
                {{ session('success') }}
            </div>
        @endif
        @if (session('info'))
            <div class="mb-5 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm leading-relaxed text-sky-800" role="status">
                {{ session('info') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm leading-relaxed text-rose-800" role="alert">
                {{ session('error') }}
            </div>
        @endif
        @yield('content')
    </main>
    <footer class="mx-auto max-w-7xl px-4 pb-4 text-center text-xs text-slate-500 sm:px-6 lg:px-8">
        © {{ date('Y') }} REKSA · Reservasi dan Kerusakan Sarana Akademik
    </footer>
</body>

</html>
