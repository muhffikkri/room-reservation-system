@extends('layouts.app')

@section('title', 'Dashboard Petugas')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Dashboard Petugas</h1>
        <p class="text-sm text-slate-500">Selamat datang! Berikut adalah ringkasan antrian reservasi dan laporan kerusakan fasilitas.</p>
    </div>

    {{-- Kartu Ringkasan Metric --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Antrian Reservasi Pending --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Reservasi Pending</span>
                <span class="rounded-full bg-amber-50 p-2 text-amber-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ $pendingReservationsCount }}</div>
            <p class="mt-1 text-xs text-slate-500">Menunggu persetujuan petugas</p>
        </div>

        {{-- Laporan Kerusakan Baru --}}
        <a href="{{ route('petugas.laporan.index', ['status' => 'baru']) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-blue-500 transition block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Laporan Baru</span>
                <span class="rounded-full bg-blue-50 p-2 text-blue-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-3 text-3xl font-bold text-blue-700">{{ $newReportsCount }}</div>
            <p class="mt-1 text-xs text-blue-600 font-medium">Perlu segera ditangani &rarr;</p>
        </a>

        {{-- Laporan Sedang Diproses --}}
        <a href="{{ route('petugas.laporan.index', ['status' => 'diproses']) }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-amber-500 transition block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Laporan Diproses</span>
                <span class="rounded-full bg-amber-50 p-2 text-amber-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-3 text-3xl font-bold text-amber-700">{{ $processingReportsCount }}</div>
            <p class="mt-1 text-xs text-amber-600 font-medium">Dalam penanganan teknis &rarr;</p>
        </a>

        {{-- Fasilitas Perbaikan --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Fasilitas Perbaikan</span>
                <span class="rounded-full bg-rose-50 p-2 text-rose-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ $repairFacilitiesCount }}</div>
            <p class="mt-1 text-xs text-slate-500">Nonaktif sementara untuk publik</p>
        </div>
    </div>

    {{-- Laporan Terbaru Perlu Tindakan --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-lg font-bold text-slate-800">Laporan Masuk Terbaru</h2>
            <a href="{{ route('petugas.laporan.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-900">
                Lihat Semua Laporan &rarr;
            </a>
        </div>

        @if($recentReports->isEmpty())
            <p class="text-sm text-slate-500 italic text-center py-4">Tidak ada laporan baru atau yang sedang diproses saat ini.</p>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($recentReports as $report)
                    <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-slate-900 text-sm">{{ $report->facility->name }}</span>
                                <span class="text-xs text-slate-500">({{ $report->facility->location }})</span>
                            </div>
                            <p class="text-xs text-slate-600 mt-0.5">
                                Pelapor: <span class="font-medium text-slate-800">{{ $report->user->name }}</span> · Kategori: <span class="capitalize">{{ str_replace('_', ' ', $report->category) }}</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            @php
                                $badgeClasses = match($report->status) {
                                    'baru' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                    'diproses' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                    default => 'bg-slate-50 text-slate-700 ring-slate-600/20',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset capitalize {{ $badgeClasses }}">
                                {{ $report->status }}
                            </span>
                            <a href="{{ route('petugas.laporan.show', $report) }}" class="rounded-md bg-indigo-50 px-2.5 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 whitespace-nowrap">
                                Tangani &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
