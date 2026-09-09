@extends('layouts.app')

@section('title', 'Buat Laporan Kerusakan')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="{{ route('laporan.index') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-700">
            &larr; Kembali ke daftar laporan
        </a>
        <h1 class="mt-2 text-2xl font-bold text-slate-800">Buat Laporan Kerusakan Baru</h1>
        <p class="text-sm text-slate-500">Isi formulir di bawah ini untuk melaporkan masalah atau kerusakan fasilitas kampus.</p>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('laporan.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Fasilitas --}}
            <div>
                <label for="facility_id" class="block text-sm font-medium text-slate-700">Fasilitas Kampus <span class="text-rose-500">*</span></label>
                <select id="facility_id" name="facility_id" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-sm">
                    <option value="" disabled {{ old('facility_id') ? '' : 'selected' }}>-- Pilih Fasilitas --</option>
                    @foreach($facilities as $facility)
                        <option value="{{ $facility->id }}" {{ old('facility_id') == $facility->id ? 'selected' : '' }}>
                            {{ $facility->name }} ({{ $facility->location }})
                        </option>
                    @endforeach
                </select>
                @error('facility_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kategori Kerusakan --}}
            <div>
                <label for="category" class="block text-sm font-medium text-slate-700">Kategori Kerusakan <span class="text-rose-500">*</span></label>
                <select id="category" name="category" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-sm">
                    <option value="" disabled {{ old('category') ? '' : 'selected' }}>-- Pilih Kategori --</option>
                    <option value="kerusakan_alat" {{ old('category') === 'kerusakan_alat' ? 'selected' : '' }}>Kerusakan Alat</option>
                    <option value="listrik" {{ old('category') === 'listrik' ? 'selected' : '' }}>Kelistrikan / Lampu / AC</option>
                    <option value="kebersihan" {{ old('category') === 'kebersihan' ? 'selected' : '' }}>Kebersihan</option>
                    <option value="sarana_prasarana" {{ old('category') === 'sarana_prasarana' ? 'selected' : '' }}>Sarana & Prasarana (Meja, Kursi, Pintu)</option>
                    <option value="lainnya" {{ old('category') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
                @error('category')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deskripsi Kerusakan --}}
            <div>
                <label for="description" class="block text-sm font-medium text-slate-700">Deskripsi Kerusakan <span class="text-rose-500">*</span></label>
                <p class="text-xs text-slate-500 mb-1">Jelaskan detail lokasi dan kondisi kerusakan secara rinci (minimal 15 karakter).</p>
                <textarea id="description" name="description" rows="4" minlength="15" maxlength="2000" required placeholder="Contoh: Proyektor di Ruang B-201 mati total dan tidak mengeluarkan tampilan gambar saat dinyalakan." class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-sm">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Upload Foto --}}
            <div>
                <label for="photo" class="block text-sm font-medium text-slate-700">Foto Bukti Kerusakan (Opsional)</label>
                <p class="text-xs text-slate-500 mb-1">Format: JPG, JPEG, PNG (Maksimal 2 MB).</p>
                <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/jpg" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                @error('photo')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Buttons --}}
            <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-5">
                <a href="{{ route('laporan.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none">
                    Batal
                </a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Kirim Laporan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
