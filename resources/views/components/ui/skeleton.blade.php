@props([
    'lines' => 1,
    'lineClass' => 'h-3 rounded bg-slate-200',
])

<div {{ $attributes->merge(['class' => 'animate-pulse space-y-2']) }} aria-hidden="true">
    @for ($i = 0; $i < $lines; $i++)
        <div class="{{ $lineClass }}"></div>
    @endfor
</div>