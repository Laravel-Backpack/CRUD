/* ===================================================================
 * Backpack Notifications — Toasts (Noty drop-in replacement)
 * =================================================================== */

(function () {
    'use strict';

    const config = window.backpackToastConfig || {};

    const TYPE_CLASSES = {
        success: 'text-bg-success',
        error: 'text-bg-danger',
        danger: 'text-bg-danger',
        warning: 'text-bg-warning',
        info: 'text-bg-info',
        information: 'text-bg-info',
        alert: 'text-bg-info',
        notice: 'text-bg-info',
        primary: 'text-bg-primary',
        secondary: 'text-bg-secondary',
    };

    function resolveContainer(position) {
        const cls = position
            ? 'backpack-toast-container position-fixed p-3 ' + position
            : (config.container_class || 'backpack-toast-container position-fixed top-0 end-0 p-3');

        let container = document.querySelector('.' + cls.split(' ').join('.'));
        if (!container) {
            container = document.createElement('div');
            container.className = cls;
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-atomic', 'true');
            document.body.appendChild(container);
        }
        return container;
    }

    function resolveOptions(raw) {
        if (typeof raw === 'string') {
            raw = { text: raw };
        }
        raw = raw || {};

        let timeout = parseInt(raw.timeout);
        if (isNaN(timeout)) {
            timeout = parseInt(config.default_timeout) || 2500;
        }

        let position = null;
        if (raw.position && config.positions && config.positions[raw.position]) {
            position = config.positions[raw.position];
        }

        return {
            type: raw.type || 'info',
            text: raw.message || raw.text || '',
            title: raw.title || '',
            icon: raw.icon || null,
            timeout: timeout,
            dismissible: raw.dismissible !== undefined ? raw.dismissible : (config.dismissible !== false),
            closeOnClick: raw.close_on_click !== undefined ? raw.close_on_click : (config.close_on_click !== false),
            className: raw.className || null,
            position: position,
        };
    }

    function buildToast(opts) {
        const el = document.createElement('div');
        const colorClass = opts.className || TYPE_CLASSES[opts.type] || 'text-bg-secondary';
        el.className = 'toast align-items-center ' + colorClass;
        el.setAttribute('role', 'alert');
        el.setAttribute('aria-live', 'assertive');
        el.setAttribute('aria-atomic', 'true');

        if (opts.timeout > 0) {
            el.setAttribute('data-bs-delay', opts.timeout);
        } else {
            el.setAttribute('data-bs-autohide', 'false');
        }

        const row = document.createElement('div');
        row.className = 'd-flex align-items-center';

        if (opts.icon) {
            const icon = document.createElement('i');
            icon.className = opts.icon + ' me-1 ms-2 fs-2';
            row.appendChild(icon);
        }

        const body = document.createElement('div');
        body.className = 'toast-body';

        if (opts.title) {
            const title = document.createElement('strong');
            title.className = 'd-block';
            title.textContent = opts.title;
            body.appendChild(title);
        }

        if (opts.text) {
            const span = document.createElement('span');
            span.innerHTML = opts.text;
            body.appendChild(span);
        }

        row.appendChild(body);

        if (opts.dismissible) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn-close me-2 ms-auto';
            btn.setAttribute('data-bs-dismiss', 'toast');
            btn.setAttribute('aria-label', 'Close');
            row.appendChild(btn);
        }

        el.appendChild(row);
        return el;
    }

    class Noty {
        constructor(options) {
            this._opts = resolveOptions(options);
        }

        show() {
            if (!this._opts.text && !this._opts.title) {
                return this;
            }

            const container = resolveContainer(this._opts.position);
            const toastEl = buildToast(this._opts);

            container.appendChild(toastEl);

            const bsToast = bootstrap.Toast.getOrCreateInstance(toastEl);
            bsToast.show();

            if (this._opts.closeOnClick) {
                toastEl.addEventListener('click', () => bsToast.hide());
            }

            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());

            return this;
        }

        static closeAll() {
            document.querySelectorAll('.backpack-toast-container').forEach(container => {
                container.querySelectorAll('.toast').forEach(el => {
                    const toast = bootstrap.Toast.getInstance(el);
                    if (toast) toast.hide();
                });
            });
        }
    }

    window.Noty = Noty;
})();


/* ===================================================================
 * Backpack Notifications — Modals (SweetAlert drop-in replacement)
 * =================================================================== */

(function () {
    'use strict';

    const SWAL_ICONS = {
        warning: 'la la-exclamation-triangle text-warning',
        error: 'la la-times-circle text-danger',
        success: 'la la-check-circle text-success',
        info: 'la la-info-circle text-info',
    };

    function classNameToBtn(cn) {
        if (!cn) return 'btn btn-secondary';
        const color = cn.replace(/^bg-/, '');
        return 'btn btn-' + color;
    }

    function resolveButtons(config) {
        if (config.buttons === true) {
            return { cancel: { text: 'Cancel', value: null, className: 'bg-secondary' },
                     confirm: { text: 'OK', value: true, className: 'bg-primary' } };
        }

        if (Array.isArray(config.buttons)) {
            const obj = {};
            config.buttons.forEach((item, i) => {
                const key = i === 0 ? 'cancel' : 'confirm';
                obj[key] = typeof item === 'string'
                    ? { text: item, value: i === 0 ? null : true, className: i === 0 ? 'bg-secondary' : 'bg-primary' }
                    : item;
            });
            return obj;
        }

        if (config.button && config.buttons === undefined) {
            const btn = typeof config.button === 'string'
                ? { text: config.button, value: true, className: 'bg-primary' }
                : config.button;
            return { confirm: btn };
        }

        return config.buttons || {};
    }

    function destroyExistingModal() {
        const existing = document.querySelector('.backpack-swal-modal');
        if (existing) {
            const instance = bootstrap.Modal.getInstance(existing);
            if (instance) instance.hide();
            existing.remove();
        }
    }

    function buildSwalContent(content) {
        if (typeof content === 'string') {
            if (content === 'input') {
                const el = document.createElement('input');
                el.type = 'text';
                el.placeholder = '';
                return el;
            }
            return null;
        }

        if (content instanceof Node) {
            return content;
        }

        if (content.element) {
            const el = document.createElement(content.element);
            if (content.attributes) {
                Object.entries(content.attributes).forEach(([key, val]) => {
                    el.setAttribute(key, val);
                });
            }
            return el;
        }

        return null;
    }

    function buildSwalModal(config) {
        destroyExistingModal();

        const el = document.createElement('div');
        el.className = 'modal fade backpack-swal-modal';
        el.setAttribute('tabindex', '-1');

        if (config.closeOnClickOutside === false) {
            el.setAttribute('data-bs-backdrop', 'static');
        }
        if (config.closeOnEsc === false) {
            el.setAttribute('data-bs-keyboard', 'false');
        }

        const dialog = document.createElement('div');
        dialog.className = 'modal-dialog modal-dialog-centered';

        const content = document.createElement('div');
        content.className = 'modal-content border-0 shadow-lg';
        if (config.className) {
            content.className += ' ' + config.className;
        }

        const hasButtons = (config.buttons !== false && config.buttons != null)
                        || (config.button && config.button !== false);

        const body = document.createElement('div');
        body.className = 'modal-body text-center py-4 px-4 position-relative';

        if (config.icon && SWAL_ICONS[config.icon]) {
            const icon = document.createElement('i');
            icon.className = SWAL_ICONS[config.icon] + ' fs-1 mb-3 d-block';
            body.appendChild(icon);
        }

        if (config.title) {
            const h5 = document.createElement('h5');
            h5.className = 'mb-2';
            h5.textContent = config.title;
            body.appendChild(h5);
        }

        if (config.text) {
            const p = document.createElement('p');
            p.className = 'text-muted mb-0';
            p.textContent = config.text;
            body.appendChild(p);
        }

        if (config.showCloseButton) {
            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'btn-close position-absolute top-0 end-0 mt-2 me-2';
            closeBtn.setAttribute('data-bs-dismiss', 'modal');
            closeBtn.setAttribute('aria-label', 'Close');
            body.appendChild(closeBtn);
        }

        if (config.content) {
            const input = buildSwalContent(config.content);
            if (input) {
                input.className = (input.className || '') + ' form-control mt-3 text-center';
                body.appendChild(input);
            }
        }

        content.appendChild(body);

        if (hasButtons) {
            const footer = document.createElement('div');
            footer.className = 'modal-footer justify-content-center border-0 pt-0';

            let buttons = resolveButtons(config);

            const keys = Object.keys(buttons);
            const lastIdx = keys.length - 1;

            keys.forEach((key, idx) => {
                const btnConfig = buttons[key];
                const isLast = idx === lastIdx;
                let className = classNameToBtn(btnConfig.className);
                if (isLast && config.dangerMode) {
                    className = 'btn btn-danger';
                }

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = className + ' px-4';
                btn.textContent = btnConfig.text || key;
                btn.setAttribute('data-swal-value', JSON.stringify(btnConfig.value));
                btn.setAttribute('data-swal-key', key);
                if (btnConfig.closeModal !== false) {
                    btn.setAttribute('data-bs-dismiss', 'modal');
                }
                footer.appendChild(btn);
            });

            content.appendChild(footer);
        }

        dialog.appendChild(content);
        el.appendChild(dialog);

        return el;
    }

    function swal(arg1, arg2, arg3) {
        let config = arg1;

        if (typeof arg1 === 'string') {
            if (arg2 === undefined) {
                config = { text: arg1 };
            } else if (typeof arg2 === 'string') {
                config = { title: arg1, text: arg2 };
                if (typeof arg3 === 'string') {
                    config.icon = arg3;
                }
            } else {
                config = Object.assign({ text: arg1 }, arg2);
            }
        }

        config = config || {};
        let currentEl = null;
        const el = buildSwalModal(config);

        document.body.appendChild(el);

        const bsModal = new bootstrap.Modal(el);
        bsModal.show();

        const input = el.querySelector('input');
        if (input) {
            input.focus();
            input.addEventListener('input', () => {
                const confirmBtn = el.querySelector('[data-swal-key="confirm"]');
                if (confirmBtn) {
                    confirmBtn.setAttribute('data-swal-value', JSON.stringify(input.value));
                }
            });
        }

        let resolved = false;

        return new Promise((resolve) => {
            function doResolve(value) {
                if (resolved) return;
                resolved = true;
                resolve(value);
            }

            el.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-swal-value]');
                if (btn) {
                    doResolve(JSON.parse(btn.getAttribute('data-swal-value')));
                }
            });

            el.addEventListener('hidden.bs.modal', () => {
                doResolve(null);
                setTimeout(() => {
                    if (el.parentNode) el.remove();
                }, 100);
            });

            if (config.timer && !config.buttons) {
                setTimeout(() => {
                    doResolve(null);
                    bsModal.hide();
                }, config.timer);
            }
        });
    }

    swal.close = function () {
        const el = document.querySelector('.backpack-swal-modal');
        if (el) {
            const instance = bootstrap.Modal.getInstance(el);
            if (instance) instance.hide();
        }
    };

    swal.setActionValue = function (value) {
        const el = document.querySelector('.backpack-swal-modal');
        if (!el) return;

        if (typeof value === 'string') {
            const confirmBtn = el.querySelector('.btn-primary[data-swal-value]') || el.querySelector('.btn-danger[data-swal-value]');
            if (confirmBtn) confirmBtn.setAttribute('data-swal-value', JSON.stringify(value));
        } else if (typeof value === 'object') {
            Object.entries(value).forEach(([key, val]) => {
                const btn = el.querySelector(`[data-swal-key="${key}"]`);
                if (btn) btn.setAttribute('data-swal-value', JSON.stringify(val));
            });
        }
    };

    window.swal = swal;
})();

