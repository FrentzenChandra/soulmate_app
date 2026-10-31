import './bootstrap';
import ApexCharts from 'apexcharts';
import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';
import { TabulatorFull as Tabulator } from 'tabulator-tables';
import Swal from 'sweetalert2';
import { Fireworks } from 'fireworks-js';

window.ApexCharts = ApexCharts;
window.Html5Qrcode = Html5Qrcode;
window.Html5QrcodeSupportedFormats = Html5QrcodeSupportedFormats;
window.Tabulator = Tabulator;
window.Swal = Swal;
window.Fireworks = Fireworks;

// Controllable Animated SVG Icons
const getIconSvg = (type) => {
    switch (type) {
        case 'success':
            return `<svg class="swal-svg-icon swal-svg-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle class="swal-svg-circle" cx="12" cy="12" r="9"></circle><path class="swal-svg-check" d="M8 12.5l2.5 2.5L16 9"></path></svg>`;
        case 'error':
            return `<svg class="swal-svg-icon swal-svg-error" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle class="swal-svg-circle" cx="12" cy="12" r="9"></circle><line class="swal-svg-line" x1="15" y1="9" x2="9" y2="15"></line><line class="swal-svg-line" x1="9" y1="9" x2="15" y2="15"></line></svg>`;
        case 'warning':
            return `<svg class="swal-svg-icon swal-svg-warning" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle class="swal-svg-circle" cx="12" cy="12" r="9"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`;
        case 'info':
        default:
            return `<svg class="swal-svg-icon swal-svg-info" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle class="swal-svg-circle" cx="12" cy="12" r="9"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`;
    }
};

// Modern Pastel Toast Helper
window.swalToast = (type = 'success', title, text = '') => {
    const toastClass = 'swal-toast-' + type;
    const config = {
        toast: true,
        position: 'top-end',
        icon: type,
        iconHtml: getIconSvg(type),
        title: title || 'Notifikasi',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        customClass: {
            popup: `swal-custom-toast ${toastClass}`,
            icon: `swal-custom-icon swal-icon-${type}`,
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    };
    if (text) {
        config.text = text;
    }
    return Swal.fire(config);
};

// Modern Pastel Modal Helper
window.swalModal = (type = 'success', title, text = '', confirmButtonText = 'Mengerti') => {
    const config = {
        icon: type,
        iconHtml: getIconSvg(type),
        title: title || 'Notifikasi',
        confirmButtonText: confirmButtonText,
        customClass: {
            popup: 'swal-custom-modal',
            icon: `swal-custom-icon swal-icon-${type}`,
            confirmButton: 'swal-confirm-btn',
            cancelButton: 'swal-cancel-btn',
        },
        buttonsStyling: false,
    };
    if (text) {
        config.text = text;
    }
    return Swal.fire(config);
};

// Modern Pastel Confirm Helper
window.swalConfirm = (title, text = '', options = {}) => {
    const iconType = options.icon || (options.danger ? 'error' : 'warning');
    const config = {
        icon: iconType,
        iconHtml: getIconSvg(iconType),
        title: title || 'Konfirmasi Tindakan',
        showCancelButton: true,
        confirmButtonText: options.confirmText || 'Ya, Lanjutkan',
        cancelButtonText: options.cancelText || 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'swal-custom-modal swal-confirm-modal',
            icon: `swal-custom-icon swal-icon-${iconType}`,
            confirmButton: options.danger ? 'swal-confirm-btn-danger' : 'swal-confirm-btn',
            cancelButton: 'swal-cancel-btn',
            actions: 'swal-confirm-actions'
        },
        buttonsStyling: false,
    };
    if (text) {
        config.text = text;
    }
    return Swal.fire(config);
};

// Global Livewire & Event Listener for SweetAlert2
const initSwalListeners = () => {
    const handleSwalEvent = (defaultType, e) => {
        const detail = e.detail;
        let type = defaultType;
        let title = '';
        let text = '';
        let isModal = false;

        if (typeof detail === 'string') {
            title = detail;
        } else if (Array.isArray(detail) && detail.length > 0) {
            const first = detail[0];
            if (typeof first === 'string') {
                title = first;
                text = detail[1] || '';
            } else if (typeof first === 'object' && first !== null) {
                type = first.type || defaultType;
                title = first.title || first.message || (type === 'success' ? 'Berhasil' : 'Pemberitahuan');
                text = first.text || (first.title ? (first.message || '') : '');
                isModal = !!first.modal;
            }
        } else if (typeof detail === 'object' && detail !== null) {
            type = detail.type || defaultType;
            title = detail.title || detail.message || (type === 'success' ? 'Berhasil' : 'Pemberitahuan');
            text = detail.text || (detail.title ? (detail.message || '') : '');
            isModal = !!detail.modal;
        }

        if (!title) {
            title = type === 'success' ? 'Berhasil!' : (type === 'error' ? 'Terjadi Kesalahan' : 'Pemberitahuan');
        }

        if (isModal) {
            window.swalModal(type, title, text);
        } else {
            window.swalToast(type, title, text);
        }
    };

    window.addEventListener('swal:success', (e) => handleSwalEvent('success', e));
    window.addEventListener('swal:error', (e) => handleSwalEvent('error', e));
    window.addEventListener('swal:warning', (e) => handleSwalEvent('warning', e));
    window.addEventListener('swal:info', (e) => handleSwalEvent('info', e));
    window.addEventListener('swal:toast', (e) => {
        const type = e.detail?.type || e.detail?.[0]?.type || 'success';
        handleSwalEvent(type, e);
    });
    window.addEventListener('swal:modal', (e) => {
        const type = e.detail?.type || e.detail?.[0]?.type || 'info';
        const title = e.detail?.title || e.detail?.[0]?.title || 'Pemberitahuan';
        const text = e.detail?.text || e.detail?.[0]?.text || '';
        window.swalModal(type, title, text);
    });
    window.addEventListener('swal:confirm', (e) => {
        const detail = Array.isArray(e.detail) ? e.detail[0] : (e.detail || {});
        const title = detail.title || 'Apakah Anda Yakin?';
        const text = detail.text || detail.message || '';
        const danger = detail.danger !== false;
        const confirmText = detail.confirmText || 'Ya, Lanjutkan';
        const cancelText = detail.cancelText || 'Batal';
        const method = detail.method || detail.action;
        const params = detail.params || [];
        const componentId = detail.componentId || e.target?.closest('[wire\\:id]')?.getAttribute('wire:id');

        window.swalConfirm(title, text, {
            danger: danger,
            confirmText: confirmText,
            cancelText: cancelText,
            icon: detail.icon || (danger ? 'error' : 'warning')
        }).then((result) => {
            if (result.isConfirmed) {
                if (method && window.Livewire) {
                    const component = componentId ? window.Livewire.find(componentId) : null;
                    if (component) {
                        component.call(method, ...params);
                    } else {
                        Livewire.dispatch(method, ...params);
                    }
                } else if (typeof detail.onConfirm === 'function') {
                    detail.onConfirm();
                }
            }
        });
    });
    window.addEventListener('swal', (e) => {
        const type = e.detail?.type || e.detail?.[0]?.type || 'info';
        handleSwalEvent(type, e);
    });
    window.addEventListener('notify', (e) => {
        const type = e.detail?.type || e.detail?.[0]?.type || 'success';
        handleSwalEvent(type, e);
    });
};

// Universal Global Click Interceptor for wire:confirm / data-confirm
const handleGlobalConfirmClick = (e) => {
    const trigger = e.target.closest('[wire\\:confirm], [data-confirm], [x-confirm]');
    if (!trigger) return;

    if (trigger.__swal_confirmed) {
        delete trigger.__swal_confirmed;
        return;
    }

    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    const message = trigger.getAttribute('wire:confirm') || 
                    trigger.getAttribute('data-confirm') || 
                    trigger.getAttribute('x-confirm') || 
                    'Apakah Anda yakin ingin melanjutkan tindakan ini?';
    const title = trigger.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
    const danger = !trigger.hasAttribute('data-confirm-safe');

    window.swalConfirm(title, message, {
        danger: danger,
        confirmText: 'Ya, Lanjutkan',
        cancelText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            trigger.__swal_confirmed = true;
            const origConfirm = trigger.__livewire_confirm;
            trigger.__livewire_confirm = (action) => action();
            
            trigger.click();
            
            setTimeout(() => {
                if (origConfirm) {
                    trigger.__livewire_confirm = origConfirm;
                }
            }, 100);
        }
    });
};

document.addEventListener('click', handleGlobalConfirmClick, true);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSwalListeners);
} else {
    initSwalListeners();
}

// =========================================================================
// Multi-Tab Logout & Expired Session Graceful Handler (Anti-419 / Auto Redirect)
// =========================================================================
const redirectToLogin = () => {
    localStorage.setItem('soulmate_logged_out', Date.now().toString());
    if (window.location.pathname !== '/login') {
        window.location.href = '/login';
    }
};

// Sync logout across open browser tabs
window.addEventListener('storage', (e) => {
    if (e.key === 'soulmate_logged_out' && e.newValue) {
        if (window.location.pathname !== '/login') {
            window.location.href = '/login';
        }
    }
});

// Clear logout marker when on /login page
if (window.location.pathname === '/login') {
    localStorage.removeItem('soulmate_logged_out');
}

// Broadcast logout on logout form submit
document.addEventListener('submit', (e) => {
    if (e.target && e.target.action && e.target.action.includes('/logout')) {
        localStorage.setItem('soulmate_logged_out', Date.now().toString());
    }
});

// Intercept Livewire 3 request failures for 419 Page Expired & 401 Unauthorized
document.addEventListener('livewire:init', () => {
    if (window.Livewire) {
        window.Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                if (status === 419 || status === 401) {
                    preventDefault();
                    redirectToLogin();
                }
            });
        });

        window.Livewire.hook('navigate', (context) => {
            if (context && typeof context.fail === 'function') {
                context.fail(({ status, preventDefault }) => {
                    if (status === 419 || status === 401) {
                        preventDefault();
                        redirectToLogin();
                    }
                });
            }
        });
    }
});


