@extends('layouts.auth')

@section('title', 'Daftar | Sistem Reservasi Fasilitas Kampus')

@section('content')
    <div>
        <div class="mb-7">
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.16em] text-[#0051d5]">Akun pengguna</p>
            <h1 class="text-3xl font-bold tracking-tight text-[#0F172A]">Buat akun baru</h1>
            <p class="mt-3 text-sm leading-relaxed text-[#475569]">
                Isi data Anda untuk mengakses reservasi dan pelaporan fasilitas kampus.
            </p>
        </div>

        <div class="rounded-xl border border-[#E2E7FF] bg-white p-6 shadow-[0_12px_30px_rgba(0,35,111,0.06)] sm:p-8">
            <div class="mb-6 rounded-lg bg-[#F2F3FF] px-4 py-3 text-xs leading-relaxed text-[#475569]">
                Akun baru berstatus menunggu verifikasi admin sebelum dapat digunakan.
            </div>

            <form method="POST" action="{{ route('register.store') }}" data-validate class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="mb-1.5 block text-sm font-semibold text-[#334155]">Nama lengkap</label>
                    <input id="name" name="name" type="text" required maxlength="100" autocomplete="name" value="{{ old('name') }}"
                        class="h-11 w-full rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 text-sm text-[#0F172A] outline-none transition focus:border-[#0051d5] focus:bg-white focus:ring-4 focus:ring-[#0051d5]/10">
                    @error('name')
                        <p class="mt-1.5 text-xs leading-relaxed text-[#B42318]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-semibold text-[#334155]">Email</label>
                    <input id="email" name="email" type="email" required autocomplete="email" value="{{ old('email') }}"
                        class="h-11 w-full rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 text-sm text-[#0F172A] outline-none transition focus:border-[#0051d5] focus:bg-white focus:ring-4 focus:ring-[#0051d5]/10">
                    @error('email')
                        <p class="mt-1.5 text-xs leading-relaxed text-[#B42318]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="identity" class="mb-1.5 block text-sm font-semibold text-[#334155]">NIM/NIP</label>
                        <input id="identity" name="identity" type="text" maxlength="30" required autocomplete="off" value="{{ old('identity') }}"
                            class="h-11 w-full rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 text-sm text-[#0F172A] outline-none transition focus:border-[#0051d5] focus:bg-white focus:ring-4 focus:ring-[#0051d5]/10">
                        @error('identity')
                            <p class="mt-1.5 text-xs leading-relaxed text-[#B42318]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="mb-1.5 block text-sm font-semibold text-[#334155]">No. HP</label>
                        <input id="phone" name="phone" type="tel" maxlength="20" required autocomplete="tel" value="{{ old('phone') }}"
                            class="h-11 w-full rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 text-sm text-[#0F172A] outline-none transition focus:border-[#0051d5] focus:bg-white focus:ring-4 focus:ring-[#0051d5]/10">
                        @error('phone')
                            <p class="mt-1.5 text-xs leading-relaxed text-[#B42318]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-semibold text-[#334155]">Password</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"
                            class="h-11 w-full rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 pr-11 text-sm text-[#0F172A] outline-none transition focus:border-[#0051d5] focus:bg-white focus:ring-4 focus:ring-[#0051d5]/10">
                        <button type="button" data-password-toggle="password" aria-label="Tampilkan password" aria-pressed="false"
                            class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-lg text-[#64748B] transition hover:text-[#00236f] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#0051d5]/40">
                            <svg class="h-5 w-5" data-icon-show fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg class="hidden h-5 w-5" data-icon-hide fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    <p class="mt-1.5 text-xs text-[#64748B]">Minimal 8 karakter.</p>
                    @error('password')
                        <p class="mt-1.5 text-xs leading-relaxed text-[#B42318]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-[#334155]">Konfirmasi password</label>
                    <div class="relative">
                        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password"
                            class="h-11 w-full rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 pr-11 text-sm text-[#0F172A] outline-none transition focus:border-[#0051d5] focus:bg-white focus:ring-4 focus:ring-[#0051d5]/10">
                        <button type="button" data-password-toggle="password_confirmation" aria-label="Tampilkan password" aria-pressed="false"
                            class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-lg text-[#64748B] transition hover:text-[#00236f] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#0051d5]/40">
                            <svg class="h-5 w-5" data-icon-show fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg class="hidden h-5 w-5" data-icon-hide fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <p class="mt-1.5 text-xs leading-relaxed text-[#B42318]">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-[#00236f] px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-[#001a52] focus:outline-none focus:ring-4 focus:ring-[#0051d5]/20 active:translate-y-px">
                    Daftar akun
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-[#475569]">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-semibold text-[#0051d5] hover:text-[#00236f] hover:underline">Masuk di sini</a>
            </p>
        </div>
    </div>
@endsection
