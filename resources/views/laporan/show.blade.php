@extends('layouts.app')

@section('title', 'Detail Laporan #' . $report->id . ' | REKSA')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="auth-clay-card rounded-[2rem] p-5 sm:p-7 lg:p-9 space-y-7">
            {{-- Header Section Inside Card --}}
            <div class="flex flex-col gap-4 border-b border-blue-100/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <a href="{{ route('laporan.index') }}"
                        class="clay-button-white mb-3 inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold text-blue-700">
                        &larr; Kembali ke daftar laporan
                    </a>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Laporan Kerusakan · REKSA</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">
                        Detail Laporan Kerusakan #{{ $report->id }}
                    </h1>
                    <p class="mt-1 text-xs text-slate-500">
                        Dilaporkan pada {{ $report->created_at->locale('id')->translatedFormat('l, d F Y, H:i') }} WIB
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <x-ui.badge :status="$report->status" />
                    <a href="{{ route('laporan.create') }}"
                        class="landing-button inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-4 py-2 text-xs font-bold text-white shadow-sm">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Buat Laporan Baru
                    </a>
                </div>
            </div>

            {{-- Detail Info Laporan --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div
                    class="flex items-center gap-3.5 rounded-2xl border border-white/90 bg-gradient-to-br from-blue-50/70 to-white/90 p-4 shadow-[inset_2px_2px_6px_rgba(99,137,193,0.08)]">
                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white bg-white text-blue-600 shadow-[0_4px_10px_rgba(59,130,246,0.15)]">
                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-medium text-slate-500">Fasilitas Kampus</p>
                        <p class="mt-0.5 text-sm font-extrabold text-[#10264a]">{{ $report->facility->name }}</p>
                        <p class="text-xs text-slate-500">{{ $report->facility->location }}</p>
                    </div>
                </div>

                <div
                    class="flex items-center gap-3.5 rounded-2xl border border-white/90 bg-gradient-to-br from-blue-50/70 to-white/90 p-4 shadow-[inset_2px_2px_6px_rgba(99,137,193,0.08)]">
                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white bg-white text-blue-600 shadow-[0_4px_10px_rgba(59,130,246,0.15)]">
                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h10M7 11h10M7 15h10M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-medium text-slate-500">Kategori Kerusakan</p>
                        <p class="mt-0.5 text-sm font-extrabold text-[#10264a]">{{ $report->categoryLabel() }}</p>
                    </div>
                </div>
            </div>

            {{-- Deskripsi Kerusakan --}}
            <section class="border-t border-blue-100/80 pt-6">
                <h2 class="mb-3 text-xs font-extrabold uppercase tracking-[0.14em] text-blue-700">
                    Deskripsi Kerusakan
                </h2>
                <div
                    class="rounded-2xl border border-white/90 bg-gradient-to-br from-blue-50/70 to-white/90 p-4 sm:p-5 shadow-[inset_2px_2px_6px_rgba(99,137,193,0.08)]">
                    <p class="text-sm leading-relaxed text-slate-700 whitespace-pre-line">{{ $report->description }}</p>
                </div>
            </section>

            {{-- Foto Bukti Kerusakan --}}
            @if ($report->photo)
                <section class="border-t border-blue-100/80 pt-6">
                    <h2 class="mb-3 text-xs font-extrabold uppercase tracking-[0.14em] text-blue-700">
                        Foto Bukti Kerusakan
                    </h2>
                    <div class="max-w-md overflow-hidden rounded-2xl border border-white/90 bg-white p-2 shadow-md">
                        <img src="{{ route('laporan.photo', $report) }}" alt="Foto laporan" loading="lazy"
                            class="img-fade h-auto max-h-80 w-full rounded-xl object-cover">
                    </div>
                </section>
            @endif

            {{-- Catatan Penanganan / Resolusi --}}
            @if ($report->resolution_note)
                <div class="rounded-2xl border border-blue-200 bg-blue-50/70 p-4.5 shadow-2xs">
                    <h3 class="text-xs font-extrabold uppercase tracking-[0.14em] text-blue-800">
                        Catatan Penanganan / Resolusi Petugas
                    </h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-800">{{ $report->resolution_note }}</p>
                    @if ($report->handledBy)
                        <p class="mt-2 text-xs font-medium text-slate-500">
                            Ditangani oleh: <span class="font-bold text-slate-700">{{ $report->handledBy->name }}</span>
                            @if ($report->handled_at)
                                ({{ $report->handled_at->locale('id')->translatedFormat('d M Y, H:i') }} WIB)
                            @endif
                        </p>
                    @endif
                </div>
            @endif

            {{-- Riwayat Perubahan Status --}}
            <section class="border-t border-blue-100/80 pt-6">
                <h2 class="mb-4 text-base font-extrabold text-[#10264a]">
                    Riwayat Status Laporan
                </h2>

                @if ($report->updates->isEmpty())
                    <p class="rounded-2xl border border-blue-100/80 bg-blue-50/50 p-4 text-xs italic text-slate-500">
                        Belum ada riwayat perubahan status. Laporan ini baru dibuat dan menunggu penanganan petugas.
                    </p>
                @else
                    <ol class="relative ml-3 space-y-4 border-l-2 border-blue-200">
                        @foreach ($report->updates as $update)
                            <li class="ml-6">
                                <span
                                    class="absolute -left-3.5 flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white shadow-sm ring-4 ring-white">
                                    {{ $loop->iteration }}
                                </span>
                                <div
                                    class="rounded-2xl border border-white/90 bg-gradient-to-br from-blue-50/60 to-white/90 p-4 shadow-2xs">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="text-xs font-semibold text-slate-800">
                                            Status diubah dari
                                            <span
                                                class="font-extrabold text-slate-600">{{ $update->old_status ?: 'Awal' }}</span>
                                            menjadi
                                            <span
                                                class="font-extrabold capitalize text-blue-600">{{ $update->new_status }}</span>
                                        </span>
                                        <time class="text-xs font-medium text-slate-500">
                                            {{ $update->created_at->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                                        </time>
                                    </div>

                                    @if ($update->note)
                                        <p
                                            class="mt-2 rounded-xl border border-blue-100/80 bg-white/90 p-2.5 text-xs text-slate-600 shadow-2xs">
                                            {{ $update->note }}
                                        </p>
                                    @endif

                                    <p class="mt-1.5 text-[11px] font-medium text-slate-400">
                                        Oleh: {{ $update->user->name ?? 'Petugas' }}
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>

            {{-- Footer Button --}}
            <div class="border-t border-blue-100/80 pt-5">
                <a href="{{ route('laporan.index') }}"
                    class="clay-button-white inline-flex justify-center rounded-full px-6 py-3 text-sm font-semibold text-blue-700">
                    &larr; Kembali ke daftar laporan
                </a>
            </div>
        </div>
    </div>
@endsection
