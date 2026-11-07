/**
 * GulaTour Heritage Interactive Motion & Multi-Layer Parallax Engine
 * PT Pabrik Gula Candi Baru (Anno 1911)
 *
 * Provides GPU-accelerated scroll parallax, pointer depth layers,
 * staggered IntersectionObserver reveals, 3D archival plate tilt,
 * and animated numeric counters while respecting prefers-reduced-motion.
 */
(function () {
    'use strict';

    const prefersReducedMotion = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!prefersReducedMotion) {
        document.documentElement.classList.add('js-motion-ready');
    }

    function initHeritageMotion() {
        const docEl = document.documentElement;
        const stickyHeader = document.querySelector('[data-sticky-header]');
        const parallaxItems = Array.from(document.querySelectorAll('[data-parallax-speed]'));
        const rotateItems = Array.from(document.querySelectorAll('[data-parallax-rotate]'));

        // 1. Auto-assign stagger delays for [data-reveal-stagger] groups
        document.querySelectorAll('[data-reveal-stagger]').forEach(function (group) {
            const stepMs = parseInt(group.getAttribute('data-reveal-stagger') || '90', 10);
            const children = group.querySelectorAll('[data-reveal]');
            children.forEach(function (child, index) {
                if (!child.style.getPropertyValue('--reveal-delay')) {
                    const delay = Math.min(index * stepMs, 540);
                    child.style.setProperty('--reveal-delay', delay + 'ms');
                }
            });
        });

        // Apply explicit data-reveal-delay attributes
        document.querySelectorAll('[data-reveal][data-reveal-delay]').forEach(function (el) {
            const delay = parseInt(el.getAttribute('data-reveal-delay') || '0', 10);
            el.style.setProperty('--reveal-delay', delay + 'ms');
        });

        // 2. Scroll-Reveal via IntersectionObserver
        const revealElements = Array.from(document.querySelectorAll('[data-reveal]'));
        if (prefersReducedMotion || !('IntersectionObserver' in window)) {
            revealElements.forEach(function (el) {
                el.classList.add('is-revealed');
            });
        } else {
            const revealObserver = new IntersectionObserver(function (entries, observer) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-revealed');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                root: null,
                rootMargin: '0px 0px -8% 0px',
                threshold: 0.08
            });

            revealElements.forEach(function (el) {
                revealObserver.observe(el);
            });
        }

        // 3. Animated Numeric Counters ([data-count-up])
        const counterElements = Array.from(document.querySelectorAll('[data-count-up]'));
        function animateCounter(el) {
            if (el.getAttribute('data-counted') === '1') {
                return;
            }
            el.setAttribute('data-counted', '1');

            const target = parseInt(el.getAttribute('data-count-up') || el.textContent || '0', 10);
            if (isNaN(target) || prefersReducedMotion) {
                el.textContent = String(target);
                return;
            }

            const startVal = parseInt(el.getAttribute('data-count-from') || '0', 10);
            const duration = parseInt(el.getAttribute('data-count-duration') || '950', 10);
            const startTime = performance.now();

            function tick(now) {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                // Ease-out cubic
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = Math.round(startVal + (target - startVal) * eased);
                el.textContent = String(current);
                if (progress < 1) {
                    requestAnimationFrame(tick);
                } else {
                    el.textContent = String(target);
                }
            }

            requestAnimationFrame(tick);
        }

        if ('IntersectionObserver' in window && !prefersReducedMotion) {
            const counterObserver = new IntersectionObserver(function (entries, observer) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.25 });

            counterElements.forEach(function (el) {
                counterObserver.observe(el);
            });
        }

        // 4. Scroll Progress Bar & Multi-Layer Scroll Parallax (requestAnimationFrame)
        let ticking = false;

        function updateScrollEffects() {
            ticking = false;
            const scrollY = window.scrollY || window.pageYOffset || 0;
            const viewportHeight = window.innerHeight || 800;
            const maxScroll = Math.max(1, document.documentElement.scrollHeight - viewportHeight);
            const progress = Math.min(1, Math.max(0, scrollY / maxScroll));

            docEl.style.setProperty('--scroll-progress', progress.toFixed(4));
            docEl.style.setProperty('--scroll-y', scrollY.toFixed(1));

            if (stickyHeader) {
                stickyHeader.classList.toggle('header-scrolled', scrollY > 18);
            }

            if (prefersReducedMotion) {
                return;
            }

            // Update vertical/horizontal scroll parallax elements
            for (let i = 0; i < parallaxItems.length; i++) {
                const item = parallaxItems[i];
                const speed = parseFloat(item.getAttribute('data-parallax-speed') || '0.12');
                const axis = item.getAttribute('data-parallax-axis') || 'y';
                const rect = item.getBoundingClientRect();

                // Only compute for elements near or inside the viewport
                if (rect.bottom < -300 || rect.top > viewportHeight + 300) {
                    continue;
                }

                const centerOffset = (rect.top + rect.height * 0.5) - (viewportHeight * 0.5);
                const shift = Math.max(-75, Math.min(75, -centerOffset * speed));

                if (axis === 'x') {
                    item.style.transform = 'translate3d(' + shift.toFixed(2) + 'px, 0, 0)';
                } else {
                    item.style.transform = 'translate3d(0, ' + shift.toFixed(2) + 'px, 0)';
                }
            }

            // Update scroll-driven mechanical rotation (e.g. 1911 Steam Flywheel)
            for (let j = 0; j < rotateItems.length; j++) {
                const rotEl = rotateItems[j];
                const factor = parseFloat(rotEl.getAttribute('data-parallax-rotate') || '0.18');
                const deg = (scrollY * factor) % 360;
                rotEl.style.transform = 'rotate(' + deg.toFixed(2) + 'deg)';
            }
        }

        function onScrollOrResize() {
            if (!ticking) {
                ticking = true;
                requestAnimationFrame(updateScrollEffects);
            }
        }

        window.addEventListener('scroll', onScrollOrResize, { passive: true });
        window.addEventListener('resize', onScrollOrResize, { passive: true });
        updateScrollEffects();

        // 5. Pointer Depth Parallax inside [data-parallax-scene] (60fps LERP Spring)
        if (!prefersReducedMotion) {
            document.querySelectorAll('[data-parallax-scene]').forEach(function (scene) {
                const layers = Array.from(scene.querySelectorAll('[data-mouse-depth]'));
                if (!layers.length) {
                    return;
                }

                let sceneRaf = null;
                let targetX = 0;
                let targetY = 0;
                let currentX = 0;
                let currentY = 0;

                function stepSceneSpring() {
                    currentX += (targetX - currentX) * 0.14;
                    currentY += (targetY - currentY) * 0.14;

                    for (let k = 0; k < layers.length; k++) {
                        const layer = layers[k];
                        const depth = parseFloat(layer.getAttribute('data-mouse-depth') || '12');
                        const dx = (currentX * depth).toFixed(2);
                        const dy = (currentY * depth).toFixed(2);
                        layer.style.transform = 'translate3d(' + dx + 'px, ' + dy + 'px, 0)';
                    }

                    if (Math.abs(targetX - currentX) > 0.008 || Math.abs(targetY - currentY) > 0.008) {
                        sceneRaf = requestAnimationFrame(stepSceneSpring);
                    } else {
                        sceneRaf = null;
                    }
                }

                scene.addEventListener('mousemove', function (e) {
                    const rect = scene.getBoundingClientRect();
                    const relX = ((e.clientX - rect.left) / Math.max(1, rect.width)) - 0.5;
                    const relY = ((e.clientY - rect.top) / Math.max(1, rect.height)) - 0.5;
                    targetX = relX * 2;
                    targetY = relY * 2;

                    if (!sceneRaf) {
                        sceneRaf = requestAnimationFrame(stepSceneSpring);
                    }
                }, { passive: true });

                scene.addEventListener('mouseleave', function () {
                    targetX = 0;
                    targetY = 0;
                    if (!sceneRaf) {
                        sceneRaf = requestAnimationFrame(stepSceneSpring);
                    }
                }, { passive: true });
            });

            // 6. Tactile 3D Archival Plate & Card Tilt ([data-tilt-card]) with Spring Physics & Specular Glare
            document.querySelectorAll('[data-tilt-card]').forEach(function (card) {
                const maxDeg = parseFloat(card.getAttribute('data-tilt-max') || '5.5');

                // Auto-inject specular brass glare overlay if not already present
                let glareEl = card.querySelector('.heritage-3d-glare');
                if (!glareEl) {
                    glareEl = document.createElement('div');
                    glareEl.className = 'heritage-3d-glare';
                    glareEl.setAttribute('aria-hidden', 'true');
                    card.appendChild(glareEl);
                }

                let targetRX = 0;
                let targetRY = 0;
                let currentRX = 0;
                let currentRY = 0;
                let cardRaf = null;

                function stepCardSpring() {
                    currentRX += (targetRX - currentRX) * 0.15;
                    currentRY += (targetRY - currentRY) * 0.15;

                    card.style.setProperty('--tilt-x', currentRX.toFixed(2) + 'deg');
                    card.style.setProperty('--tilt-y', currentRY.toFixed(2) + 'deg');
                    card.style.setProperty('--tilt-x-num', currentRX.toFixed(2));
                    card.style.setProperty('--tilt-y-num', currentRY.toFixed(2));

                    if (Math.abs(targetRX - currentRX) > 0.04 || Math.abs(targetRY - currentRY) > 0.04) {
                        cardRaf = requestAnimationFrame(stepCardSpring);
                    } else {
                        cardRaf = null;
                    }
                }

                card.addEventListener('mousemove', function (e) {
                    const rect = card.getBoundingClientRect();
                    const px = (e.clientX - rect.left) / Math.max(1, rect.width);
                    const py = (e.clientY - rect.top) / Math.max(1, rect.height);
                    const isExploded = card.classList.contains('diorama-exploded');
                    const baseRX = isExploded ? 11 : 0;
                    const baseRY = isExploded ? -15 : 0;

                    targetRX = baseRX + (0.5 - py) * maxDeg * 2;
                    targetRY = baseRY + (px - 0.5) * maxDeg * 2;

                    card.style.setProperty('--glare-x', (px * 100).toFixed(1) + '%');
                    card.style.setProperty('--glare-y', (py * 100).toFixed(1) + '%');
                    card.style.setProperty('--glare-alpha', '1');

                    if (!cardRaf) {
                        cardRaf = requestAnimationFrame(stepCardSpring);
                    }
                }, { passive: true });

                card.addEventListener('mouseleave', function () {
                    const isExploded = card.classList.contains('diorama-exploded');
                    targetRX = isExploded ? 11 : 0;
                    targetRY = isExploded ? -15 : 0;
                    card.style.setProperty('--glare-alpha', '0');

                    if (!cardRaf) {
                        cardRaf = requestAnimationFrame(stepCardSpring);
                    }
                }, { passive: true });
            });
        }

        // 7. Interactive 3D Diorama Exploded-Layer Toggle ([data-diorama-explode-toggle])
        document.querySelectorAll('[data-diorama-explode-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetId = btn.getAttribute('data-diorama-explode-toggle');
                const stageCard = document.getElementById(targetId);
                if (!stageCard) {
                    return;
                }

                const isNowExploded = stageCard.classList.toggle('diorama-exploded');
                btn.setAttribute('aria-pressed', isNowExploded ? 'true' : 'false');

                const labelSpan = btn.querySelector('[data-explode-label]');
                if (labelSpan) {
                    labelSpan.textContent = isNowExploded
                        ? 'Tutup Bedah 3D (Mode Normal)'
                        : 'Mode Bedah 3D (Exploded View)';
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeritageMotion);
    } else {
        initHeritageMotion();
    }
})();
