const CACHE = 'phantom-v5';

// Immediately unregister self and clear ALL caches
self.addEventListener('install', e => {
  self.skipWaiting();
});

self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys()
      .then(keys => Promise.all(keys.map(k => caches.delete(k))))
      .then(() => self.clients.claim())
      .then(() => {
        // Tell all open tabs to reload
        self.clients.matchAll({ type: 'window' }).then(clients => {
          clients.forEach(client => client.postMessage({ type: 'SW_RESET' }));
        });
      })
  );
});

// Pass everything directly to network — no interception at all
