@extends('layouts.app')

@section('title', 'Riwayat Reservasi Saya')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Riwayat Reservasi Saya</h1>
                <p class="text-sm text-slate-500">Kelola dan pantau status permohonan peminjaman fasilitas kampus Anda.</p>
            </div>
            <a href="{{ route('reservasi.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                + Ajukan Reservasi Baru
            </a>
        </div>

        {{-- Filter Status --}}
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3">
            @php
                $statuses = [
                    '' => 'Semua',
                    'pending' => 'Menunggu Persetujuan',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    'cancelled_by_user' => 'Dibatalkan Pengguna',
                    'cancelled_by_officer' => 'Dibatalkan Petugas',
                ];
            @endphp

            @foreach ($statuses as $key => $label)
                <a href="{{ route('reservasi.index', $key ? ['status' => $key] : []) }}"
                    class="rounded-md px-3 py-1.5 text-xs font-medium transition-colors {{ (string) request('status') === (string) $key ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Tabel / List Reservasi --}}
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            @if ($reservations->isEmpty())
                <div class="p-8 text-center text-slate-500">
                    <p class="text-sm">Belum ada data reservasi.</p>
                    <a href="{{ route('reservasi.create') }}"
                        class="mt-2 inline-block text-sm font-medium text-indigo-600 hover:underline">
                        Ajukan reservasi sekarang &rarr;
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Fasilitas</th>
                                <th class="px-6 py-3">Waktu Pelaksanaan</th>
                                <th class="px-6 py-3">Tujuan</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($reservations as $res)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 font-medium text-slate-900">
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
                                                default => ucfirst($res->status),
                                            } }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('reservasi.show', $res) }}"
                                            class="font-medium text-indigo-600 hover:text-indigo-800">
                                            Lihat Detail &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($reservations->hasPages())
                    <div class="border-t border-slate-200 p-4">
                        {{ $reservations->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
