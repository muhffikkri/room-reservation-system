@php
    $statusClasses = [
        'aktif' => 'bg-green-50 text-green-700 ring-green-200',
        'perbaikan' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'nonaktif' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];
@endphp
<span class="{{ $class ?? '' }} inline-flex items-center gap-1 rounded-full bg-white/95 px-2.5 py-1 text-xs font-medium shadow-sm ring-1 {{ $statusClasses[$status] ?? $statusClasses['nonaktif'] }}">
    @if ($status === 'aktif')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
    @elseif ($status === 'perbaikan')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a4 4 0 0 0-5.6 5.6L4 17l3 3 5.1-5.1a4 4 0 0 0 5.6-5.6L15 12l-3-3 2.7-2.7Z"/></svg>
    @else
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="m5.6 5.6 12.8 12.8"/></svg>
    @endif
    {{ ucfirst($status) }}
</span>
