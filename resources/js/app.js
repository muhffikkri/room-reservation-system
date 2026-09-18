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

const landing = document.querySelector('[data-landing]');

if (landing !== null) {
    const navLinks = landing.querySelectorAll('[data-landing-nav][data-nav-active]');
    const sections = landing.querySelectorAll('[data-landing-section]');
    const tabs = landing.querySelectorAll('[data-landing-tab]');
    const grids = landing.querySelectorAll('[data-facility-grid]');
    const indicator = landing.querySelector('[data-facility-indicator]');
    const schedule = document.getElementById('jadwal-preview');

    const activateNav = (sectionId) => {
        navLinks.forEach((link) => {
            const active = link.dataset.landingNav === sectionId;

            link.className = active ? link.dataset.navActive : link.dataset.navInactive;

            if (active) {
                link.setAttribute('aria-current', 'page');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    };

    const initialSection = window.location.hash.slice(1);

    activateNav([...sections].some((section) => section.id === initialSection) ? initialSection : 'top');

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

    const activateFacility = (facilityId) => {
        tabs.forEach((tab) => {
            const active = tab.dataset.landingTab === String(facilityId);

            tab.className = active ? tab.dataset.tabActive : tab.dataset.tabInactive;
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
            tab.tabIndex = active ? 0 : -1;
        });

        grids.forEach((grid) => {
            grid.classList.toggle('hidden', grid.dataset.facilityGrid !== String(facilityId));

            if (grid.dataset.facilityGrid === String(facilityId) && indicator !== null) {
                indicator.textContent = `Fasilitas: ${grid.dataset.facilityName}`;
            }
        });
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => activateFacility(tab.dataset.landingTab));
    });

    tabs[0]?.parentElement?.addEventListener('keydown', (event) => {
        if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft' && event.key !== 'Home' && event.key !== 'End') {
            return;
        }

        const currentIndex = [...tabs].findIndex((tab) => tab.tabIndex === 0);
        let nextIndex = currentIndex;

        if (event.key === 'ArrowRight') {
            nextIndex = Math.min(currentIndex + 1, tabs.length - 1);
        }

        if (event.key === 'ArrowLeft') {
            nextIndex = Math.max(currentIndex - 1, 0);
        }

        if (event.key === 'Home') {
            nextIndex = 0;
        }

        if (event.key === 'End') {
            nextIndex = tabs.length - 1;
        }

        const nextTab = tabs[nextIndex];

        if (nextTab !== undefined) {
            event.preventDefault();
            nextTab.focus();
            activateFacility(nextTab.dataset.landingTab);
        }
    });

    landing.querySelectorAll('[data-landing-go]').forEach((button) => {
        button.addEventListener('click', () => {
            activateFacility(button.dataset.landingGo);

            if (schedule !== null) {
                schedule.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}
