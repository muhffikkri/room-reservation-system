<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistem Reservasi Fasilitas Kampus')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#F8FAFC] text-[#0F172A] antialiased">
    <a href="#content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-[#00236f] focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
        Lewati ke konten utama
    </a>
    <nav class="border-b border-[#E2E7FF] bg-white/95 backdrop-blur" aria-label="Navigasi utama">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex min-h-18 items-center justify-between gap-4">
                <a href="{{ url('/') }}" class="flex shrink-0 items-center gap-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2" aria-label="Kembali ke halaman utama">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#E2E7FF] text-[#00236f]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M3 21h18M5 21V8.5L12 4l7 4.5V21M8 21v-7h8v7M8 10h.01M12 10h.01M16 10h.01" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <span class="hidden text-sm font-semibold tracking-tight text-[#00236f] sm:block">Sistem Reservasi Fasilitas</span>
                    <span class="text-sm font-semibold tracking-tight text-[#00236f] sm:hidden">SRF Kampus</span>
                </a>

                @auth
                    @php($role = auth()->user()->role)
                    <div class="hidden items-center gap-1 lg:flex">
                        @if ($role === 'pengguna')
                            <a href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Ringkasan</a>
                            <a href="{{ route('reservasi.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('reservasi.*') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Reservasi Saya</a>
                            <a href="{{ route('laporan.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('laporan.*') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Laporan Kerusakan</a>
                        @elseif ($role === 'petugas')
                            <a href="{{ route('petugas.dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('petugas.dashboard') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Operasional</a>
                            <a href="{{ route('petugas.reservasi.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('petugas.reservasi.*') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Antrian Reservasi</a>
                            <a href="{{ route('petugas.laporan.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('petugas.laporan.*') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Laporan</a>
                        @else
                            <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Administrasi</a>
                            <a href="{{ route('admin.pengguna.verifikasi') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.pengguna.verifikasi') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Verifikasi Akun</a>
                            <a href="{{ route('admin.fasilitas.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.fasilitas.*') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Fasilitas</a>
                            <a href="{{ route('admin.rekap.occupancy') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.rekap.occupancy*') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Rekap Okupansi</a>
                            <a href="{{ route('admin.rekap.damage') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('admin.rekap.damage*') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Rekap Kerusakan</a>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        @php($unreadNotifications = auth()->user()->unreadNotifications()->count())
                        <div class="relative">
                            <button type="button" data-notification-toggle aria-expanded="false" aria-controls="notification-panel"
                                class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-[#E2E7FF] text-[#00236f] transition-colors hover:bg-[#F2F3FF] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2"
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
                                            <a href="{{ route('notifications.read', $notification->id) }}"
                                                class="block rounded-lg px-3 py-2 text-sm transition hover:bg-[#F8FAFC] {{ $notification->read_at === null ? 'bg-[#F2F3FF]' : '' }}">
                                                <span class="{{ $notification->read_at === null ? 'font-semibold text-[#00236f]' : 'text-slate-600' }}">{{ $notification->data['message'] ?? '' }}</span>
                                                <span class="mt-0.5 block text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                                            </a>
                                        </li>
                                    @empty
                                        <li class="px-3 py-4 text-center text-sm text-slate-500">Belum ada notifikasi.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                        <span class="hidden max-w-36 truncate text-sm font-medium text-slate-600 sm:block">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                            @csrf
                            <button type="submit" class="rounded-lg px-3 py-2 text-sm font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">Keluar</button>
                        </form>
                        <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-[#E2E7FF] px-3 py-2 text-sm font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2 lg:hidden" data-mobile-menu-toggle aria-expanded="false" aria-controls="mobile-navigation">
                            <span>Menu</span>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" /></svg>
                        </button>
                    </div>
                @endauth
            </div>

            @auth
                <div id="mobile-navigation" class="hidden border-t border-[#EEF2FF] py-3 lg:hidden" data-mobile-menu>
                    <div class="grid gap-1 text-sm font-medium">
                        @if ($role === 'pengguna')
                            <a href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2.5 {{ request()->routeIs('dashboard') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Ringkasan</a>
                            <a href="{{ route('reservasi.index') }}" class="rounded-lg px-3 py-2.5 {{ request()->routeIs('reservasi.*') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Reservasi Saya</a>
                            <a href="{{ route('laporan.index') }}" class="rounded-lg px-3 py-2.5 {{ request()->routeIs('laporan.*') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Laporan Kerusakan</a>
                        @elseif ($role === 'petugas')
                            <a href="{{ route('petugas.dashboard') }}" class="rounded-lg px-3 py-2.5 {{ request()->routeIs('petugas.dashboard') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Operasional</a>
                            <a href="{{ route('petugas.reservasi.index') }}" class="rounded-lg px-3 py-2.5 {{ request()->routeIs('petugas.reservasi.*') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Antrian Reservasi</a>
                            <a href="{{ route('petugas.laporan.index') }}" class="rounded-lg px-3 py-2.5 {{ request()->routeIs('petugas.laporan.*') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Laporan</a>
                        @else
                            <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.dashboard') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Administrasi</a>
                            <a href="{{ route('admin.pengguna.verifikasi') }}" class="rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.pengguna.verifikasi') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Verifikasi Akun</a>
                            <a href="{{ route('admin.fasilitas.index') }}" class="rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.fasilitas.*') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Fasilitas</a>
                            <a href="{{ route('admin.rekap.occupancy') }}" class="rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.rekap.occupancy*') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Rekap Okupansi</a>
                            <a href="{{ route('admin.rekap.damage') }}" class="rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.rekap.damage*') ? 'bg-[#F2F3FF] text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC] hover:text-[#00236f]' }}">Rekap Kerusakan</a>
                        @endif
                    </div>
                    <div class="mt-3 flex items-center justify-between border-t border-[#EEF2FF] px-3 pt-3 text-sm">
                        <span class="max-w-52 truncate text-slate-600">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="font-semibold text-[#00236f] hover:text-[#0051d5] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">Keluar</button>
                        </form>
                    </div>
                </div>
            @endauth
        </div>
    </nav>
    <main id="content" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
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
</body>

</html>
