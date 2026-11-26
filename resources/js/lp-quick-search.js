(function () {
    function initQuickSearch() {
        const modal = document.getElementById('lp-quick-search-modal');
        if (!modal) {
            return;
        }

        const input = document.getElementById('lp-quick-search-input');
        const closeBtn = document.getElementById('lp-quick-search-close');
        const submitRow = document.getElementById('lp-quick-search-submit-row');
        const queryPreview = document.getElementById('lp-quick-search-query-preview');
        const navItems = Array.from(modal.querySelectorAll('[data-quick-nav-item]'));
        const navEmpty = document.getElementById('lp-quick-search-nav-empty');

        const railSearchTrigger = document.getElementById('quick-search-trigger');
        const headerExpandShell = document.getElementById('header-expand-search');
        const headerExpandTrigger = document.getElementById('header-expand-search-trigger');
        const headerExpandFields = document.getElementById('header-expand-search-fields');
        const headerExpandInput = document.getElementById('global-search-input');
        const headerExpandClose = document.getElementById('header-expand-search-close');

        const EXPANDED_WIDTH_CLASSES = ['w-[260px]', 'md:w-[320px]', 'lg:w-[360px]'];

        function filterNavItems() {
            if (!input) {
                return;
            }
            const raw = input.value.trim();
            const q = raw.toLowerCase();

            if (submitRow && queryPreview) {
                if (raw.length > 0) {
                    queryPreview.textContent = raw;
                    submitRow.classList.remove('hidden');
                } else {
                    submitRow.classList.add('hidden');
                }
            }

            let visibleCount = 0;
            navItems.forEach(function (item) {
                const label = item.getAttribute('data-quick-nav-label') || '';
                const matched = q === '' || label.includes(q);
                item.classList.toggle('hidden', !matched);
                if (matched) {
                    visibleCount++;
                }
            });

            if (navEmpty) {
                navEmpty.classList.toggle('hidden', visibleCount > 0);
            }
        }

        function openModal() {
            modal.classList.remove('hidden');
            filterNavItems();
            if (input) {
                window.setTimeout(function () {
                    input.focus();
                    input.select();
                }, 10);
            }
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        function setHeaderSearchExpanded(expanded, focusInput) {
            if (!headerExpandShell || !headerExpandFields || !headerExpandTrigger) {
                return;
            }
            headerExpandShell.setAttribute('data-expanded', expanded ? '1' : '0');

            if (expanded) {
                headerExpandShell.classList.remove('w-9', 'bg-[#F7F7F8]', 'border-[#E5E7EB]', 'hover:border-[#CBD5E1]');
                headerExpandShell.classList.add(...EXPANDED_WIDTH_CLASSES, 'bg-white', 'border-[#E53935]', 'ring-2', 'ring-[#E53935]/10');
                headerExpandTrigger.classList.remove('text-[#4B5563]');
                headerExpandTrigger.classList.add('text-[#E53935]');
                headerExpandFields.classList.remove('opacity-0', 'pointer-events-none');
                headerExpandFields.classList.add('opacity-100');

                if (focusInput && headerExpandInput) {
                    window.setTimeout(function () {
                        headerExpandInput.focus();
                        headerExpandInput.select();
                    }, 120);
                }
            } else {
                headerExpandShell.classList.remove(...EXPANDED_WIDTH_CLASSES, 'bg-white', 'border-[#E53935]', 'ring-2', 'ring-[#E53935]/10');
                headerExpandShell.classList.add('w-9', 'bg-[#F7F7F8]', 'border-[#E5E7EB]', 'hover:border-[#CBD5E1]');
                headerExpandTrigger.classList.remove('text-[#E53935]');
                headerExpandTrigger.classList.add('text-[#4B5563]');
                headerExpandFields.classList.add('opacity-0', 'pointer-events-none');
                headerExpandFields.classList.remove('opacity-100');
            }
        }

        if (railSearchTrigger) {
            railSearchTrigger.addEventListener('click', function () {
                openModal();
            });
        }

        if (headerExpandTrigger && headerExpandShell) {
            headerExpandTrigger.addEventListener('click', function () {
                if (window.innerWidth < 640) {
                    openModal();
                    return;
                }
                const isExpanded = headerExpandShell.getAttribute('data-expanded') === '1';
                if (!isExpanded) {
                    setHeaderSearchExpanded(true, true);
                } else if (headerExpandInput && headerExpandInput.value.trim() !== '') {
                    headerExpandShell.submit();
                } else if (headerExpandInput) {
                    headerExpandInput.focus();
                }
            });
        }

        if (headerExpandClose && headerExpandShell) {
            headerExpandClose.addEventListener('click', function () {
                if (headerExpandInput && headerExpandInput.value.trim() !== '') {
                    headerExpandInput.value = '';
                    headerExpandInput.focus();
                    return;
                }
                setHeaderSearchExpanded(false, false);
            });
        }

        if (headerExpandInput) {
            headerExpandInput.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    event.preventDefault();
                    headerExpandInput.blur();
                    setHeaderSearchExpanded(false, false);
                }
            });
        }

        document.addEventListener('click', function (event) {
            if (!headerExpandShell || headerExpandShell.getAttribute('data-expanded') !== '1') {
                return;
            }
            if (headerExpandShell.contains(event.target)) {
                return;
            }
            if (!headerExpandInput || headerExpandInput.value.trim() === '') {
                setHeaderSearchExpanded(false, false);
            }
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        if (input) {
            input.addEventListener('input', filterNavItems);
        }

        document.addEventListener('keydown', function (event) {
            const key = (event.key || '').toLowerCase();

            if ((event.ctrlKey || event.metaKey) && key === 'k') {
                event.preventDefault();
                if (modal.classList.contains('hidden')) {
                    openModal();
                } else {
                    closeModal();
                }
                return;
            }

            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initQuickSearch);
    } else {
        initQuickSearch();
    }
})();
