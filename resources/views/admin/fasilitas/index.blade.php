@extends('layouts.app')

@section('title', 'Kelola Fasilitas')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Master data</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#00236f]">Kelola Fasilitas</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Tambah, ubah, dan atur ketersediaan fasilitas kampus. Menonaktifkan tidak menghapus data riwayat.
            </p>
        </div>
        <a href="{{ route('admin.fasilitas.create') }}"
           class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-[#0051d5] px-4 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#00236f] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">
            Tambah Fasilitas
        </a>
    </div>

    <div class="mt-8 rounded-2xl border border-[#E2E7FF] bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.fasilitas.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="min-w-64 flex-1">
                <label for="q" class="mb-1 block text-sm font-medium text-slate-700">Cari fasilitas</label>
                <input id="q" name="q" type="text" value="{{ $keyword }}" placeholder="Cari nama fasilitas…"
                       class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]">
            </div>
            <button type="submit"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-[#0051d5] px-4 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#00236f]">
                Cari
            </button>
            @if ($keyword !== '')
                <a href="{{ route('admin.fasilitas.index') }}"
                   class="inline-flex h-10 items-center justify-center rounded-lg border border-[#D6DDF8] bg-white px-4 text-sm font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF]">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-[#E2E7FF] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-[#E2E7FF] bg-[#F8FAFC] text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-6 py-3">Fasilitas</th>
                        <th class="px-6 py-3">Tipe</th>
                        <th class="px-6 py-3">Lokasi</th>
                        <th class="px-6 py-3">Kapasitas</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EEF2FF]">
                    @forelse ($facilities as $facility)
                        <tr class="transition-colors hover:bg-[#F8FAFC]">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($facility->photo !== null)
                                        <img src="{{ Storage::disk('public')->url($facility->photo) }}" alt="Foto {{ $facility->name }}" loading="lazy"
                                             class="img-fade h-11 w-16 shrink-0 rounded-lg border border-slate-200 object-cover">
                                    @else
                                        <div class="flex h-11 w-16 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs text-slate-400">
                                            Tanpa foto
                                        </div>
                                    @endif
                                    <p class="font-semibold text-[#00236f]">{{ $facility->name }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-slate-700">
                                @if ($facility->type === 'ruang_kelas')
                                    Ruang Kelas
                                @elseif ($facility->type === 'aula')
                                    Aula
                                @elseif ($facility->type === 'laboratorium')
                                    Laboratorium
                                @elseif ($facility->type === 'alat')
                                    Alat
                                @else
                                    Lapangan
                                @endif
                            </td>
                            <td class="px-6 py-3 text-slate-700">{{ $facility->location }}</td>
                            <td class="px-6 py-3 text-slate-700">{{ $facility->capacity }}</td>
                            <td class="px-6 py-3">
                                @if ($facility->status === 'aktif')
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-200">Aktif</span>
                                @elseif ($facility->status === 'perbaikan')
                                    <span class="inline-flex items-center rounded-full bg-orange-50 px-2.5 py-1 text-xs font-semibold text-orange-700 ring-1 ring-inset ring-orange-200">Dalam Perbaikan</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('admin.fasilitas.edit', $facility) }}"
                                       class="inline-flex h-8 items-center rounded-lg border border-[#D6DDF8] bg-white px-3 text-xs font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF]">
                                        Edit
                                    </a>
                                    @if ($facility->status === 'nonaktif')
                                        <form method="POST" action="{{ route('admin.fasilitas.activate', $facility) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="inline-flex h-8 items-center rounded-lg bg-green-600 px-3 text-xs font-semibold text-white transition-colors hover:bg-green-700">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST"
                                              action="{{ route('admin.fasilitas.deactivate', $facility) }}"
                                              data-confirm-message="Fasilitas {{ $facility->name }} akan dinonaktifkan dan tidak dapat direservasi. Lanjutkan?">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="inline-flex h-8 items-center rounded-lg bg-red-600 px-3 text-xs font-semibold text-white transition-colors hover:bg-red-700">
                                                Nonaktifkan
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center">
                                <p class="text-sm font-medium text-[#00236f]">Tidak ada fasilitas</p>
                                <p class="mt-1 text-sm text-slate-500">Fasilitas yang Anda tambahkan akan tampil di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
