@extends('layouts.app')

@section('title', 'Rekap Frekuensi Kerusakan Fasilitas')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Laporan & Rekap</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#00236f]">
                Rekap Frekuensi Kerusakan Fasilitas
            </h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Ringkasan laporan kerusakan per fasilitas dan kategori kerusakan.
            </p>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="mt-6 rounded-2xl border border-[#E2E7FF] bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.rekap.damage') }}" class="flex flex-wrap items-end gap-3">
            <div class="min-w-48 flex-1">
                <label for="start_date" class="mb-1 block text-sm font-medium text-slate-700">Tanggal Mulai</label>
                <input id="start_date" name="start_date" type="date" value="{{ $startDate }}"
                       class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]">
            </div>
            <div class="min-w-48 flex-1">
                <label for="end_date" class="mb-1 block text-sm font-medium text-slate-700">Tanggal Akhir</label>
                <input id="end_date" name="end_date" type="date" value="{{ $endDate }}"
                       class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]">
            </div>
            <button type="submit"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-[#0051d5] px-4 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#00236f]">
                Filter
            </button>
            <a href="{{ route('admin.rekap.damage') }}"
               class="inline-flex h-10 items-center justify-center rounded-lg border border-[#D6DDF8] bg-white px-4 text-sm font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF]">
                Reset
            </a>
            <div class="flex-1"></div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.rekap.damage.export.csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-green-600 px-4 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-green-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Ekspor CSV
                </a>
                <a href="{{ route('admin.rekap.damage.export.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                   class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-red-600 px-4 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-red-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M14 2v6h6" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 13H8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 17H8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10 9H8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Ekspor PDF
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-xl border border-[#E2E7FF] bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-600">Fasilitas Dilaporkan</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[#00236f]">{{ $recap['summary']['total_facilities_with_reports'] }}</p>
        </div>
        <div class="rounded-xl border border-[#E2E7FF] bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-600">Total Laporan</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-[#00236f]">{{ $recap['summary']['total_reports'] }}</p>
        </div>
        <div class="rounded-xl border border-[#E2E7FF] bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-600">Baru</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-sky-700">{{ $recap['summary']['total_baru'] }}</p>
        </div>
        <div class="rounded-xl border border-[#E2E7FF] bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-600">Diproses</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-amber-700">{{ $recap['summary']['total_diproses'] }}</p>
        </div>
        <div class="rounded-xl border border-[#E2E7FF] bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-600">Selesai</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-green-700">{{ $recap['summary']['total_selesai'] }}</p>
        </div>
    </div>

    <!-- Data Table: Per Fasilitas -->
    <div class="mt-6 overflow-hidden rounded-2xl border border-[#E2E7FF] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-[#E2E7FF] bg-[#F8FAFC] text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-6 py-3">Fasilitas</th>
                        <th class="px-6 py-3">Tipe</th>
                        <th class="px-6 py-3">Lokasi</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Baru</th>
                        <th class="px-6 py-3 text-right">Diproses</th>
                        <th class="px-6 py-3 text-right">Selesai</th>
                        <th class="px-6 py-3 text-right">Ditolak</th>
                        <th class="px-6 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EEF2FF]">
                    @forelse ($recap['data'] as $item)
                        <tr class="transition-colors hover:bg-[#F8FAFC]">
                            <td class="px-6 py-3">
                                <p class="font-semibold text-[#00236f]">{{ $item['facility_name'] }}</p>
                            </td>
                            <td class="px-6 py-3 text-slate-700">
                                @php
                                    $typeLabels = [
                                        'ruang_kelas' => 'Ruang Kelas',
                                        'aula' => 'Aula',
                                        'laboratorium' => 'Laboratorium',
                                        'alat' => 'Alat',
                                        'lapangan' => 'Lapangan',
                                    ];
                                @endphp
                                {{ $typeLabels[$item['facility_type']] ?? $item['facility_type'] }}
                            </td>
                            <td class="px-6 py-3 text-slate-700">{{ $item['facility_location'] }}</td>
                            <td class="px-6 py-3">
                                @if ($item['status'] === 'aktif')
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-200">Aktif</span>
                                @elseif ($item['status'] === 'perbaikan')
                                    <span class="inline-flex items-center rounded-full bg-orange-50 px-2.5 py-1 text-xs font-semibold text-orange-700 ring-1 ring-inset ring-orange-200">Dalam Perbaikan</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right text-slate-700">
                                @if ($item['baru_count'] > 0)
                                    <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700 ring-1 ring-inset ring-sky-200">{{ $item['baru_count'] }}</span>
                                @else
                                    {{ $item['baru_count'] }}
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right text-slate-700">
                                @if ($item['diproses_count'] > 0)
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">{{ $item['diproses_count'] }}</span>
                                @else
                                    {{ $item['diproses_count'] }}
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right text-slate-700">
                                @if ($item['selesai_count'] > 0)
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-200">{{ $item['selesai_count'] }}</span>
                                @else
                                    {{ $item['selesai_count'] }}
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right text-slate-700">
                                @if ($item['ditolak_count'] > 0)
                                    <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-200">{{ $item['ditolak_count'] }}</span>
                                @else
                                    {{ $item['ditolak_count'] }}
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right font-medium text-[#00236f]">{{ $item['total_reports'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center">
                                <p class="text-sm font-medium text-[#00236f]">Tidak ada data fasilitas dengan laporan kerusakan</p>
                            </td>
                        </tr>
                    @endforelse
                    <!-- Summary Row -->
                    <tr class="bg-[#F3F4F6] font-bold">
                        <td class="px-6 py-3">TOTAL</td>
                        <td class="px-6 py-3"></td>
                        <td class="px-6 py-3"></td>
                        <td class="px-6 py-3"></td>
                        <td class="px-6 py-3 text-right">{{ $recap['summary']['total_baru'] }}</td>
                        <td class="px-6 py-3 text-right">{{ $recap['summary']['total_diproses'] }}</td>
                        <td class="px-6 py-3 text-right">{{ $recap['summary']['total_selesai'] }}</td>
                        <td class="px-6 py-3 text-right">{{ $recap['summary']['total_ditolak'] }}</td>
                        <td class="px-6 py-3 text-right">{{ $recap['summary']['total_reports'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Category Breakdown -->
    @if (!empty($recap['summary']['by_category']))
        <div class="mt-6 overflow-hidden rounded-2xl border border-[#E2E7FF] bg-white shadow-sm">
            <div class="px-6 py-4 border-b border-[#E2E7FF] bg-[#F8FAFC]">
                <h2 class="text-lg font-semibold text-[#00236f]">Rincian per Kategori Kerusakan</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-[#E2E7FF] bg-[#F8FAFC] text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="px-6 py-3">Kategori</th>
                            <th class="px-6 py-3 text-right">Jumlah Laporan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EEF2FF]">
                        @foreach ($recap['summary']['by_category'] as $category => $count)
                            <tr class="transition-colors hover:bg-[#F8FAFC]">
                                <td class="px-6 py-3 font-medium text-[#00236f]">{{ $category }}</td>
                                <td class="px-6 py-3 text-right text-slate-700">{{ $count }}</td>
                            </tr>
                        @endforeach
                        <tr class="bg-[#F3F4F6] font-bold">
                            <td class="px-6 py-3">TOTAL</td>
                            <td class="px-6 py-3 text-right">{{ $recap['summary']['total_reports'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="mt-4 text-sm text-slate-500 text-center">
        Periode: {{ \Carbon\Carbon::parse($recap['date_range']['start'])->isoFormat('D MMMM Y') }} s/d {{ \Carbon\Carbon::parse($recap['date_range']['end'])->isoFormat('D MMMM Y') }}
    </div>
@endsection