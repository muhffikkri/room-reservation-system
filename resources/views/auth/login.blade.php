@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="mb-4 text-lg font-semibold">Login</h1>

            <form method="POST" action="{{ route('login.store') }}" data-validate>
                @csrf
                <div class="mb-4">
                    <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                    <input id="email" name="email" type="email" required autofocus
                           value="{{ old('email') }}"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="mb-1 block text-sm font-medium">Password</label>
                    <input id="password" name="password" type="password" required
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <label class="mb-4 flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remember" class="rounded border-slate-300">
                    Ingat saya
                </label>

                <button type="submit"
                        class="w-full rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    Login
                </button>
            </form>

            <p class="mt-4 text-center text-sm text-slate-600">
                Belum punya akun?
                <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Registrasi</a>
            </p>
        </div>
    </div>
@endsection
