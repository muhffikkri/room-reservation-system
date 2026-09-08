@props([
    'name' => null,
    'id' => null,
    'placeholder' => null,
    'options' => [],
    'selected' => null,
])

@php
    $selectId = $id ?? $name;
    $hasError = $name && $errors->has($name);
@endphp

<div>
    <div class="relative">
        <select
            @if ($name) name="{{ $name }}" @endif
            @if ($selectId) id="{{ $selectId }}" @endif
            {{ $attributes->merge([
                'class' => 'h-10 w-full appearance-none rounded-lg border bg-white pl-3 pr-9 text-sm text-slate-900 transition focus:outline-none focus:ring-4 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500 '
                    . ($hasError
                        ? 'border-red-500 focus:border-red-500 focus:ring-red-100'
                        : 'border-slate-300 focus:border-blue-500 focus:ring-blue-100'),
            ]) }}
        >
            @if ($placeholder)
                <option value="" @selected(is_null($selected))>{{ $placeholder }}</option>
            @endif

            @if (count($options) > 0)
                @foreach ($options as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" @selected((string) $selected === (string) $optionValue)>
                        {{ $optionLabel }}
                    </option>
                @endforeach
            @else
                {{ $slot }}
            @endif
        </select>

        <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
             viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>

    @if ($hasError)
        <p class="mt-1.5 text-xs text-red-600">{{ $errors->first($name) }}</p>
    @endif
</div>