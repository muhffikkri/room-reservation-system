<article class="landing-card group flex h-full flex-col overflow-hidden rounded-3xl border border-white/80" data-facility-id="{{ $facility->id }}">
    <div class="relative aspect-[16/9] overflow-hidden bg-[#f2f3ff]">
        <img src="{{ $facility->display_image_url }}" alt="{{ $facility->name }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">        @include('landing._facility-status', ['status' => $facility->status, 'class' => 'absolute right-3 top-3'])
    </div>
    <div class="flex flex-1 flex-col justify-between gap-4 p-4 sm:p-5">
        <div class="space-y-2">
            <h2 class="text-base font-bold text-slate-900">{{ $facility->name }}</h2>
            <span class="inline-block rounded-full border border-blue-100 bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-600">{{ \App\Models\Facility::TYPE_LABELS[$facility->type] ?? \Illuminate\Support\Str::headline($facility->type) }}</span>
            <p class="text-sm text-[#475569]">{{ $facility->location }} · Kapasitas {{ $facility->capacity }} orang</p>
            {{-- min-height menjaga tinggi card seragam walau deskripsi kosong atau
                 pendek; pemotongan kata punya satu pemilik di model. --}}
            <p class="line-clamp-3 min-h-[3.75rem] text-xs leading-relaxed text-slate-500">{{ $facility->short_description }}</p>
        </div>
        <a href="{{ route('fasilitas.jadwal', ['facility' => $facility, 'from' => $from ?? 'all']) }}" class="clay-button-white flex h-10 w-full items-center justify-center gap-2 rounded-xl text-xs font-semibold text-blue-700">Lihat Jadwal</a>
    </div>
</article>
