@extends('layouts.app')

@use('App\Models\Facility')

@section('title', 'Tambah Fasilitas')

@section('content')
    <div class="mx-auto max-w-3xl space-y-5">
        <section class="auth-clay-card rounded-[2rem] p-5 sm:p-7 lg:p-8" aria-labelledby="facility-create-heading">
            <div class="border-b border-blue-100/80 pb-6">
                <a href="{{ route('admin.fasilitas.index') }}"
                    class="clay-button-white mb-3 inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold text-blue-700">
                    &larr; Kembali ke daftar fasilitas
                </a>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Master data</p>
                <h1 id="facility-create-heading"
                    class="mt-1 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">Tambah Fasilitas</h1>
                <p class="mt-1 text-sm leading-relaxed text-slate-600">Fasilitas baru langsung berstatus aktif dan dapat
                    direservasi.</p>
            </div>

            <form method="POST" action="{{ route('admin.fasilitas.store') }}" enctype="multipart/form-data" data-validate
                class="mt-7 grid gap-5 sm:grid-cols-2">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700">Nama fasilitas</label>
                    <input id="name" name="name" type="text" required maxlength="120" value="{{ old('name') }}"
                        class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                    @error('name')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-semibold text-slate-700">Tipe</label>
                    <select id="type" name="type" required
                        class="landing-input mt-1.5 block h-11 w-full rounded-2xl px-4 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                        <option value="">Pilih tipe</option>
                        @foreach (Facility::TYPE_LABELS as $typeValue => $typeLabel)
                            <option value="{{ $typeValue }}" @selected(old('type') === $typeValue)>{{ $typeLabel }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="location" class="block text-sm font-semibold text-slate-700">Lokasi</label>
                    <input id="location" name="location" type="text" required maxlength="120" value="{{ old('location') }}"
                        placeholder="Gedung / area"
                        class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                    @error('location')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="capacity" class="block text-sm font-semibold text-slate-700">Kapasitas</label>
                    <input id="capacity" name="capacity" type="number" required min="1" max="100000"
                        value="{{ old('capacity') }}"
                        class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                    @error('capacity')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-semibold text-slate-700">Deskripsi</label>
                    <textarea id="description" name="description" rows="4" maxlength="2000"
                        placeholder="Informasi tambahan fasilitas (opsional)"
                        class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-blue-500/15">{{ old('description') }}</textarea>
                    <p class="mt-1.5 text-xs text-slate-500">Maksimal {{ \App\Models\Facility::MAX_DESCRIPTION_WORDS }} kata agar card fasilitas tetap rapi.</p>
                    @error('description')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="photo" class="block text-sm font-semibold text-slate-700">Foto fasilitas</label>
                    <p class="mb-1.5 text-xs text-slate-500">Format JPG/PNG, maksimal 2 MB. (opsional)</p>
                    <input id="photo" name="photo" type="file" accept=".jpg,.jpeg,.png" data-image-preview="photo-preview"
                        class="landing-input block w-full rounded-2xl px-3 py-2.5 text-xs text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-blue-600 file:px-4 file:py-1.5 file:text-xs file:font-bold file:text-white hover:file:bg-blue-700">
                    <img id="photo-preview" alt="Pratinjau foto fasilitas"
                        class="mt-3 hidden aspect-[16/9] w-full max-w-xs rounded-2xl border border-white object-cover shadow-[0_8px_20px_rgba(112,151,205,0.18)]">
                    @error('photo')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div
                    class="flex flex-wrap items-center justify-end gap-3 border-t border-blue-100/80 pt-5 sm:col-span-2">
                    <a href="{{ route('admin.fasilitas.index') }}"
                        class="clay-button-white inline-flex items-center justify-center rounded-full px-6 py-2.5 text-sm font-semibold text-blue-700">
                        Batal
                    </a>
                    <button type="submit" data-submit-loading data-loading-label="Menyimpan..."
                        class="landing-button inline-flex items-center justify-center rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-7 py-2.5 text-sm font-bold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)]">
                        Simpan Fasilitas
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
