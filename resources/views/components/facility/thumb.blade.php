@props([
    'facility',
    'size' => 'h-11 w-11',
])

{{--
    Peta gambar bawaan milik Facility::TYPE_FALLBACK_IMAGES lewat accessor
    display_image_url, jadi komponen ini tidak punya daftar sendiri.
--}}
<span
    class="flex {{ $size }} shrink-0 items-center justify-center overflow-hidden rounded-xl border border-blue-100 bg-white p-1 shadow-sm">
    <img src="{{ $facility?->display_image_url }}"
        alt="{{ $facility?->name ?? 'Fasilitas' }}" loading="lazy" class="h-full w-full rounded-lg object-cover">
</span>
