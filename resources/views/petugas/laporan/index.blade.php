@extends('layouts.app')

@section('title', 'Antrian Laporan Kerusakan - Petugas')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Antrian Laporan Kerusakan</h1>
        <p class="text-sm text-slate-500">Kelola dan proses laporan kerusakan fasilitas kampus dari pengguna.</p>
    </div>

    {{-- Ringkasan Jumlah Status --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
        <a href="{{ route('petugas.laporan.index') }}" class="rounded-xl border p-4 shadow-sm transition {{ empty($status) ? 'border-indigo-600 bg-indigo-50/50 ring-2 ring-indigo-500' : 'border-slate-200 bg-white hover:border-slate-300' }}">
            <div class="text-xs font-semibold text-slate-500">Semua Laporan</div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ $counts['total'] }}</div>
        </a>
        <a href="{{ route('petugas.laporan.index', ['status' => 'baru']) }}" class="rounded-xl border p-4 shadow-sm transition {{ $status === 'baru' ? 'border-blue-600 bg-blue-50/50 ring-2 ring-blue-500' : 'border-slate-200 bg-white hover:border-slate-300' }}">
            <div class="text-xs font-semibold text-blue-600">Baru</div>
            <div class="mt-2 text-2xl font-bold text-blue-700">{{ $counts['baru'] }}</div>
        </a>
        <a href="{{ route('petugas.laporan.index', ['status' => 'diproses']) }}" class="rounded-xl border p-4 shadow-sm transition {{ $status === 'diproses' ? 'border-amber-600 bg-amber-50/50 ring-2 ring-amber-500' : 'border-slate-200 bg-white hover:border-slate-300' }}">
            <div class="text-xs font-semibold text-amber-600">Diproses</div>
            <div class="mt-2 text-2xl font-bold text-amber-700">{{ $counts['diproses'] }}</div>
        </a>
        <a href="{{ route('petugas.laporan.index', ['status' => 'selesai']) }}" class="rounded-xl border p-4 shadow-sm transition {{ $status === 'selesai' ? 'border-emerald-600 bg-emerald-50/50 ring-2 ring-emerald-500' : 'border-slate-200 bg-white hover:border-slate-300' }}">
            <div class="text-xs font-semibold text-emerald-600">Selesai</div>
            <div class="mt-2 text-2xl font-bold text-emerald-700">{{ $counts['selesai'] }}</div>
        </a>
        <a href="{{ route('petugas.laporan.index', ['status' => 'ditolak']) }}" class="rounded-xl border p-4 shadow-sm transition {{ $status === 'ditolak' ? 'border-rose-600 bg-rose-50/50 ring-2 ring-rose-500' : 'border-slate-200 bg-white hover:border-slate-300' }}">
            <div class="text-xs font-semibold text-rose-600">Ditolak</div>
            <div class="mt-2 text-2xl font-bold text-rose-700">{{ $counts['ditolak'] }}</div>
        </a>
    </div>

    {{-- Tabel Antrian Laporan --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        @if($reports->isEmpty())
            <div class="p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-slate-900">Tidak ada laporan</h3>
                <p class="mt-1 text-sm text-slate-500">Tidak ditemukan laporan kerusakan dengan filter status ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-200">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 font-semibold">Pelapor</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold">Fasilitas</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold">Kategori</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold">Status Laporan</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold">Tanggal</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($reports as $report)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-medium text-slate-900">
                                    <div>{{ $report->user->name }}</div>
                                    <div class="text-xs text-slate-500 font-normal">{{ $report->user->email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900">{{ $report->facility->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $report->facility->location }}</div>
                                </td>
                                <td class="px-6 py-4 capitalize">
                                    {{ str_replace('_', ' ', $report->category) }}
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $badgeClasses = match($report->status) {
                                            'baru' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                            'diproses' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                            'selesai' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                            'ditolak' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                                            default => 'bg-slate-50 text-slate-700 ring-slate-600/20',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset capitalize {{ $badgeClasses }}">
                                        {{ $report->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500">
                                    {{ $report->created_at->format('d M Y, H:i') }} WIB
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('petugas.laporan.show', $report) }}" class="inline-flex items-center rounded-md bg-indigo-50 px-2.5 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                                        Proses / Detail &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($reports->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $reports->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
