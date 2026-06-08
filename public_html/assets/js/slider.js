// slider.js — Swiper.js initialization
document.addEventListener('DOMContentLoaded', () => {
    // Hero slider
    if (document.querySelector('.heroSwiper')) {
        new Swiper('.heroSwiper', {
            loop: true, autoplay: { delay: 5000, disableOnInteraction: false },
            pagination: { el: '.swiper-pagination', clickable: true },
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
            effect: 'fade', fadeEffect: { crossFade: true },
        });
    }
    // Brand carousel — replaced with CSS marquee (no Swiper needed)
    // Product carousels
    document.querySelectorAll('.productSwiper').forEach(el => {
        new Swiper(el, {
            slidesPerView: 1.2, spaceBetween: 16,
            navigation: { nextEl: el.querySelector('.swiper-button-next'), prevEl: el.querySelector('.swiper-button-prev') },
            breakpoints: { 480: { slidesPerView: 2 }, 768: { slidesPerView: 3 }, 1024: { slidesPerView: 4 } },
        });
    });
});
