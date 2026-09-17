@extends('layouts.app')

@section('title', 'Buat Laporan Kerusakan')

@section('content')
<div class="mx-auto max-w-2xl space-y-8">
    <div>
        <a href="{{ route('laporan.index') }}" class="inline-flex items-center text-sm font-medium text-slate-500 transition hover:text-[#00236f]">
            &larr; Kembali ke daftar laporan
        </a>
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Pelaporan fasilitas</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#00236f]">Buat Laporan Kerusakan Baru</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Isi formulir di bawah ini untuk melaporkan masalah atau kerusakan fasilitas kampus.</p>
    </div>

    <div class="rounded-2xl border border-[#E2E7FF] bg-white p-6 shadow-[0_14px_40px_rgba(15,23,42,0.06)] sm:p-8">
        <form method="POST" action="{{ route('laporan.store') }}" enctype="multipart/form-data" class="space-y-7">
            @csrf

            {{-- Fasilitas --}}
            <div>
                <label for="facility_id" class="block text-sm font-medium text-slate-700">Fasilitas Kampus <span class="text-rose-500">*</span></label>
                <select id="facility_id" name="facility_id" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-[#0051d5] focus:outline-none focus:ring-1 focus:ring-[#0051d5]">
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
                <select id="category" name="category" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-[#0051d5] focus:outline-none focus:ring-1 focus:ring-[#0051d5]">
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
                <p class="mb-1 text-xs text-slate-500">Jelaskan detail lokasi dan kondisi kerusakan secara rinci (minimal 15 karakter).</p>
                <textarea id="description" name="description" rows="4" minlength="15" maxlength="2000" required placeholder="Contoh: Proyektor di Ruang B-201 mati total dan tidak mengeluarkan tampilan gambar saat dinyalakan." class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-[#0051d5] focus:outline-none focus:ring-1 focus:ring-[#0051d5]">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Upload Foto --}}
            <div>
                <label for="photo" class="block text-sm font-medium text-slate-700">Foto Bukti Kerusakan (Opsional)</label>
                <p class="mb-1 text-xs text-slate-500">Format: JPG, JPEG, PNG (Maksimal 2 MB).</p>
                <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/jpg" class="block w-full text-sm text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-[#F2F3FF] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#0051d5] hover:file:bg-[#E2E7FF]">
                @error('photo')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Buttons --}}
            <div class="flex items-center justify-end gap-3 border-t border-[#EEF2FF] pt-5">
                <a href="{{ route('laporan.index') }}" class="rounded-lg border border-[#D6DDF8] bg-white px-4 py-2 text-sm font-medium text-[#00236f] shadow-sm transition hover:bg-[#F2F3FF] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">
                    Batal
                </a>
                <button type="submit" data-submit-loading data-loading-label="Mengirim..."
                    class="rounded-lg bg-[#0051d5] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#00236f] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">
                    Kirim Laporan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
