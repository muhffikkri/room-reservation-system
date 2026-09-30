@extends('layouts.public-landing')

@section('title', $facility->name . ' - Detail Fasilitas')

@section('content')
    <a href="{{ route('fasilitas.index') }}" class="clay-button-white inline-flex min-h-11 items-center rounded-full px-5 text-sm font-semibold text-blue-700">← Kembali ke Katalog</a>

    <div class="grid gap-5 lg:grid-cols-3">
        <section class="landing-panel overflow-hidden rounded-3xl lg:col-span-2" aria-labelledby="facility-heading">
            <img src="{{ $facility->display_image_url }}" alt="{{ $facility->name }}" decoding="async" class="aspect-video w-full bg-blue-50 object-cover">
            <div class="space-y-6 p-5 sm:p-7">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ $types[$facility->type] ?? ucfirst($facility->type) }}</span>
                    <x-ui.badge :status="$facility->status" dot />
                </div>
                <div>
                    <h1 id="facility-heading" class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $facility->name }}</h1>
                    <p class="mt-2 text-sm text-slate-600">{{ $facility->location }} · Kapasitas {{ $facility->capacity }} Orang</p>
                </div>
                <div class="space-y-2 border-t border-blue-100 pt-5">
                    <h2 class="text-base font-bold text-slate-900">Deskripsi & Fasilitas</h2>
                    <p class="whitespace-pre-line break-words text-sm leading-relaxed text-slate-600">{{ $facility->description ? $facility->short_description : 'Belum ada deskripsi tambahan untuk fasilitas ini.' }}</p>
                </div>
            </div>
        </section>
        <div class="space-y-5">
            <section class="landing-panel space-y-5 rounded-3xl p-5 sm:p-7" aria-labelledby="availability-heading">
                <h2 id="availability-heading" class="text-lg font-bold text-slate-900">Aksi & Ketersediaan</h2>
                @if ($facility->status === 'aktif')
                    <p class="text-sm leading-relaxed text-slate-600">Fasilitas Aktif. Fasilitas ini siap digunakan dan dapat direservasi pada jam operasional kampus.</p>
                @elseif ($facility->status === 'perbaikan')
                    <p class="text-sm leading-relaxed text-slate-600">Sedang Dalam Perbaikan. Fasilitas ini sedang dalam masa perawatan/perbaikan sehingga tidak dapat dipesan sementara waktu.</p>
                @else
                    <p class="text-sm leading-relaxed text-slate-600">Fasilitas Non-aktif. Fasilitas ini sedang dinonaktifkan oleh administrator sistem.</p>
                @endif
                <a href="{{ route('fasilitas.jadwal', $facility) }}" class="landing-button inline-flex min-h-11 w-full items-center justify-center rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-3 text-center text-sm font-semibold text-white">Lihat Jadwal Slot Ketersediaan</a>
                @if ($facility->status === 'aktif')
                    @if (!auth()->check() || auth()->user()->role === 'pengguna')
                        <a href="{{ route('reservasi.create', ['facility_id' => $facility->id]) }}" class="clay-button-white inline-flex min-h-11 w-full items-center justify-center rounded-full px-5 py-3 text-center text-sm font-semibold text-blue-700">{{ auth()->check() ? 'Ajukan Reservasi' : 'Login untuk Mengajukan Reservasi' }}</a>
                    @endif
                @else
                    <p class="rounded-2xl bg-slate-100 p-3 text-center text-xs font-semibold text-slate-500">Reservasi Tidak Tersedia</p>
                @endif
            </section>
            <section class="landing-panel space-y-3 rounded-3xl p-5 sm:p-7" aria-labelledby="rules-heading">
                <h2 id="rules-heading" class="text-lg font-bold text-slate-900">Ketentuan Penggunaan</h2>
                <ul class="list-inside list-disc space-y-3 text-sm leading-relaxed text-slate-600">
                    <li><strong>Jam Operasional:</strong> Pukul 07.00 – 20.00 WIB (26 slot/hari, 30 menit per slot).</li>
                    <li><strong>Batas Pengajuan:</strong> Reservasi diajukan minimal 1 jam sebelum waktu mulai.</li>
                    <li><strong>Privasi Publik:</strong> Jadwal dan ketersediaan dapat dilihat tanpa menampilkan identitas pemohon.</li>
                </ul>
            </section>
        </div>
    </div>
@endsection
