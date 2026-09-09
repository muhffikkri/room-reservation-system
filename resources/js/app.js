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

const landing = document.querySelector('[data-landing]');

if (landing !== null) {
    const tabs = landing.querySelectorAll('[data-landing-tab]');
    const grids = landing.querySelectorAll('[data-facility-grid]');
    const indicator = landing.querySelector('[data-facility-indicator]');
    const schedule = document.getElementById('jadwal-preview');

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