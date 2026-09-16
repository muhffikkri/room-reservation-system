@props([
    'padded' => true,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-white shadow-sm']) }}>
    @isset($header)
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            {{ $header }}
        </div>
    @endisset

    <div @class(['p-5 sm:p-6' => $padded])>
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="border-t border-slate-200 px-5 py-4 sm:px-6">
            {{ $footer }}
        </div>
    @endisset
</div>