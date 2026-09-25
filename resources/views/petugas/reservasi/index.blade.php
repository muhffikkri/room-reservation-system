@extends('layouts.app')

@section('title', 'Antrian Reservasi')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Pusat operasional</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#00236f]">Antrian Reservasi</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Setujui, tolak, atau batalkan reservasi yang diajukan pengguna.
            </p>
        </div>
        <a href="{{ route('petugas.dashboard') }}"
           class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-[#D6DDF8] bg-white px-4 text-sm font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">
            Kembali ke Dashboard
        </a>
    </div>

    {{-- Tab Rekapitulasi --}}
    <div class="mt-6 flex flex-wrap gap-2" role="tablist" aria-label="Tab rekapitulasi reservasi">
        <a href="{{ route('petugas.reservasi.index', ['tab' => 'menunggu']) }}"
           role="tab" aria-selected="{{ $tab === 'menunggu' ? 'true' : 'false' }}"
           class="rounded-lg px-4 py-2 text-sm font-semibold transition {{ $tab === 'menunggu' ? 'bg-amber-500 text-white' : 'bg-[#F2F3FF] text-[#00236f] hover:bg-[#E2E7FF]' }}">
            Menunggu Approval
        </a>
        <a href="{{ route('petugas.reservasi.index', ['tab' => 'selesai']) }}"
           role="tab" aria-selected="{{ $tab === 'selesai' ? 'true' : 'false' }}"
           class="rounded-lg px-4 py-2 text-sm font-semibold transition {{ $tab === 'selesai' ? 'bg-emerald-600 text-white' : 'bg-[#F2F3FF] text-[#00236f] hover:bg-[#E2E7FF]' }}">
            Selesai
        </a>
    </div>

    <div class="mt-8 rounded-2xl border border-[#E2E7FF] bg-white p-4 shadow-sm">
        <form id="petugasFilterForm" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="status" class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                <select id="status" name="status"
                        class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]"
                        data-debounce="300">
                    <option value="">Semua status</option>
                    <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Menunggu Persetujuan</option>
                    <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Disetujui</option>
                    <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Ditolak</option>
                    <option value="rejected_by_system" @selected(($filters['status'] ?? '') === 'rejected_by_system')>Ditolak oleh Sistem</option>
                    <option value="cancelled_by_user" @selected(($filters['status'] ?? '') === 'cancelled_by_user')>Dibatalkan Pengguna</option>
                    <option value="cancelled_by_officer" @selected(($filters['status'] ?? '') === 'cancelled_by_officer')>Dibatalkan Petugas</option>
                    <option value="cancelled_by_system" @selected(($filters['status'] ?? '') === 'cancelled_by_system')>Dibatalkan oleh Sistem</option>
                </select>
            </div>
            <div>
                <label for="date" class="mb-1 block text-sm font-medium text-slate-700">Tanggal</label>
                <input id="date" name="date" type="date" value="{{ $filters['date'] ?? '' }}"
                       class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]"
                       data-debounce="300">
            </div>
            <button type="button" id="reset-filter"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-[#0051d5] px-4 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#00236f] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">
                Reset Filter
            </button>
        </form>
    </div>

    <div id="loading-indicator" class="mt-6 hidden">
        <div class="rounded-2xl border border-[#E2E7FF] bg-white p-8 shadow-sm text-center">
            <svg class="animate-spin h-8 w-8 text-[#0051d5] mx-auto" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.37 0 0 5.37 0 12h4z"></path>
            </svg>
            <p class="mt-2 text-sm text-slate-500">Memuat data...</p>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-[#E2E7FF] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="reservation-table">
                <thead class="border-b border-[#E2E7FF] bg-[#F8FAFC] text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-6 py-3">Pemohon</th>
                        <th class="px-6 py-3">Fasilitas</th>
                        <th class="px-6 py-3">Jadwal</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Waktu Diajukan</th>
                        <th class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EEF2FF]" id="reservation-body">
                    @forelse ($reservations as $reservation)
                        <tr class="transition-colors hover:bg-[#F8FAFC]">
                            <td class="px-6 py-3">
                                <p class="font-semibold text-[#00236f]">{{ $reservation->user->name }}</p>
                                <p class="text-xs text-slate-600">{{ $reservation->user->email }}</p>
                            </td>
                            <td class="px-6 py-3">
                                <p class="font-medium text-[#00236f]">{{ $reservation->facility->name }}</p>
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
                                @elseif ($reservation->status === 'cancelled_by_system')
                                    <span class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 ring-1 ring-inset ring-violet-200">Dibatalkan oleh Sistem</span>
                                @elseif ($reservation->status === 'rejected_by_system')
                                    <span class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 ring-1 ring-inset ring-violet-200">Ditolak oleh Sistem</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-orange-50 px-2.5 py-1 text-xs font-semibold text-orange-700 ring-1 ring-inset ring-orange-200">Dibatalkan Petugas</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-slate-600 text-xs whitespace-nowrap">
                                {{ $reservation->created_at->format('d M Y, H.i') }}
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('petugas.reservasi.show', $reservation) }}"
                                       class="inline-flex h-8 items-center rounded-lg border border-[#D6DDF8] bg-white px-3 text-xs font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF]">
                                        Detail
                                    </a>
                                    @if ($reservation->status === 'pending')
                                        <button type="button" data-open-dialog="approve-{{ $reservation->id }}"
                                                class="inline-flex h-8 items-center rounded-lg bg-[#0051d5] px-3 text-xs font-semibold text-white transition-colors hover:bg-[#00236f]">
                                            Setujui
                                        </button>
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

                        <dialog id="approve-{{ $reservation->id }}"
                                class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl backdrop:bg-slate-950/40">
                            <h3 class="text-lg font-semibold text-[#00236f]">Setujui reservasi?</h3>
                            <p class="mt-1 text-sm text-slate-600">
                                {{ $reservation->facility->name }} ·
                                {{ $reservation->start_time->format('d M Y H.i') }} – {{ $reservation->end_time->format('H.i') }}
                            </p>
                            <form method="POST" action="{{ route('petugas.reservasi.approve', $reservation) }}" class="mt-4">
                                @csrf
                                <div class="mt-4 flex items-center justify-end gap-2">
                                    <button type="button" data-close-dialog="approve-{{ $reservation->id }}"
                                            class="inline-flex h-10 items-center justify-center rounded-lg border border-[#D6DDF8] bg-white px-4 text-sm font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF]">
                                        Kembali
                                    </button>
                                    <button type="submit"
                                            class="inline-flex h-10 items-center justify-center rounded-lg bg-[#0051d5] px-4 text-sm font-semibold text-white transition-colors hover:bg-[#00236f]">
                                        Konfirmasi Setujui
                                    </button>
                                </div>
                            </form>
                        </dialog>
                        <dialog id="reject-{{ $reservation->id }}"
                                class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl backdrop:bg-slate-950/40">
                            <h3 class="text-lg font-semibold text-[#00236f]">Tolak reservasi?</h3>
                            <p class="mt-1 text-sm text-slate-600">
                                {{ $reservation->facility->name }} ·
                                {{ $reservation->start_time->format('d M Y H.i') }} – {{ $reservation->end_time->format('H.i') }}
                            </p>
                            <form method="POST" action="{{ route('petugas.reservasi.reject', $reservation) }}" class="mt-4">
                                @csrf
                                <div>
                                    <label for="reject-reason-{{ $reservation->id }}" class="mb-1 block text-sm font-medium text-slate-700">Alasan penolakan</label>
                                    <textarea id="reject-reason-{{ $reservation->id }}" name="reason" rows="3" required minlength="10" maxlength="255"
                                              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]"
                                              placeholder="Jelaskan alasan penolakan (min. 10 karakter)"></textarea>
                                    @error('reason')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="mt-4 flex items-center justify-end gap-2">
                                    <button type="button" data-close-dialog="reject-{{ $reservation->id }}"
                                            class="inline-flex h-10 items-center justify-center rounded-lg border border-[#D6DDF8] bg-white px-4 text-sm font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF]">
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
                            <h3 class="text-lg font-semibold text-[#00236f]">Batalkan reservasi?</h3>
                            <p class="mt-1 text-sm text-slate-600">
                                {{ $reservation->facility->name }} ·
                                {{ $reservation->start_time->format('d M Y H.i') }} – {{ $reservation->end_time->format('H.i') }}
                            </p>
                            <form method="POST" action="{{ route('petugas.reservasi.cancel', $reservation) }}" class="mt-4">
                                @csrf
                                <div>
                                    <label for="cancel-reason-{{ $reservation->id }}" class="mb-1 block text-sm font-medium text-slate-700">Alasan pembatalan</label>
                                    <textarea id="cancel-reason-{{ $reservation->id }}" name="cancel_reason" rows="3" required minlength="10" maxlength="255"
                                              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]"
                                              placeholder="Jelaskan alasan pembatalan (min. 10 karakter)"></textarea>
                                    @error('cancel_reason')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div class="mt-4 flex items-center justify-end gap-2">
                                    <button type="button" data-close-dialog="cancel-{{ $reservation->id }}"
                                            class="inline-flex h-10 items-center justify-center rounded-lg border border-[#D6DDF8] bg-white px-4 text-sm font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF]">
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
                            <td colspan="6" class="px-6 py-10 text-center">
                                <p class="text-sm font-medium text-[#00236f]">Tidak ada reservasi</p>
                                <p class="mt-1 text-sm text-slate-500">Reservasi yang diajukan pengguna akan tampil di sini sesuai filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reservations->hasPages())
            <div class="border-t border-[#EEF2FF] px-6 py-4" id="pagination-container">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>
@endsection
