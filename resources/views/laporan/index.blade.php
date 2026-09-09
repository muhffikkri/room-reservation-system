@extends('layouts.app')

@section('title', 'Daftar Laporan Kerusakan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Laporan Kerusakan Saya</h1>
            <p class="text-sm text-slate-500">Kelola dan pantau status laporan kerusakan fasilitas kampus yang telah Anda kirimkan.</p>
        </div>
        <div>
            <a href="{{ route('laporan.create') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="mr-2 -ml-0.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Laporan Baru
            </a>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        @if($reports->isEmpty())
            <div class="p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-slate-900">Belum ada laporan</h3>
                <p class="mt-1 text-sm text-slate-500">Anda belum pernah membuat laporan kerusakan fasilitas.</p>
                <div class="mt-6">
                    <a href="{{ route('laporan.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        Buat Laporan Sekarang
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-200">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 font-semibold">Fasilitas</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold">Kategori</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold">Status</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold">Tanggal Dibuat</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($reports as $report)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-medium text-slate-900">
                                    <div>{{ $report->facility->name }}</div>
                                    <div class="text-xs text-slate-500 font-normal">{{ $report->facility->location }}</div>
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
                                    <a href="{{ route('laporan.show', $report) }}" class="font-medium text-indigo-600 hover:text-indigo-900 text-xs">
                                        Detail &rarr;
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
