document.addEventListener('DOMContentLoaded', function () {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const introLoader = document.getElementById('introLoader');
    const shouldRunIntroLoader = document.documentElement.classList.contains('show-intro-loader') && introLoader !== null;
    let pageAnimationsStarted = false;

    const startPageAnimations = () => {
        if (pageAnimationsStarted || !window.gsap) {
            return;
        }

        pageAnimationsStarted = true;

        if (window.ScrollTrigger) {
            window.gsap.registerPlugin(window.ScrollTrigger);
        }

        window.gsap.to('.logo-shimmer', {
            backgroundPosition: '240% center',
            duration: 6,
            repeat: -1,
            ease: 'none',
        });

        window.gsap.to('.brand-mark', {
            y: -3,
            duration: 2.4,
            repeat: -1,
            yoyo: true,
            ease: 'sine.inOut',
        });

        window.gsap
            .timeline({ defaults: { ease: 'power3.out' } })
            .from('.hero-logo', { opacity: 0, scale: 0.7, duration: 0.7 })
            .from('.hero-section .reveal-item', { opacity: 0, y: 30, stagger: 0.12, duration: 0.8 }, '-=0.25');

        if (!prefersReducedMotion) {
            window.gsap.to('.hero-orb--one', {
                x: 90,
                y: -30,
                duration: 9,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
            });
            window.gsap.to('.hero-orb--two', {
                x: -70,
                y: 40,
                duration: 10,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
            });
            window.gsap.to('.hero-orb--three', {
                x: 60,
                y: -40,
                duration: 12,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
            });
            window.gsap.to('.hero-aurora', {
                opacity: 0.55,
                duration: 3,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
            });
        }

        if (window.ScrollTrigger) {
            window.gsap.utils.toArray('.reveal-section').forEach((section) => {
                const targets = section.querySelectorAll('.reveal-item');
                if (!targets.length) {
                    return;
                }

                window.gsap.from(targets, {
                    opacity: 0,
                    y: 36,
                    duration: 0.9,
                    stagger: 0.08,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: section,
                        start: 'top 82%',
                    },
                });
            });
        }
    };

    const finishIntroLoader = () => {
        document.documentElement.classList.remove('show-intro-loader');
        document.documentElement.classList.add('intro-loader-seen');
        if (introLoader) {
            introLoader.setAttribute('aria-hidden', 'true');
        }

        try {
            window.localStorage.setItem('nyscc_intro_loader_seen', '1');
        } catch (error) {
            // Ignore storage issues.
        }

        startPageAnimations();
    };

    if (shouldRunIntroLoader) {
        introLoader.setAttribute('aria-hidden', 'false');
        const progressLine = introLoader.querySelector('.intro-loader__line > span');

        if (window.gsap && !prefersReducedMotion) {
            const loaderTl = window.gsap.timeline({
                defaults: { ease: 'power2.out' },
                onComplete: finishIntroLoader,
            });

            loaderTl
                .fromTo('.intro-loader__logo', { opacity: 0, scale: 0.84, y: 8 }, { opacity: 1, scale: 1, y: 0, duration: 0.72 })
                .fromTo('.intro-loader__brand', { opacity: 0, y: 10 }, { opacity: 1, y: 0, duration: 0.56 }, '-=0.4');

            if (progressLine) {
                loaderTl.to(progressLine, { scaleX: 1, duration: 1.15, ease: 'power2.inOut' }, '-=0.15');
            } else {
                loaderTl.to({}, { duration: 1.15 });
            }

            loaderTl.to(introLoader, { opacity: 0, duration: 0.46, ease: 'power1.out', delay: 0.16 });
        } else {
            if (progressLine) {
                progressLine.style.transform = 'scaleX(1)';
            }

            window.setTimeout(function () {
                if (introLoader) {
                    introLoader.style.opacity = '0';
                }
                finishIntroLoader();
            }, prefersReducedMotion ? 260 : 1100);
        }
    } else {
        startPageAnimations();
    }

    const setupDetailGallery = (gallery) => {
        const items = Array.from(gallery.querySelectorAll('.detail-media-item'));
        const thumbs = Array.from(gallery.querySelectorAll('.detail-thumb'));

        if (!items.length || !thumbs.length) {
            return;
        }

        const showItem = (targetIndex) => {
            items.forEach((item, index) => {
                const isActive = index === targetIndex;
                item.classList.toggle('pointer-events-none', !isActive);

                if (window.gsap) {
                    window.gsap.to(item, {
                        opacity: isActive ? 1 : 0,
                        duration: 0.28,
                        ease: 'power2.out',
                    });
                } else {
                    item.style.opacity = isActive ? '1' : '0';
                }

                if (item.tagName === 'VIDEO') {
                    if (isActive) {
                        const playPromise = item.play();
                        if (playPromise && typeof playPromise.catch === 'function') {
                            playPromise.catch(function () {
                                // Some browsers can still block autoplay.
                            });
                        }
                    } else {
                        item.pause();
                    }
                }
            });

            thumbs.forEach((thumb, index) => {
                const isActive = index === targetIndex;
                thumb.classList.toggle('border-gold/70', isActive);
                thumb.classList.toggle('border-zinc-700', !isActive);
            });
        };

        thumbs.forEach((thumb) => {
            thumb.addEventListener('click', function () {
                const targetIndex = Number.parseInt(thumb.dataset.targetIndex || '0', 10);
                if (Number.isNaN(targetIndex)) {
                    return;
                }

                showItem(targetIndex);
            });
        });

        showItem(0);
    };

    const copyTextToClipboard = async (text) => {
        const value = String(text || '').trim();
        if (value === '') {
            return false;
        }

        if (navigator.clipboard && window.isSecureContext) {
            try {
                await navigator.clipboard.writeText(value);
                return true;
            } catch (error) {
                // Fallback below.
            }
        }

        const textarea = document.createElement('textarea');
        textarea.value = value;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();

        let copied = false;
        try {
            copied = document.execCommand('copy');
        } catch (error) {
            copied = false;
        }

        document.body.removeChild(textarea);
        return copied;
    };

    const setupBankingCardsCopy = () => {
        const cards = Array.from(document.querySelectorAll('.banking-card--copyable'));
        if (!cards.length) {
            return;
        }

        const feedbackTimers = new WeakMap();
        const showFeedback = (card, success) => {
            const badge = card.querySelector('.banking-card__copy-badge');
            if (badge) {
                badge.textContent = success ? 'Kopyalandı' : 'Kopyalanamadı';
            }

            card.classList.remove('is-copied', 'is-copy-failed');
            void card.offsetWidth;
            card.classList.add(success ? 'is-copied' : 'is-copy-failed');

            const existingTimer = feedbackTimers.get(card);
            if (existingTimer) {
                window.clearTimeout(existingTimer);
            }

            const timer = window.setTimeout(function () {
                card.classList.remove('is-copied', 'is-copy-failed');
                if (badge) {
                    badge.textContent = 'Kopyalandı';
                }
                feedbackTimers.delete(card);
            }, 1700);

            feedbackTimers.set(card, timer);
        };

        const triggerCopy = async (card) => {
            const valueElement = card.querySelector('.banking-card__value');
            const textToCopy = valueElement ? (valueElement.innerText || valueElement.textContent || '').trim() : '';
            if (textToCopy === '') {
                showFeedback(card, false);
                return;
            }

            const copied = await copyTextToClipboard(textToCopy);
            showFeedback(card, copied);
        };

        cards.forEach((card) => {
            card.addEventListener('click', function () {
                triggerCopy(card);
            });

            card.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }
                event.preventDefault();
                triggerCopy(card);
            });
        });
    };

    document.querySelectorAll('.detail-gallery').forEach(setupDetailGallery);
    setupBankingCardsCopy();
});
