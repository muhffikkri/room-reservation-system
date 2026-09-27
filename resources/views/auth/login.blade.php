@extends('layouts.auth')

@section('title', 'Masuk | REKSA')

@section('content')
    <div class="landing-panel rounded-[2rem] p-5 shadow-[0_30px_60px_-15px_rgba(66,120,225,0.16),0_12px_32px_-4px_rgba(66,120,225,0.08),inset_0_1px_2px_rgba(255,255,255,0.95)] sm:p-7">
        <div class="mb-7">
            <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Akses akun</p>
            <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Masuk ke akun Anda</h2>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                Gunakan akun yang sudah aktif untuk mengelola reservasi dan laporan fasilitas.
            </p>
        </div>

        <div class="rounded-[1.6rem] border border-white/90 bg-white/65 p-4 shadow-[0_14px_28px_-6px_rgba(100,149,237,0.08),0_4px_10px_-2px_rgba(100,149,237,0.04),inset_0_1px_2px_rgba(255,255,255,0.9)] backdrop-blur-md sm:p-6">
            <form method="POST" action="{{ route('login.store') }}" data-validate class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label>
                    <input id="email" name="email" type="email" required autofocus autocomplete="email"
                        value="{{ old('email') }}"
                        placeholder="nama@email.com" class="landing-input h-11 w-full rounded-2xl px-4 text-sm text-slate-800 outline-none transition focus:ring-4 focus:ring-blue-500/15">
                    @error('email')
                        <p class="mt-1.5 text-xs leading-relaxed text-[#B42318]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">Password</label>
                    <div class="flex items-center gap-2">
                        <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Masukkan password"
                            class="landing-input h-11 min-w-0 flex-1 rounded-2xl px-4 text-sm text-slate-800 outline-none transition focus:ring-4 focus:ring-blue-500/15">
                        <button type="button" data-password-toggle="password" aria-label="Tampilkan password" aria-pressed="false"
                            class="landing-button flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-white bg-white/80 text-slate-500 transition hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500/40">
                            <svg class="h-5 w-5" data-icon-show fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg class="hidden h-5 w-5" data-icon-hide fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs leading-relaxed text-[#B42318]">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-blue-200 text-blue-600 focus:ring-blue-500">
                    Ingat saya
                </label>

                <button type="submit" data-submit-loading data-loading-label="Memproses..."
                    class="landing-button inline-flex h-12 w-full items-center justify-center gap-2 rounded-full bg-gradient-to-b from-blue-500 to-blue-700 px-4 text-sm font-bold text-white shadow-[0_10px_24px_-2px_rgba(37,99,235,0.42),inset_0_2px_4px_rgba(255,255,255,0.38),inset_0_-2px_4px_rgba(0,0,0,0.18)] focus:outline-none focus:ring-4 focus:ring-blue-500/20">
                    Masuk <span aria-hidden="true">→</span>
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-bold text-blue-700 transition hover:text-blue-900 hover:underline">Daftar sekarang</a>
            </p>
        </div>
    </div>
@endsection
