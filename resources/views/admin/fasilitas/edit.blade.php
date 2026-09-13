@extends('layouts.app')

@section('title', 'Edit Fasilitas')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Master data</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#00236f]">Edit {{ $facility->name }}</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">Ubah data fasilitas. Kosongkan foto untuk mempertahankan foto saat ini.</p>
            </div>
            <a href="{{ route('admin.fasilitas.index') }}"
               class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-[#D6DDF8] bg-white px-4 text-sm font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">
                Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('admin.fasilitas.update', $facility) }}" enctype="multipart/form-data" data-validate
              class="mt-8 rounded-2xl border border-[#E2E7FF] bg-white p-6 shadow-[0_14px_40px_rgba(15,23,42,0.06)] sm:p-8">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nama fasilitas</label>
                    <input id="name" name="name" type="text" required maxlength="120"
                           value="{{ old('name', $facility->name) }}"
                           class="w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="type" class="mb-1 block text-sm font-medium text-slate-700">Tipe</label>
                    <select id="type" name="type" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]">
                        <option value="">Pilih tipe</option>
                        <option value="ruang_kelas" @selected(old('type', $facility->type) === 'ruang_kelas')>Ruang Kelas</option>
                        <option value="aula" @selected(old('type', $facility->type) === 'aula')>Aula</option>
                        <option value="laboratorium" @selected(old('type', $facility->type) === 'laboratorium')>Laboratorium</option>
                        <option value="alat" @selected(old('type', $facility->type) === 'alat')>Alat</option>
                        <option value="lapangan" @selected(old('type', $facility->type) === 'lapangan')>Lapangan</option>
                    </select>
                    @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="location" class="mb-1 block text-sm font-medium text-slate-700">Lokasi</label>
                    <input id="location" name="location" type="text" required maxlength="120"
                           value="{{ old('location', $facility->location) }}"
                           placeholder="Gedung / area"
                           class="w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]">
                    @error('location')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="capacity" class="mb-1 block text-sm font-medium text-slate-700">Kapasitas</label>
                    <input id="capacity" name="capacity" type="number" required min="1" max="100000"
                           value="{{ old('capacity', $facility->capacity) }}"
                           class="w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]">
                    @error('capacity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Deskripsi</label>
                    <textarea id="description" name="description" rows="4" maxlength="2000"
                              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]"
                              placeholder="Informasi tambahan fasilitas (opsional)">{{ old('description', $facility->description) }}</textarea>
                    @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="photo" class="mb-1 block text-sm font-medium text-slate-700">Foto fasilitas</label>
                    <div class="mb-2 flex items-center gap-3">
                        @if ($facility->photo !== null)
                            <img src="{{ Storage::disk('public')->url($facility->photo) }}" alt="Foto saat ini {{ $facility->name }}"
                                 id="photo-preview" class="aspect-[16/9] w-full max-w-xs rounded-lg border border-slate-200 object-cover">
                            <p class="text-xs text-slate-500">Foto saat ini. Unggah file baru untuk menggantinya.</p>
                        @else
                            <img id="photo-preview" alt="Pratinjau foto fasilitas"
                                 class="hidden aspect-[16/9] w-full max-w-xs rounded-lg border border-slate-200 object-cover">
                            <p class="text-xs text-slate-500">Belum ada foto. Unggah untuk menambahkan.</p>
                        @endif
                    </div>
                    <input id="photo" name="photo" type="file" accept=".jpg,.jpeg,.png" data-image-preview="photo-preview"
                           class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 file:mr-3 file:rounded-lg file:border-0 file:bg-[#F2F3FF] file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-[#0051d5] transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]">
                    <p class="mt-1 text-xs text-slate-500">Format JPG/PNG, maksimal 2 MB. (opsional)</p>
                    @error('photo')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-2">
                <a href="{{ route('admin.fasilitas.index') }}"
                   class="inline-flex h-10 items-center justify-center rounded-lg border border-[#D6DDF8] bg-white px-4 text-sm font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF]">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-[#0051d5] px-4 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#00236f] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0051d5] focus-visible:ring-offset-2">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection
