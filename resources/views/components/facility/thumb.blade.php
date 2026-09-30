@props([
    'facility',
    'size' => 'h-11 w-11',
])

@php
    // Foto fasilitas yang diunggah, atau gambar bawaan sesuai tipenya.
    // Peta gambar bawaan ini satu-satunya pemiliknya; halaman lain cukup
    // memakai komponen ini.
    $fallbackImages = [
        'aula' => 'aula.webp',
        'laboratorium' => 'lab-komputer.webp',
        'lapangan' => 'lapangan-futsal.webp',
        'ruang_kelas' => 'ruang-kelas.webp',
        'alat' => 'proyektor.webp',
    ];
@endphp

<span
    class="flex {{ $size }} shrink-0 items-center justify-center overflow-hidden rounded-xl border border-blue-100 bg-white p-1 shadow-sm">
    <img src="{{ $facility?->photo_url ?: asset('images/'.($fallbackImages[$facility?->type] ?? 'aula.webp')) }}"
        alt="{{ $facility?->name ?? 'Fasilitas' }}" loading="lazy" class="h-full w-full rounded-lg object-cover">
</span>
