import './bootstrap';

import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Alpine = Alpine;
window.Swal = Swal;

const originalSwalFire = Swal.fire.bind(Swal);

const swalBaseOptions = {
    buttonsStyling: true,
    scrollbarPadding: false,
    heightAuto: false,
    width: 'min(92vw, 34rem)',
    padding: '1.5rem',
    target: document.body,
    showClass: {
        popup: 'swal2-animate-in',
        backdrop: 'swal2-backdrop-in',
    },
    hideClass: {
        popup: 'swal2-animate-out',
        backdrop: 'swal2-backdrop-out',
    },
    customClass: {
        confirmButton: 'swal2-confirm-btn',
        cancelButton: 'swal2-cancel-btn',
        denyButton: 'swal2-deny-btn',
        popup: 'swal2-popup--app',
        container: 'swal2-container--app',
        actions: 'swal2-actions--app',
        htmlContainer: 'swal2-html-container--app',
    },
};

const buildSwalOptions = (options = {}) => ({
    ...swalBaseOptions,
    ...options,
    target: options.target || document.body,
    showClass: {
        ...swalBaseOptions.showClass,
        ...(options.showClass || {}),
    },
    hideClass: {
        ...swalBaseOptions.hideClass,
        ...(options.hideClass || {}),
    },
    customClass: {
        ...swalBaseOptions.customClass,
        ...(options.customClass || {}),
    },
});

window.swalFire = function (options = {}) {
    if (!window.Swal) {
        return null;
    }

    return originalSwalFire(buildSwalOptions(options));
};

Swal.fire = function (options = {}) {
    return window.swalFire(options);
};

window.confirmSwal = async function ({
    title = 'Are you sure?',
    text = '',
    icon = 'warning',
    confirmButtonText = 'Yes',
    cancelButtonText = 'Cancel',
    confirmButtonColor = '#ef4444',
    reverseButtons = true,
} = {}) {
    const result = await window.swalFire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText,
        confirmButtonColor,
        reverseButtons,
    });

    return Boolean(result?.isConfirmed);
};

window.dispatchSwal = function (detail = {}) {
    window.dispatchEvent(new CustomEvent('swal', { detail }));
};

window.addEventListener('swal', (event) => {
    window.swalFire(event.detail || {});
});

const bindSwalInterceptors = () => {
    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('form[data-swal-confirm]')) {
            return;
        }

        if (form.dataset.swalSkipGlobal === 'true') {
            return;
        }

        event.preventDefault();

        const confirmed = await window.confirmSwal({
            title: form.dataset.swalTitle || 'Are you sure?',
            text: form.dataset.swalText || 'This action cannot be undone.',
            icon: form.dataset.swalIcon || 'warning',
            confirmButtonText: form.dataset.swalConfirmButton || 'Yes',
            cancelButtonText: form.dataset.swalCancelButton || 'Cancel',
            confirmButtonColor: form.dataset.swalConfirmColor || '#ef4444',
        });

        if (confirmed) {
            form.submit();
        }
    });

    document.addEventListener('click', async (event) => {
        const link = event.target.closest?.('a[data-swal-link-confirm]');
        if (!link) {
            return;
        }

        if (link.dataset.swalSkipGlobal === 'true') {
            return;
        }

        event.preventDefault();

        const confirmed = await window.confirmSwal({
            title: link.dataset.swalTitle || 'Are you sure?',
            text: link.dataset.swalText || 'This action cannot be undone.',
            icon: link.dataset.swalIcon || 'warning',
            confirmButtonText: link.dataset.swalConfirmButton || 'Download',
            cancelButtonText: link.dataset.swalCancelButton || 'Cancel',
            confirmButtonColor: link.dataset.swalConfirmColor || '#111827',
        });

        if (confirmed) {
            window.location.href = link.href;
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindSwalInterceptors, { once: true });
} else {
    bindSwalInterceptors();
}

// Alpine.start(); // Commented out to avoid conflict with Livewire v4
