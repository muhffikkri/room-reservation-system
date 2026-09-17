@extends('layouts.app')

@section('title', 'Detail Laporan #' . $report->id)

@section('content')
<div class="mx-auto max-w-3xl space-y-8">
    <div>
        <a href="{{ route('laporan.index') }}" class="inline-flex items-center text-sm font-medium text-slate-500 transition hover:text-[#00236f]">
            &larr; Kembali ke daftar laporan
        </a>
        <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-semibold tracking-tight text-[#00236f]">Detail Laporan #{{ $report->id }}</h1>
            <div class="flex flex-wrap items-center gap-2">
                @php
                    $badgeClasses = match($report->status) {
                        'baru' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
                        'diproses' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                        'selesai' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                        'ditolak' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                        default => 'bg-slate-50 text-slate-700 ring-slate-600/20',
                    };
                @endphp
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold capitalize ring-1 ring-inset {{ $badgeClasses }}">
                    Status: {{ $report->status }}
                </span>
                <a href="{{ route('laporan.create') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-[#0051d5] px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#00236f] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Laporkan Kerusakan Lainnya
                </a>
            </div>
        </div>
        <p class="mt-1 text-xs text-slate-500">Dilaporkan pada {{ $report->created_at->format('d M Y, H:i') }} WIB</p>
    </div>

    {{-- Detail Informasi Laporan --}}
    <div class="space-y-6 rounded-2xl border border-[#E2E7FF] bg-white p-6 shadow-[0_14px_40px_rgba(15,23,42,0.06)] sm:p-8">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-[0.16em] text-[#0051d5]">Fasilitas</h3>
                <p class="mt-1 text-base font-semibold text-[#00236f]">{{ $report->facility->name }}</p>
                <p class="text-xs text-slate-500">{{ $report->facility->location }}</p>
            </div>
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-[0.16em] text-[#0051d5]">Kategori Kerusakan</h3>
                <p class="mt-1 text-base font-medium capitalize text-[#00236f]">{{ str_replace('_', ' ', $report->category) }}</p>
            </div>
        </div>

        <div class="border-t border-[#EEF2FF] pt-4">
            <h3 class="text-xs font-semibold uppercase tracking-[0.16em] text-[#0051d5]">Deskripsi Kerusakan</h3>
            <p class="mt-2 text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $report->description }}</p>
        </div>

        @if($report->photo)
            <div class="border-t border-[#EEF2FF] pt-4">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-[#0051d5]">Foto Bukti Kerusakan</h3>
                <div class="max-w-md overflow-hidden rounded-xl border border-[#E2E7FF] bg-[#F8FAFC]">
                    <img src="{{ route('laporan.photo', $report) }}" alt="Foto laporan" class="w-full object-cover max-h-80">
                </div>
            </div>
        @endif

        @if($report->resolution_note)
            <div class="rounded-xl border border-[#D6DDF8] border-l-4 border-l-[#0051d5] bg-[#F8FAFC] p-4">
                <h3 class="text-xs font-semibold uppercase tracking-[0.16em] text-[#00236f]">Catatan Penanganan / Resolusi</h3>
                <p class="mt-1 text-sm text-slate-800 leading-relaxed">{{ $report->resolution_note }}</p>
                @if($report->handledBy)
                    <p class="mt-2 text-xs text-slate-500">Ditangani oleh: <span class="font-medium text-slate-700">{{ $report->handledBy->name }}</span> ({{ $report->handled_at?->format('d M Y, H:i') }} WIB)</p>
                @endif
            </div>
        @endif
    </div>

    {{-- Riwayat Perubahan Status (Report Updates Audit Trail) --}}
    <div class="space-y-4 rounded-2xl border border-[#E2E7FF] bg-white p-6 shadow-sm sm:p-8">
        <h2 class="text-lg font-semibold tracking-tight text-[#00236f]">Riwayat Status Laporan</h2>

        @if($report->updates->isEmpty())
            <p class="text-sm text-slate-500 italic">Belum ada riwayat perubahan status. Laporan ini baru dibuat dan menunggu penanganan petugas.</p>
        @else
            <ol class="relative ml-3 space-y-6 border-l border-[#D6DDF8]">
                @foreach($report->updates as $update)
                    <li class="ml-6">
                        <span class="absolute -left-3 flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 ring-4 ring-white text-slate-500 text-xs font-bold">
                            {{ $loop->iteration }}
                        </span>
                        <div class="rounded-xl border border-[#EEF2FF] bg-[#F8FAFC] p-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="text-xs font-semibold text-slate-800">
                                    Status diubah dari
                                    <span class="font-bold text-slate-600">{{ $update->old_status ?: 'Awal' }}</span>
                                    menjadi
                                    <span class="font-bold capitalize text-[#0051d5]">{{ $update->new_status }}</span>
                                </span>
                                <time class="text-xs text-slate-400">{{ $update->created_at->format('d M Y, H:i') }} WIB</time>
                            </div>

                            @if($update->note)
                                <p class="mt-2 rounded border border-[#E2E7FF] bg-white p-2 text-xs text-slate-600">{{ $update->note }}</p>
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
