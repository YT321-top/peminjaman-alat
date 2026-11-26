(function () {
    const STORAGE_KEY = 'lp_peminjam_nav_rect';
    const MAX_AGE_MS = 4000;

    function initPeminjamNav() {
        const track = document.getElementById('peminjam-nav-track');
        const glider = document.getElementById('peminjam-nav-glider');
        if (!track || !glider) {
            return;
        }

        const tabs = Array.from(track.querySelectorAll('[data-peminjam-tab]'));
        const activeTab = track.querySelector('[data-peminjam-tab][data-active="1"]') || tabs[0];
        if (!activeTab) {
            return;
        }

        function setGliderRect(left, width, animate) {
            if (!animate) {
                glider.style.transition = 'none';
            }
            glider.style.width = width + 'px';
            glider.style.transform = 'translate3d(' + left + 'px, 0, 0)';
            glider.classList.remove('opacity-0');

            if (!animate) {
                void glider.offsetWidth;
                glider.style.transition = '';
            }
        }

        function getTabRect(tab) {
            return {
                left: tab.offsetLeft,
                width: tab.offsetWidth,
            };
        }

        let prevRect = null;
        try {
            const raw = window.sessionStorage.getItem(STORAGE_KEY);
            if (raw) {
                window.sessionStorage.removeItem(STORAGE_KEY);
                const parsed = JSON.parse(raw);
                if (
                    parsed &&
                    typeof parsed.left === 'number' &&
                    typeof parsed.width === 'number' &&
                    parsed.pathname !== window.location.pathname &&
                    Date.now() - (parsed.ts || 0) < MAX_AGE_MS
                ) {
                    prevRect = parsed;
                }
            }
        } catch (e) {
            prevRect = null;
        }

        const targetRect = getTabRect(activeTab);

        if (
            prevRect &&
            (Math.abs(prevRect.left - targetRect.left) > 2 || Math.abs(prevRect.width - targetRect.width) > 2)
        ) {
            track.classList.add('lp-glider-ready');
            setGliderRect(prevRect.left, prevRect.width, false);
            window.requestAnimationFrame(function () {
                setGliderRect(targetRect.left, targetRect.width, true);
            });
        } else {
            track.classList.add('lp-glider-ready');
            setGliderRect(targetRect.left, targetRect.width, false);
        }

        // Pastikan tab aktif terlihat bila overflow horizontal di layar kecil
        if (typeof activeTab.scrollIntoView === 'function' && track.scrollWidth > track.clientWidth) {
            activeTab.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'auto' });
        }

        let clickedInsideBar = false;

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                clickedInsideBar = true;
                const trackBox = track.getBoundingClientRect();
                const gliderBox = glider.getBoundingClientRect();
                const currentLeft = gliderBox.width > 0
                    ? gliderBox.left - trackBox.left + track.scrollLeft
                    : activeTab.offsetLeft;
                const currentWidth = gliderBox.width > 0 ? gliderBox.width : activeTab.offsetWidth;

                const nextRect = getTabRect(tab);
                setGliderRect(nextRect.left, nextRect.width, true);

                try {
                    window.sessionStorage.setItem(
                        STORAGE_KEY,
                        JSON.stringify({
                            left: Math.round(currentLeft),
                            width: Math.round(currentWidth),
                            pathname: window.location.pathname,
                            ts: Date.now(),
                        })
                    );
                } catch (e) {
                    // Abaikan jika sessionStorage dibatasi
                }
            });
        });

        window.addEventListener('pagehide', function () {
            if (clickedInsideBar) {
                return;
            }
            const r = getTabRect(activeTab);
            try {
                window.sessionStorage.setItem(
                    STORAGE_KEY,
                    JSON.stringify({
                        left: Math.round(r.left),
                        width: Math.round(r.width),
                        pathname: window.location.pathname,
                        ts: Date.now(),
                    })
                );
            } catch (e) {
                // Abaikan jika sessionStorage dibatasi
            }
        });

        window.addEventListener('resize', function () {
            const current = track.querySelector('[data-peminjam-tab][data-active="1"]') || tabs[0];
            if (current) {
                const r = getTabRect(current);
                setGliderRect(r.left, r.width, false);
            }
        });

        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(function () {
                const current = track.querySelector('[data-peminjam-tab][data-active="1"]') || tabs[0];
                if (current) {
                    const r = getTabRect(current);
                    setGliderRect(r.left, r.width, false);
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPeminjamNav);
    } else {
        initPeminjamNav();
    }
})();
