@extends('layouts.app')

@section('title', 'Registrasi')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="mb-1 text-lg font-semibold">Registrasi</h1>
            <p class="mb-4 text-sm text-slate-600">
                Hanya untuk mahasiswa/dosen/staf. Akun dapat digunakan setelah diverifikasi admin.
            </p>

            <form method="POST" action="{{ route('register.store') }}" data-validate>
                @csrf
                <div class="mb-4">
                    <label for="name" class="mb-1 block text-sm font-medium">Nama lengkap</label>
                    <input id="name" name="name" type="text" required maxlength="100" value="{{ old('name') }}"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                    <input id="email" name="email" type="email" required value="{{ old('email') }}"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label for="identity" class="mb-1 block text-sm font-medium">NIM/NIP (opsional)</label>
                    <input id="identity" name="identity" type="text" maxlength="30" value="{{ old('identity') }}"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    @error('identity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label for="phone" class="mb-1 block text-sm font-medium">No. HP (opsional)</label>
                    <input id="phone" name="phone" type="text" maxlength="20" value="{{ old('phone') }}"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="mb-1 block text-sm font-medium">Password</label>
                    <input id="password" name="password" type="password" required minlength="8"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium">Konfirmasi password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    @error('password_confirmation')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                        class="w-full rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    Daftar
                </button>
            </form>

            <p class="mt-4 text-center text-sm text-slate-600">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Login</a>
            </p>
        </div>
    </div>
@endsection
