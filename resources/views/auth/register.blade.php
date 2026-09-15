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
                    <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"
                        class="h-11 w-full rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 text-sm text-[#0F172A] outline-none transition focus:border-[#0051d5] focus:bg-white focus:ring-4 focus:ring-[#0051d5]/10">
                    <p class="mt-1.5 text-xs text-[#64748B]">Minimal 8 karakter.</p>
                    @error('password')
                        <p class="mt-1.5 text-xs leading-relaxed text-[#B42318]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-[#334155]">Konfirmasi password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password"
                        class="h-11 w-full rounded-lg border border-[#CBD5E1] bg-[#F8FAFC] px-3 text-sm text-[#0F172A] outline-none transition focus:border-[#0051d5] focus:bg-white focus:ring-4 focus:ring-[#0051d5]/10">
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
