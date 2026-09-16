@extends('layouts.app')

@section('title', 'Tambah Akun Pengguna')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="rounded-2xl border border-[#E2E7FF] bg-white p-6 shadow-[0_14px_40px_rgba(15,23,42,0.06)] sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Manajemen akun</p>
            <h1 class="mt-2 text-xl font-semibold tracking-tight text-[#00236f]">Tambah Akun Pengguna</h1>
            <p class="mt-2 mb-5 text-sm leading-6 text-slate-600">
                Akun langsung aktif tanpa verifikasi karena admin yang membuatnya.
            </p>

            <form method="POST" action="{{ route('admin.pengguna.store') }}" data-validate>
                @csrf
                <div class="mb-4">
                    <label for="name" class="mb-1 block text-sm font-medium">Nama lengkap</label>
                    <input id="name" name="name" type="text" required maxlength="100" value="{{ old('name') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                    <input id="email" name="email" type="email" required value="{{ old('email') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label for="identity" class="mb-1 block text-sm font-medium">NIM/NIP</label>
                    <input id="identity" name="identity" type="text" required maxlength="30" value="{{ old('identity') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]">
                    @error('identity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label for="phone" class="mb-1 block text-sm font-medium">No. HP</label>
                    <input id="phone" name="phone" type="text" required maxlength="20" value="{{ old('phone') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]">
                    @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="mb-1 block text-sm font-medium">Password</label>
                    <input id="password" name="password" type="password" required minlength="8"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]">
                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium">Konfirmasi password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]">
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-[#0051d5] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#00236f] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">
                    Buat Akun Pengguna
                </button>
            </form>
        </div>
    </div>
@endsection
