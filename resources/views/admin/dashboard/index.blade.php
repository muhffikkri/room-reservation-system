@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Pusat administrasi</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#00236f]">
                Dashboard Admin
            </h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Ringkasan operasional: antrean, fasilitas dalam perbaikan, dan akun yang menunggu verifikasi.
            </p>
        </div>
        <a href="{{ route('admin.pengguna.verifikasi') }}"
           class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-[#0051d5] px-4 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#00236f] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">
            Verifikasi Akun
        </a>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-[#E2E7FF] bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-600">Reservasi Menunggu</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[#00236f]">{{ $pendingReservationCount }}</p>
            <p class="mt-1 text-xs text-slate-500">ditangani petugas operasional</p>
        </div>
        <div class="rounded-xl border border-[#E2E7FF] bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-600">Laporan Baru</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-sky-700">{{ $newReportCount }}</p>
            <p class="mt-1 text-xs text-slate-500">belum diambil petugas</p>
        </div>
        <div class="rounded-xl border border-[#E2E7FF] bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-600">Fasilitas Dalam Perbaikan</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-rose-700">{{ $repairFacilityCount }}</p>
            <p class="mt-1 text-xs text-slate-500">menunggu selesai laporan</p>
        </div>
        <div class="rounded-xl border border-[#E2E7FF] bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-600">Akun Perlu Verifikasi</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-amber-700">{{ $pendingAccountCount }}</p>
            <p class="mt-1 text-xs text-slate-500">registrasi mandiri menunggu admin</p>
        </div>
    </div>
@endsection
