@extends('layouts.app')

@section('title', 'Kelola Fasilitas')

@section('content')
    @php
        $clayTableHead = 'border-b border-blue-100/80 bg-blue-50/50 px-5 py-3.5 text-[11px] font-extrabold uppercase tracking-[0.14em] text-blue-700';
        $clayTableCell = 'px-5 py-4 align-middle';
        // Aksi baris mengikuti token clay yang sama dengan halaman lain:
        // Edit putih, Aktifkan hijau, Nonaktifkan merah.
        $chipLayout = 'inline-flex h-9 items-center justify-center whitespace-nowrap rounded-full px-4 text-xs font-bold';
        $editChip = "clay-pressable {$chipLayout} text-slate-600";
        $dangerChip = "clay-button-danger {$chipLayout}";
        $activateChip = "inline-flex {$chipLayout} border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white text-emerald-700 transition hover:from-emerald-100 hover:to-white";
    @endphp

    <div class="space-y-5">
        <section class="auth-clay-card rounded-[2rem] p-5 sm:p-7 lg:p-8" aria-labelledby="facilities-heading">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Master data</p>
                    <h1 id="facilities-heading"
                        class="mt-0.5 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">Kelola Fasilitas</h1>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600">Tambah, ubah, dan atur ketersediaan fasilitas
                        kampus. Menonaktifkan tidak menghapus data riwayat.</p>
                </div>
                <a href="{{ route('admin.fasilitas.create') }}"
                    class="landing-button inline-flex min-h-11 items-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-2.5 text-sm font-bold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)]">
                    Tambah Fasilitas
                </a>
            </div>

            <form method="GET" action="{{ route('admin.fasilitas.index') }}"
                class="mt-6 flex flex-wrap items-end gap-3 rounded-2xl border border-blue-100/70 bg-blue-50/40 p-4">
                <div class="min-w-64 flex-1">
                    <label for="q" class="block text-xs font-bold uppercase tracking-[0.12em] text-slate-600">Cari
                        fasilitas</label>
                    <input id="q" name="q" type="text" value="{{ $keyword }}" placeholder="Cari nama fasilitas…"
                        class="landing-input mt-1.5 block h-11 w-full rounded-2xl px-4 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                </div>
                <button type="submit"
                    class="landing-button inline-flex h-11 items-center justify-center rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-6 text-sm font-bold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)]">
                    Cari
                </button>
                @if ($keyword !== '')
                    <a href="{{ route('admin.fasilitas.index') }}"
                        class="clay-pressable inline-flex h-11 items-center justify-center rounded-full px-5 text-sm font-semibold text-blue-700">
                        Reset
                    </a>
                @endif
            </form>

            <div class="clay-inset mt-6 overflow-hidden rounded-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-full text-left text-sm text-slate-600">
                        <thead>
                            <tr>
                                <th scope="col" class="{{ $clayTableHead }}">Fasilitas</th>
                                <th scope="col" class="{{ $clayTableHead }}">Tipe</th>
                                <th scope="col" class="{{ $clayTableHead }}">Lokasi</th>
                                <th scope="col" class="{{ $clayTableHead }} text-right">Kapasitas</th>
                                <th scope="col" class="{{ $clayTableHead }}">Status</th>
                                <th scope="col" class="{{ $clayTableHead }} text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-100/70">
                            @forelse ($facilities as $facility)
                                <tr class="transition-colors hover:bg-white/70">
                                    <td class="{{ $clayTableCell }}">
                                        <div class="flex items-center gap-3">
                                            @if ($facility->photo !== null)
                                                <img src="{{ $facility->photo_url }}" alt="Foto {{ $facility->name }}"
                                                    loading="lazy"
                                                    class="img-fade h-12 w-16 shrink-0 rounded-xl border border-white object-cover shadow-[0_4px_10px_rgba(116,155,211,0.16)]">
                                            @else
                                                <span
                                                    class="flex h-12 w-16 shrink-0 items-center justify-center rounded-xl border border-blue-100/80 bg-white text-[10px] font-bold uppercase tracking-wide text-slate-400">
                                                    Tanpa foto
                                                </span>
                                            @endif
                                            <p class="font-bold text-[#10264a]">{{ $facility->name }}</p>
                                        </div>
                                    </td>
                                    <td class="{{ $clayTableCell }} text-slate-700">{{ $facility->typeLabel() }}</td>
                                    <td class="{{ $clayTableCell }} text-slate-700">{{ $facility->location }}</td>
                                    <td class="{{ $clayTableCell }} text-right font-bold text-[#10264a]">
                                        {{ $facility->capacity }}</td>
                                    <td class="{{ $clayTableCell }}">
                                        <x-ui.badge :status="$facility->status" dot />
                                    </td>
                                    <td class="{{ $clayTableCell }}">
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            <a href="{{ route('admin.fasilitas.edit', $facility) }}"
                                                class="{{ $editChip }}">Edit</a>
                                            @if ($facility->status === 'nonaktif')
                                                <form method="POST" action="{{ route('admin.fasilitas.activate', $facility) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="{{ $activateChip }}">Aktifkan</button>
                                                </form>
                                            @else
                                                <form method="POST"
                                                    action="{{ route('admin.fasilitas.deactivate', $facility) }}"
                                                    data-confirm-message="Fasilitas {{ $facility->name }} akan dinonaktifkan dan tidak dapat direservasi. Lanjutkan?">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="{{ $dangerChip }}">Nonaktifkan</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-14 text-center">
                                        <span
                                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-white bg-white text-2xl text-blue-600 shadow-[0_4px_10px_rgba(59,130,246,0.15)]">
                                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                            </svg>
                                        </span>
                                        <p class="mt-4 text-base font-bold text-slate-800">Tidak ada fasilitas</p>
                                        <p class="mt-1 text-sm text-slate-500">Fasilitas yang Anda tambahkan akan tampil di
                                            sini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="mt-5 text-xs text-slate-500">{{ $facilities->count() }} fasilitas ditampilkan
                @if ($keyword !== '')
                    untuk pencarian &ldquo;{{ $keyword }}&rdquo;
                @endif
            </p>
        </section>
    </div>
@endsection
