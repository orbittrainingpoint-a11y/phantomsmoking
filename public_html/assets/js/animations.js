// animations.js
document.addEventListener('DOMContentLoaded', () => {
    if (typeof AOS !== 'undefined') {
        AOS.init({ duration: 600, easing: 'ease-out', once: true, offset: 60 });
    }
});
