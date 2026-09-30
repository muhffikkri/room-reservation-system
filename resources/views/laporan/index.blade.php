@extends('layouts.app')

@section('title', 'Daftar Laporan Kerusakan')

@section('content')
    <div class="space-y-6">
        <div class="auth-clay-card overflow-hidden rounded-[2rem] p-5 sm:p-7 lg:p-8">
            <div
                class="mb-7 flex flex-col gap-4 border-b border-blue-100/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Pelaporan fasilitas</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">Laporan Kerusakan Saya
                    </h1>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600">Kelola dan pantau status laporan kerusakan
                        fasilitas kampus yang telah Anda kirimkan.</p>
                </div>
                <div>
                    <a href="{{ route('laporan.create') }}"
                        class="landing-button inline-flex shrink-0 items-center justify-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-3 text-sm font-bold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        Buat Laporan Baru
                    </a>
                </div>
            </div>

            @if ($reports->isEmpty())
                <div class="px-4 py-14 text-center">
                    <span
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-2xl text-blue-600 shadow-inner">⚠</span>
                    <h3 class="mt-4 text-base font-bold text-slate-800">Belum ada laporan</h3>
                    <p class="mt-1 text-xs text-slate-500">Anda belum pernah membuat laporan kerusakan fasilitas.</p>
                    <div class="mt-6">
                        <a href="{{ route('laporan.create') }}"
                            class="landing-button inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-2.5 text-xs font-bold text-white shadow-sm">
                            Buat Laporan Sekarang →
                        </a>
                    </div>
                </div>
            @else
                @php
                    $facilityFallbackImages = [
                        'aula' => 'aula.webp',
                        'laboratorium' => 'lab-komputer.webp',
                        'lapangan' => 'lapangan-futsal.webp',
                        'ruang_kelas' => 'ruang-kelas.webp',
                        'alat' => 'proyektor.webp',
                    ];
                    $reportStatuses = [
                        'baru' => ['label' => 'Baru', 'class' => 'bg-sky-50 text-sky-700'],
                        'diproses' => ['label' => 'Diproses', 'class' => 'bg-amber-50 text-amber-700'],
                        'selesai' => ['label' => 'Selesai', 'class' => 'bg-emerald-50 text-emerald-700'],
                        'ditolak' => ['label' => 'Ditolak', 'class' => 'bg-rose-50 text-rose-700'],
                    ];
                @endphp
                <div class="space-y-3">
                    @foreach ($reports as $report)
                        @php($status = $reportStatuses[$report->status] ?? ['label' => ucfirst($report->status), 'class' => 'bg-slate-100 text-slate-600'])
                        <a href="{{ route('laporan.show', $report) }}"
                            class="dashboard-clay-list clay-pressable flex items-center justify-between gap-4 rounded-2xl p-4.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="flex min-w-0 items-center gap-3.5">
                                <span
                                    class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-blue-100 bg-white p-1 shadow-sm">
                                    <img src="{{ $report->facility?->photo_url ?: asset('images/' . ($facilityFallbackImages[$report->facility?->type] ?? 'aula.webp')) }}"
                                        alt="" class="h-full w-full rounded-lg object-cover">
                                </span>
                                <span class="min-w-0">
                                    <span
                                        class="block truncate text-sm font-bold text-slate-800 sm:text-base">{{ $report->facility?->name ?? 'Fasilitas dihapus' }}</span>
                                    <span
                                        class="mt-0.5 block truncate text-xs text-slate-500">{{ $report->categoryLabel() }}
                                        · {{ $report->created_at->format('d M Y, H:i') }} WIB</span>
                                </span>
                            </span>
                            <span class="shrink-0 flex items-center gap-3">
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-bold {{ $status['class'] }}">{{ $status['label'] }}</span>
                                <span class="hidden text-xs font-bold text-blue-700 sm:inline">&rarr;</span>
                            </span>
                        </a>
                    @endforeach
                </div>
                @if ($reports->hasPages())
                    <div class="mt-5 border-t border-blue-100/80 pt-4">
                        {{ $reports->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
