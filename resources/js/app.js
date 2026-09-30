import { initializeReservationForm } from './reservation-form';

// =========================================================================
// Cursor tail spring physics
// =========================================================================
const TRAIL_COUNT = 7;
const SPACING = 22;
const TENSION = 0.055;
const DAMPING = 0.75;
const RETRACT_TENSION = 0.09;
const RETRACT_DAMPING = 0.72;
const trailPool = [];
let mouseX = -200;
let mouseY = -200;
let lastMoveTime = 0;
const tailCanvas = document.createElement('canvas');
const tailContext = tailCanvas.getContext('2d');
let tailPixelRatio = Math.min(window.devicePixelRatio || 1, 2);

tailCanvas.className = 'cursor-tail-canvas';
tailCanvas.setAttribute('aria-hidden', 'true');
document.body.appendChild(tailCanvas);

function resizeTailCanvas() {
    tailPixelRatio = Math.min(window.devicePixelRatio || 1, 2);
    tailCanvas.width = Math.round(window.innerWidth * tailPixelRatio);
    tailCanvas.height = Math.round(window.innerHeight * tailPixelRatio);
    tailCanvas.style.width = `${window.innerWidth}px`;
    tailCanvas.style.height = `${window.innerHeight}px`;
    tailContext.setTransform(tailPixelRatio, 0, 0, tailPixelRatio, 0, 0);
}

function drawCursorTail() {
    tailContext.clearRect(0, 0, window.innerWidth, window.innerHeight);

    if (mouseX < -100) {
        return;
    }

    const points = [{ x: mouseX, y: mouseY, emerge: 1 }];

    trailPool.forEach((node) => {
        if (node.emerge > 0.03) {
            points.push({ x: node.x, y: node.y, emerge: node.emerge });
        }
    });

    if (points.length < 2) {
        return;
    }

    const leftEdge = [];
    const rightEdge = [];
    const lastPointIndex = points.length - 1;

    points.forEach((point, index) => {
        const previousPoint = points[Math.max(0, index - 1)];
        const nextPoint = points[Math.min(lastPointIndex, index + 1)];
        const tangentX = nextPoint.x - previousPoint.x;
        const tangentY = nextPoint.y - previousPoint.y;
        const tangentLength = Math.hypot(tangentX, tangentY) || 1;
        const taper = 1 - index / points.length;
        const halfWidth = (1.1 + taper * 6.4) * point.emerge;
        const normalX = -tangentY / tangentLength;
        const normalY = tangentX / tangentLength;

        leftEdge.push({ x: point.x + normalX * halfWidth, y: point.y + normalY * halfWidth });
        rightEdge.push({ x: point.x - normalX * halfWidth, y: point.y - normalY * halfWidth });
    });

    const head = points[0];
    const tail = points[lastPointIndex];
    const gradient = tailContext.createLinearGradient(head.x, head.y, tail.x, tail.y);

    gradient.addColorStop(0, 'rgba(51, 156, 255, 0.55)');
    gradient.addColorStop(0.45, 'rgba(65, 158, 255, 0.3)');
    gradient.addColorStop(1, 'rgba(89, 181, 255, 0.04)');

    tailContext.beginPath();
    tailContext.moveTo(leftEdge[0].x, leftEdge[0].y);

    for (let index = 1; index < leftEdge.length - 1; index += 1) {
        const midpointX = (leftEdge[index].x + leftEdge[index + 1].x) / 2;
        const midpointY = (leftEdge[index].y + leftEdge[index + 1].y) / 2;

        tailContext.quadraticCurveTo(leftEdge[index].x, leftEdge[index].y, midpointX, midpointY);
    }

    tailContext.lineTo(leftEdge[lastPointIndex].x, leftEdge[lastPointIndex].y);
    tailContext.lineTo(rightEdge[lastPointIndex].x, rightEdge[lastPointIndex].y);

    for (let index = rightEdge.length - 2; index > 0; index -= 1) {
        const midpointX = (rightEdge[index].x + rightEdge[index - 1].x) / 2;
        const midpointY = (rightEdge[index].y + rightEdge[index - 1].y) / 2;

        tailContext.quadraticCurveTo(rightEdge[index].x, rightEdge[index].y, midpointX, midpointY);
    }

    tailContext.lineTo(rightEdge[0].x, rightEdge[0].y);
    tailContext.closePath();
    tailContext.fillStyle = gradient;
    tailContext.shadowColor = 'rgba(44, 149, 255, 0.55)';
    tailContext.shadowBlur = 8;
    tailContext.fill();
    tailContext.shadowBlur = 0;
}

window.addEventListener('resize', resizeTailCanvas, { passive: true });
resizeTailCanvas();

for (let i = 0; i < TRAIL_COUNT; i++) {
    trailPool.push({ x: -200, y: -200, vx: 0, vy: 0, emerge: 0 });
}

function animateTrail() {
    const now = performance.now();
    const moving = mouseX > -100 && (now - lastMoveTime) < 80;

    drawCursorTail();

    for (let i = 0; i < TRAIL_COUNT; i++) {
        const node = trailPool[i];
        const leader = i === 0 ? { x: mouseX, y: mouseY } : trailPool[i - 1];

        // Sequential emerge: each node waits for the previous to be partially emerged
        if (moving) {
            const prevEmerge = i === 0 ? 1 : trailPool[i - 1].emerge;
            if (prevEmerge > 0.35) {
                node.emerge = Math.min(1, node.emerge + 0.09);
            }
        } else {
            // Sequential retract: last node shrinks first, then next, etc.
            const nextEmerge = i === TRAIL_COUNT - 1 ? 0 : trailPool[i + 1].emerge;
            if (nextEmerge < 0.1) {
                node.emerge = Math.max(0, node.emerge - 0.055);
            }
        }

        // Spring physics — retract toward cursor when collapsing, follow chain when emerged
        const retracting = !moving && node.emerge < 0.5;
        const tx = retracting ? mouseX : leader.x;
        const ty = retracting ? mouseY : leader.y;
        const t = retracting ? RETRACT_TENSION : TENSION;
        const d = retracting ? RETRACT_DAMPING : DAMPING;

        node.vx += (tx - node.x) * t;
        node.vy += (ty - node.y) * t;
        node.vx *= d;
        node.vy *= d;
        node.x += node.vx;
        node.y += node.vy;

        // Spacing from leader (only when visible)
        if (node.emerge > 0.05 && moving) {
            const sx = node.x - leader.x;
            const sy = node.y - leader.y;
            const sd = Math.hypot(sx, sy);
            if (sd > 0 && sd < SPACING) {
                const push = (SPACING - sd) / sd * 0.4;
                node.x += sx * push;
                node.y += sy * push;
            }
        }

    }

    requestAnimationFrame(animateTrail);
}

document.addEventListener('mousemove', (event) => {
    const target = event.target instanceof Element ? event.target : document.documentElement;
    const cursorStyle = window.getComputedStyle(target).cursor;
    let offsetX = 15;
    let offsetY = 17;

    if (cursorStyle.includes('link_cursor')) {
        offsetX = 7;
    } else if (cursorStyle.includes('text_cursor')) {
        offsetX = 0;
        offsetY = 0;
    }

    const newX = event.clientX + offsetX;
    const newY = event.clientY + offsetY;

    if (mouseX < -100) {
        trailPool.forEach((n) => { n.x = newX; n.y = newY; n.vx = 0; n.vy = 0; n.emerge = 0; });
    }

    mouseX = newX;
    mouseY = newY;
    lastMoveTime = performance.now();
}, { passive: true });

document.addEventListener('mouseleave', () => {
    mouseX = -200;
    mouseY = -200;
    lastMoveTime = 0;
});

if (window.matchMedia('(hover: hover) and (pointer: fine)').matches
    && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    requestAnimationFrame(animateTrail);
}

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
const loadingIndicator = document.getElementById('loading-indicator');
const reservationBody = document.getElementById('reservation-body');
const paginationContainer = document.getElementById('pagination-container');

// Class yang sama dengan view petugas/reservasi/index: baris hasil AJAX harus
// memakai token clay yang sama dengan render Blade, kalau tidak tampilan
// berubah begitu tab diganti.
const clayTableCell = 'px-5 py-4 align-middle';
// Aksi baris: Tolak (merah seperti tombol Keluar), Setujui (biru), Batalkan
// (merah), dan Detail (putih) paling kanan.
const clayDetailChip =
    'clay-pressable inline-flex h-9 items-center justify-center whitespace-nowrap rounded-full px-4 text-xs font-bold text-slate-600';
const clayApproveChip =
    'landing-button inline-flex h-9 items-center justify-center whitespace-nowrap rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-4 text-xs font-bold text-white';
const clayRejectChip =
    'clay-button-danger inline-flex h-9 items-center justify-center whitespace-nowrap rounded-full px-4 text-xs font-bold';
const dialogShell =
    'w-full max-w-md rounded-[1.8rem] border border-white/90 bg-gradient-to-br from-white/98 to-blue-50/80 p-6 shadow-[0_24px_60px_rgba(16,38,74,0.24),inset_2px_2px_6px_rgba(255,255,255,0.9)] backdrop:bg-slate-950/40';

if (reservationBody !== null) {
    let queueRequest;
    const fetchReservations = (url) => {
        const params = new URLSearchParams(new URL(url, window.location.origin).search);
        queueRequest?.abort();
        queueRequest = new AbortController();

        if (loadingIndicator !== null) loadingIndicator.classList.remove('hidden');

        fetch(`/petugas/reservasi/data?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: queueRequest.signal,
        })
            .then((response) => response.json())
            .then((data) => {
                if (loadingIndicator !== null) loadingIndicator.classList.add('hidden');

                document.querySelectorAll('[data-ajax-dialog]').forEach((dialog) => dialog.remove());

                reservationBody.innerHTML = '';
                if (data.reservations.length === 0) {
                    reservationBody.innerHTML = `
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center">
                                    <p class="text-base font-bold text-slate-800">Tidak ada reservasi</p>
                                    <p class="mt-1 text-sm text-slate-500">Belum ada reservasi pada tab ini.</p>
                                </td>
                            </tr>`;
                } else {
                    data.reservations.forEach((res) => {
                        const row = document.createElement('tr');
                        row.className = 'transition-colors hover:bg-white/70';

                        const statusHtml = res.status_html;
                        const actionsHtml = getActionsHtml(res);

                        row.innerHTML = `
                                <td class="${clayTableCell}">
                                    <p class="font-bold text-[#10264a]">${escapeHtml(res.user.name)}</p>
                                    <p class="text-xs text-slate-500">${escapeHtml(res.user.email)}</p>
                                </td>
                                <td class="${clayTableCell}">
                                    <p class="font-bold text-[#10264a]">${escapeHtml(res.facility.name)}</p>
                                    <p class="text-xs text-slate-500">${escapeHtml(res.facility.location)}</p>
                                </td>
                                <td class="${clayTableCell} whitespace-nowrap text-slate-700">
                                    ${formatDate(res.start_time)}
                                    <span class="mt-0.5 block text-xs text-slate-500">${formatTime(res.start_time)} – ${formatTime(res.end_time)} WIB</span>
                                </td>
                                <td class="${clayTableCell}">${statusHtml}</td>
                                <td class="${clayTableCell} whitespace-nowrap text-xs text-slate-500">${formatDate(res.created_at)} WIB</td>
                                <td class="${clayTableCell}">
                                    ${actionsHtml}
                                </td>
                            `;
                        reservationBody.appendChild(row);

                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                        const schedule = `${escapeHtml(res.facility.name)} · ${formatDate(res.start_time)}, ${formatTime(res.start_time)} – ${formatTime(res.end_time)} WIB`;
                        const dialogs = [];

                        if (res.status === 'pending') {
                            dialogs.push(`
                                <dialog data-ajax-dialog id="approve-${res.id}" class="${dialogShell}" aria-labelledby="approve-${res.id}-title">
                                    <h3 id="approve-${res.id}-title" class="text-lg font-extrabold tracking-tight text-[#10264a]">Setujui reservasi?</h3>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-600">${schedule}</p>
                                    <form method="POST" action="/petugas/reservasi/${res.id}/approve" class="mt-5">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <p class="clay-inset rounded-2xl p-3 text-xs leading-relaxed text-slate-600">Menyetujui mengunci slot dan menolak reservasi lain yang bertabrakan.</p>
                                        <div class="mt-4 flex items-center justify-end gap-2">
                                            <button type="button" data-close-dialog="approve-${res.id}" class="clay-pressable inline-flex h-10 items-center justify-center rounded-full px-5 text-sm font-semibold text-blue-700">Kembali</button>
                                            <button type="submit" class="landing-button inline-flex h-10 items-center justify-center rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 text-sm font-bold text-white">Konfirmasi Setujui</button>
                                        </div>
                                    </form>
                                </dialog>
                                <dialog data-ajax-dialog id="reject-${res.id}" class="${dialogShell}" aria-labelledby="reject-${res.id}-title">
                                    <h3 id="reject-${res.id}-title" class="text-lg font-extrabold tracking-tight text-[#10264a]">Tolak reservasi?</h3>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-600">${schedule}</p>
                                    <form method="POST" action="/petugas/reservasi/${res.id}/reject" class="mt-5">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <div>
                                            <label for="reject-reason-${res.id}" class="block text-sm font-semibold text-slate-700">Alasan penolakan</label>
                                            <textarea id="reject-reason-${res.id}" name="reason" rows="3" required minlength="10" maxlength="255" placeholder="Jelaskan alasan penolakan (min. 10 karakter)" class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-rose-500/15"></textarea>
                                        </div>
                                        <div class="mt-4 flex items-center justify-end gap-2">
                                            <button type="button" data-close-dialog="reject-${res.id}" class="clay-pressable inline-flex h-10 items-center justify-center rounded-full px-5 text-sm font-semibold text-blue-700">Kembali</button>
                                            <button type="submit" class="clay-button-danger inline-flex h-10 items-center justify-center rounded-full px-5 text-sm font-bold">Tolak Reservasi</button>
                                        </div>
                                    </form>
                                </dialog>`);
                        }

                        if (res.status === 'approved') {
                            dialogs.push(`
                                <dialog data-ajax-dialog id="cancel-${res.id}" class="${dialogShell}" aria-labelledby="cancel-${res.id}-title">
                                    <h3 id="cancel-${res.id}-title" class="text-lg font-extrabold tracking-tight text-[#10264a]">Batalkan reservasi?</h3>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-600">${schedule}</p>
                                    <form method="POST" action="/petugas/reservasi/${res.id}/cancel" class="mt-5">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <div>
                                            <label for="cancel-reason-${res.id}" class="block text-sm font-semibold text-slate-700">Alasan pembatalan</label>
                                            <textarea id="cancel-reason-${res.id}" name="cancel_reason" rows="3" required minlength="10" maxlength="255" placeholder="Jelaskan alasan pembatalan (min. 10 karakter)" class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-rose-500/15"></textarea>
                                        </div>
                                        <div class="mt-4 flex items-center justify-end gap-2">
                                            <button type="button" data-close-dialog="cancel-${res.id}" class="clay-pressable inline-flex h-10 items-center justify-center rounded-full px-5 text-sm font-semibold text-blue-700">Kembali</button>
                                            <button type="submit" class="clay-button-danger inline-flex h-10 items-center justify-center rounded-full px-5 text-sm font-bold">Batalkan Reservasi</button>
                                        </div>
                                    </form>
                                </dialog>`);
                        }

                        document.body.insertAdjacentHTML('beforeend', dialogs.join(''));
                    });
                }

                if (paginationContainer !== null) {
                    paginationContainer.innerHTML = data.pagination_html;
                    paginationContainer.classList.toggle('hidden', data.pagination.last_page <= 1);
                }
                history.pushState({}, '', url);

                const activeTab = params.get('tab');

                if (activeTab !== null) {
                    setActiveQueueTab(activeTab);
                }
            })
            .catch((error) => {
                if (error.name === 'AbortError') return;
                if (loadingIndicator !== null) loadingIndicator.classList.add('hidden');
                console.error('Failed to fetch reservations:', error);
            });
    };

    // Tab dan pagination antrean dimuat lewat AJAX; klik dimodifikasi tetap
    // membuka tab browser sepertita biasanya.
    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const link = event.target.closest('a[data-queue-tab], #pagination-container a[href]');

        if (link === null) {
            return;
        }

        const url = link.getAttribute('href');

        if (url === null || new URL(url, window.location.href).origin !== window.location.origin) {
            return;
        }

        event.preventDefault();
        fetchReservations(url);
    });
}

function getActionsHtml(res) {
    let html = '<div class="flex flex-wrap items-center justify-end gap-2">';

    if (res.status === 'pending') {
        html += `<button type="button" data-open-dialog="reject-${res.id}" class="${clayRejectChip}">Tolak</button>`;
        html += `<button type="button" data-open-dialog="approve-${res.id}" class="${clayApproveChip}">Setujui</button>`;
    }

    if (res.status === 'approved') {
        html += `<button type="button" data-open-dialog="cancel-${res.id}" class="${clayRejectChip}">Batalkan</button>`;
    }

    html += `<a href="/petugas/reservasi/${res.id}" class="${clayDetailChip}">Detail</a>`;

    return html + '</div>';
}

function setActiveQueueTab(tabKey) {
    document.querySelectorAll('a[data-queue-tab]').forEach((link) => {
        const active = link.dataset.tabActive.split(' ');
        const inactive = link.dataset.tabInactive.split(' ');
        const isActive = link.dataset.queueTab === tabKey;

        // Kedua set kelas dilepas dulu supaya tab yang baru tidak aktif tidak
        // menyisakan gaya gradient dari tab sebelumnya.
        link.classList.remove(...active, ...inactive);
        link.classList.add(...(isActive ? active : inactive));

        if (link.dataset.queueTab === tabKey) {
            link.setAttribute('aria-current', 'page');
        } else {
            link.removeAttribute('aria-current');
        }
    });
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
            const className = isActive ? link.dataset.navActive : link.dataset.navInactive;
            if (link.className !== className) {
                link.className = className;
            }

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

    const header = landingPage.querySelector('header');
    let navFrame = null;
    const updateNav = () => {
        navFrame = null;
        const boundary = header.getBoundingClientRect().bottom + 32;
        const activeSection = sections.filter((section) => section.getBoundingClientRect().top <= boundary).at(-1);
        activateNav(activeSection?.id ?? 'top');
    };
    const scheduleNavUpdate = () => {
        if (navFrame === null) {
            navFrame = requestAnimationFrame(updateNav);
        }
    };
    const updateHeaderOffset = () => {
        document.documentElement.style.setProperty('--landing-header-offset', `${header.getBoundingClientRect().height + 20}px`);
        scheduleNavUpdate();
    };

    window.addEventListener('scroll', scheduleNavUpdate, { passive: true });
    window.addEventListener('resize', updateHeaderOffset);
    updateHeaderOffset();
}

// Animasi masuk card untuk seluruh halaman: satu pantulan (spring) untuk card
// di dalam, dan fade untuk card/panel besar. Klasifikasi card memakai pemilik
// kelas yang sudah ada; nilai gerakan ada di @keyframes clay-card-spring dan
// clay-panel-fade. Target hanya dianimasikan saat pertama kali masuk viewport.
const motionReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const motionTargets = {
    // Card besar: panel halaman, container, navigasi, dan holder tabel. Elemen
    // sticky dikecualikan karena tidak pernah keluar viewport, dan kartu di
    // dalam juga dikecualikan karena beberapa kartu statistik memakai clay-inset
    // sekaligus.
    panel: {
        selector: ':is(.auth-clay-card, .dashboard-clay-card, .landing-panel, .clay-nav, .clay-inset):not(.sticky):not(:is(.dashboard-clay-stat, .dashboard-clay-list, .landing-card, .landing-step-card, .clay-pressable))',
        className: 'is-faded',
    },
    card: {
        selector: ':is(.dashboard-clay-stat, .dashboard-clay-list, .landing-card, .landing-step-card, .clay-pressable)',
        className: 'is-springed',
    },
};
let motionObserver = null;

const motionClassFor = (element) => (
    element.matches(motionTargets.panel.selector) ? motionTargets.panel.className : motionTargets.card.className
);

const observeMotionCards = (root) => {
    if (motionObserver === null || root === null) {
        return;
    }

    Object.values(motionTargets).forEach((target) => {
        root.querySelectorAll(target.selector).forEach((element) => {
            if (element.classList.contains(target.className)) {
                return;
            }
            const position = element.parentElement ? [...element.parentElement.children].indexOf(element) : 0;

            element.classList.add('motion-pending');
            element.style.setProperty('--motion-delay', `${Math.min(Math.max(position, 0), 2) * 70}ms`);
            motionObserver.observe(element);
        });
    });
};

if (document.body !== null && 'IntersectionObserver' in window && !motionReducedMotion) {
    document.body.classList.add('motion-ready');

    motionObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            const element = entry.target;
            if (!entry.isIntersecting) {
                return;
            }

            element.classList.remove('motion-pending');
            element.classList.add(motionClassFor(element));
            motionObserver.unobserve(element);
        });
    }, { rootMargin: '0px 0px -5% 0px', threshold: 0 });

    observeMotionCards(document);
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
                    // Card baru dari server perlu observe ulang supaya animasi
                    // pop saat scroll tetap jalan untuk hasil pencarian.
                    observeMotionCards(landingGridContainer);
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
