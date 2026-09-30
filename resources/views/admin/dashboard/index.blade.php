@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="dashboard-page space-y-7">
        <section
            class="dashboard-clay-card landing-panel relative grid min-h-64 overflow-hidden rounded-[2rem] px-6 py-7 sm:px-9 lg:grid-cols-[1fr_18rem] lg:items-center lg:px-11 lg:py-9"
            aria-labelledby="admin-welcome-heading">
            <div class="relative z-10 max-w-3xl pr-32 sm:pr-52 lg:pr-0">
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Administrasi kampus</p>
                <h1 id="admin-welcome-heading"
                    class="mt-1 text-2xl font-extrabold leading-tight tracking-tight text-[#10264a] sm:text-3xl xl:text-[2.5rem]">
                    Dashboard Admin
                </h1>
                <p class="mt-2 text-sm leading-relaxed text-slate-600 sm:text-base">
                    Halo, {{ auth()->user()->name }}. Pantau akun, fasilitas, dan rekap operasional kampus dari satu
                    halaman.
                </p>
                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <a href="{{ route('admin.pengguna.verifikasi') }}"
                        class="landing-button inline-flex min-h-11 items-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-3 text-sm font-semibold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)] transition hover:from-blue-700 hover:to-blue-600 sm:px-6">
                        Verifikasi Akun <span aria-hidden="true">&rarr;</span>
                    </a>
                    <a href="{{ route('admin.fasilitas.index') }}"
                        class="clay-button-white inline-flex min-h-11 items-center gap-2 rounded-full px-5 py-3 text-sm font-semibold text-blue-700 sm:px-6">
                        Kelola Fasilitas
                    </a>
                </div>
            </div>
            <div
                class="dashboard-mascot pointer-events-none absolute inset-y-0 right-0 z-0 flex w-32 items-end justify-center sm:w-52 lg:w-72">
                <img src="{{ asset('images/maskot-greetings.webp') }}" alt="Maskot REKSA menyapa admin"
                    class="relative z-10 h-full w-full object-contain object-bottom drop-shadow-[0_14px_18px_rgba(37,99,235,0.2)]">
            </div>
        </section>

        <section class="dashboard-clay-card landing-panel rounded-[2rem] p-5 sm:p-6" aria-labelledby="admin-summary-heading">
            <div class="mb-5">
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Ringkasan sistem</p>
                <h2 id="admin-summary-heading" class="mt-1 text-xl font-extrabold tracking-tight text-[#10264a]">Kondisi
                    sistem hari ini</h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <a href="{{ route('admin.pengguna.verifikasi') }}"
                    class="dashboard-clay-stat clay-inset flex items-center gap-4 rounded-2xl p-4 transition hover:-translate-y-0.5 sm:p-5">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-[0_6px_12px_rgba(245,158,11,0.25),inset_0_1px_0_rgba(255,255,255,0.45)]">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-600">Akun Perlu Verifikasi</p>
                        <p class="mt-1 text-3xl font-extrabold leading-none tracking-tight text-slate-900">
                            {{ $pendingAccountCount }}</p>
                        <p class="mt-2 text-[11px] text-slate-500">registrasi mandiri menunggu admin</p>
                    </div>
                </a>
                <article class="dashboard-clay-stat clay-inset flex items-center gap-4 rounded-2xl p-4 sm:p-5">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-500 text-white shadow-[0_6px_12px_rgba(37,99,235,0.25),inset_0_1px_0_rgba(255,255,255,0.45)]">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" d="M12 7v5l3 2" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-600">Reservasi Menunggu</p>
                        <p class="mt-1 text-3xl font-extrabold leading-none tracking-tight text-slate-900">
                            {{ $pendingReservationCount }}</p>
                        <p class="mt-2 text-[11px] text-slate-500">ditangani petugas operasional</p>
                    </div>
                </article>
                <article class="dashboard-clay-stat clay-inset flex items-center gap-4 rounded-2xl p-4 sm:p-5">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-cyan-500 text-white shadow-[0_6px_12px_rgba(6,182,212,0.24),inset_0_1px_0_rgba(255,255,255,0.45)]">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7 3h7l5 5v13H5V3h2Zm6 1v5h5M8 13h8M8 17h8" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-600">Laporan Baru</p>
                        <p class="mt-1 text-3xl font-extrabold leading-none tracking-tight text-slate-900">
                            {{ $newReportCount }}</p>
                        <p class="mt-2 text-[11px] text-slate-500">belum diambil petugas</p>
                    </div>
                </article>
                <article class="dashboard-clay-stat clay-inset flex items-center gap-4 rounded-2xl p-4 sm:p-5">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-[0_6px_12px_rgba(245,158,11,0.25),inset_0_1px_0_rgba(255,255,255,0.45)]">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.7 6.3a5 5 0 0 0-6.4 6.4L3 18l3 3 5.3-5.3a5 5 0 0 0 6.4-6.4L14 13l-3-3 3.7-3.7Z" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-600">Laporan Diproses</p>
                        <p class="mt-1 text-3xl font-extrabold leading-none tracking-tight text-slate-900">
                            {{ $processedReportCount }}</p>
                        <p class="mt-2 text-[11px] text-slate-500">sedang ditangani</p>
                    </div>
                </article>
                <article class="dashboard-clay-stat clay-inset flex items-center gap-4 rounded-2xl p-4 sm:p-5">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-500 text-white shadow-[0_6px_12px_rgba(244,63,94,0.24),inset_0_1px_0_rgba(255,255,255,0.45)]">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 4a6 6 0 0 0-5.2 8.9L3 15.7V20h4.3l2.8-2.8A6 6 0 0 0 19 9.7M15 5l4 4m-1.5-6.5 4 4" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-600">Fasilitas Dalam Perbaikan</p>
                        <p class="mt-1 text-3xl font-extrabold leading-none tracking-tight text-slate-900">
                            {{ $repairFacilityCount }}</p>
                        <p class="mt-2 text-[11px] text-slate-500">menunggu selesai laporan</p>
                    </div>
                </article>
            </div>
        </section>
    </div>
@endsection
