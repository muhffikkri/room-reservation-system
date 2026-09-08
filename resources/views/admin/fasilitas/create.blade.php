@extends('layouts.app')

@section('title', 'Tambah Fasilitas')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tambah Fasilitas</h1>
                <p class="mt-1 text-sm text-slate-600">Fasilitas baru langsung berstatus aktif dan dapat direservasi.</p>
            </div>
            <a href="{{ route('admin.fasilitas.index') }}"
               class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('admin.fasilitas.store') }}" enctype="multipart/form-data" data-validate
              class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nama fasilitas</label>
                    <input id="name" name="name" type="text" required maxlength="120" value="{{ old('name') }}"
                           class="w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="type" class="mb-1 block text-sm font-medium text-slate-700">Tipe</label>
                    <select id="type" name="type" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                        <option value="">Pilih tipe</option>
                        <option value="ruang_kelas" @selected(old('type') === 'ruang_kelas')>Ruang Kelas</option>
                        <option value="aula" @selected(old('type') === 'aula')>Aula</option>
                        <option value="laboratorium" @selected(old('type') === 'laboratorium')>Laboratorium</option>
                        <option value="alat" @selected(old('type') === 'alat')>Alat</option>
                        <option value="lapangan" @selected(old('type') === 'lapangan')>Lapangan</option>
                    </select>
                    @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="location" class="mb-1 block text-sm font-medium text-slate-700">Lokasi</label>
                    <input id="location" name="location" type="text" required maxlength="120" value="{{ old('location') }}"
                           placeholder="Gedung / area"
                           class="w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    @error('location')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="capacity" class="mb-1 block text-sm font-medium text-slate-700">Kapasitas</label>
                    <input id="capacity" name="capacity" type="number" required min="1" max="100000" value="{{ old('capacity') }}"
                           class="w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    @error('capacity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Deskripsi</label>
                    <textarea id="description" name="description" rows="4" maxlength="2000"
                              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100"
                              placeholder="Informasi tambahan fasilitas (opsional)">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="photo" class="mb-1 block text-sm font-medium text-slate-700">Foto fasilitas</label>
                    <input id="photo" name="photo" type="file" accept=".jpg,.jpeg,.png" data-image-preview="photo-preview"
                           class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-blue-800 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    <p class="mt-1 text-xs text-slate-500">Format JPG/PNG, maksimal 2 MB. (opsional)</p>
                    <img id="photo-preview" alt="Pratinjau foto fasilitas"
                         class="mt-3 hidden aspect-[16/9] w-full max-w-xs rounded-lg border border-slate-200 object-cover">
                    @error('photo')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-2">
                <a href="{{ route('admin.fasilitas.index') }}"
                   class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-900 px-4 text-sm font-semibold text-white transition-colors hover:bg-blue-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                    Simpan Fasilitas
                </button>
            </div>
        </form>
    </div>
@endsection