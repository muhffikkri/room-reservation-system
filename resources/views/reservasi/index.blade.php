@extends('layouts.app')

@section('title', 'Riwayat Reservasi Saya')

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Aktivitas pengguna</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#00236f]">Riwayat Reservasi Saya</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">Kelola dan pantau status permohonan peminjaman fasilitas kampus Anda.</p>
            </div>
            <a href="{{ route('reservasi.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-[#0051d5] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#00236f] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">
                + Ajukan Reservasi Baru
            </a>
        </div>

        {{-- Filter Status --}}
        <div class="flex flex-wrap items-center gap-2 border-b border-[#E2E7FF] pb-3">
            @php
                $statuses = [
                    '' => 'Semua',
                    'pending' => 'Menunggu Persetujuan',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    'cancelled_by_user' => 'Dibatalkan Pengguna',
                    'cancelled_by_officer' => 'Dibatalkan Petugas',
                    'cancelled_by_system' => 'Gagal',
                ];
            @endphp

            @foreach ($statuses as $key => $label)
                <a href="{{ route('reservasi.index', $key ? ['status' => $key] : []) }}"
                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors {{ (string) request('status') === (string) $key ? 'bg-[#F2F3FF] font-semibold text-[#00236f]' : 'text-slate-600 hover:bg-[#F8FAFC]' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Tabel / List Reservasi --}}
        <div class="overflow-hidden rounded-2xl border border-[#E2E7FF] bg-white shadow-sm">
            @if ($reservations->isEmpty())
                <div class="p-10 text-center text-slate-500">
                    <p class="text-sm">Belum ada data reservasi.</p>
                    <a href="{{ route('reservasi.create') }}"
                        class="mt-2 inline-block text-sm font-semibold text-[#0051d5] hover:text-[#00236f] hover:underline">
                        Ajukan reservasi sekarang &rarr;
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="border-b border-[#E2E7FF] bg-[#F8FAFC] text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Fasilitas</th>
                                <th class="px-6 py-3">Waktu Pelaksanaan</th>
                                <th class="px-6 py-3">Tujuan</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EEF2FF]">
                            @foreach ($reservations as $res)
                                <tr class="transition-colors hover:bg-[#F8FAFC]">
                                    <td class="px-6 py-4 font-semibold text-[#00236f]">
                                        {{ $res->facility->name }}
                                        <span
                                            class="block text-xs font-normal text-slate-500">{{ $res->facility->location }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $res->start_time->translatedFormat('d M Y') }}
                                        <span class="block text-xs text-slate-500">
                                            {{ $res->start_time->format('H:i') }} - {{ $res->end_time->format('H:i') }} WIB
                                        </span>
                                    </td>
                                    <td class="max-w-xs truncate px-6 py-4" title="{{ $res->purpose }}">
                                        {{ $res->purpose }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <x-ui.badge :status="$res->status">
                                            {{ match ($res->status) {
                                                'pending' => 'Menunggu Persetujuan',
                                                'approved' => 'Disetujui',
                                                'rejected' => 'Ditolak',
                                                'cancelled_by_user' => 'Dibatalkan Pengguna',
                                                'cancelled_by_officer' => 'Dibatalkan Petugas',
                                                'cancelled_by_system' => 'Gagal',
                                                default => ucfirst($res->status),
                                            } }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('reservasi.show', $res) }}"
                                            class="font-semibold text-[#0051d5] hover:text-[#00236f]">
                                            Lihat Detail &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($reservations->hasPages())
                    <div class="border-t border-[#EEF2FF] p-4">
                        {{ $reservations->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
