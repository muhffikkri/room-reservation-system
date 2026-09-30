@extends('layouts.app')

@section('title', 'Buat Laporan Kerusakan')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="auth-clay-card rounded-[2rem] p-5 sm:p-7 lg:p-8">
            <div class="mb-7 border-b border-blue-100/80 pb-6">
                <a href="{{ route('laporan.index') }}"
                    class="clay-pressable mb-3 inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold text-blue-700">
                    &larr; Kembali ke daftar laporan
                </a>
                <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Pelaporan fasilitas</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">Buat Laporan Kerusakan
                    Baru</h1>
                <p class="mt-1 text-sm leading-relaxed text-slate-600">Isi formulir di bawah ini untuk melaporkan masalah
                    atau kerusakan fasilitas kampus.</p>
            </div>

            <form method="POST" action="{{ route('laporan.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Fasilitas --}}
                <div>
                    <label for="facility_id" class="block text-sm font-semibold text-slate-700">Fasilitas Kampus <span
                            class="text-rose-500">*</span></label>
                    <select id="facility_id" name="facility_id" required
                        class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                        <option value="" disabled {{ old('facility_id') ? '' : 'selected' }}>-- Pilih Fasilitas --
                        </option>
                        @foreach ($facilities as $facility)
                            <option value="{{ $facility->id }}" {{ old('facility_id') == $facility->id ? 'selected' : '' }}>
                                {{ $facility->name }} ({{ $facility->location }})
                            </option>
                        @endforeach
                    </select>
                    @error('facility_id')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kategori Kerusakan --}}
                <div>
                    <label for="category" class="block text-sm font-semibold text-slate-700">Kategori Kerusakan <span
                            class="text-rose-500">*</span></label>
                    <select id="category" name="category" required
                        class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                        <option value="" disabled {{ old('category') ? '' : 'selected' }}>-- Pilih Kategori --
                        </option>
                        @foreach (\App\Models\Report::CATEGORIES as $value => $label)
                            <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>
                                {{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Deskripsi Kerusakan --}}
                <div>
                    <label for="description" class="block text-sm font-semibold text-slate-700">Deskripsi Kerusakan <span
                            class="text-rose-500">*</span></label>
                    <p class="mb-1 text-xs text-slate-500">Jelaskan detail lokasi dan kondisi kerusakan secara rinci
                        (minimal 15 karakter).</p>
                    <textarea id="description" name="description" rows="4" minlength="15" maxlength="2000" required
                        placeholder="Contoh: Proyektor di Ruang B-201 mati total dan tidak mengeluarkan tampilan gambar saat dinyalakan."
                        class="landing-input block w-full rounded-2xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/15">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Upload Foto --}}
                <div>
                    <label for="photo" class="block text-sm font-semibold text-slate-700">Foto Bukti Kerusakan
                        (Opsional)</label>
                    <p class="mb-1 text-xs text-slate-500">Format: JPG, JPEG, PNG (Maksimal 2 MB).</p>
                    <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/jpg"
                        class="landing-input block w-full rounded-2xl px-3 py-2.5 text-xs text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-blue-600 file:px-4 file:py-1.5 file:text-xs file:font-bold file:text-white hover:file:bg-blue-700">
                    @error('photo')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Buttons --}}
                <div class="flex items-center justify-end gap-3 border-t border-blue-100/80 pt-5">
                    <a href="{{ route('laporan.index') }}"
                        class="clay-pressable inline-flex justify-center rounded-full px-6 py-2.5 text-sm font-semibold text-blue-700">
                        Batal
                    </a>
                    <button type="submit" data-submit-loading data-loading-label="Mengirim..."
                        class="landing-button rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-7 py-2.5 text-sm font-bold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)]">
                        Kirim Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
