/**
 * LabPinjam Custom Minimalist Dropdown (Lightweight & Zero-Lag)
 * Menggantikan popup <select> bawaan OS pada seluruh elemen <select> (statis & dinamis).
 */
(function () {
    const CHEVRON_SVG =
        '<svg class="lp-dd-chevron w-3.5 h-3.5 text-[#6B7280] shrink-0 transition-transform duration-150" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>';

    const CHECK_SVG =
        '<svg class="w-3.5 h-3.5 text-[#E53935] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>';

    let activeInstance = null;
    let portalEl = null;

    function getPortal() {
        if (!portalEl) {
            portalEl = document.createElement('div');
            portalEl.id = 'lp-dd-portal';
            portalEl.className =
                'fixed z-[9999] hidden bg-white rounded-xl border border-[#E5E7EB] shadow-lg p-1 max-h-56 overflow-y-auto lp-dd-menu';
            portalEl.setAttribute('role', 'listbox');
            document.body.appendChild(portalEl);
        }
        return portalEl;
    }

    function detectHeightClass(select) {
        if (select.classList.contains('h-10') || select.classList.contains('py-2')) {
            return 'h-10';
        }
        if (select.classList.contains('h-8')) {
            return 'h-8';
        }
        return 'h-9';
    }

    function detectWidthClasses(select) {
        const widthTokens = [
            'w-full',
            'w-auto',
            'sm:w-40',
            'sm:w-44',
            'sm:w-48',
            'sm:w-56',
            'sm:w-auto',
            'flex-1',
            'min-w-0',
            'shrink-0',
        ];
        const classes = widthTokens.filter((cls) => select.classList.contains(cls));
        if (classes.length > 0) {
            return classes.join(' ');
        }

        const parent = select.parentElement;
        if (parent) {
            const style = window.getComputedStyle(parent);
            if ((style.display === 'flex' || style.display === 'inline-flex') && style.flexDirection.startsWith('row')) {
                return 'w-auto shrink-0';
            }
        }

        return 'w-full';
    }

    function getSelectedOption(select) {
        if (select.selectedIndex >= 0 && select.options[select.selectedIndex]) {
            return select.options[select.selectedIndex];
        }
        return select.options[0] || null;
    }

    function syncTrigger(instance) {
        const { select, trigger, labelEl } = instance;
        const selected = getSelectedOption(select);
        const text = selected ? selected.textContent.trim() : '';
        const isEmpty = !selected || selected.value === '';

        if (labelEl.textContent !== text) {
            labelEl.textContent = text;
        }

        const targetClass = isEmpty
            ? 'truncate text-[#6B7280] font-normal'
            : 'truncate text-[#1F2937] font-medium';
        if (labelEl.className !== targetClass) {
            labelEl.className = targetClass;
        }

        trigger.disabled = !!select.disabled;
        trigger.classList.toggle('opacity-60', !!select.disabled);
        trigger.classList.toggle('cursor-not-allowed', !!select.disabled);
        trigger.classList.toggle('bg-[#F7F7F8]', !!select.disabled);
    }

    function setOpenAppearance(instance, open) {
        const { trigger, chevron } = instance;
        trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
            trigger.classList.remove('border-[#E5E7EB]', 'hover:border-[#CBD5E1]');
            trigger.classList.add('border-[#E53935]', 'ring-2', 'ring-[#E53935]/10');
            if (chevron) {
                chevron.classList.add('rotate-180', 'text-[#E53935]');
                chevron.classList.remove('text-[#6B7280]');
            }
        } else {
            trigger.classList.add('border-[#E5E7EB]', 'hover:border-[#CBD5E1]');
            trigger.classList.remove('border-[#E53935]', 'ring-2', 'ring-[#E53935]/10');
            if (chevron) {
                chevron.classList.remove('rotate-180', 'text-[#E53935]');
                chevron.classList.add('text-[#6B7280]');
            }
        }
    }

    function onDocumentClick(e) {
        if (!activeInstance) return;
        const portal = getPortal();
        if (!activeInstance.wrapper.contains(e.target) && !portal.contains(e.target)) {
            closeActive();
        }
    }

    function onDocumentKeydown(e) {
        if (e.key === 'Escape' && activeInstance) {
            const trigger = activeInstance.trigger;
            closeActive();
            trigger.focus();
        }
    }

    function onViewportScroll(e) {
        if (!activeInstance) return;
        const portal = getPortal();
        if (portal && (e.target === portal || portal.contains(e.target))) {
            return;
        }
        closeActive();
    }

    function attachOpenListeners() {
        document.addEventListener('click', onDocumentClick);
        document.addEventListener('keydown', onDocumentKeydown);
        window.addEventListener('resize', closeActive);
        document.addEventListener('scroll', onViewportScroll, true);
    }

    function detachOpenListeners() {
        document.removeEventListener('click', onDocumentClick);
        document.removeEventListener('keydown', onDocumentKeydown);
        window.removeEventListener('resize', closeActive);
        document.removeEventListener('scroll', onViewportScroll, true);
    }

    function closeActive() {
        if (!activeInstance) return;
        const portal = getPortal();
        portal.classList.add('hidden');
        portal.textContent = '';
        setOpenAppearance(activeInstance, false);
        activeInstance = null;
        detachOpenListeners();
    }

    function positionPortal(instance) {
        const portal = getPortal();
        const rect = instance.trigger.getBoundingClientRect();
        const viewportH = window.innerHeight;
        const viewportW = window.innerWidth;

        portal.style.minWidth = Math.max(rect.width, 160) + 'px';
        portal.style.maxWidth = Math.min(Math.max(rect.width + 64, 240), viewportW - 24) + 'px';

        portal.style.visibility = 'hidden';
        portal.classList.remove('hidden');
        const menuH = Math.min(portal.scrollHeight, 224);
        const menuW = portal.offsetWidth;

        const spaceBelow = viewportH - rect.bottom - 10;
        const spaceAbove = rect.top - 10;
        const openUp = spaceBelow < menuH && spaceAbove > spaceBelow;

        const top = openUp
            ? Math.max(8, rect.top - menuH - 6)
            : Math.min(viewportH - menuH - 8, rect.bottom + 6);

        let left = rect.left;
        if (left + menuW > viewportW - 12) {
            left = Math.max(12, rect.right - menuW);
        }

        portal.style.top = top + 'px';
        portal.style.left = left + 'px';
        portal.style.visibility = 'visible';
    }

    function openInstance(instance) {
        if (instance.select.disabled) return;
        if (activeInstance === instance) {
            closeActive();
            return;
        }

        closeActive();
        activeInstance = instance;
        setOpenAppearance(instance, true);

        const portal = getPortal();
        const frag = document.createDocumentFragment();
        const currentVal = instance.select.value;
        let selectedItemBtn = null;

        Array.from(instance.select.options).forEach((opt) => {
            const isSelected = opt.value === currentVal;
            const isMuted = opt.value === '';

            const itemBtn = document.createElement('button');
            itemBtn.type = 'button';
            itemBtn.setAttribute('role', 'option');
            itemBtn.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            itemBtn.disabled = opt.disabled;

            itemBtn.className = isSelected
                ? 'w-full px-2.5 py-2 rounded-lg text-left text-xs flex items-center justify-between gap-2 transition-colors cursor-pointer bg-[#FEF2F2] text-[#E53935] font-semibold'
                : 'w-full px-2.5 py-2 rounded-lg text-left text-xs flex items-center justify-between gap-2 transition-colors cursor-pointer hover:bg-[#F7F7F8] ' +
                  (isMuted ? 'text-[#6B7280] font-normal hover:text-[#1F2937]' : 'text-[#1F2937] font-medium');

            if (opt.disabled) {
                itemBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }

            const textSpan = document.createElement('span');
            textSpan.className = 'truncate';
            textSpan.textContent = opt.textContent.trim();
            itemBtn.appendChild(textSpan);

            if (isSelected) {
                const checkWrap = document.createElement('span');
                checkWrap.innerHTML = CHECK_SVG;
                itemBtn.appendChild(checkWrap.firstChild);
                selectedItemBtn = itemBtn;
            }

            itemBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (opt.disabled) return;
                const changed = instance.select.value !== opt.value;
                instance.select.value = opt.value;
                syncTrigger(instance);
                closeActive();
                if (changed) {
                    instance.select.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });

            frag.appendChild(itemBtn);
        });

        portal.textContent = '';
        portal.appendChild(frag);
        positionPortal(instance);
        attachOpenListeners();

        if (selectedItemBtn) {
            const itemTop = selectedItemBtn.offsetTop;
            const itemBottom = itemTop + selectedItemBtn.offsetHeight;
            if (itemBottom > portal.clientHeight) {
                portal.scrollTop = itemTop - portal.clientHeight / 2 + selectedItemBtn.offsetHeight / 2;
            }
        }
    }

    function enhanceSelect(select) {
        if (!select || select.dataset.lpDdEnhanced === '1' || select.multiple) {
            return;
        }

        select.dataset.lpDdEnhanced = '1';

        const wrapper = document.createElement('div');
        wrapper.className = 'lp-dd relative ' + detectWidthClasses(select);

        const parent = select.parentNode;
        if (!parent) return;
        parent.insertBefore(wrapper, select);
        wrapper.appendChild(select);

        select.classList.add(
            'opacity-0',
            'pointer-events-none',
            'absolute',
            'inset-0',
            'w-full',
            'h-full',
            'm-0'
        );
        select.tabIndex = -1;
        select.setAttribute('aria-hidden', 'true');

        const heightClass = detectHeightClass(select);
        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className =
            'lp-dd-trigger w-full ' +
            heightClass +
            ' px-3 rounded-xl bg-white border border-[#E5E7EB] hover:border-[#CBD5E1] flex items-center justify-between gap-2 text-xs text-left transition-colors cursor-pointer focus:outline-none';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');

        const ariaLabel = select.getAttribute('aria-label');
        if (ariaLabel) {
            trigger.setAttribute('aria-label', ariaLabel);
        }

        const labelEl = document.createElement('span');
        labelEl.className = 'truncate';
        trigger.appendChild(labelEl);

        const chevronWrap = document.createElement('span');
        chevronWrap.innerHTML = CHEVRON_SVG;
        const chevron = chevronWrap.firstChild;
        trigger.appendChild(chevron);

        wrapper.appendChild(trigger);

        const instance = { select, wrapper, trigger, labelEl, chevron };
        select._lpDdInstance = instance;

        const valueDescriptor = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value');
        if (valueDescriptor && valueDescriptor.get && valueDescriptor.set) {
            Object.defineProperty(select, 'value', {
                configurable: true,
                get() {
                    return valueDescriptor.get.call(this);
                },
                set(nextVal) {
                    valueDescriptor.set.call(this, nextVal);
                    syncTrigger(instance);
                },
            });
        }

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            openInstance(instance);
        });

        trigger.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openInstance(instance);
            }
        });

        if (select.id) {
            document.querySelectorAll('label[for="' + CSS.escape(select.id) + '"]').forEach((lbl) => {
                lbl.addEventListener('click', function (e) {
                    e.preventDefault();
                    trigger.focus();
                    openInstance(instance);
                });
            });
        }

        select.addEventListener('change', function () {
            syncTrigger(instance);
        });

        const optObserver = new MutationObserver(function () {
            syncTrigger(instance);
        });
        optObserver.observe(select, { childList: true, attributes: true, attributeFilter: ['disabled'] });

        syncTrigger(instance);
    }

    function enhanceAll(root = document) {
        if (!root || !root.querySelectorAll) return;
        root.querySelectorAll('select:not([data-lp-dd-enhanced="1"])').forEach(enhanceSelect);
    }

    function init() {
        enhanceAll(document);

        let pendingEnhance = false;
        const bodyObserver = new MutationObserver(function (mutations) {
            for (let i = 0; i < mutations.length; i++) {
                const mutTarget = mutations[i].target;
                if (
                    mutTarget &&
                    mutTarget.nodeType === 1 &&
                    mutTarget.closest &&
                    mutTarget.closest('#pv-alat-illu-slot, #illu-icon-grid, #toast-container')
                ) {
                    continue;
                }

                const added = mutations[i].addedNodes;
                for (let j = 0; j < added.length; j++) {
                    const node = added[j];
                    if (node.nodeType !== 1) continue;
                    const tag = node.nodeName ? node.nodeName.toUpperCase() : '';
                    if (tag === 'SVG' || tag === 'PATH' || tag === 'RECT' || tag === 'CIRCLE') continue;
                    if (
                        node.id === 'lp-dd-portal' ||
                        node.id === 'pv-alat-illu-slot' ||
                        node.id === 'illu-icon-grid' ||
                        node.id === 'toast-container' ||
                        node.classList.contains('lp-dd')
                    ) {
                        continue;
                    }
                    if (tag === 'SELECT' && node.dataset.lpDdEnhanced !== '1') {
                        pendingEnhance = true;
                        break;
                    }
                    if (node.firstElementChild && node.querySelector('select:not([data-lp-dd-enhanced="1"])')) {
                        pendingEnhance = true;
                        break;
                    }
                }
                if (pendingEnhance) break;
            }

            if (pendingEnhance) {
                pendingEnhance = false;
                requestAnimationFrame(() => enhanceAll(document));
            }
        });

        bodyObserver.observe(document.body, { childList: true, subtree: true });
    }

    window.LpDropdown = {
        enhanceAll,
        sync: function (select) {
            if (select && select._lpDdInstance) {
                syncTrigger(select._lpDdInstance);
            }
        },
        close: closeActive,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
