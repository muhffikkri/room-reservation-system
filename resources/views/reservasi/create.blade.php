@extends('layouts.app')

@section('title', 'Ajukan Reservasi Fasilitas')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="auth-clay-card rounded-[2rem] p-5 sm:p-7 lg:p-9">
            <div
                class="mb-7 flex flex-col gap-4 border-b border-blue-100/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <a href="{{ route('reservasi.index') }}"
                        class="clay-button-white mb-3 inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold text-blue-700">
                        &larr; Kembali ke riwayat reservasi
                    </a>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Pemesanan fasilitas · REKSA
                    </p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">Ajukan Reservasi
                        Fasilitas</h1>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600">Pilih fasilitas, tanggal, dan slot waktu yang
                        tersedia untuk mengajukan peminjaman.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('reservasi.store') }}" id="reservationForm" data-reservation-form
                data-create-url="{{ route('reservasi.create') }}"
                data-slots="{{ json_encode($slots, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}"
                data-max-duration-slots="{{ $maxDurationSlots }}" data-old-start-time="{{ old('start_time') }}"
                data-old-end-time="{{ old('end_time') }}" class="space-y-8">
                @csrf

                {{-- Fasilitas & Tanggal (Grid 2 Kolom) --}}
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    {{-- Fasilitas --}}
                    <div>
                        <label for="facility_id" class="block text-sm font-medium text-slate-700">Fasilitas Kampus <span
                                class="text-rose-500">*</span></label>
                        <select id="facility_id" name="facility_id" required
                            class="landing-input mt-1 block w-full rounded-xl px-3 py-3 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="" disabled {{ !$selectedFacility ? 'selected' : '' }}>-- Pilih Fasilitas
                                --</option>
                            @foreach ($facilities as $facility)
                                <option value="{{ $facility->id }}"
                                    {{ (string) old('facility_id', $selectedFacility?->id) === (string) $facility->id ? 'selected' : '' }}>
                                    {{ $facility->name }} ({{ $facility->location }})
                                </option>
                            @endforeach
                        </select>
                        @error('facility_id')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal --}}
                    <div>
                        <label for="date" class="block text-sm font-medium text-slate-700">Tanggal Penggunaan <span
                                class="text-rose-500">*</span></label>
                        <input type="date" id="date" name="date" required min="{{ date('Y-m-d') }}"
                            value="{{ old('date', $selectedDate->format('Y-m-d')) }}"
                            class="landing-input mt-1 block w-full rounded-xl px-3 py-3 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        @error('date')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Grid Ketersediaan Slot Waktu Interaktif --}}
                @if ($selectedFacility)
                    <div class="space-y-4 rounded-3xl border border-blue-100/80 bg-blue-50/40 p-5 shadow-2xs sm:p-6">
                        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">Jadwal Ketersediaan</h2>
                                <p class="text-xs text-slate-600">Jam operasional 07.00–20.00 WIB · interval 30 menit</p>
                            </div>
                            <button type="button" id="resetSelectionBtn"
                                class="clay-button-danger hidden self-start rounded-full px-4 py-1.5 text-xs font-bold shadow-sm sm:self-auto">
                                Batalkan Pilihan Slot
                            </button>
                        </div>

                        <div
                            class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-blue-100 bg-blue-50/80 p-3.5">
                            <p class="text-sm font-semibold text-slate-800">
                                {{ $selectedDate->translatedFormat('l, d F Y') }}</p>
                            <div class="inline-flex items-center gap-3 text-xs font-medium text-slate-700">
                                <span>Tampilkan jadwal yang bisa dipilih saja</span>
                                <button type="button" role="switch" aria-checked="true"
                                    aria-label="Tampilkan jadwal yang bisa dipilih saja" data-available-filter
                                    class="availability-toggle">
                                    <span class="availability-toggle-thumb"></span>
                                </button>
                            </div>
                        </div>

                        {{-- Legend Keterangan Warna --}}
                        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600"
                            aria-label="Keterangan status jadwal">
                            <span class="inline-flex items-center gap-2">
                                <span class="h-3 w-3 rounded bg-emerald-200 ring-1 ring-emerald-400"></span>
                                Dapat direservasi
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <span class="h-3 w-3 rounded bg-blue-600 ring-1 ring-blue-700"></span>
                                Terpilih
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <span class="h-3 w-3 rounded bg-rose-200 ring-1 ring-rose-400"></span>
                                Sudah direservasi
                            </span>
                            <span class="inline-flex items-center gap-2">
                                <span class="h-3 w-3 rounded bg-slate-200 ring-1 ring-slate-400"></span>
                                Tidak dapat dipilih
                            </span>
                        </div>

                        {{-- Grid 26 Slot 1:1 Aspect Ratio --}}
                        <div id="slotGridContainer" data-schedule-slots
                            class="grid grid-cols-3 gap-2.5 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-9 select-none">
                            @foreach ($slots as $index => $slot)
                                @php
                                    $state = $slot['state'];
                                    $isAvailable = $state === 'available';
                                    $isBooked = $state === 'booked';
                                    $isInactive = in_array($state, ['inactive', 'past'], true);
                                @endphp

                                <div data-slot-index="{{ $index }}" data-start="{{ $slot['start'] }}"
                                    data-end="{{ $slot['end'] }}" data-state="{{ $state }}"
                                    data-slot-state="{{ $state }}"
                                    class="slot-item clay-slot-item relative flex aspect-square flex-col items-center justify-center rounded-2xl p-1.5 text-center transition-all duration-150
                                    @if ($isAvailable) cursor-pointer clay-slot-available
                                    @elseif($isBooked)
                                        clay-slot-booked cursor-not-allowed opacity-80
                                    @else
                                        clay-slot-inactive cursor-not-allowed opacity-60 @endif">
                                    <span class="text-xs font-bold leading-tight sm:text-sm">{{ $slot['start'] }}</span>
                                    <span class="text-[10px] opacity-80 sm:text-[11px]">{{ $slot['end'] }}</span>

                                    <span
                                        class="slot-status-label mt-1 inline-block rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider
                                    @if ($isAvailable) bg-emerald-200/80 text-emerald-800
                                    @elseif($isBooked)
                                        bg-rose-200/80 text-rose-800 line-through
                                    @else
                                        bg-black/5 text-slate-500 @endif">
                                        @if ($isAvailable)
                                            Tersedia
                                        @elseif($isBooked)
                                            Terisi
                                        @else
                                            Tidak Aktif
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <p data-empty-slots
                            class="hidden rounded-xl border border-blue-100 bg-blue-50/70 p-5 text-center text-sm text-slate-600">
                            Tidak ada jadwal tersedia yang bisa dipilih pada tanggal ini.</p>

                        @if ($errors->has('slot'))
                            <p class="mt-2 text-xs font-medium text-rose-600">{{ $errors->first('slot') }}</p>
                        @endif
                    </div>
                @endif

                {{-- Pemilihan Waktu Mulai & Selesai (Sinkronisasi dengan Grid) --}}
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="start_time" class="block text-sm font-semibold text-slate-700">Waktu Mulai <span
                                class="text-rose-500">*</span></label>
                        <select id="start_time" name="start_time" required
                            class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <option value="" disabled {{ old('start_time') ? '' : 'selected' }}>-- Pilih Waktu Mulai
                                --</option>
                        </select>
                        @error('start_time')
                            <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_time" class="block text-sm font-semibold text-slate-700">Waktu Selesai <span
                                class="text-rose-500">*</span></label>
                        <select id="end_time" name="end_time" required
                            class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <option value="" disabled {{ old('end_time') ? '' : 'selected' }}>-- Pilih Jam Mulai Dulu
                                --</option>
                        </select>
                        @error('end_time')
                            <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div
                    class="grid gap-2 rounded-2xl border border-blue-100 bg-blue-50/70 p-4 text-xs leading-relaxed text-slate-600 sm:grid-cols-3">
                    <p><span class="font-bold text-blue-600">•</span> Durasi minimal: 1 slot (30 menit).</p>
                    <p><span class="font-bold text-blue-600">•</span> Durasi maksimal: 8 slot (4 jam) per reservasi.</p>
                    <p><span class="font-bold text-blue-600">•</span> Waktu mulai minimal: 1 jam dari waktu saat ini.</p>
                </div>

                {{-- Tujuan Peminjaman --}}
                <div>
                    <label for="purpose" class="block text-sm font-semibold text-slate-700">Tujuan Penggunaan <span
                            class="text-rose-500">*</span></label>
                    <p class="mb-1 text-xs text-slate-500">Jelaskan kegiatan atau keperluan peminjaman (minimal 10
                        karakter).</p>
                    <textarea id="purpose" name="purpose" rows="3" minlength="10" maxlength="255" required
                        placeholder="Contoh: Rapat koordinasi panitia seminar nasional BEM kampus."
                        class="landing-input block w-full rounded-2xl px-4 py-3 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/15">{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tombol Aksi --}}
                <div
                    class="flex flex-col-reverse items-stretch justify-end gap-3 border-t border-blue-100/80 pt-5 sm:flex-row sm:items-center">
                    <a href="{{ route('reservasi.index') }}"
                        class="clay-button-white inline-flex justify-center rounded-full px-6 py-3 text-sm font-semibold text-blue-700">
                        Batal
                    </a>
                    <button type="submit" id="submitBtn"
                        class="landing-button rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-7 py-3 text-sm font-bold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)]">
                        Ajukan Reservasi
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
