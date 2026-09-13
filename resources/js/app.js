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

    landing.querySelectorAll('[data-landing-go]').forEach((button) => {
        button.addEventListener('click', () => {
            activateFacility(button.dataset.landingGo);

            if (schedule !== null) {
                schedule.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}
