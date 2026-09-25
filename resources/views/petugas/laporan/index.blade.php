@extends('layouts.app')

@section('title', 'Antrian Laporan Kerusakan - Petugas')

@section('content')
<div class="space-y-8">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Pusat operasional</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#00236f]">Antrian Laporan Kerusakan</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Kelola dan proses laporan kerusakan fasilitas kampus dari pengguna.</p>
    </div>

    {{-- Tab Rekapitulasi --}}
    <div class="flex flex-wrap gap-2" role="tablist" aria-label="Tab rekapitulasi laporan">
        <a href="{{ route('petugas.laporan.index', ['tab' => 'menunggu']) }}"
           role="tab" aria-selected="{{ $tab === 'menunggu' ? 'true' : 'false' }}"
           class="rounded-lg px-4 py-2 text-sm font-semibold transition {{ $tab === 'menunggu' ? 'bg-amber-500 text-white' : 'bg-[#F2F3FF] text-[#00236f] hover:bg-[#E2E7FF]' }}">
            Menunggu Approval
        </a>
        <a href="{{ route('petugas.laporan.index', ['tab' => 'selesai']) }}"
           role="tab" aria-selected="{{ $tab === 'selesai' ? 'true' : 'false' }}"
           class="rounded-lg px-4 py-2 text-sm font-semibold transition {{ $tab === 'selesai' ? 'bg-emerald-600 text-white' : 'bg-[#F2F3FF] text-[#00236f] hover:bg-[#E2E7FF]' }}">
            Selesai
        </a>
    </div>

    {{-- Ringkasan Jumlah Status --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
        <a href="{{ route('petugas.laporan.index', ['tab' => $tab]) }}" class="rounded-xl border p-4 shadow-sm transition {{ empty($status) ? 'border-[#0051d5] bg-[#F2F3FF] ring-2 ring-[#E2E7FF]' : 'border-[#E2E7FF] bg-white hover:border-[#D6DDF8]' }}">
            <div class="text-xs font-semibold text-slate-500">Semua Laporan</div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ $counts['total'] }}</div>
        </a>
        <a href="{{ route('petugas.laporan.index', ['tab' => $tab, 'status' => 'baru']) }}" class="rounded-xl border p-4 shadow-sm transition {{ $status === 'baru' ? 'border-sky-600 bg-sky-50/50 ring-2 ring-sky-200' : 'border-[#E2E7FF] bg-white hover:border-[#D6DDF8]' }}">
            <div class="text-xs font-semibold text-sky-600">Baru</div>
            <div class="mt-2 text-2xl font-semibold text-sky-700">{{ $counts['baru'] }}</div>
        </a>
        <a href="{{ route('petugas.laporan.index', ['tab' => $tab, 'status' => 'diproses']) }}" class="rounded-xl border p-4 shadow-sm transition {{ $status === 'diproses' ? 'border-amber-600 bg-amber-50/50 ring-2 ring-amber-200' : 'border-[#E2E7FF] bg-white hover:border-[#D6DDF8]' }}">
            <div class="text-xs font-semibold text-amber-600">Diproses</div>
            <div class="mt-2 text-2xl font-bold text-amber-700">{{ $counts['diproses'] }}</div>
        </a>
        <a href="{{ route('petugas.laporan.index', ['tab' => $tab, 'status' => 'selesai']) }}" class="rounded-xl border p-4 shadow-sm transition {{ $status === 'selesai' ? 'border-emerald-600 bg-emerald-50/50 ring-2 ring-emerald-200' : 'border-[#E2E7FF] bg-white hover:border-[#D6DDF8]' }}">
            <div class="text-xs font-semibold text-emerald-600">Selesai</div>
            <div class="mt-2 text-2xl font-bold text-emerald-700">{{ $counts['selesai'] }}</div>
        </a>
        <a href="{{ route('petugas.laporan.index', ['tab' => $tab, 'status' => 'ditolak']) }}" class="rounded-xl border p-4 shadow-sm transition {{ $status === 'ditolak' ? 'border-rose-600 bg-rose-50/50 ring-2 ring-rose-200' : 'border-[#E2E7FF] bg-white hover:border-[#D6DDF8]' }}">
            <div class="text-xs font-semibold text-rose-600">Ditolak</div>
            <div class="mt-2 text-2xl font-bold text-rose-700">{{ $counts['ditolak'] }}</div>
        </a>
    </div>

    {{-- Tabel Antrian Laporan --}}
    <div class="overflow-hidden rounded-2xl border border-[#E2E7FF] bg-white shadow-sm">
        @if($reports->isEmpty())
            <div class="p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-[#0051d5]/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-[#00236f]">Tidak ada laporan</h3>
                <p class="mt-1 text-sm text-slate-500">Tidak ditemukan laporan kerusakan dengan filter status ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="border-b border-[#E2E7FF] bg-[#F8FAFC] text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 font-semibold">Pelapor</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold">Fasilitas</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold">Kategori</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold">Status Laporan</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold">Waktu Diajukan</th>
                            <th scope="col" class="px-6 py-3.5 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EEF2FF]">
                        @foreach($reports as $report)
                            <tr class="transition-colors hover:bg-[#F8FAFC]">
                                <td class="px-6 py-4 font-semibold text-[#00236f]">
                                    <div>{{ $report->user->name }}</div>
                                    <div class="text-xs text-slate-500 font-normal">{{ $report->user->email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900">{{ $report->facility->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $report->facility->location }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    {{ $report->categoryLabel() }}
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $badgeClasses = match($report->status) {
                                            'baru' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
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
                                    <a href="{{ route('petugas.laporan.show', $report) }}" class="inline-flex items-center rounded-lg bg-[#F2F3FF] px-2.5 py-1.5 text-xs font-semibold text-[#0051d5] transition hover:bg-[#E2E7FF] hover:text-[#00236f]">
                                        Proses / Detail &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($reports->hasPages())
                <div class="border-t border-[#EEF2FF] px-6 py-4">
                    {{ $reports->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
