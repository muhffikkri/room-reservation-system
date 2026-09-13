<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistem Reservasi Fasilitas Kampus')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <nav class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
            <div class="flex items-center gap-6">
                <a href="{{ url('/') }}" class="font-semibold text-slate-900">Sistem Reservasi Fasilitas</a>
                @auth
                    @if (auth()->user()->role === 'pengguna')
                        <div class="hidden sm:flex items-center gap-4 text-sm font-medium">
                            <a href="{{ route('reservasi.index') }}"
                                class="text-slate-600 hover:text-indigo-600 {{ request()->routeIs('reservasi.*') ? 'text-indigo-600 font-semibold' : '' }}">Reservasi
                                Saya</a>
                            <a href="{{ route('laporan.index') }}"
                                class="text-slate-600 hover:text-indigo-600 {{ request()->routeIs('laporan.*') ? 'text-indigo-600 font-semibold' : '' }}">Laporan
                                Kerusakan</a>
                        </div>
                    @endif
                @endauth
            </div>
            <div class="flex items-center gap-4 text-sm">
                @auth
                    <span class="text-slate-600">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-slate-600 hover:text-slate-900">Logout</button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>
    <main class="mx-auto max-w-6xl px-4 py-8">
        @if (session('success'))
            <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ session('error') }}
            </div>
        @endif
        @yield('content')
    </main>
</body>

</html>
