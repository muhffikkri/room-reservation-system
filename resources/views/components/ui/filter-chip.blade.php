@props([
    'href',
    'label' => null,
    'active' => false,
    'tab' => null,
])

@php
    // Satu pemilik gaya filter/tab: biru untuk terpilih, putih clay untuk yang
    // lain. Tab antrean reservasi juga=data-queue-tab supaya app.js bisa
    // menukar kelas active/inactive dari atribut ini, bukan dari salinan kelas.
    $chipBase = 'shrink-0 rounded-full px-4 py-2 text-xs font-bold';
    $chipActive = 'landing-button bg-gradient-to-r from-blue-600 to-blue-500 text-white';
    $chipInactive = 'clay-button-white text-slate-600';
@endphp

<a href="{{ $href }}" @class([$chipBase, $active ? $chipActive : $chipInactive])
    @if ($tab !== null)
        data-queue-tab="{{ $tab }}"
        data-tab-active="{{ $chipActive }}"
        data-tab-inactive="{{ $chipInactive }}"
    @endif
    @if ($active) aria-current="page" @endif>
    {{ $slot->isEmpty() ? $label : $slot }}
</a>
