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

        // 5. Pointer Depth Parallax inside [data-parallax-scene]
        if (!prefersReducedMotion) {
            document.querySelectorAll('[data-parallax-scene]').forEach(function (scene) {
                const layers = Array.from(scene.querySelectorAll('[data-mouse-depth]'));
                if (!layers.length) {
                    return;
                }

                let sceneRaf = null;
                let targetX = 0;
                let targetY = 0;

                scene.addEventListener('mousemove', function (e) {
                    const rect = scene.getBoundingClientRect();
                    const relX = ((e.clientX - rect.left) / Math.max(1, rect.width)) - 0.5;
                    const relY = ((e.clientY - rect.top) / Math.max(1, rect.height)) - 0.5;
                    targetX = relX * 2; // -1 to +1
                    targetY = relY * 2;

                    if (!sceneRaf) {
                        sceneRaf = requestAnimationFrame(function () {
                            sceneRaf = null;
                            for (let k = 0; k < layers.length; k++) {
                                const layer = layers[k];
                                const depth = parseFloat(layer.getAttribute('data-mouse-depth') || '12');
                                const dx = (targetX * depth).toFixed(2);
                                const dy = (targetY * depth).toFixed(2);
                                layer.style.transform = 'translate3d(' + dx + 'px, ' + dy + 'px, 0)';
                            }
                        });
                    }
                }, { passive: true });

                scene.addEventListener('mouseleave', function () {
                    for (let k = 0; k < layers.length; k++) {
                        layers[k].style.transform = 'translate3d(0px, 0px, 0)';
                    }
                }, { passive: true });
            });

            // 6. Tactile 3D Archival Plate & Card Tilt ([data-tilt-card])
            document.querySelectorAll('[data-tilt-card]').forEach(function (card) {
                const maxDeg = parseFloat(card.getAttribute('data-tilt-max') || '2.8');

                card.addEventListener('mousemove', function (e) {
                    const rect = card.getBoundingClientRect();
                    const px = ((e.clientX - rect.left) / Math.max(1, rect.width)) - 0.5;
                    const py = ((e.clientY - rect.top) / Math.max(1, rect.height)) - 0.5;
                    const rotateX = (-py * maxDeg * 2).toFixed(2) + 'deg';
                    const rotateY = (px * maxDeg * 2).toFixed(2) + 'deg';
                    card.style.setProperty('--tilt-x', rotateX);
                    card.style.setProperty('--tilt-y', rotateY);
                }, { passive: true });

                card.addEventListener('mouseleave', function () {
                    card.style.setProperty('--tilt-x', '0deg');
                    card.style.setProperty('--tilt-y', '0deg');
                }, { passive: true });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeritageMotion);
    } else {
        initHeritageMotion();
    }
})();
