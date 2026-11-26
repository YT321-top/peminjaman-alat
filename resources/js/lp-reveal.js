/**
 * LabPinjam Render-Ready & Scroll-Triggered Entry Reveal (Opsi 2: 600ms santai)
 * Card baru menjalankan animasi masuk setelah konten & gambar di dalamnya siap ter-render
 * serta benar-benar masuk ke area viewport (tidak langsung muncul saat masih di bawah layar).
 */
(function () {
    const DURATION_MS = 600;
    const STAGGER_MS = 75;
    const MAX_BATCH_STAGGER_MS = 350;
    const IMAGE_WAIT_CAP_MS = 600;

    function collectRevealTargets() {
        const mainEl = document.querySelector('main');
        if (!mainEl) return [];

        const candidates = mainEl.querySelectorAll(
            '.lp-card-3d, .lp-card, .lp-card-lg, .lp-panel-lg, article, section'
        );

        const targets = [];
        candidates.forEach((el) => {
            if (el.closest('.fixed, [role="dialog"], .no-reveal')) return;
            if (el.classList.contains('hidden')) return;

            if (
                el.tagName === 'SECTION' &&
                !el.classList.contains('lp-panel-lg') &&
                el.querySelector('.lp-card-3d, .lp-card, .lp-card-lg, .lp-panel-lg, article')
            ) {
                return;
            }

            targets.push(el);
        });

        return targets;
    }

    function markTargets() {
        const targets = collectRevealTargets();
        targets.forEach((el) => {
            if (!el.classList.contains('lp-revealed') && !el.classList.contains('is-visible')) {
                el.classList.add('lp-reveal');
            }
        });
        return targets;
    }

    function waitElementImagesReady(el) {
        const imgs = Array.from(el.querySelectorAll('img')).filter(
            (img) => !img.complete || img.naturalWidth === 0
        );
        if (imgs.length === 0) {
            return Promise.resolve();
        }

        return new Promise((resolve) => {
            let done = false;
            const finish = () => {
                if (done) return;
                done = true;
                resolve();
            };

            const timer = window.setTimeout(finish, IMAGE_WAIT_CAP_MS);
            let remaining = imgs.length;

            const onImgSettled = () => {
                remaining--;
                if (remaining <= 0) {
                    window.clearTimeout(timer);
                    finish();
                }
            };

            imgs.forEach((img) => {
                img.addEventListener('load', onImgSettled, { once: true });
                img.addEventListener('error', onImgSettled, { once: true });
            });
        });
    }

    function triggerReveal(el, delayMs) {
        if (el.classList.contains('is-visible') || el.classList.contains('lp-revealed')) {
            return;
        }
        el.style.setProperty('--lp-reveal-delay', delayMs + 'ms');
        el.classList.add('is-visible');

        window.setTimeout(function () {
            el.classList.remove('lp-reveal', 'is-visible');
            el.classList.add('lp-revealed');
            el.style.removeProperty('--lp-reveal-delay');
        }, DURATION_MS + delayMs + 50);
    }

    function revealWhenRendered(el, delayMs, observer) {
        if (observer) {
            observer.unobserve(el);
        }
        if (el.dataset.lpRevealPending === '1') {
            return;
        }
        el.dataset.lpRevealPending = '1';

        waitElementImagesReady(el).then(function () {
            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(function () {
                    triggerReveal(el, delayMs);
                });
            });
        });
    }

    function isMeaningfullyVisible(rect, viewportH) {
        // Pastikan bukan sekadar mengintip 1px di tepi bawah layar
        const visiblePx = Math.min(rect.bottom, viewportH) - Math.max(rect.top, 0);
        return visiblePx >= 28 && rect.top < viewportH - 24;
    }

    function init() {
        const targets = markTargets();
        if (targets.length === 0) return;

        const reducedMotion =
            window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (reducedMotion || !('IntersectionObserver' in window)) {
            targets.forEach((el) => {
                el.classList.remove('lp-reveal', 'is-visible');
                el.classList.add('lp-revealed');
            });
            return;
        }

        const observer = new IntersectionObserver(
            function (entries) {
                const entering = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort((a, b) => {
                        const ra = a.boundingClientRect;
                        const rb = b.boundingClientRect;
                        if (Math.abs(ra.top - rb.top) > 16) {
                            return ra.top - rb.top;
                        }
                        return ra.left - rb.left;
                    });

                entering.forEach((entry, idx) => {
                    const delay = Math.min(idx * STAGGER_MS, MAX_BATCH_STAGGER_MS);
                    revealWhenRendered(entry.target, delay, observer);
                });
            },
            {
                threshold: 0.12,
                rootMargin: '0px 0px -36px 0px',
            }
        );

        const scheduleInitialCheck = () => {
            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(function () {
                    const viewportH = window.innerHeight || 800;
                    let aboveFoldIdx = 0;

                    targets.forEach((el) => {
                        const rect = el.getBoundingClientRect();
                        if (rect.width === 0 && rect.height === 0) {
                            observer.observe(el);
                            return;
                        }
                        if (isMeaningfullyVisible(rect, viewportH)) {
                            const delay = Math.min(aboveFoldIdx * STAGGER_MS, MAX_BATCH_STAGGER_MS);
                            aboveFoldIdx++;
                            revealWhenRendered(el, delay, null);
                        } else {
                            observer.observe(el);
                        }
                    });
                });
            });
        };

        let initialChecked = false;
        const runInitialOnce = () => {
            if (initialChecked) return;
            initialChecked = true;
            scheduleInitialCheck();
        };

        // Tunggu page load agar aset ter-render, dengan batas aman 350ms agar tidak tertahan font eksternal saat offline
        if (document.readyState === 'complete') {
            runInitialOnce();
        } else {
            window.addEventListener('load', runInitialOnce, { once: true });
            window.setTimeout(runInitialOnce, 350);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
