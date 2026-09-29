{{--
    Satu-satunya pemilik markup card grid fasilitas pada landing page.

    Dipakai oleh render server (landing/index.blade.php) dan oleh renderer
    pencarian langsung yang menerima HTML dari HomeController::ajaxFacilities.
    Karena itu hasil pencarian dan render awal tidak bisa berbeda: batas kata
    deskripsi, tinggi blok deskripsi, dan tautan detail semuanya hidup di sini.
--}}
<article class="landing-card group facility-card flex h-full flex-col overflow-hidden rounded-3xl border border-white/80 bg-white shadow-[0_10px_30px_-5px_rgba(186,215,248,0.45),0_0_0_1px_rgba(255,255,255,0.8)_inset] transition-all hover:-translate-y-0.5 hover:shadow-lg" data-facility-id="{{ $facility->id }}">
    <div class="relative aspect-[16/9] w-full overflow-hidden bg-[#f2f3ff]">
        <img src="{{ $facility->display_image_url }}" alt="{{ $facility->name }}" loading="lazy" class="img-fade h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
        @include('landing._facility-status', ['status' => $facility->status, 'class' => 'absolute right-3 top-3'])
    </div>
    <div class="flex flex-1 flex-col justify-between gap-4 p-4 sm:p-5">
        <div class="space-y-2">
            <h3 class="text-base font-bold text-slate-900">{{ $facility->name }}</h3>
            <span class="inline-block rounded-full border border-blue-100 bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-600">{{ \App\Models\Facility::TYPE_LABELS[$facility->type] ?? \Illuminate\Support\Str::headline($facility->type) }}</span>
            <div class="space-y-1 pt-1">
                <div class="flex items-center gap-2 text-sm text-[#475569]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 text-[#94A3B8]" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.5 7-11a7 7 0 10-14 0c0 5.5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>
                    </svg>
                    <span>{{ $facility->location }}</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-[#475569]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 text-[#94A3B8]" aria-hidden="true">
                        <circle cx="9" cy="8" r="3.5"/><path stroke-linecap="round" d="M3 20v-1a6 6 0 0112 0v1M16 8.5a3 3 0 010 5.5M17.5 15.2a6 6 0 013.5 4.8"/>
                    </svg>
                    <span>Kapasitas: {{ $facility->capacity }} orang</span>
                </div>
                {{-- Blok ini punya min-height agar tinggi card tetap sama walau deskripsi
                     singkat atau kosong, dan line-clamp menahan teks panjang. --}}
                <div class="flex min-h-[5rem] flex-col gap-1 pt-1">
                    <p class="line-clamp-3 text-xs leading-relaxed text-slate-500">{{ $facility->short_description }}</p>
                    @if ($facility->description)
                        <a href="{{ route('fasilitas.show', ['facility' => $facility]) }}" class="mt-auto w-fit text-[11px] font-semibold text-blue-600 transition-colors hover:text-blue-700">Lihat Deskripsi Lengkap</a>
                    @endif
                </div>
            </div>
        </div>
        <a href="{{ route('fasilitas.jadwal', ['facility' => $facility, 'from' => 'home']) }}"
                class="landing-button flex h-10 w-full items-center justify-center gap-1.5 rounded-xl border border-blue-200 bg-white/80 text-xs font-semibold text-blue-700 transition-colors hover:bg-blue-50">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-[18px] w-[18px]" aria-hidden="true">
                <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4m8-4v4M3 10h18"/>
            </svg>
            Lihat Jadwal
        </a>
    </div>
</article>
