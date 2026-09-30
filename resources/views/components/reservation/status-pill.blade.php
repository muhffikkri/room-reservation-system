@props([
    'status',
    'plain' => false,
])

@php
    // Warna pill adalah presentasi dan tetap tinggal di view layer; labelnya
    // diambil dari kosakata Reservation agar tidak ada daftar status ganda.
    $palette = match ($status) {
        'pending' => ['bg-amber-50', 'text-amber-700', 'ring-amber-200'],
        'approved' => ['bg-green-50', 'text-green-700', 'ring-green-200'],
        'rejected' => ['bg-red-50', 'text-red-700', 'ring-red-200'],
        'rejected_by_system' => ['bg-violet-50', 'text-violet-700', 'ring-violet-200'],
        'cancelled_by_system' => ['bg-violet-50', 'text-violet-700', 'ring-violet-200'],
        'cancelled_by_user' => ['bg-slate-100', 'text-slate-600', 'ring-slate-200'],
        // cancelled_by_officer dan status yang belum dikenal tetap oranye,
        // persis seperti rantai @else yang sebelumnya mencakup keduanya.
        default => ['bg-orange-50', 'text-orange-700', 'ring-orange-200'],
    };
@endphp

{{-- Varian "plain" untuk kolom status tabel: hanya teks bewarna status tanpa
     fill dan ring, sehingga label tetap satu pemilik dengan pill. --}}
@if ($plain)
    <span {{ $attributes->merge(['class' => "text-xs font-extrabold {$palette[1]}"]) }}>{{ \App\Models\Reservation::statusLabel($status) }}</span>
@else
    <span class="inline-flex items-center rounded-full {{ $palette[0] }} px-2.5 py-1 text-xs font-semibold {{ $palette[1] }} ring-1 ring-inset {{ $palette[2] }}">{{ \App\Models\Reservation::statusLabel($status) }}</span>
@endif
