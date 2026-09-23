export function initializeReservationForm(form) {
    const rawSlots = parseSlots(form.dataset.slots);
    const maxDurationSlots = Number.parseInt(form.dataset.maxDurationSlots || '8', 10);
    const oldStartTime = form.dataset.oldStartTime || null;
    const oldEndTime = form.dataset.oldEndTime || null;
    const facilitySelect = form.querySelector('#facility_id');
    const dateInput = form.querySelector('#date');
    const startTimeSelect = form.querySelector('#start_time');
    const endTimeSelect = form.querySelector('#end_time');
    const resetBtn = form.querySelector('#resetSelectionBtn');
    const submitBtn = form.querySelector('#submitBtn');
    const slotElements = form.querySelectorAll('.slot-item');

    if (
        !(facilitySelect instanceof HTMLSelectElement) ||
        !(dateInput instanceof HTMLInputElement) ||
        !(startTimeSelect instanceof HTMLSelectElement) ||
        !(endTimeSelect instanceof HTMLSelectElement)
    ) {
        return;
    }

    function parseSlots(serializedSlots) {
        try {
            const slots = JSON.parse(serializedSlots || '[]');

            return Array.isArray(slots) ? slots : [];
        } catch {
            return [];
        }
    }

    function reloadAvailability() {
        const facilityId = facilitySelect.value;
        const dateValue = dateInput.value;

        if (!facilityId || !dateValue || !form.dataset.createUrl) {
            return;
        }

        const params = new URLSearchParams({
            facility_id: facilityId,
            date: dateValue,
        });

        window.location.assign(form.dataset.createUrl + '?' + params.toString());
    }

    function populateStartTimeOptions() {
        startTimeSelect.replaceChildren(new Option('-- Pilih Waktu Mulai --', '', true, true));

        rawSlots.forEach((slot) => {
            if (slot.state !== 'available') {
                return;
            }

            const option = new Option(slot.start + ' WIB', slot.start);
            option.selected = oldStartTime !== null && oldStartTime === slot.start;
            startTimeSelect.appendChild(option);
        });
    }

    function updateEndTimeOptions(selectedStart) {
        endTimeSelect.replaceChildren(new Option('-- Pilih Waktu Selesai --', '', true, true));

        if (!selectedStart) {
            endTimeSelect.replaceChildren(new Option('-- Pilih Jam Mulai Dulu --', '', true, true));
            return;
        }

        const startIndex = rawSlots.findIndex((slot) => slot.start === selectedStart);

        if (startIndex === -1) {
            return;
        }

        let count = 0;

        for (let i = startIndex; i < rawSlots.length && count < maxDurationSlots; i++) {
            const currentSlot = rawSlots[i];

            if (currentSlot.state !== 'available') {
                break;
            }

            count++;
            const hours = count * 0.5;
            const option = new Option(
                currentSlot.end + ' WIB (' + hours + ' jam)',
                currentSlot.end,
            );

            option.selected = oldEndTime !== null
                ? oldEndTime === currentSlot.end
                : count === 1;
            endTimeSelect.appendChild(option);
        }
    }

    function syncGridVisual(start, end) {
        let hasSelection = false;

        slotElements.forEach((element) => {
            const slotStart = element.dataset.start;
            const slotEnd = element.dataset.end;
            const state = element.dataset.state;
            const label = element.querySelector('.slot-status-label');
            const selected = Boolean(start && end)
                && slotStart >= start
                && slotEnd <= end
                && state === 'available';

            if (selected) {
                hasSelection = true;
                element.classList.remove(
                    'border-emerald-400',
                    'bg-white',
                    'text-emerald-950',
                    'hover:bg-[#F2F3FF]',
                );
                element.classList.add(
                    'border-[#00236f]',
                    'bg-[#00236f]',
                    'text-white',
                    'font-bold',
                    'ring-2',
                    'ring-[#0051d5]',
                    'shadow-md',
                );

                if (label !== null) {
                    label.textContent = 'Dipilih';
                    label.className =
                        'slot-status-label mt-1 inline-block rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-white text-[#00236f]';
                }

                return;
            }

            element.classList.remove(
                'border-[#00236f]',
                'bg-[#00236f]',
                'text-white',
                'font-bold',
                'ring-2',
                'ring-[#0051d5]',
                'shadow-md',
            );

            if (state === 'available') {
                element.classList.add(
                    'border-emerald-400',
                    'bg-white',
                    'text-emerald-950',
                    'hover:bg-[#F2F3FF]',
                );

                if (label !== null) {
                    label.textContent = 'Tersedia';
                    label.className =
                        'slot-status-label mt-1 inline-block rounded px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wider bg-emerald-100 text-emerald-800';
                }
            }
        });

        resetBtn?.classList.toggle('hidden', !hasSelection);
    }

    function selectRangeByIndex(firstIndex, secondIndex) {
        const from = Math.min(firstIndex, secondIndex);
        const to = Math.max(firstIndex, secondIndex);

        if (to - from >= maxDurationSlots) {
            return;
        }

        for (let i = from; i <= to; i++) {
            if (rawSlots[i]?.state !== 'available') {
                return;
            }
        }

        const startSlot = rawSlots[from];
        const endSlot = rawSlots[to];

        if (startSlot === undefined || endSlot === undefined) {
            return;
        }

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

    facilitySelect.addEventListener('change', reloadAvailability);
    dateInput.addEventListener('change', reloadAvailability);
    startTimeSelect.addEventListener('change', () => {
        updateEndTimeOptions(startTimeSelect.value);
        syncGridVisual(startTimeSelect.value, endTimeSelect.value);
    });
    endTimeSelect.addEventListener('change', () => {
        syncGridVisual(startTimeSelect.value, endTimeSelect.value);
    });

    let isMouseDown = false;
    let dragStartIndex = null;

    slotElements.forEach((element) => {
        if (element.dataset.state !== 'available') {
            return;
        }

        const index = Number.parseInt(element.dataset.slotIndex || '', 10);

        element.addEventListener('mousedown', (event) => {
            event.preventDefault();
            isMouseDown = true;
            dragStartIndex = index;

            if (startTimeSelect.value === element.dataset.start
                && endTimeSelect.value === element.dataset.end) {
                clearSelection();
                isMouseDown = false;
                dragStartIndex = null;
                return;
            }

            selectRangeByIndex(index, index);
        });

        element.addEventListener('mouseenter', () => {
            if (isMouseDown && dragStartIndex !== null) {
                selectRangeByIndex(dragStartIndex, index);
            }
        });
    });

    window.addEventListener('mouseup', () => {
        isMouseDown = false;
    });

    resetBtn?.addEventListener('click', clearSelection);
    form.addEventListener('submit', () => {
        if (submitBtn instanceof HTMLButtonElement) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Mengirim Permohonan...';
        }
    });

    populateStartTimeOptions();

    if (oldStartTime !== null) {
        startTimeSelect.value = oldStartTime;
        updateEndTimeOptions(oldStartTime);

        if (oldEndTime !== null) {
            endTimeSelect.value = oldEndTime;
        }

        syncGridVisual(startTimeSelect.value, endTimeSelect.value);
    }
}
