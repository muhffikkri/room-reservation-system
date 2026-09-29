@php
    $images = [
        'aula' => 'aula.jpg',
        'laboratorium' => 'lab-komputer.jpg',
        'lapangan' => 'lapangan-futsal.jpg',
        'ruang_kelas' => 'ruang-kelas.jpg',
        'alat' => 'proyektor.jpg',
    ];
@endphp
<article class="landing-card group flex flex-col overflow-hidden rounded-3xl border border-white/80" data-facility-id="{{ $facility->id }}">
    <div class="relative aspect-[16/9] overflow-hidden bg-[#f2f3ff]">
        <img src="{{ $facility->photo_url ?: asset('images/'.($images[$facility->type] ?? 'aula.jpg')) }}" alt="{{ $facility->name }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
        @include('landing._facility-status', ['status' => $facility->status, 'class' => 'absolute right-3 top-3'])
    </div>
    <div class="flex flex-1 flex-col justify-between gap-4 p-4 sm:p-5">
        <div class="space-y-2">
            <h2 class="text-base font-bold text-slate-900">{{ $facility->name }}</h2>
            <span class="inline-block rounded-full border border-blue-100 bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-600">{{ $types[$facility->type] ?? ucfirst($facility->type) }}</span>
            <p class="text-sm text-[#475569]">{{ $facility->location }} · Kapasitas {{ $facility->capacity }} orang</p>
            @if ($facility->description)<p class="text-xs leading-relaxed text-slate-500">{{ \Illuminate\Support\Str::limit($facility->description, 110) }}</p>@endif
        </div>
        <a href="{{ route('fasilitas.jadwal', ['facility' => $facility, 'from' => $from ?? 'all']) }}" class="landing-button flex h-10 w-full items-center justify-center gap-2 rounded-xl border border-blue-200 bg-white/80 text-xs font-semibold text-blue-700 hover:bg-blue-50">Lihat Jadwal</a>
    </div>
</article>
