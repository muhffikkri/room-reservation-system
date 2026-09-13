@extends('layouts.auth')

@section('title', 'Masuk | Sistem Reservasi Fasilitas Kampus')

@section('content')
    <div>
        <div class="mb-7">
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.16em] text-[#0051d5]">Akses akun</p>
            <h1 class="text-3xl font-bold tracking-tight text-[#0F172A]">Masuk ke akun Anda</h1>
            <p class="mt-3 text-sm leading-relaxed text-[#475569]">
                Gunakan akun yang sudah aktif untuk mengelola reservasi dan laporan fasilitas.
            </p>
        </div>

        <div class="rounded-xl border border-[#E2E7FF] bg-white p-6 shadow-[0_12px_30px_rgba(0,35,111,0.06)] sm:p-8">
            <form method="POST" action="{{ route('login.store') }}" data-validate class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-semibold text-[#334155]">Email</label>
                    <input id="email" name="email" type="email" required autofocus autocomplete="email"
                        value="{{ old('email') }}"
                        class="h-11 w-full rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 text-sm text-[#0F172A] outline-none transition focus:border-[#0051d5] focus:bg-white focus:ring-4 focus:ring-[#0051d5]/10">
                    @error('email')
                        <p class="mt-1.5 text-xs leading-relaxed text-[#B42318]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-semibold text-[#334155]">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                        class="h-11 w-full rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 text-sm text-[#0F172A] outline-none transition focus:border-[#0051d5] focus:bg-white focus:ring-4 focus:ring-[#0051d5]/10">
                    @error('password')
                        <p class="mt-1.5 text-xs leading-relaxed text-[#B42318]">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-[#475569]">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-[#CBD5E1] text-[#00236f] focus:ring-[#0051d5]">
                    Ingat saya
                </label>

                <button type="submit"
                    class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-[#00236f] px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-[#001a52] focus:outline-none focus:ring-4 focus:ring-[#0051d5]/20 active:translate-y-px">
                    Masuk
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-[#475569]">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-[#0051d5] hover:text-[#00236f] hover:underline">Daftar sekarang</a>
            </p>
        </div>
    </div>
@endsection
