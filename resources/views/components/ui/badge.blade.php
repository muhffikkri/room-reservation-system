@props([
    'status' => null,
    'dot' => false,
])

@php
    // Mapping terpusat lintas entitas — reservasi, laporan, fasilitas, akun.
    // Warna diambil dari DESIGN.md §3 & §4 (skema 50/700/200 sesuai contoh kelas siap-pakai §4.1).
    $palette = match ($status) {
        'aktif', 'approved', 'selesai' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'ring' => 'ring-green-200', 'dot' => 'bg-green-500'],
        'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'ring' => 'ring-amber-200', 'dot' => 'bg-amber-500'],
        'diproses' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'ring' => 'ring-blue-200', 'dot' => 'bg-blue-500'],
        'baru' => ['bg' => 'bg-cyan-50', 'text' => 'text-cyan-700', 'ring' => 'ring-cyan-200', 'dot' => 'bg-cyan-500'],
        'perbaikan', 'cancelled_by_officer' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'ring' => 'ring-orange-200', 'dot' => 'bg-orange-500'],
        'rejected', 'ditolak' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'ring' => 'ring-red-200', 'dot' => 'bg-red-500'],
        'nonaktif', 'cancelled_by_user' => ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'ring' => 'ring-slate-200', 'dot' => 'bg-slate-400'],
        default => ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'ring' => 'ring-slate-200', 'dot' => 'bg-slate-400'],
    };
@endphp

<span {{ $attributes->merge([
    'class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {$palette['bg']} {$palette['text']} {$palette['ring']}",
]) }}>
    @if ($dot)
        <span class="h-1.5 w-1.5 rounded-full {{ $palette['dot'] }}"></span>
    @endif
    {{ $slot }}
</span>