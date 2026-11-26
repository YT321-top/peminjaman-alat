(function () {
    const DEFAULT_DURATION = 4200;

    const TONE_META = {
        success: {
            accentBar: 'bg-[#16A34A]',
            iconWrap: 'bg-[#ECFDF5] text-[#16A34A] border-[#BBF7D0]',
            title: 'Berhasil',
            titleColor: 'text-[#1F2937]',
            msgColor: 'text-[#6B7280]',
            iconSvg:
                '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>',
        },
        warning: {
            accentBar: 'bg-[#D97706]',
            iconWrap: 'bg-[#FFFBEB] text-[#D97706] border-[#FDE68A]',
            title: 'Perhatian',
            titleColor: 'text-[#1F2937]',
            msgColor: 'text-[#6B7280]',
            iconSvg:
                '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86l-7.5 13A1 1 0 003.65 18.5h16.7a1 1 0 00.86-1.64l-7.5-13a1 1 0 00-1.72 0z"/></svg>',
        },
        error: {
            accentBar: 'bg-[#DC2626]',
            iconWrap: 'bg-[#FEF2F2] text-[#DC2626] border-[#FECACA]',
            title: 'Terjadi Kendala',
            titleColor: 'text-[#1F2937]',
            msgColor: 'text-[#DC2626]',
            iconSvg:
                '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>',
        },
    };

    function dismissToast(el) {
        if (!el || el.dataset.dismissing === '1') {
            return;
        }
        el.dataset.dismissing = '1';
        if (el._lpTimer) {
            clearTimeout(el._lpTimer);
            el._lpTimer = null;
        }
        el.classList.remove('lp-toast-enter');
        el.classList.add('lp-toast-leave');
        window.setTimeout(function () {
            if (el.parentNode) {
                el.parentNode.removeChild(el);
            }
        }, 190);
    }

    function bindToastBehavior(el, duration) {
        if (!el || el.dataset.toastBound === '1') {
            return;
        }
        el.dataset.toastBound = '1';

        const ms = Number(duration) > 0 ? Number(duration) : DEFAULT_DURATION;
        el.style.setProperty('--lp-toast-duration', ms + 'ms');

        let remaining = ms;
        let startedAt = Date.now();

        const schedule = function (delay) {
            if (el._lpTimer) {
                clearTimeout(el._lpTimer);
            }
            startedAt = Date.now();
            el._lpTimer = window.setTimeout(function () {
                dismissToast(el);
            }, delay);
        };

        schedule(remaining);

        el.addEventListener('mouseenter', function () {
            if (el._lpTimer) {
                clearTimeout(el._lpTimer);
                el._lpTimer = null;
                remaining = Math.max(400, remaining - (Date.now() - startedAt));
            }
        });

        el.addEventListener('mouseleave', function () {
            if (el.dataset.dismissing !== '1') {
                schedule(remaining);
            }
        });

        const closeBtn = el.querySelector('[data-toast-close]');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                dismissToast(el);
            });
        }
    }

    function getContainer() {
        let container = document.getElementById('lp-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'lp-toast-container';
            container.className =
                'no-print fixed top-20 right-4 sm:right-6 z-50 flex flex-col gap-2.5 w-[calc(100vw-2rem)] max-w-sm pointer-events-none';
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-atomic', 'true');
            document.body.appendChild(container);
        }
        return container;
    }

    function showToast(options) {
        const opts = typeof options === 'string' ? { message: options } : options || {};
        const type = TONE_META[opts.type] ? opts.type : 'success';
        const meta = TONE_META[type];
        const title = opts.title || meta.title;
        const message = opts.message || '';
        const duration = Number(opts.duration) > 0 ? Number(opts.duration) : DEFAULT_DURATION;

        if (!message) {
            return null;
        }

        const container = getContainer();
        const toast = document.createElement('div');
        toast.className =
            'lp-toast lp-toast-enter pointer-events-auto relative overflow-hidden rounded-xl bg-white border border-[#E5E7EB] shadow-md';
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');

        const body = document.createElement('div');
        body.className = 'flex items-start gap-3 px-4 py-3.5 pr-10';

        const iconWrap = document.createElement('div');
        iconWrap.className =
            'w-8 h-8 rounded-lg border flex items-center justify-center shrink-0 ' + meta.iconWrap;
        iconWrap.innerHTML = meta.iconSvg;

        const textWrap = document.createElement('div');
        textWrap.className = 'min-w-0 flex-1';

        const titleEl = document.createElement('p');
        titleEl.className = 'text-xs font-semibold leading-5 ' + meta.titleColor;
        titleEl.textContent = title;

        const msgEl = document.createElement('p');
        msgEl.className = 'text-xs leading-relaxed mt-0.5 break-words ' + meta.msgColor;
        msgEl.textContent = message;

        textWrap.appendChild(titleEl);
        textWrap.appendChild(msgEl);

        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.setAttribute('data-toast-close', '');
        closeBtn.setAttribute('aria-label', 'Tutup notifikasi');
        closeBtn.className =
            'absolute top-2.5 right-2.5 w-7 h-7 rounded-full inline-flex items-center justify-center text-[#9CA3AF] hover:text-[#1F2937] hover:bg-[#F3F4F6] transition-colors';
        closeBtn.innerHTML =
            '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';

        const track = document.createElement('div');
        track.className = 'h-1 w-full bg-[#F3F4F6] overflow-hidden';

        const bar = document.createElement('div');
        bar.className = 'lp-toast-bar h-full w-full ' + meta.accentBar;

        track.appendChild(bar);
        body.appendChild(iconWrap);
        body.appendChild(textWrap);
        toast.appendChild(body);
        toast.appendChild(closeBtn);
        toast.appendChild(track);

        container.appendChild(toast);
        bindToastBehavior(toast, duration);

        return toast;
    }

    function initServerToasts() {
        const container = document.getElementById('lp-toast-container');
        if (!container) {
            return;
        }
        const items = container.querySelectorAll('[data-toast-item]');
        items.forEach(function (item) {
            const duration = item.getAttribute('data-duration') || DEFAULT_DURATION;
            bindToastBehavior(item, duration);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initServerToasts);
    } else {
        initServerToasts();
    }

    window.LpToast = {
        show: showToast,
        success: function (message, title) {
            return showToast({ type: 'success', message: message, title: title });
        },
        warning: function (message, title) {
            return showToast({ type: 'warning', message: message, title: title });
        },
        error: function (message, title) {
            return showToast({ type: 'error', message: message, title: title });
        },
        dismiss: dismissToast,
    };
})();
