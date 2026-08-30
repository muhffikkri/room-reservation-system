@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="mb-2 text-lg font-semibold">Selamat datang, {{ $user->name }}</h1>
        <p class="text-sm text-slate-600">
            Role: <span class="font-medium">{{ $user->role }}</span> ·
            Status akun: <span class="font-medium">{{ $user->account_status }}</span>
        </p>
        <p class="mt-4 text-sm text-slate-500">
            Fitur reservasi dan pelaporan akan tersedia di milestone berikutnya.
        </p>
    </div>
@endsection
