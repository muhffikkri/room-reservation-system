@props([
    'sticky' => false,
])

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-full text-left text-sm">
            @isset($head)
                <thead @class([
                    'bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600',
                    'sticky top-0 z-10' => $sticky,
                ])>
                    <tr>{{ $head }}</tr>
                </thead>
            @endisset

            <tbody class="divide-y divide-slate-100">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>