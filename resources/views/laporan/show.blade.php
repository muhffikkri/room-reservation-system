@extends('layouts.app')

@section('title', 'Detail Laporan #' . $report->id)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <a href="{{ route('laporan.index') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-700">
            &larr; Kembali ke daftar laporan
        </a>
        <div class="mt-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h1 class="text-2xl font-bold text-slate-800">Detail Laporan #{{ $report->id }}</h1>
            <div>
                @php
                    $badgeClasses = match($report->status) {
                        'baru' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                        'diproses' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                        'selesai' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                        'ditolak' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                        default => 'bg-slate-50 text-slate-700 ring-slate-600/20',
                    };
                @endphp
                <span class="inline-flex items-center rounded-md px-3 py-1 text-xs font-semibold ring-1 ring-inset capitalize {{ $badgeClasses }}">
                    Status: {{ $report->status }}
                </span>
            </div>
        </div>
        <p class="text-xs text-slate-500 mt-1">Dilaporkan pada {{ $report->created_at->format('d M Y, H:i') }} WIB</p>
    </div>

    {{-- Detail Informasi Laporan --}}
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Fasilitas</h3>
                <p class="mt-1 text-base font-semibold text-slate-900">{{ $report->facility->name }}</p>
                <p class="text-xs text-slate-500">{{ $report->facility->location }}</p>
            </div>
            <div>
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kategori Kerusakan</h3>
                <p class="mt-1 text-base font-medium text-slate-900 capitalize">{{ str_replace('_', ' ', $report->category) }}</p>
            </div>
        </div>

        <div class="border-t border-slate-100 pt-4">
            <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Deskripsi Kerusakan</h3>
            <p class="mt-2 text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $report->description }}</p>
        </div>

        @if($report->photo)
            <div class="border-t border-slate-100 pt-4">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Foto Bukti Kerusakan</h3>
                <div class="overflow-hidden rounded-lg border border-slate-200 max-w-md bg-slate-50">
                    <img src="{{ asset('storage/' . $report->photo) }}" alt="Foto laporan" class="w-full object-cover max-h-80">
                </div>
            </div>
        @endif

        @if($report->resolution_note)
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 border-l-4 border-l-indigo-500">
                <h3 class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Catatan Penanganan / Resolusi</h3>
                <p class="mt-1 text-sm text-slate-800 leading-relaxed">{{ $report->resolution_note }}</p>
                @if($report->handledBy)
                    <p class="mt-2 text-xs text-slate-500">Ditangani oleh: <span class="font-medium text-slate-700">{{ $report->handledBy->name }}</span> ({{ $report->handled_at?->format('d M Y, H:i') }} WIB)</p>
                @endif
            </div>
        @endif
    </div>

    {{-- Riwayat Perubahan Status (Report Updates Audit Trail) --}}
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <h2 class="text-lg font-bold text-slate-800">Riwayat Status Laporan</h2>

        @if($report->updates->isEmpty())
            <p class="text-sm text-slate-500 italic">Belum ada riwayat perubahan status. Laporan ini baru dibuat dan menunggu penanganan petugas.</p>
        @else
            <ol class="relative border-l border-slate-200 ml-3 space-y-6">
                @foreach($report->updates as $update)
                    <li class="ml-6">
                        <span class="absolute -left-3 flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 ring-4 ring-white text-slate-500 text-xs font-bold">
                            {{ $loop->iteration }}
                        </span>
                        <div class="rounded-lg border border-slate-100 bg-slate-50/50 p-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="text-xs font-semibold text-slate-800">
                                    Status diubah dari
                                    <span class="font-bold text-slate-600">{{ $update->old_status ?: 'Awal' }}</span>
                                    menjadi
                                    <span class="font-bold text-indigo-600 capitalize">{{ $update->new_status }}</span>
                                </span>
                                <time class="text-xs text-slate-400">{{ $update->created_at->format('d M Y, H:i') }} WIB</time>
                            </div>

                            @if($update->note)
                                <p class="mt-2 text-xs text-slate-600 bg-white p-2 rounded border border-slate-200">{{ $update->note }}</p>
                            @endif

                            <p class="mt-1 text-[11px] text-slate-400">Oleh: {{ $update->user->name ?? 'Petugas' }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
</div>
@endsection
