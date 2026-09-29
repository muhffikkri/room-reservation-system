import { initializeReservationForm } from './reservation-form';

document.addEventListener('click', (event) => {
    const openTrigger = event.target.closest('[data-open-dialog]');

    if (openTrigger !== null) {
        const dialog = document.getElementById(openTrigger.dataset.openDialog);

        if (dialog instanceof HTMLDialogElement && ! dialog.open) {
            dialog.showModal();
        }

        return;
    }

    const closeTrigger = event.target.closest('[data-close-dialog]');

    if (closeTrigger !== null) {
        const dialog = closeTrigger.closest('dialog');

        if (dialog instanceof HTMLDialogElement) {
            dialog.close();
        }

        return;
    }
});

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (form instanceof HTMLFormElement
        && form.dataset.confirmMessage
        && ! window.confirm(form.dataset.confirmMessage)) {
        event.preventDefault();
    }
});

document.addEventListener('change', (event) => {
    const input = event.target;

    if (!(input instanceof HTMLInputElement)
        || input.dataset.submitOnChange === undefined
        || input.form === null) {
        return;
    }

    input.form.requestSubmit();
});

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);

        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        const willShow = input.type === 'password';

        input.type = willShow ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(willShow).toLowerCase());
        button.setAttribute('aria-label', willShow ? 'Sembunyikan password' : 'Tampilkan password');

        button.querySelectorAll('[data-icon-show], [data-icon-hide]').forEach((icon) => {
            const show = icon.dataset.iconShow !== undefined;

            icon.classList.toggle('hidden', show ? willShow : ! willShow);
        });
    });
});

document.querySelectorAll('[data-reservation-form]').forEach(initializeReservationForm);

document.querySelectorAll('[data-image-preview]').forEach((input) => {
    const preview = document.getElementById(input.dataset.imagePreview);

    input.addEventListener('change', () => {
        const file = input.files[0];

        if (preview instanceof HTMLImageElement && file !== undefined) {
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('hidden');
        }
    });
});

document.querySelectorAll('img.img-fade').forEach((img) => {
    const reveal = () => img.classList.add('is-loaded');

    if (img.complete && img.naturalWidth > 0) {
        reveal();
    } else {
        img.addEventListener('load', reveal);
        img.addEventListener('error', reveal);
    }
});

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || event.defaultPrevented) {
        return;
    }

    const button = form.querySelector('button[data-submit-loading]');

    if (button === null || button.disabled) {
        return;
    }

    button.disabled = true;
    button.classList.add('cursor-not-allowed', 'opacity-80');
    button.innerHTML = ''
        + '<svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">'
        + '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>'
        + '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.37 0 0 5.37 0 12h4z"></path>'
        + '</svg>'
        + '<span>' + (button.dataset.loadingLabel || 'Menyimpan...') + '</span>';
});

document.querySelectorAll('dialog').forEach((dialog) => {
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
});

document.querySelectorAll('[data-mobile-menu-toggle]').forEach((toggle) => {
    const menu = document.getElementById(toggle.getAttribute('aria-controls'));

    if (menu === null) {
        return;
    }

    toggle.addEventListener('click', () => {
        const isOpen = toggle.getAttribute('aria-expanded') === 'true';

        toggle.setAttribute('aria-expanded', String(! isOpen));
        menu.classList.toggle('hidden', isOpen);
    });
});

document.querySelectorAll('[data-notification-toggle]').forEach((toggle) => {
    const panel = document.getElementById(toggle.getAttribute('aria-controls'));

    if (panel === null) {
        return;
    }

    toggle.addEventListener('click', (event) => {
        event.stopPropagation();

        const isOpen = toggle.getAttribute('aria-expanded') === 'true';

        toggle.setAttribute('aria-expanded', String(! isOpen));
        panel.classList.toggle('hidden', isOpen);
    });

    document.addEventListener('click', (event) => {
        if (toggle.contains(event.target) || panel.contains(event.target)) {
            return;
        }

        toggle.setAttribute('aria-expanded', 'false');
        panel.classList.add('hidden');
    });
});

const petugasFilterForm = document.getElementById('petugasFilterForm');
const resetFilterBtn = document.getElementById('reset-filter');
const loadingIndicator = document.getElementById('loading-indicator');
const reservationBody = document.getElementById('reservation-body');
const paginationContainer = document.getElementById('pagination-container');

if (petugasFilterForm !== null) {
    const statusSelect = document.getElementById('status');
    const dateInput = document.getElementById('date');
    let debounceTimer = null;

    const fetchReservations = () => {
        const params = new URLSearchParams();
        const status = statusSelect.value;
        const date = dateInput.value;
        const tab = new URLSearchParams(window.location.search).get('tab');

        if (status) params.set('status', status);
        if (date) params.set('date', date);
        if (tab) params.set('tab', tab);

        if (loadingIndicator !== null) loadingIndicator.classList.remove('hidden');

        fetch(`/petugas/reservasi/data?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((response) => response.json())
            .then((data) => {
                if (loadingIndicator !== null) loadingIndicator.classList.add('hidden');

                document.querySelectorAll('[data-ajax-dialog]').forEach((dialog) => dialog.remove());

                if (reservationBody !== null) {
                    reservationBody.innerHTML = '';
                    if (data.reservations.length === 0) {
                        reservationBody.innerHTML = `
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center">
                                    <p class="text-sm font-medium text-[#00236f]">Tidak ada reservasi</p>
                                    <p class="mt-1 text-sm text-slate-500">Reservasi yang diajukan pengguna akan tampil di sini sesuai filter.</p>
                                </td>
                            </tr>`;
                    } else {
                        data.reservations.forEach((res) => {
                            const row = document.createElement('tr');
                            row.className = 'transition-colors hover:bg-[#F8FAFC]';

                            const statusHtml = getStatusBadge(res.status);
                            const actionsHtml = getActionsHtml(res);

                            row.innerHTML = `
                                <td class="px-6 py-3">
                                    <p class="font-semibold text-[#00236f]">${escapeHtml(res.user.name)}</p>
                                    <p class="text-xs text-slate-600">${escapeHtml(res.user.email)}</p>
                                </td>
                                <td class="px-6 py-3">
                                    <p class="font-medium text-[#00236f]">${escapeHtml(res.facility.name)}</p>
                                    <p class="text-xs text-slate-600">${escapeHtml(res.facility.location)}</p>
                                </td>
                                <td class="whitespace-nowrap px-6 py-3 text-slate-700">
                                    ${formatDate(res.start_time)}
                                    <br>
                                    <span class="text-xs text-slate-600">${formatTime(res.start_time)} – ${formatTime(res.end_time)}</span>
                                </td>
                                <td class="px-6 py-3">${statusHtml}</td>
                                <td class="px-6 py-3 text-slate-600 text-xs whitespace-nowrap">${formatDate(res.created_at)}</td>
                                <td class="px-6 py-3">${actionsHtml}</td>
                            `;
                            reservationBody.appendChild(row);

                            const dialogHtml = `
                                <dialog data-ajax-dialog id="approve-${res.id}" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl backdrop:bg-slate-950/40">
                                    <h3 class="text-lg font-semibold text-[#00236f]">Setujui reservasi?</h3>
                                    <p class="mt-1 text-sm text-slate-600">${escapeHtml(res.facility.name)} · ${formatDate(res.start_time)} ${formatTime(res.start_time)} – ${formatTime(res.end_time)}</p>
                                    <form method="POST" action="/petugas/reservasi/${res.id}/approve" class="mt-4">
                                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content ?? ''}">
                                        <div class="mt-4 flex items-center justify-end gap-2">
                                            <button type="button" data-close-dialog="approve-${res.id}" class="inline-flex h-10 items-center justify-center rounded-lg border border-[#D6DDF8] bg-white px-4 text-sm font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF]">Kembali</button>
                                            <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-[#0051d5] px-4 text-sm font-semibold text-white transition-colors hover:bg-[#00236f]">Konfirmasi Setujui</button>
                                        </div>
                                    </form>
                                </dialog>
                                <dialog data-ajax-dialog id="reject-${res.id}" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl backdrop:bg-slate-950/40">
                                    <h3 class="text-lg font-semibold text-[#00236f]">Tolak reservasi?</h3>
                                    <p class="mt-1 text-sm text-slate-600">${escapeHtml(res.facility.name)} · ${formatDate(res.start_time)} ${formatTime(res.start_time)} – ${formatTime(res.end_time)}</p>
                                    <form method="POST" action="/petugas/reservasi/${res.id}/reject" class="mt-4">
                                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content ?? ''}">
                                        <div class="mb-3">
                                            <label class="mb-1 block text-sm font-medium text-slate-700">Alasan penolakan</label>
                                            <textarea name="reason" rows="3" required minlength="10" maxlength="255" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]" placeholder="Jelaskan alasan penolakan (min. 10 karakter)"></textarea>
                                        </div>
                                        <div class="mt-4 flex items-center justify-end gap-2">
                                            <button type="button" data-close-dialog="reject-${res.id}" class="inline-flex h-10 items-center justify-center rounded-lg border border-[#D6DDF8] bg-white px-4 text-sm font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF]">Kembali</button>
                                            <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-red-600 px-4 text-sm font-semibold text-white transition-colors hover:bg-red-700">Tolak Reservasi</button>
                                        </div>
                                    </form>
                                </dialog>
                                <dialog data-ajax-dialog id="cancel-${res.id}" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl backdrop:bg-slate-950/40">
                                    <h3 class="text-lg font-semibold text-[#00236f]">Batalkan reservasi?</h3>
                                    <p class="mt-1 text-sm text-slate-600">${escapeHtml(res.facility.name)} · ${formatDate(res.start_time)} ${formatTime(res.start_time)} – ${formatTime(res.end_time)}</p>
                                    <form method="POST" action="/petugas/reservasi/${res.id}/cancel" class="mt-4">
                                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content ?? ''}">
                                        <div class="mb-3">
                                            <label class="mb-1 block text-sm font-medium text-slate-700">Alasan pembatalan</label>
                                            <textarea name="cancel_reason" rows="3" required minlength="10" maxlength="255" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-[#0051d5] focus:outline-none focus:ring-4 focus:ring-[#E2E7FF]" placeholder="Jelaskan alasan pembatalan (min. 10 karakter)"></textarea>
                                        </div>
                                        <div class="mt-4 flex items-center justify-end gap-2">
                                            <button type="button" data-close-dialog="cancel-${res.id}" class="inline-flex h-10 items-center justify-center rounded-lg border border-[#D6DDF8] bg-white px-4 text-sm font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF]">Kembali</button>
                                            <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-red-600 px-4 text-sm font-semibold text-white transition-colors hover:bg-red-700">Batalkan Reservasi</button>
                                        </div>
                                    </form>
                                </dialog>`;
                            document.body.insertAdjacentHTML('beforeend', dialogHtml);
                        });
                    }
                }

                if (paginationContainer !== null) {
                    paginationContainer.style.display = data.pagination.total > 0 ? 'block' : 'none';
                }
            })
            .catch((error) => {
                if (loadingIndicator !== null) loadingIndicator.classList.add('hidden');
                console.error('Failed to fetch reservations:', error);
            });
    };

    const scheduleDebounce = (fn, delay) => {
        return () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fn, delay);
        };
    };

    statusSelect.addEventListener('change', scheduleDebounce(fetchReservations, 300));
    dateInput.addEventListener('change', scheduleDebounce(fetchReservations, 300));

    if (resetFilterBtn !== null) {
        resetFilterBtn.addEventListener('click', () => {
            statusSelect.value = '';
            dateInput.value = '';
            fetchReservations();
        });
    }
}

function getStatusBadge(status) {
    const badges = {
        pending: '<span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">Menunggu Persetujuan</span>',
        approved: '<span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-200">Disetujui</span>',
        rejected: '<span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-200">Ditolak</span>',
        cancelled_by_user: '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">Dibatalkan Pengguna</span>',
        cancelled_by_officer: '<span class="inline-flex items-center rounded-full bg-orange-50 px-2.5 py-1 text-xs font-semibold text-orange-700 ring-1 ring-inset ring-orange-200">Dibatalkan Petugas</span>',
        cancelled_by_system: '<span class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 ring-1 ring-inset ring-violet-200">Dibatalkan oleh Sistem</span>',
        rejected_by_system: '<span class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 ring-1 ring-inset ring-violet-200">Ditolak oleh Sistem</span>',
    };
    return badges[status] || status;
}

function getActionsHtml(res) {
    let html = '<div class="flex flex-wrap gap-2">';
    html += `<a href="/petugas/reservasi/${res.id}" class="inline-flex h-8 items-center rounded-lg border border-[#D6DDF8] bg-white px-3 text-xs font-semibold text-[#00236f] transition-colors hover:bg-[#F2F3FF]">Detail</a>`;
    if (res.status === 'pending') {
        html += `<button type="button" data-open-dialog="approve-${res.id}" class="inline-flex h-8 items-center rounded-lg bg-[#0051d5] px-3 text-xs font-semibold text-white transition-colors hover:bg-[#00236f]">Setujui</button>`;
        html += `<button type="button" data-open-dialog="reject-${res.id}" class="inline-flex h-8 items-center rounded-lg border border-red-300 bg-white px-3 text-xs font-semibold text-red-600 transition-colors hover:bg-red-50">Tolak</button>`;
    }
    if (['pending', 'approved'].includes(res.status)) {
        html += `<button type="button" data-open-dialog="cancel-${res.id}" class="inline-flex h-8 items-center rounded-lg bg-red-600 px-3 text-xs font-semibold text-white transition-colors hover:bg-red-700">Batalkan</button>`;
    }
    return html + '</div>';
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function formatDate(dateStr) {
    const d = new Date(dateStr);
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
}

function formatTime(dateStr) {
    const d = new Date(dateStr);
    return `${String(d.getHours()).padStart(2, '0')}.${String(d.getMinutes()).padStart(2, '0')}`;
}

// Landing page live search
const landingFilterForm = document.getElementById('landingFilterForm');
const landingResetBtn = document.getElementById('landing-reset-filter');
const landingLoading = document.getElementById('landing-loading');
const landingGridContainer = document.getElementById('landing-grid-container');
const facilityCountNum = document.getElementById('facility-count-num');

const landingPage = document.querySelector('[data-landing]');

if (landingPage !== null) {
    const navLinks = [...landingPage.querySelectorAll('[data-landing-nav][data-nav-active]')];
    const sections = [...landingPage.querySelectorAll('[data-landing-section]')];

    const activateNav = (sectionId) => {
        navLinks.forEach((link) => {
            const isActive = link.dataset.landingNav === sectionId;
            link.className = isActive ? link.dataset.navActive : link.dataset.navInactive;

            if (isActive) {
                link.setAttribute('aria-current', 'page');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    };

    const initialSection = window.location.hash.slice(1);
    activateNav(sections.some((section) => section.id === initialSection) ? initialSection : 'top');

    navLinks.forEach((link) => {
        link.addEventListener('click', () => activateNav(link.dataset.landingNav));
    });

    if ('IntersectionObserver' in window) {
        const navObserver = new IntersectionObserver((entries) => {
            const visibleSections = entries
                .filter((entry) => entry.isIntersecting)
                .sort((first, second) => second.intersectionRatio - first.intersectionRatio);

            if (visibleSections[0] !== undefined) {
                activateNav(visibleSections[0].target.id);
            }
        }, { rootMargin: '-20% 0px -60% 0px', threshold: [0, 0.25, 0.5, 0.75, 1] });

        sections.forEach((section) => navObserver.observe(section));
    }
}

document.querySelectorAll('[data-facility-filters]').forEach((form) => {
    let searchTimer = null;
    const searchInput = form.querySelector('[data-search-filter]');

    form.querySelectorAll('[data-auto-filter]').forEach((filter) => {
        filter.addEventListener('change', () => form.requestSubmit());
    });

    searchInput?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => form.requestSubmit(), 350);
    });
});

if (landingFilterForm !== null) {
    const searchInput = document.getElementById('search-input');
    const typeSelect = document.getElementById('filter-type');
    const locationSelect = document.getElementById('filter-location');
    const capacitySelect = document.getElementById('filter-capacity');
    let debounceTimer = null;

    const fetchLandingFacilities = () => {
        const params = new URLSearchParams();
        const q = searchInput.value.trim();
        const type = typeSelect.value;
        const location = locationSelect.value;
        const capacity = capacitySelect.value;

        if (q) params.set('q', q);
        if (type) params.set('type', type);
        if (location) params.set('location', location);
        if (capacity) params.set('capacity', capacity);

        if (landingLoading !== null) landingLoading.classList.remove('hidden');
        if (landingGridContainer !== null) landingGridContainer.style.opacity = '0.5';

        fetch(`/home/facilities?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((response) => response.json())
            .then((data) => {
                if (landingLoading !== null) landingLoading.classList.add('hidden');
                if (landingGridContainer !== null) landingGridContainer.style.opacity = '1';

                // Update count
                if (facilityCountNum !== null) {
                    facilityCountNum.textContent = data.total;
                }

                // Update grid cards. Markup berasal dari server (HomeController),
                // sehingga hasil pencarian memakai card yang sama persis dengan
                // render awal, termasuk pesan kosong di dalam container.
                if (landingGridContainer !== null) {
                    landingGridContainer.innerHTML = data.html;
                }
            })
            .catch((error) => {
                if (landingLoading !== null) landingLoading.classList.add('hidden');
                if (landingGridContainer !== null) landingGridContainer.style.opacity = '1';
                console.error('Failed to fetch facilities:', error);
            });
    };

    const scheduleDebounce = (fn, delay) => {
        return () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fn, delay);
        };
    };

    searchInput.addEventListener('input', scheduleDebounce(fetchLandingFacilities, 300));
    typeSelect.addEventListener('change', scheduleDebounce(fetchLandingFacilities, 300));
    locationSelect.addEventListener('change', scheduleDebounce(fetchLandingFacilities, 300));
    capacitySelect.addEventListener('change', scheduleDebounce(fetchLandingFacilities, 300));

    if (landingResetBtn !== null) {
        landingResetBtn.addEventListener('click', () => {
            searchInput.value = '';
            typeSelect.value = '';
            locationSelect.value = '';
            capacitySelect.value = '';
            fetchLandingFacilities();
        });
    }
}

const availableSlotsToggle = document.querySelector('[data-available-filter]');
const scheduleSlots = document.querySelector('[data-schedule-slots]');
const emptyScheduleSlots = document.querySelector('[data-empty-slots]');

if (availableSlotsToggle !== null && scheduleSlots !== null) {
    const filterAvailableSlots = () => {
        const onlyAvailable = availableSlotsToggle.getAttribute('aria-checked') === 'true';
        let visibleSlots = 0;

        scheduleSlots.querySelectorAll('[data-slot-state]').forEach((slot) => {
            const isAvailable = slot.dataset.slotState === 'available';
            const isVisible = !onlyAvailable || isAvailable;
            slot.classList.toggle('hidden', !isVisible);
            visibleSlots += isVisible ? 1 : 0;
        });

        emptyScheduleSlots?.classList.toggle('hidden', visibleSlots !== 0);
    };

    availableSlotsToggle.addEventListener('click', () => {
        const isOn = availableSlotsToggle.getAttribute('aria-checked') === 'true';
        availableSlotsToggle.setAttribute('aria-checked', String(!isOn));
        filterAvailableSlots();
    });
    filterAvailableSlots();
}
