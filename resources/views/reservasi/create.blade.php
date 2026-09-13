@extends('layouts.app')

@section('title', 'Ajukan Reservasi Fasilitas')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <a href="{{ route('reservasi.index') }}"
                class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-700">
                &larr; Kembali ke riwayat reservasi
            </a>
            <h1 class="mt-2 text-2xl font-bold text-slate-800">Ajukan Reservasi Fasilitas</h1>
            <p class="text-sm text-slate-500">Pilih fasilitas, tanggal, dan slot waktu yang tersedia untuk mengajukan
                peminjaman.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('reservasi.store') }}" id="reservationForm" class="space-y-6">
                @csrf

                {{-- Fasilitas & Tanggal (Grid 2 Kolom) --}}
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    {{-- Fasilitas --}}
                    <div>
                        <label for="facility_id" class="block text-sm font-medium text-slate-700">Fasilitas Kampus <span
                                class="text-rose-500">*</span></label>
                        <select id="facility_id" name="facility_id" required
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-sm">
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
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-sm">
                        @error('date')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Grid Ketersediaan Slot Waktu Interaktif --}}
                @if ($selectedFacility)
                    <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-5">
                        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-sm font-semibold text-slate-800">
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
                                class="flex items-center gap-1.5 rounded-lg border border-indigo-600 bg-indigo-600 px-2.5 py-1 text-white font-medium shadow-2xs">
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
                                Tidak Aktif (&lt; 30 Mnt / Lewat)
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
                                    @if ($isAvailable) border-emerald-400 bg-white text-emerald-950 hover:border-indigo-500 hover:bg-indigo-50 cursor-pointer shadow-2xs
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
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-sm">
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
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-sm">
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
                    &bull; Waktu mulai minimal: 30 menit dari waktu saat ini.
                </p>

                {{-- Tujuan Peminjaman --}}
                <div>
                    <label for="purpose" class="block text-sm font-medium text-slate-700">Tujuan Penggunaan <span
                            class="text-rose-500">*</span></label>
                    <p class="text-xs text-slate-500 mb-1">Jelaskan kegiatan atau keperluan peminjaman (minimal 10
                        karakter).</p>
                    <textarea id="purpose" name="purpose" rows="3" minlength="10" maxlength="255" required
                        placeholder="Contoh: Rapat koordinasi panitia seminar nasional BEM kampus."
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-sm">{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-5">
                    <a href="{{ route('reservasi.index') }}"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none">
                        Batal
                    </a>
                    <button type="submit" id="submitBtn"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Ajukan Reservasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Data slot dari server
        const rawSlots = @json($slots);
        const oldStartTime = @json(old('start_time'));
        const oldEndTime = @json(old('end_time'));

        const facilitySelect = document.getElementById('facility_id');
        const dateInput = document.getElementById('date');
        const startTimeSelect = document.getElementById('start_time');
        const endTimeSelect = document.getElementById('end_time');
        const resetBtn = document.getElementById('resetSelectionBtn');
        const slotElements = document.querySelectorAll('.slot-item');

        // 1. Reload saat fasilitas atau tanggal berubah
        function reloadAvailability() {
            const facilityId = facilitySelect.value;
            const dateVal = dateInput.value;
            if (facilityId && dateVal) {
                window.location.href = `{{ route('reservasi.create') }}?facility_id=${facilityId}&date=${dateVal}`;
            }
        }

        facilitySelect?.addEventListener('change', reloadAvailability);
        dateInput?.addEventListener('change', reloadAvailability);

        // 2. Inisialisasi dropdown Waktu Mulai (hanya yang berstatus 'available')
        function populateStartTimeOptions() {
            startTimeSelect.innerHTML = '<option value="" disabled selected>-- Pilih Waktu Mulai --</option>';

            rawSlots.forEach((slot, index) => {
                if (slot.state === 'available') {
                    const opt = document.createElement('option');
                    opt.value = slot.start;
                    opt.textContent = `${slot.start} WIB`;
                    if (oldStartTime && oldStartTime === slot.start) {
                        opt.selected = true;
                    }
                    startTimeSelect.appendChild(opt);
                }
            });
        }

        // 3. Inisialisasi dropdown Waktu Selesai berdasarkan Waktu Mulai yang dipilih
        function updateEndTimeOptions(selectedStart) {
            endTimeSelect.innerHTML = '<option value="" disabled selected>-- Pilih Waktu Selesai --</option>';

            if (!selectedStart) {
                endTimeSelect.innerHTML = '<option value="" disabled selected>-- Pilih Jam Mulai Dulu --</option>';
                return;
            }

            const startIndex = rawSlots.findIndex(s => s.start === selectedStart);
            if (startIndex === -1) return;

            // Maksimal 8 slot (4 jam)
            const maxSlots = 8;
            let count = 0;

            for (let i = startIndex; i < rawSlots.length && count < maxSlots; i++) {
                const currentSlot = rawSlots[i];

                // Jika slot di tengah rentang bukan available (misal booked atau inactive), hentikan pilihan
                if (currentSlot.state !== 'available') {
                    break;
                }

                count++;
                const opt = document.createElement('option');
                opt.value = currentSlot.end;
                const hours = (count * 0.5);
                opt.textContent = `${currentSlot.end} WIB (${hours} jam)`;

                if (oldEndTime && oldEndTime === currentSlot.end) {
                    opt.selected = true;
                } else if (!oldEndTime && count === 1) {
                    // Default pilih 1 slot pertama (30 menit)
                    opt.selected = true;
                }

                endTimeSelect.appendChild(opt);
            }
        }

        // 4. Update highlight visual pada Grid Slot
        function syncGridVisual(start, end) {
            let hasSelection = false;

            slotElements.forEach(el => {
                const slotStart = el.dataset.start;
                const slotEnd = el.dataset.end;
                const state = el.dataset.state;
                const label = el.querySelector('.slot-status-label');

                if (start && end && slotStart >= start && slotEnd <= end && state === 'available') {
                    hasSelection = true;
                    el.classList.remove('border-emerald-400', 'bg-white', 'text-emerald-950', 'hover:bg-indigo-50');
                    el.classList.add('border-indigo-600', 'bg-indigo-600', 'text-white', 'font-bold', 'ring-2',
                        'ring-indigo-400', 'shadow-md');
                    if (label) {
                        label.textContent = 'Dipilih';
                        label.className =
                            'slot-status-label mt-1 inline-block rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-white text-indigo-700';
                    }
                } else {
                    el.classList.remove('border-indigo-600', 'bg-indigo-600', 'text-white', 'font-bold', 'ring-2',
                        'ring-indigo-400', 'shadow-md');
                    if (state === 'available') {
                        el.classList.add('border-emerald-400', 'bg-white', 'text-emerald-950',
                        'hover:bg-indigo-50');
                        if (label) {
                            label.textContent = 'Tersedia';
                            label.className =
                                'slot-status-label mt-1 inline-block rounded px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wider bg-emerald-100 text-emerald-800';
                        }
                    }
                }
            });

            if (resetBtn) {
                resetBtn.classList.toggle('hidden', !hasSelection);
            }
        }

        // 5. Interaksi Grid: Klik & Drag / Hold Range Selection
        let isMouseDown = false;
        let dragStartIndex = null;

        slotElements.forEach(el => {
            const state = el.dataset.state;
            const index = parseInt(el.dataset.slotIndex, 10);

            if (state !== 'available') return;

            // Klik tunggal atau mulai drag
            el.addEventListener('mousedown', (e) => {
                e.preventDefault();
                isMouseDown = true;
                dragStartIndex = index;

                // Jika slot yang sama diklik saat sudah terpilih sendiri, unselect
                if (startTimeSelect.value === el.dataset.start && endTimeSelect.value === el.dataset.end) {
                    clearSelection();
                    isMouseDown = false;
                    dragStartIndex = null;
                    return;
                }

                selectRangeByIndex(dragStartIndex, dragStartIndex);
            });

            // Hover saat drag / hold
            el.addEventListener('mouseenter', () => {
                if (isMouseDown && dragStartIndex !== null) {
                    selectRangeByIndex(dragStartIndex, index);
                }
            });
        });

        window.addEventListener('mouseup', () => {
            isMouseDown = false;
        });

        function selectRangeByIndex(idx1, idx2) {
            const from = Math.min(idx1, idx2);
            const to = Math.max(idx1, idx2);

            // Batasi maksimal 8 slot (4 jam)
            if (to - from >= 8) {
                return;
            }

            // Cek apakah ada slot yang booked atau inactive di tengah range
            for (let i = from; i <= to; i++) {
                if (rawSlots[i].state !== 'available') {
                    return; // Jangan izinkan melompati slot yang terisi/tidak aktif
                }
            }

            const startSlot = rawSlots[from];
            const endSlot = rawSlots[to];

            startTimeSelect.value = startSlot.start;
            updateEndTimeOptions(startSlot.start);
            endTimeSelect.value = endSlot.end;

            syncGridVisual(startSlot.start, endSlot.end);
        }

        function clearSelection() {
            startTimeSelect.value = '';
            updateEndTimeOptions(null);
            syncGridVisual(null, null);
        }

        resetBtn?.addEventListener('click', clearSelection);

        // Event listener saat dropdown diubah manual
        startTimeSelect?.addEventListener('change', () => {
            updateEndTimeOptions(startTimeSelect.value);
            syncGridVisual(startTimeSelect.value, endTimeSelect.value);
        });

        endTimeSelect?.addEventListener('change', () => {
            syncGridVisual(startTimeSelect.value, endTimeSelect.value);
        });

        // Jalankan populate awal
        populateStartTimeOptions();
        if (oldStartTime) {
            startTimeSelect.value = oldStartTime;
            updateEndTimeOptions(oldStartTime);
            if (oldEndTime) {
                endTimeSelect.value = oldEndTime;
            }
            syncGridVisual(startTimeSelect.value, endTimeSelect.value);
        }

        // Proteksi double submit form
        const form = document.getElementById('reservationForm');
        const submitBtn = document.getElementById('submitBtn');

        form?.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Mengirim Permohonan...';
        });
    </script>
@endsection
