// Front Site Main JavaScript
(function() {
    'use strict';

    // Mobile Navigation Toggle
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            
            // Animate toggle button
            const spans = navToggle.querySelectorAll('span');
            if (navMenu.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translate(8px, 8px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(7px, -7px)';
            } else {
                spans[0].style.transform = '';
                spans[1].style.opacity = '1';
                spans[2].style.transform = '';
            }
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
                const spans = navToggle.querySelectorAll('span');
                spans[0].style.transform = '';
                spans[1].style.opacity = '1';
                spans[2].style.transform = '';
            }
        });
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href.length > 1) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // Animate on scroll (simple version)
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe elements with animation class
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });

    // ===== Announcements Carousel =====
    document.querySelectorAll('[data-carousel]').forEach(initCarousel);

    function initCarousel(root) {
        const track  = root.querySelector('[data-carousel-track]');
        const slides = root.querySelectorAll('[data-carousel-slide]');
        const prev   = root.querySelector('[data-carousel-prev]');
        const next   = root.querySelector('[data-carousel-next]');
        const dots   = root.querySelectorAll('[data-carousel-dot]');
        if (!track || slides.length === 0) return;

        let current = 0;
        const total = slides.length;
        const AUTOPLAY_MS = 7000;
        let autoplayTimer = null;

        const goTo = (index, smooth = true) => {
            current = (index + total) % total;
            slides[current].scrollIntoView({
                behavior: smooth ? 'smooth' : 'auto',
                block: 'nearest',
                inline: 'center',
            });
            updateDots();
            updateArrows();
        };

        const updateDots = () => {
            dots.forEach((d, i) => d.classList.toggle('is-active', i === current));
        };

        const updateArrows = () => {
            if (!prev || !next) return;
            // مع carousel دائري (loop) لا نعطّل الأسهم؛ نتركها مفعّلة دائماً
            prev.disabled = false;
            next.disabled = false;
        };

        if (prev) prev.addEventListener('click', () => { stopAutoplay(); goTo(current - 1); restartAutoplay(); });
        if (next) next.addEventListener('click', () => { stopAutoplay(); goTo(current + 1); restartAutoplay(); });
        dots.forEach((d, i) => d.addEventListener('click', () => { stopAutoplay(); goTo(i); restartAutoplay(); }));

        // مزامنة current مع السكرول اليدوي (سحب / لمس)
        let scrollDebounce;
        track.addEventListener('scroll', () => {
            clearTimeout(scrollDebounce);
            scrollDebounce = setTimeout(() => {
                const center = track.scrollLeft + track.clientWidth / 2;
                let closest = 0, minDist = Infinity;
                slides.forEach((slide, i) => {
                    const slideCenter = slide.offsetLeft + slide.clientWidth / 2;
                    const dist = Math.abs(slideCenter - center);
                    if (dist < minDist) { minDist = dist; closest = i; }
                });
                if (closest !== current) {
                    current = closest;
                    updateDots();
                }
            }, 100);
        });

        // ===== Autoplay =====
        const startAutoplay = () => {
            if (total <= 1) return;
            autoplayTimer = setInterval(() => goTo(current + 1), AUTOPLAY_MS);
        };
        const stopAutoplay = () => { if (autoplayTimer) clearInterval(autoplayTimer); autoplayTimer = null; };
        const restartAutoplay = () => { stopAutoplay(); startAutoplay(); };

        // إيقاف عند hover ومتابعة عند المغادرة
        root.addEventListener('mouseenter', stopAutoplay);
        root.addEventListener('mouseleave', startAutoplay);

        // إيقاف عند فقد التركيز على الـ tab (لا نضيع موارد)
        document.addEventListener('visibilitychange', () => {
            document.hidden ? stopAutoplay() : startAutoplay();
        });

        // التشغيل الأولي
        updateArrows();
        startAutoplay();
    }
})();

