/**
 * LabPinjam Custom Illustrated Confirm Dialog
 * Menggantikan native window.confirm() pada seluruh form[data-confirm].
 */
(function () {
    const ILLUSTRATIONS = {
        delete:
            '<svg viewBox="0 0 140 112" class="w-28 h-24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
            '<circle cx="70" cy="56" r="42" fill="#FEF2F2"/>' +
            '<circle cx="40" cy="40" r="14" fill="#FEE2E2" opacity="0.7"/>' +
            '<circle cx="100" cy="64" r="13" fill="#FEE2E2" opacity="0.7"/>' +
            '<path d="M24 68C24 63.6 27.6 60 32 60H34C35.8 56.4 39.6 54 44 54C49.5 54 54 58.5 54 64C54 68.4 50.4 72 46 72H30C26.7 72 24 69.3 24 68Z" fill="#FFFFFF" stroke="#1F2937" stroke-width="2" stroke-linejoin="round"/>' +
            '<path d="M92 36C92 32.7 94.7 30 98 30H100C101.6 27.6 104.3 26 107.5 26C112.2 26 116 29.8 116 34.5C116 38.1 113.1 41 109.5 41H97C94.2 41 92 38.8 92 36Z" fill="#FFFFFF" stroke="#1F2937" stroke-width="2" stroke-linejoin="round"/>' +
            '<rect x="61" y="20" width="18" height="7" rx="3" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.4"/>' +
            '<rect x="44" y="27" width="52" height="10" rx="4" fill="#E53935" stroke="#1F2937" stroke-width="2.4"/>' +
            '<path d="M50 37H90L85.5 85C85.1 88.4 82.3 91 78.9 91H61.1C57.7 91 54.9 88.4 54.5 85L50 37Z" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.4" stroke-linejoin="round"/>' +
            '<path d="M53 48H87L84.5 85C84.2 87.2 82.3 89 80 89H60C57.7 89 55.8 87.2 55.5 85L53 48Z" fill="#FEF2F2"/>' +
            '<path d="M62 48V78M70 48V78M78 48V78" stroke="#E53935" stroke-width="2.4" stroke-linecap="round"/>' +
            '<path d="M35 30L37 32M37 30L35 32" stroke="#E53935" stroke-width="1.8" stroke-linecap="round"/>' +
            '<circle cx="105" cy="78" r="2.2" fill="#E53935"/>' +
            '</svg>',

        approve:
            '<svg viewBox="0 0 140 112" class="w-28 h-24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
            '<circle cx="70" cy="56" r="42" fill="#F0FDF4"/>' +
            '<circle cx="38" cy="64" r="13" fill="#DCFCE7"/>' +
            '<circle cx="102" cy="40" r="14" fill="#DCFCE7"/>' +
            '<rect x="44" y="24" width="52" height="66" rx="8" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.4"/>' +
            '<rect x="57" y="18" width="26" height="11" rx="4" fill="#16A34A" stroke="#1F2937" stroke-width="2.2"/>' +
            '<path d="M55 44H85M55 54H75" stroke="#CBD5E1" stroke-width="2.5" stroke-linecap="round"/>' +
            '<circle cx="82" cy="70" r="15" fill="#16A34A" stroke="#1F2937" stroke-width="2.4"/>' +
            '<path d="M75.5 70L80 74.5L88.5 65.5" stroke="#FFFFFF" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>' +
            '</svg>',

        reject:
            '<svg viewBox="0 0 140 112" class="w-28 h-24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
            '<circle cx="70" cy="56" r="42" fill="#FEF2F2"/>' +
            '<circle cx="100" cy="66" r="13" fill="#FEE2E2"/>' +
            '<rect x="44" y="24" width="52" height="66" rx="8" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.4"/>' +
            '<path d="M55 40H85M55 50H77" stroke="#CBD5E1" stroke-width="2.5" stroke-linecap="round"/>' +
            '<circle cx="82" cy="70" r="15" fill="#E53935" stroke="#1F2937" stroke-width="2.4"/>' +
            '<path d="M77 65L87 75M87 65L77 75" stroke="#FFFFFF" stroke-width="2.8" stroke-linecap="round"/>' +
            '</svg>',

        return:
            '<svg viewBox="0 0 140 112" class="w-28 h-24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
            '<circle cx="70" cy="56" r="42" fill="#F7F7F8"/>' +
            '<circle cx="40" cy="40" r="14" fill="#FEE2E2" opacity="0.65"/>' +
            '<rect x="38" y="38" width="54" height="44" rx="7" fill="#FFFFFF" stroke="#1F2937" stroke-width="2.4"/>' +
            '<path d="M53 38V31C53 28.8 54.8 27 57 27H73C75.2 27 77 28.8 77 31V38" stroke="#1F2937" stroke-width="2.4"/>' +
            '<path d="M38 54H92" stroke="#E5E7EB" stroke-width="2"/>' +
            '<circle cx="90" cy="72" r="15" fill="#1F2937" stroke="#FFFFFF" stroke-width="2.5"/>' +
            '<path d="M86 68L81 72L86 76" stroke="#FFFFFF" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"/>' +
            '<path d="M82 72H94C96.2 72 98 70.2 98 68V66" stroke="#E53935" stroke-width="2.3" stroke-linecap="round"/>' +
            '</svg>',
    };

    const DEFAULT_OK_TEXT = {
        delete: 'Ya, Hapus',
        approve: 'Ya, Setujui',
        reject: 'Ya, Tolak',
        return: 'Ya, Kembalikan',
    };

    let pendingForm = null;

    function getEls() {
        return {
            modal: document.getElementById('lp-confirm-modal'),
            card: document.getElementById('lp-confirm-card'),
            illu: document.getElementById('lp-confirm-illu'),
            title: document.getElementById('lp-confirm-title'),
            targetWrap: document.getElementById('lp-confirm-target-wrap'),
            target: document.getElementById('lp-confirm-target'),
            msg: document.getElementById('lp-confirm-msg'),
            okBtn: document.getElementById('lp-confirm-ok'),
            cancelBtn: document.getElementById('lp-confirm-cancel'),
            closeBtn: document.getElementById('lp-confirm-close'),
        };
    }

    function openConfirm(options, form) {
        const els = getEls();
        if (!els.modal) return false;

        const kind = ILLUSTRATIONS[options.kind] ? options.kind : 'delete';
        pendingForm = form || null;

        els.illu.innerHTML = ILLUSTRATIONS[kind];
        els.title.textContent = options.title || 'Konfirmasi Tindakan';

        if (options.target) {
            els.target.textContent = options.target;
            els.targetWrap.classList.remove('hidden');
        } else {
            els.target.textContent = '';
            els.targetWrap.classList.add('hidden');
        }

        if (options.message) {
            els.msg.textContent = options.message;
            els.msg.classList.remove('hidden');
        } else {
            els.msg.textContent = '';
            els.msg.classList.add('hidden');
        }

        els.okBtn.textContent = options.okText || DEFAULT_OK_TEXT[kind] || 'Ya, Lanjutkan';

        if (kind === 'approve') {
            els.okBtn.className =
                'min-w-[168px] h-10 px-6 rounded-full border-[1.5px] border-[#16A34A] bg-[#16A34A] hover:bg-[#15803D] text-white text-xs font-bold shadow-sm transition cursor-pointer';
        } else {
            els.okBtn.className =
                'min-w-[168px] h-10 px-6 rounded-full border-[1.5px] border-[#E53935] bg-[#E53935] hover:bg-[#C62828] text-white text-xs font-bold shadow-sm transition cursor-pointer';
        }

        els.modal.classList.remove('hidden');
        els.modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        if (els.card) {
            els.card.classList.remove('lp-modal-pop');
            requestAnimationFrame(() => {
                els.card.classList.add('lp-modal-pop');
            });
        }

        els.okBtn.focus();
        return true;
    }

    function closeConfirm() {
        const els = getEls();
        if (!els.modal || els.modal.classList.contains('hidden')) return;
        els.modal.classList.add('hidden');
        els.modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        pendingForm = null;
    }

    function init() {
        const els = getEls();
        if (!els.modal) return;

        if (els.cancelBtn) els.cancelBtn.addEventListener('click', closeConfirm);
        if (els.closeBtn) els.closeBtn.addEventListener('click', closeConfirm);

        els.modal.addEventListener('click', function (e) {
            if (e.target === els.modal) closeConfirm();
        });

        els.okBtn.addEventListener('click', function () {
            const formToSubmit = pendingForm;
            closeConfirm();
            if (formToSubmit) {
                formToSubmit.dataset.lpConfirmed = '1';
                formToSubmit.submit();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !els.modal.classList.contains('hidden')) {
                closeConfirm();
            }
        });

        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form || !form.dataset || !form.dataset.confirm) return;
            if (form.dataset.lpConfirmed === '1') return;

            e.preventDefault();
            openConfirm(
                {
                    kind: form.dataset.confirmKind || 'delete',
                    title: form.dataset.confirm,
                    target: form.dataset.confirmTarget || '',
                    message: form.dataset.confirmMessage || '',
                    okText: form.dataset.confirmOk || '',
                },
                form
            );
        });
    }

    window.LpConfirm = { open: openConfirm, close: closeConfirm };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
