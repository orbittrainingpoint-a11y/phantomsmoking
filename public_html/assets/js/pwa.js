// pwa.js — Install prompt logic
(function () {
  // Register service worker
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').catch(() => {});
  }

  // Only run on mobile
  if (window.innerWidth > 900) return;

  // Already installed (standalone mode) — don't show
  if (window.matchMedia('(display-mode: standalone)').matches) return;
  if (window.navigator.standalone === true) return; // iOS Safari

  const STORAGE_KEY = 'pwa_prompt';
  const REVISIT_THRESHOLD = 10;

  let data = {};
  try { data = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'); } catch (e) {}

  // If dismissed permanently, stop
  if (data.dismissed) return;

  // Count this visit
  data.visits = (data.visits || 0) + 1;

  // Show on 1st visit, or every 10 visits after dismissal
  const shouldShow = data.visits === 1 || (data.lastDismissedAt && data.visits - data.lastDismissedAt >= REVISIT_THRESHOLD);

  try { localStorage.setItem(STORAGE_KEY, JSON.stringify(data)); } catch (e) {}

  if (!shouldShow) return;

  let deferredPrompt = null;

  window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    deferredPrompt = e;
    showBanner();
  });

  // iOS Safari fallback (no beforeinstallprompt support)
  const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
  const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
  if (isIos && isSafari) {
    // Small delay so page loads first
    setTimeout(showBanner, 1500);
  }

  function showBanner() {
    const banner = document.getElementById('pwaBanner');
    if (!banner) return;
    setTimeout(() => banner.classList.add('show'), 800);
  }

  window._pwaInstall = async function () {
    const banner = document.getElementById('pwaBanner');
    if (deferredPrompt) {
      deferredPrompt.prompt();
      const { outcome } = await deferredPrompt.userChoice;
      deferredPrompt = null;
      if (outcome === 'accepted') {
        data.dismissed = true;
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(data)); } catch (e) {}
      }
    } else {
      // iOS — show instructions
      document.getElementById('pwaIosHint')?.classList.toggle('show');
      return;
    }
    banner?.classList.remove('show');
  };

  window._pwaDismiss = function () {
    const banner = document.getElementById('pwaBanner');
    banner?.classList.remove('show');
    data.lastDismissedAt = data.visits;
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(data)); } catch (e) {}
  };
})();
