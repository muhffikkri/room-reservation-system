@extends('layouts.app')

@section('title', 'Antrian Reservasi')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Antrian Reservasi</h1>
            <p class="mt-1 text-sm text-slate-600">
                Setujui, tolak, atau batalkan reservasi yang diajukan pengguna.
            </p>
        </div>
        <a href="{{ route('petugas.dashboard') }}"
           class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
            Kembali ke Dashboard
        </a>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('petugas.reservasi.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="status" class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                <select id="status" name="status"
                        class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    <option value="">Semua status</option>
                    <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Menunggu Persetujuan</option>
                    <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Disetujui</option>
                    <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Ditolak</option>
                    <option value="cancelled_by_user" @selected(($filters['status'] ?? '') === 'cancelled_by_user')>Dibatalkan Pengguna</option>
                    <option value="cancelled_by_officer" @selected(($filters['status'] ?? '') === 'cancelled_by_officer')>Dibatalkan Petugas</option>
                </select>
            </div>
            <div>
                <label for="date" class="mb-1 block text-sm font-medium text-slate-700">Tanggal</label>
                <input id="date" name="date" type="date" value="{{ $filters['date'] ?? '' }}"
                       class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
            </div>
            <button type="submit"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-900 px-4 text-sm font-semibold text-white transition-colors hover:bg-blue-800">
                Filter
            </button>
            @if (($filters['status'] ?? null) || ($filters['date'] ?? null))
                <a href="{{ route('petugas.reservasi.index') }}"
                   class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-6 py-3">Pemohon</th>
                        <th class="px-6 py-3">Fasilitas</th>
                        <th class="px-6 py-3">Jadwal</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($reservations as $reservation)
                        <tr class="transition-colors hover:bg-slate-50">
                            <td class="px-6 py-3">
                                <p class="font-medium text-slate-900">{{ $reservation->user->name }}</p>
                                <p class="text-xs text-slate-600">{{ $reservation->user->email }}</p>
                            </td>
                            <td class="px-6 py-3">
                                <p class="text-slate-900">{{ $reservation->facility->name }}</p>
                                <p class="text-xs text-slate-600">{{ $reservation->facility->location }}</p>
                            </td>
                            <td class="whitespace-nowrap px-6 py-3 text-slate-700">
                                {{ $reservation->start_time->format('d M Y') }}
                                <br>
                                <span class="text-xs text-slate-600">
                                    {{ $reservation->start_time->format('H.i') }} – {{ $reservation->end_time->format('H.i') }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                @if ($reservation->status === 'pending')
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">Menunggu Persetujuan</span>
                                @elseif ($reservation->status === 'approved')
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-200">Disetujui</span>
                                @elseif ($reservation->status === 'rejected')
                                    <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-200">Ditolak</span>
                                @elseif ($reservation->status === 'cancelled_by_user')
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">Dibatalkan Pengguna</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-orange-50 px-2.5 py-1 text-xs font-semibold text-orange-700 ring-1 ring-inset ring-orange-200">Dibatalkan Petugas</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('petugas.reservasi.show', $reservation) }}"
                                       class="inline-flex h-8 items-center rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                                        Detail
                                    </a>
                                    @if ($reservation->status === 'pending')
                                        <form method="POST" action="{{ route('petugas.reservasi.approve', $reservation) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex h-8 items-center rounded-lg bg-blue-900 px-3 text-xs font-semibold text-white transition-colors hover:bg-blue-800">
                                                Setujui
                                            </button>
                                        </form>
                                        <button type="button" data-open-dialog="reject-{{ $reservation->id }}"
                                                class="inline-flex h-8 items-center rounded-lg border border-red-300 bg-white px-3 text-xs font-semibold text-red-600 transition-colors hover:bg-red-50">
                                            Tolak
                                        </button>
                                    @endif
                                    @if (in_array($reservation->status, ['pending', 'approved'], true))
                                        <button type="button" data-open-dialog="cancel-{{ $reservation->id }}"
                                                class="inline-flex h-8 items-center rounded-lg bg-red-600 px-3 text-xs font-semibold text-white transition-colors hover:bg-red-700">
                                            Batalkan
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <dialog id="reject-{{ $reservation->id }}"
                                class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl backdrop:bg-slate-950/40">
                            <h3 class="text-lg font-semibold text-slate-900">Tolak reservasi?</h3>
                            <p class="mt-1 text-sm text-slate-600">
                                {{ $reservation->facility->name }} ·
                                {{ $reservation->start_time->format('d M Y H.i') }} – {{ $reservation->end_time->format('H.i') }}
                            </p>
                            <form method="POST" action="{{ route('petugas.reservasi.reject', $reservation) }}" class="mt-4">
                                @csrf
                                <div>
                                    <label for="reject-reason-{{ $reservation->id }}" class="mb-1 block text-sm font-medium text-slate-700">Alasan penolakan</label>
                                    <textarea id="reject-reason-{{ $reservation->id }}" name="reason" rows="3" required minlength="10" maxlength="255"
                                              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100"
                                              placeholder="Jelaskan alasan penolakan (min. 10 karakter)"></textarea>
                                    @error('reason')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="mt-4 flex items-center justify-end gap-2">
                                    <button type="button" data-close-dialog="reject-{{ $reservation->id }}"
                                            class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                                        Kembali
                                    </button>
                                    <button type="submit"
                                            class="inline-flex h-10 items-center justify-center rounded-lg bg-red-600 px-4 text-sm font-semibold text-white transition-colors hover:bg-red-700">
                                        Tolak Reservasi
                                    </button>
                                </div>
                            </form>
                        </dialog>

                        <dialog id="cancel-{{ $reservation->id }}"
                                class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl backdrop:bg-slate-950/40">
                            <h3 class="text-lg font-semibold text-slate-900">Batalkan reservasi?</h3>
                            <p class="mt-1 text-sm text-slate-600">
                                {{ $reservation->facility->name }} ·
                                {{ $reservation->start_time->format('d M Y H.i') }} – {{ $reservation->end_time->format('H.i') }}
                            </p>
                            <form method="POST" action="{{ route('petugas.reservasi.cancel', $reservation) }}" class="mt-4">
                                @csrf
                                <div>
                                    <label for="cancel-reason-{{ $reservation->id }}" class="mb-1 block text-sm font-medium text-slate-700">Alasan pembatalan</label>
                                    <textarea id="cancel-reason-{{ $reservation->id }}" name="cancel_reason" rows="3" required minlength="10" maxlength="255"
                                              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100"
                                              placeholder="Jelaskan alasan pembatalan (min. 10 karakter)"></textarea>
                                    @error('cancel_reason')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="mt-4 flex items-center justify-end gap-2">
                                    <button type="button" data-close-dialog="cancel-{{ $reservation->id }}"
                                            class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                                        Kembali
                                    </button>
                                    <button type="submit"
                                            class="inline-flex h-10 items-center justify-center rounded-lg bg-red-600 px-4 text-sm font-semibold text-white transition-colors hover:bg-red-700">
                                        Batalkan Reservasi
                                    </button>
                                </div>
                            </form>
                        </dialog>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center">
                                <p class="text-sm font-medium text-slate-900">Tidak ada reservasi</p>
                                <p class="mt-1 text-sm text-slate-500">Reservasi yang diajukan pengguna akan tampil di sini sesuai filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reservations->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>
@endsection