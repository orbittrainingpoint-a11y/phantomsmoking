// main.js — Global app initialization

// Global error guard — prevents JS crashes from causing chrome-error frame issues
window.addEventListener('error', e => {
    console.error('JS Error:', e.message, e.filename, e.lineno);
});
window.addEventListener('unhandledrejection', e => {
    console.warn('Unhandled promise rejection:', e.reason);
    e.preventDefault();
});

document.addEventListener('DOMContentLoaded', () => {
    initStickyHeader();
    initScrollTop();
    initMobileNav();
    initTabs();
    autoHideToasts();
});

function initStickyHeader() {
    const header = document.getElementById('siteHeader');
    if (!header) return;
    window.addEventListener('scroll', () => {
        header.classList.toggle('scrolled', window.scrollY > 50);
    }, { passive: true });
}

function initScrollTop() {
    const btn = document.getElementById('scrollTop');
    if (!btn) return;
    window.addEventListener('scroll', () => {
        btn.classList.toggle('show', window.scrollY > 400);
    }, { passive: true });
}

function initMobileNav() {
    // handled by inline onclick
}

function toggleMobileNav() {
    const nav     = document.getElementById('mobileNav');
    const overlay = document.getElementById('mobileNavOverlay');
    const toggle  = document.getElementById('navToggle');
    nav?.classList.toggle('open');
    overlay?.classList.toggle('show');
    toggle?.classList.toggle('open');
    document.body.style.overflow = nav?.classList.contains('open') ? 'hidden' : '';
}

function toggleMobileSub(id) {
    const sub = document.getElementById(id);
    sub?.classList.toggle('open');
}

function toggleCartDrawer() {
    const drawer = document.getElementById('cartDrawer');
    const overlay = document.getElementById('cartOverlay');
    drawer?.classList.toggle('open');
    overlay?.classList.toggle('show');
    document.body.style.overflow = drawer?.classList.contains('open') ? 'hidden' : '';
}

function initTabs() {
    // handled by switchTab()
}

function switchTab(id, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + id)?.classList.add('active');
    btn?.classList.add('active');
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
    toast.innerHTML = `<i class="fas ${icon}"></i>${message}`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

function autoHideToasts() {
    document.querySelectorAll('.toast').forEach(t => {
        setTimeout(() => t.remove(), 4000);
    });
}

async function subscribeNewsletter(e) {
    e.preventDefault();
    const input = e.target.querySelector('input[type=email]');
    const email = input?.value;
    if (!email) return;
    const res = await fetch('/api/newsletter/subscribe', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
    });
    const data = await res.json();
    showToast(data.message || data.error, data.success ? 'success' : 'error');
    if (data.success && input) input.value = '';
}
