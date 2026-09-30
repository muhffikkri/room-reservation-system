@extends('layouts.public-landing')

@section('title', 'Jadwal ' . $facility->name . ' - REKSA')

@section('content')
    @php
        $typeLabel = $types[$facility->type] ?? ucfirst($facility->type);
        $facilityImage =
            $facility->photo_url ?:
            asset(
                'images/' .
                    ([
                        'aula' => 'aula.webp',
                        'laboratorium' => 'lab-komputer.webp',
                        'lapangan' => 'lapangan-futsal.webp',
                        'ruang_kelas' => 'ruang-kelas.webp',
                        'alat' => 'proyektor.webp',
                    ][$facility->type] ??
                        'aula.webp'),
            );
        $slotClasses = [
            'available' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
            'booked' => 'border-rose-200 bg-rose-50 text-rose-700',
            'past' => 'border-slate-200 bg-slate-100 text-slate-400',
            'inactive' => 'border-slate-200 bg-slate-100 text-slate-400',
        ];
        $slotLabels = [
            'available' => 'Tersedia',
            'booked' => 'Sudah direservasi',
            'past' => 'Waktu Lewat',
            'inactive' => 'Tidak Aktif',
        ];
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ ($from ?? 'all') === 'home' ? route('home') : route('fasilitas.index') }}"
            class="landing-button inline-flex items-center gap-2 rounded-full border border-blue-100 bg-white/80 px-4 py-2.5 text-sm font-semibold text-blue-700">←
            Kembali</a>
        <span
            class="rounded-full border border-white/80 bg-white/70 px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm">Jadwal
            fasilitas</span>
    </div>

    <section
        class="landing-panel grid gap-5 overflow-hidden rounded-3xl p-5 shadow-[0_10px_30px_-5px_rgba(186,215,248,0.45),0_0_0_1px_rgba(255,255,255,0.8)_inset] sm:grid-cols-[240px_1fr] sm:p-7">
        <div class="overflow-hidden rounded-2xl bg-blue-50"><img src="{{ $facilityImage }}" alt="{{ $facility->name }}"
                class="h-full min-h-48 w-full object-cover"></div>
        <div class="flex flex-col justify-center gap-3">
            <div class="flex flex-wrap items-center gap-2"><span
                    class="rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ $typeLabel }}</span><span
                    class="rounded-full px-3 py-1 text-xs font-semibold {{ $facility->status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($facility->status) }}</span>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $facility->name }}</h1>
            <p class="text-sm text-slate-600">{{ $facility->location }} <span class="px-1 text-blue-300">•</span> Kapasitas
                {{ $facility->capacity }} orang</p>
            @if ($facility->description)
                <p class="max-w-3xl text-sm leading-relaxed text-slate-600">{{ $facility->description }}</p>
            @endif
            @if ($facility->status !== 'aktif')
                <p class="rounded-xl border border-amber-100 bg-amber-50/80 p-3 text-xs text-amber-800">Fasilitas sedang
                    {{ $facility->status }} sehingga slot tidak dapat dipilih untuk reservasi.</p>
            @endif
        </div>
    </section>

    <section
        class="landing-panel space-y-5 rounded-3xl p-5 shadow-[0_10px_30px_-5px_rgba(186,215,248,0.45),0_0_0_1px_rgba(255,255,255,0.8)_inset] sm:p-7">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <h2 class="text-lg font-bold text-slate-900 sm:text-xl">Jadwal Ketersediaan</h2>
                <p class="mt-1 text-xs text-slate-600">Jam operasional 07.00–20.00 WIB · interval 30 menit</p>
            </div>
            <form method="GET" action="{{ route('fasilitas.jadwal', $facility) }}"
                class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="from" value="{{ $from ?? 'all' }}">
                <div><label for="date" class="mb-1.5 block text-xs font-semibold text-slate-600">Pilih
                        tanggal</label><input id="date" name="date" type="date"
                        min="{{ now()->toDateString() }}" value="{{ $selectedDate->toDateString() }}"
                        data-submit-on-change class="landing-input h-10 rounded-xl px-3 text-sm"></div>
            </form>
        </div>
        <div
            class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-blue-100 bg-blue-50/60 p-3.5">
            <p class="text-sm font-semibold text-slate-800">{{ $selectedDate->translatedFormat('l, d F Y') }}</p>
            <div class="inline-flex items-center gap-3 text-xs font-medium text-slate-700">
                <span>Tampilkan jadwal yang bisa dipilih saja</span>
                <button type="button" role="switch" aria-checked="true"
                    aria-label="Tampilkan jadwal yang bisa dipilih saja" data-available-filter class="availability-toggle">
                    <span class="availability-toggle-thumb"></span>
                </button>
            </div>
        </div>
        <div class="flex flex-wrap gap-4 text-xs text-slate-600" aria-label="Keterangan status jadwal">
            <span class="inline-flex items-center gap-2"><span
                    class="h-3 w-3 rounded bg-emerald-200 ring-1 ring-emerald-300"></span>Dapat direservasi</span>
            <span class="inline-flex items-center gap-2"><span
                    class="h-3 w-3 rounded bg-rose-200 ring-1 ring-rose-300"></span>Sudah direservasi</span>
            <span class="inline-flex items-center gap-2"><span
                    class="h-3 w-3 rounded bg-slate-200 ring-1 ring-slate-300"></span>Tidak dapat dipilih</span>
        </div>
        <div data-schedule-slots class="grid grid-cols-3 gap-2.5 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-9">
            @foreach ($slots as $slot)
                @if ($slot['state'] === 'available' && $facility->status === 'aktif')
                    <a href="{{ route('login') }}" data-slot-state="available"
                        title="Tersedia · klik untuk login dan pesan"
                        class="clay-slot-item clay-slot-available flex aspect-square flex-col items-center justify-center rounded-2xl p-1.5 text-center focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                        <span class="text-xs font-bold leading-tight sm:text-sm">{{ $slot['start'] }}</span>
                        <span class="text-[10px] opacity-80 sm:text-[11px]">{{ $slot['end'] }}</span>
                        <span
                            class="mt-1 rounded-full bg-emerald-200/80 px-2 py-0.5 text-[9px] font-bold text-emerald-800">Tersedia</span>
                    </a>
                @else
                    @php
                        $stateClass = match ($slot['state']) {
                            'booked' => 'clay-slot-booked',
                            default => 'clay-slot-inactive',
                        };
                    @endphp
                    <div data-slot-state="{{ $slot['state'] }}"
                        class="clay-slot-item flex aspect-square flex-col items-center justify-center rounded-2xl p-1.5 text-center {{ $stateClass }}">
                        <span class="text-xs font-bold leading-tight sm:text-sm">{{ $slot['start'] }}</span>
                        <span class="text-[10px] opacity-80 sm:text-[11px]">{{ $slot['end'] }}</span>
                        <span
                            class="mt-1 rounded-full bg-black/5 px-2 py-0.5 text-[9px] font-semibold">{{ $slotLabels[$slot['state']] ?? $slot['state'] }}</span>
                    </div>
                @endif
            @endforeach
        </div>
        <p data-empty-slots
            class="hidden rounded-xl border border-blue-100 bg-blue-50/70 p-5 text-center text-sm text-slate-600">Tidak ada
            jadwal tersedia yang bisa dipilih pada tanggal ini.</p>
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-blue-100 pt-4">
            <p class="max-w-2xl text-xs leading-relaxed text-slate-500">Ketersediaan jadwal ditampilkan untuk publik tanpa
                identitas pemohon. Login diperlukan untuk mengajukan reservasi.</p>
            @if ($facility->status === 'aktif')
                @auth<a
                        href="{{ route('reservasi.create', ['facility_id' => $facility->id, 'date' => $selectedDate->toDateString()]) }}"
                        class="landing-button rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-2.5 text-xs font-semibold text-white">Ajukan
                        Reservasi</a>
                @else<a href="{{ route('login') }}"
                        class="landing-button rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-2.5 text-xs font-semibold text-white">Login
                    untuk Reservasi</a>@endauth
            @endif
        </div>
    </section>
@endsection
