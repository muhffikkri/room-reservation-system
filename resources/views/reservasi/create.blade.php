@extends('layouts.app')

@section('title', 'Ajukan Reservasi Fasilitas')

@section('content')
    <div class="mx-auto max-w-4xl space-y-8">
        <div>
            <a href="{{ route('reservasi.index') }}"
                class="inline-flex items-center text-sm font-medium text-slate-500 transition hover:text-[#00236f]">
                &larr; Kembali ke riwayat reservasi
            </a>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Pemesanan fasilitas</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#00236f]">Ajukan Reservasi Fasilitas</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Pilih fasilitas, tanggal, dan slot waktu yang tersedia untuk mengajukan
                peminjaman.</p>
        </div>

        <div class="rounded-2xl border border-[#E2E7FF] bg-white p-6 shadow-[0_14px_40px_rgba(15,23,42,0.06)] sm:p-8">
            <form method="POST" action="{{ route('reservasi.store') }}" id="reservationForm"
                data-reservation-form
                data-create-url="{{ route('reservasi.create') }}"
                data-slots="{{ json_encode($slots, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}"
                data-max-duration-slots="{{ $maxDurationSlots }}"
                data-old-start-time="{{ old('start_time') }}"
                data-old-end-time="{{ old('end_time') }}"
                class="space-y-8">
                @csrf

                {{-- Fasilitas & Tanggal (Grid 2 Kolom) --}}
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    {{-- Fasilitas --}}
                    <div>
                        <label for="facility_id" class="block text-sm font-medium text-slate-700">Fasilitas Kampus <span
                                class="text-rose-500">*</span></label>
                        <select id="facility_id" name="facility_id" required
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-[#0051d5] focus:outline-none focus:ring-1 focus:ring-[#0051d5]">
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
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-[#0051d5] focus:outline-none focus:ring-1 focus:ring-[#0051d5]">
                        @error('date')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Grid Ketersediaan Slot Waktu Interaktif --}}
                @if ($selectedFacility)
                    <div class="rounded-xl border border-[#E2E7FF] bg-[#F8FAFC] p-5">
                        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-sm font-semibold text-[#00236f]">
                                    Jadwal Ketersediaan Slot: {{ $selectedDate->translatedFormat('d F Y') }}
                                </h2>
                                <p class="text-xs text-slate-500">
                                    Jam Operasional: 07:00 – 20:00 WIB &bull; Klik atau seret (drag) untuk memilih rentang
                                    waktu.
                                </p>
                            </div>
                            <button type="button" id="resetSelectionBtn"
                                class="hidden text-xs font-semibold text-rose-600 hover:text-rose-800 hover:underline">
                                Batalkan Pilihan Slot
                            </button>
                        </div>

                        {{-- Legend / Keterangan Warna yang Menonjol --}}
                        <div class="mb-4 flex flex-wrap items-center gap-3 text-xs">
                            <div
                                class="flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-white px-2.5 py-1 text-emerald-800 font-medium shadow-2xs">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                Tersedia (Bisa Dipilih)
                            </div>
                            <div
                                class="flex items-center gap-1.5 rounded-lg border border-[#00236f] bg-[#00236f] px-2.5 py-1 font-medium text-white shadow-2xs">
                                <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                                Terpilih
                            </div>
                            <div
                                class="flex items-center gap-1.5 rounded-lg border border-rose-300 bg-rose-50 px-2.5 py-1 text-rose-700 font-medium shadow-2xs">
                                <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                                Terisi (Approved)
                            </div>
<div
                                class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-200 px-2.5 py-1 text-slate-600 font-medium shadow-2xs">
                                <span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                                Tidak Aktif (< 1 Jam / Lewat)
                            </div>
                        </div>

                        {{-- Grid 26 Slot --}}
                        <div id="slotGridContainer"
                            class="grid grid-cols-2 gap-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-7 select-none">
                            @foreach ($slots as $index => $slot)
                                @php
                                    $state = $slot['state'];
                                    $isAvailable = $state === 'available';
                                    $isBooked = $state === 'booked';
                                    $isInactive = in_array($state, ['inactive', 'past'], true);
                                @endphp

                                <div data-slot-index="{{ $index }}" data-start="{{ $slot['start'] }}"
                                    data-end="{{ $slot['end'] }}" data-state="{{ $state }}"
                                    class="slot-item relative flex flex-col items-center justify-center rounded-lg border p-2.5 text-center transition-all duration-150
                                    @if ($isAvailable) cursor-pointer border-emerald-400 bg-white text-emerald-950 shadow-2xs hover:border-[#0051d5] hover:bg-[#F2F3FF]
                                    @elseif($isBooked)
                                        border-rose-300 bg-rose-50/90 text-rose-600 cursor-not-allowed opacity-80
                                    @else
                                        border-slate-200 bg-slate-100 text-slate-400 cursor-not-allowed opacity-60 @endif">
                                    <span class="text-sm font-bold tracking-tight">{{ $slot['start'] }}</span>
                                    <span class="text-[10px] font-medium opacity-80">s/d {{ $slot['end'] }}</span>

                                    <span
                                        class="slot-status-label mt-1 inline-block rounded px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wider
                                    @if ($isAvailable) bg-emerald-100 text-emerald-800
                                    @elseif($isBooked)
                                        bg-rose-200/80 text-rose-800 line-through
                                    @else
                                        bg-slate-200 text-slate-500 @endif">
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

                        @if ($errors->has('slot'))
                            <p class="mt-2 text-xs font-medium text-rose-600">{{ $errors->first('slot') }}</p>
                        @endif
                    </div>
                @endif

                {{-- Pemilihan Waktu Mulai & Selesai (Sinkronisasi dengan Grid) --}}
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-slate-700">Waktu Mulai <span
                                class="text-rose-500">*</span></label>
                        <select id="start_time" name="start_time" required
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-[#0051d5] focus:outline-none focus:ring-1 focus:ring-[#0051d5]">
                            <option value="" disabled {{ old('start_time') ? '' : 'selected' }}>-- Pilih Waktu Mulai
                                --</option>
                        </select>
                        @error('start_time')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_time" class="block text-sm font-medium text-slate-700">Waktu Selesai <span
                                class="text-rose-500">*</span></label>
                        <select id="end_time" name="end_time" required
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-[#0051d5] focus:outline-none focus:ring-1 focus:ring-[#0051d5]">
                            <option value="" disabled {{ old('end_time') ? '' : 'selected' }}>-- Pilih Jam Mulai Dulu
                                --</option>
                        </select>
                        @error('end_time')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <p class="text-xs text-slate-500">
                    &bull; Durasi minimal: 1 slot (30 menit).<br>
                    &bull; Durasi maksimal: 8 slot (4 jam) per reservasi.<br>
                    &bull; Waktu mulai minimal: 1 jam dari waktu saat ini.
                </p>

                {{-- Tujuan Peminjaman --}}
                <div>
                    <label for="purpose" class="block text-sm font-medium text-slate-700">Tujuan Penggunaan <span
                            class="text-rose-500">*</span></label>
                    <p class="mb-1 text-xs text-slate-500">Jelaskan kegiatan atau keperluan peminjaman (minimal 10
                        karakter).</p>
                    <textarea id="purpose" name="purpose" rows="3" minlength="10" maxlength="255" required
                        placeholder="Contoh: Rapat koordinasi panitia seminar nasional BEM kampus."
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-[#0051d5] focus:outline-none focus:ring-1 focus:ring-[#0051d5]">{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex items-center justify-end gap-3 border-t border-[#EEF2FF] pt-5">
                    <a href="{{ route('reservasi.index') }}"
                        class="rounded-lg border border-[#D6DDF8] bg-white px-4 py-2 text-sm font-medium text-[#00236f] shadow-sm transition hover:bg-[#F2F3FF] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">
                        Batal
                    </a>
                    <button type="submit" id="submitBtn"
                        class="rounded-lg bg-[#0051d5] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#00236f] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">
                        Ajukan Reservasi
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
