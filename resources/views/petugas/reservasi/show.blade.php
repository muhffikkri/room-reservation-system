@extends('layouts.app')

@section('title', 'Detail Reservasi')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Detail Reservasi</h1>
            <p class="mt-1 text-sm text-slate-600">
                Reservasi No. {{ str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT) }}
            </p>
        </div>
        <a href="{{ route('petugas.reservasi.index') }}"
           class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
            Kembali ke Antrian
        </a>
    </div>

    <div class="mt-6 grid grid-cols-12 gap-6">
        <main class="col-span-12 lg:col-span-8">
            <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-base font-semibold text-slate-900">Informasi Reservasi</h2>
                </div>
                <dl class="divide-y divide-slate-100 px-6">
                    <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-slate-600">Fasilitas</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $reservation->facility->name }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-slate-600">Lokasi</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $reservation->facility->location }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-slate-600">Jadwal</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">
                            {{ $reservation->start_time->format('l, d M Y') }} ·
                            {{ $reservation->start_time->format('H.i') }} – {{ $reservation->end_time->format('H.i') }}
                        </dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-slate-600">Tujuan</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $reservation->purpose }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-slate-600">Status</dt>
                        <dd class="sm:col-span-2">
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
                        </dd>
                    </div>
                    @if ($reservation->reject_reason !== null)
                        <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-3">
                            <dt class="text-sm font-medium text-slate-600">Alasan Penolakan</dt>
                            <dd class="text-sm text-slate-900 sm:col-span-2">{{ $reservation->reject_reason }}</dd>
                        </div>
                    @endif
                    @if ($reservation->cancel_reason !== null)
                        <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-3">
                            <dt class="text-sm font-medium text-slate-600">Alasan Pembatalan</dt>
                            <dd class="text-sm text-slate-900 sm:col-span-2">{{ $reservation->cancel_reason }}</dd>
                        </div>
                    @endif
                    @if ($reservation->decided_at !== null)
                        <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-3">
                            <dt class="text-sm font-medium text-slate-600">Diputuskan</dt>
                            <dd class="text-sm text-slate-900 sm:col-span-2">
                                {{ $reservation->decidedBy?->name ?? '-' }} ·
                                {{ $reservation->decided_at->format('d M Y H.i') }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </section>

            <section class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-base font-semibold text-slate-900">Informasi Pemohon</h2>
                </div>
                <dl class="divide-y divide-slate-100 px-6">
                    <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-slate-600">Nama</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $reservation->user->name }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-slate-600">Email</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $reservation->user->email }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-slate-600">NIM/NIP</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $reservation->user->identity ?? '-' }}</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-1 py-4 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-slate-600">No. HP</dt>
                        <dd class="text-sm text-slate-900 sm:col-span-2">{{ $reservation->user->phone ?? '-' }}</dd>
                    </div>
                </dl>
            </section>
        </main>

        <aside class="col-span-12 lg:col-span-4">
            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-slate-900">Aksi</h2>
                <p class="mt-1 text-sm text-slate-600">
                    Menyetujui mengunci slot (dicek bentrok terhadap resevasi approved lainnya).
                </p>

                @if ($reservation->status === 'pending')
                    <div class="mt-4 flex flex-col gap-3">
                        <form method="POST" action="{{ route('petugas.reservasi.approve', $reservation) }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-blue-900 px-4 text-sm font-semibold text-white transition-colors hover:bg-blue-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                                Setujui Reservasi
                            </button>
                        </form>
                        <button type="button" data-open-dialog="reject-detail"
                                class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg border border-red-300 bg-white px-4 text-sm font-semibold text-red-600 transition-colors hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2">
                            Tolak Reservasi
                        </button>
                    </div>
                @endif

                @if (in_array($reservation->status, ['pending', 'approved'], true))
                    <button type="button" data-open-dialog="cancel-detail"
                            class="mt-3 inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-red-600 px-4 text-sm font-semibold text-white transition-colors hover:bg-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2">
                        Batalkan Reservasi
                    </button>
                @endif
            </section>
        </aside>
    </div>

    <dialog id="reject-detail"
            class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl backdrop:bg-slate-950/40">
        <h3 class="text-lg font-semibold text-slate-900">Tolak reservasi?</h3>
        <p class="mt-1 text-sm text-slate-600">
            {{ $reservation->facility->name }} ·
            {{ $reservation->start_time->format('d M Y H.i') }} – {{ $reservation->end_time->format('H.i') }}
        </p>
        <form method="POST" action="{{ route('petugas.reservasi.reject', $reservation) }}" class="mt-4">
            @csrf
            <div>
                <label for="reject-reason-detail" class="mb-1 block text-sm font-medium text-slate-700">Alasan penolakan</label>
                <textarea id="reject-reason-detail" name="reason" rows="3" required minlength="10" maxlength="255"
                          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100"
                          placeholder="Jelaskan alasan penolakan (min. 10 karakter)"></textarea>
                @error('reason')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="mt-4 flex items-center justify-end gap-2">
                <button type="button" data-close-dialog="reject-detail"
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

    <dialog id="cancel-detail"
            class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl backdrop:bg-slate-950/40">
        <h3 class="text-lg font-semibold text-slate-900">Batalkan reservasi?</h3>
        <p class="mt-1 text-sm text-slate-600">
            {{ $reservation->facility->name }} ·
            {{ $reservation->start_time->format('d M Y H.i') }} – {{ $reservation->end_time->format('H.i') }}
        </p>
        <form method="POST" action="{{ route('petugas.reservasi.cancel', $reservation) }}" class="mt-4">
            @csrf
            <div>
                <label for="cancel-reason-detail" class="mb-1 block text-sm font-medium text-slate-700">Alasan pembatalan</label>
                <textarea id="cancel-reason-detail" name="cancel_reason" rows="3" required minlength="10" maxlength="255"
                          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100"
                          placeholder="Jelaskan alasan pembatalan (min. 10 karakter)"></textarea>
                @error('cancel_reason')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="mt-4 flex items-center justify-end gap-2">
                <button type="button" data-close-dialog="cancel-detail"
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
@endsection