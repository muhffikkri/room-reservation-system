@props([
    'slots' => [],              // array precomputed dari Controller: [['start' => '07:00', 'end' => '07:30', 'state' => 'available'], ...]
    'selectable' => false,       // false = mode publik/read-only (dipakai fasilitas.jadwal)
    'name' => 'start_time',      // dipakai hanya kalau selectable = true (untuk form Orang 3)
    'selectedValue' => null,
])

@php
    $stateClasses = [
        'available' => 'border-slate-300 bg-white text-slate-700 hover:border-blue-500 hover:bg-blue-50',
        'selected'  => 'border-blue-900 bg-blue-900 text-white',
        'booked'    => 'cursor-not-allowed border-red-200 bg-red-50 text-red-500',
        'inactive'  => 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400',
        'past'      => 'cursor-not-allowed border-transparent bg-slate-50 text-slate-300',
    ];
    $stateLabels = [
        'available' => 'Tersedia',
        'selected'  => 'Dipilih',
        'booked'    => 'Terisi',
        'inactive'  => 'Tidak aktif',
        'past'      => 'Sudah lewat',
    ];
@endphp

<div>
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6">
        @foreach ($slots as $slot)
            @php
                $state = $slot['state'] ?? 'inactive';
                $isChecked = $selectable && (string) $selectedValue === (string) ($slot['start'] ?? null);
                $effectiveState = $isChecked ? 'selected' : $state;
                $isClickable = $selectable && $state === 'available';
            @endphp

            @if ($isClickable)
                <label class="block">
                    <input type="radio" name="{{ $name }}" value="{{ $slot['start'] }}" class="peer sr-only" @checked($isChecked)>
                    <span class="flex h-11 w-full cursor-pointer items-center justify-center rounded-lg border text-sm font-medium transition-colors peer-focus-visible:ring-2 peer-focus-visible:ring-blue-500 peer-focus-visible:ring-offset-2 {{ $stateClasses[$effectiveState] }}">
                        {{ $slot['start'] }}
                    </span>
                </label>
            @else
                <span class="flex h-11 w-full items-center justify-center rounded-lg border text-sm font-medium {{ $stateClasses[$effectiveState] }}"
                      title="{{ $stateLabels[$effectiveState] ?? '' }}">
                    {{ $slot['start'] ?? '' }}
                </span>
            @endif
        @endforeach
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-slate-600">
        <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded border border-slate-300 bg-white"></span> Tersedia</span>
        <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded border border-red-200 bg-red-50"></span> Terisi</span>
        <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded border border-slate-200 bg-slate-100"></span> Tidak Aktif</span>
    </div>
</div>