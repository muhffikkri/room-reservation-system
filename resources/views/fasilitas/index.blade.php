@extends('layouts.public-landing')

@section('title', 'Semua Fasilitas Kampus - REKSA')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ ($from ?? 'home') === 'home' ? route('home') : route('fasilitas.index') }}" class="clay-button-white inline-flex items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold text-blue-700">← Kembali</a>
        <span class="rounded-full border border-white/80 bg-white/70 px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm">{{ $facilities->total() }} fasilitas</span>
    </div>

    <section class="landing-panel space-y-5 rounded-3xl p-5 shadow-[0_10px_30px_-5px_rgba(186,215,248,0.45),0_0_0_1px_rgba(255,255,255,0.8)_inset] sm:p-7">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-700">Eksplorasi kampus</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Semua Fasilitas Kampus</h1>
            <p class="mt-2 text-sm text-slate-600">Temukan fasilitas, lihat detailnya, lalu periksa jadwal ketersediaan.</p>
        </div>
        <form method="GET" action="{{ route('fasilitas.index') }}" data-facility-filters class="grid grid-cols-1 items-end gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <input type="hidden" name="from" value="{{ $from ?? 'home' }}">
            <div><label for="q" class="mb-1.5 block text-xs font-semibold text-slate-600">Pencarian</label><input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama fasilitas" data-search-filter class="landing-input h-10 w-full rounded-xl px-3 text-sm"></div>
            <div><label for="tipe" class="mb-1.5 block text-xs font-semibold text-slate-600">Jenis</label><select id="tipe" name="tipe" data-auto-filter class="landing-input h-10 w-full rounded-xl px-3 text-sm"><option value="">Semua Jenis</option>@foreach ($types as $value => $label)<option value="{{ $value }}" @selected(($filters['tipe'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div><label for="lokasi" class="mb-1.5 block text-xs font-semibold text-slate-600">Lokasi</label><select id="lokasi" name="lokasi" data-auto-filter class="landing-input h-10 w-full rounded-xl px-3 text-sm"><option value="">Semua Lokasi</option>@foreach ($locations as $location)<option value="{{ $location }}" @selected(($filters['lokasi'] ?? '') === $location)>{{ $location }}</option>@endforeach</select></div>
            <div><label for="kapasitas" class="mb-1.5 block text-xs font-semibold text-slate-600">Kapasitas</label><select id="kapasitas" name="kapasitas" data-auto-filter class="landing-input h-10 w-full rounded-xl px-3 text-sm"><option value="">Semua Kapasitas</option><option value="lt_40" @selected(($filters['kapasitas'] ?? '') === 'lt_40')>&lt; 40 orang</option><option value="40_100" @selected(($filters['kapasitas'] ?? '') === '40_100')>40–100 orang</option><option value="gt_100" @selected(($filters['kapasitas'] ?? '') === 'gt_100')>&gt; 100 orang</option></select></div>
            <a href="{{ route('fasilitas.index', ['from' => $from ?? 'home']) }}" class="clay-button-white inline-flex h-10 items-center justify-center rounded-xl px-4 text-sm font-semibold text-slate-700">Reset Filter</a>
        </form>
    </section>

    @if ($facilities->isEmpty())
        <div class="landing-panel rounded-3xl p-10 text-center"><h2 class="font-bold text-slate-900">Fasilitas tidak ditemukan</h2><p class="mt-1 text-sm text-slate-600">Ubah filter pencarian untuk melihat fasilitas lainnya.</p></div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">@foreach ($facilities as $facility)@include('landing._facility-card', ['facility' => $facility, 'types' => $types, 'from' => 'all'])@endforeach</div>
        <div class="landing-panel rounded-2xl p-4">{{ $facilities->links() }}</div>
    @endif
@endsection
