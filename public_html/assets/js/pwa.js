// pwa.js — Safe PWA install prompt, no fetch interception
(function () {

  // Step 1: Unregister any broken old SW and clear all caches first
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistrations().then(regs => {
      regs.forEach(reg => reg.unregister());
    });
    if ('caches' in window) {
      caches.keys().then(keys => keys.forEach(k => caches.delete(k)));
    }
    // Re-register clean SW after a short delay
    setTimeout(() => {
      navigator.serviceWorker.register('/sw.js').catch(() => {});
    }, 2000);
  }

  // Step 2: Install prompt — only on mobile
  if (window.innerWidth > 900) return;
  if (window.matchMedia('(display-mode: standalone)').matches) return;
  if (window.navigator.standalone === true) return;

  const STORAGE_KEY = 'pwa_prompt';
  const REVISIT_THRESHOLD = 10;

  let data = {};
  try { data = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'); } catch (e) {}
  if (data.dismissed) return;

  data.visits = (data.visits || 0) + 1;
  const shouldShow = data.visits === 1 ||
    (data.lastDismissedAt && data.visits - data.lastDismissedAt >= REVISIT_THRESHOLD);
  try { localStorage.setItem(STORAGE_KEY, JSON.stringify(data)); } catch (e) {}

  if (!shouldShow) return;

  let deferredPrompt = null;

  window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    deferredPrompt = e;
    setTimeout(showBanner, 800);
  });

  // iOS Safari fallback
  const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
  const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
  if (isIos && isSafari) {
    setTimeout(showBanner, 1500);
  }

  function showBanner() {
    const banner = document.getElementById('pwaBanner');
    if (banner) banner.classList.add('show');
  }

  window._pwaInstall = async function () {
    if (deferredPrompt) {
      deferredPrompt.prompt();
      const { outcome } = await deferredPrompt.userChoice;
      deferredPrompt = null;
      if (outcome === 'accepted') {
        data.dismissed = true;
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(data)); } catch (e) {}
      }
    } else {
      document.getElementById('pwaIosHint')?.classList.toggle('show');
      return;
    }
    document.getElementById('pwaBanner')?.classList.remove('show');
  };

  window._pwaDismiss = function () {
    document.getElementById('pwaBanner')?.classList.remove('show');
    data.lastDismissedAt = data.visits;
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(data)); } catch (e) {}
  };

})();
