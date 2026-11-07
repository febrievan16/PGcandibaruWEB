/**
 * GulaTour Heritage Interactive Motion, GSAP Orchestration & WebGL 3D Engine
 * PT Pabrik Gula Candi Baru (Anno 1911)
 *
 * Integrates Three.js WebGL 3D models (GLTFLoader + Procedural 1911 Steam Mill)
 * with GreenSock (GSAP + ScrollTrigger + quickTo + unified ticker) while
 * respecting prefers-reduced-motion and viewport visibility pausing.
 */
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

(function () {
    'use strict';

    const prefersReducedMotion =
        window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

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

        // 2. Scroll-Reveal via IntersectionObserver + GSAP Stagger Polish
        const revealElements = Array.from(document.querySelectorAll('[data-reveal]'));
        if (prefersReducedMotion || !('IntersectionObserver' in window)) {
            revealElements.forEach(function (el) {
                el.classList.add('is-revealed');
            });
        } else {
            const revealObserver = new IntersectionObserver(
                function (entries, observer) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-revealed');
                            observer.unobserve(entry.target);
                        }
                    });
                },
                {
                    root: null,
                    rootMargin: '0px 0px -8% 0px',
                    threshold: 0.08,
                }
            );

            revealElements.forEach(function (el) {
                revealObserver.observe(el);
            });
        }

        // 3. Animated Numeric Counters ([data-count-up]) via GSAP Tween
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
            const durationSec =
                parseInt(el.getAttribute('data-count-duration') || '950', 10) / 1000;
            const proxy = { val: startVal };

            gsap.to(proxy, {
                val: target,
                duration: durationSec,
                ease: 'power3.out',
                onUpdate: function () {
                    el.textContent = String(Math.round(proxy.val));
                },
            });
        }

        if ('IntersectionObserver' in window && !prefersReducedMotion) {
            const counterObserver = new IntersectionObserver(
                function (entries, observer) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            animateCounter(entry.target);
                            observer.unobserve(entry.target);
                        }
                    });
                },
                { threshold: 0.25 }
            );

            counterElements.forEach(function (el) {
                counterObserver.observe(el);
            });
        }

        // 4. Scroll Progress Bar & Multi-Layer Scroll Parallax
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

            for (let i = 0; i < parallaxItems.length; i++) {
                const item = parallaxItems[i];
                const speed = parseFloat(item.getAttribute('data-parallax-speed') || '0.12');
                const axis = item.getAttribute('data-parallax-axis') || 'y';
                const rect = item.getBoundingClientRect();

                if (rect.bottom < -300 || rect.top > viewportHeight + 300) {
                    continue;
                }

                const centerOffset = rect.top + rect.height * 0.5 - viewportHeight * 0.5;
                const shift = Math.max(-75, Math.min(75, -centerOffset * speed));

                if (axis === 'x') {
                    item.style.transform = 'translate3d(' + shift.toFixed(2) + 'px, 0, 0)';
                } else {
                    item.style.transform = 'translate3d(0, ' + shift.toFixed(2) + 'px, 0)';
                }
            }

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

        // 5. Pointer Depth Parallax inside [data-parallax-scene] via GSAP quickTo
        if (!prefersReducedMotion) {
            document.querySelectorAll('[data-parallax-scene]').forEach(function (scene) {
                const layers = Array.from(scene.querySelectorAll('[data-mouse-depth]'));
                if (!layers.length) {
                    return;
                }

                const layerControllers = layers.map(function (layer) {
                    const depth = parseFloat(layer.getAttribute('data-mouse-depth') || '12');
                    return {
                        depth: depth,
                        qx: gsap.quickTo(layer, 'x', { duration: 0.65, ease: 'power3.out' }),
                        qy: gsap.quickTo(layer, 'y', { duration: 0.65, ease: 'power3.out' }),
                    };
                });

                scene.addEventListener(
                    'mousemove',
                    function (e) {
                        const rect = scene.getBoundingClientRect();
                        const relX = ((e.clientX - rect.left) / Math.max(1, rect.width) - 0.5) * 2;
                        const relY = ((e.clientY - rect.top) / Math.max(1, rect.height) - 0.5) * 2;

                        for (let k = 0; k < layerControllers.length; k++) {
                            const ctrl = layerControllers[k];
                            ctrl.qx(relX * ctrl.depth);
                            ctrl.qy(relY * ctrl.depth);
                        }
                    },
                    { passive: true }
                );

                scene.addEventListener(
                    'mouseleave',
                    function () {
                        for (let k = 0; k < layerControllers.length; k++) {
                            const ctrl = layerControllers[k];
                            ctrl.qx(0);
                            ctrl.qy(0);
                        }
                    },
                    { passive: true }
                );
            });

            // 6. Tactile 3D Archival Plate & Card Tilt ([data-tilt-card]) via GSAP quickTo
            document.querySelectorAll('[data-tilt-card]').forEach(function (card) {
                const maxDeg = parseFloat(card.getAttribute('data-tilt-max') || '5.5');

                let glareEl = card.querySelector('.heritage-3d-glare');
                if (!glareEl) {
                    glareEl = document.createElement('div');
                    glareEl.className = 'heritage-3d-glare';
                    glareEl.setAttribute('aria-hidden', 'true');
                    card.appendChild(glareEl);
                }

                const state = { rx: 0, ry: 0 };
                const applyCardTilt = function () {
                    card.style.setProperty('--tilt-x', state.rx.toFixed(2) + 'deg');
                    card.style.setProperty('--tilt-y', state.ry.toFixed(2) + 'deg');
                    card.style.setProperty('--tilt-x-num', state.rx.toFixed(2));
                    card.style.setProperty('--tilt-y-num', state.ry.toFixed(2));
                };

                const quickRX = gsap.quickTo(state, 'rx', {
                    duration: 0.55,
                    ease: 'power3.out',
                    onUpdate: applyCardTilt,
                });
                const quickRY = gsap.quickTo(state, 'ry', {
                    duration: 0.55,
                    ease: 'power3.out',
                    onUpdate: applyCardTilt,
                });

                card.addEventListener(
                    'mousemove',
                    function (e) {
                        const rect = card.getBoundingClientRect();
                        const px = (e.clientX - rect.left) / Math.max(1, rect.width);
                        const py = (e.clientY - rect.top) / Math.max(1, rect.height);
                        const isExploded = card.classList.contains('diorama-exploded');
                        const baseRX = isExploded ? 8 : 0;
                        const baseRY = isExploded ? -10 : 0;

                        quickRX(baseRX + (0.5 - py) * maxDeg * 2);
                        quickRY(baseRY + (px - 0.5) * maxDeg * 2);

                        card.style.setProperty('--glare-x', (px * 100).toFixed(1) + '%');
                        card.style.setProperty('--glare-y', (py * 100).toFixed(1) + '%');
                        card.style.setProperty('--glare-alpha', '1');
                    },
                    { passive: true }
                );

                card.addEventListener(
                    'mouseleave',
                    function () {
                        const isExploded = card.classList.contains('diorama-exploded');
                        quickRX(isExploded ? 8 : 0);
                        quickRY(isExploded ? -10 : 0);
                        card.style.setProperty('--glare-alpha', '0');
                    },
                    { passive: true }
                );
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
                        : 'Mode 3D Layer (Bedah Mesin)';
                }

                if (isNowExploded) {
                    stageCard.style.setProperty('--tilt-x', '11deg');
                    stageCard.style.setProperty('--tilt-y', '-15deg');
                    stageCard.style.setProperty('--tilt-x-num', '11');
                    stageCard.style.setProperty('--tilt-y-num', '-15');
                } else {
                    stageCard.style.setProperty('--tilt-x', '0deg');
                    stageCard.style.setProperty('--tilt-y', '0deg');
                    stageCard.style.setProperty('--tilt-x-num', '0');
                    stageCard.style.setProperty('--tilt-y-num', '0');
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
