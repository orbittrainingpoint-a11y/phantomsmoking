const CACHE = 'phantom-v3';
const SHELL = [
  '/',
  '/assets/css/root.css',
  '/assets/css/layout.css',
  '/assets/css/components.css',
  '/assets/css/header.css',
  '/assets/css/footer.css',
  '/assets/images/logo.webp',
  '/assets/images/placeholder.jpg',
  '/assets/images/icon-192.png',
  '/assets/images/icon-512.png'
];

self.addEventListener('install', e => {
  e.waitUntil(caches.open(CACHE).then(c => c.addAll(SHELL)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

// Network first, fall back to cache for navigation; cache first for assets
self.addEventListener('fetch', e => {
  const { request } = e;
  const url = new URL(request.url);

  // Skip non-GET, cross-origin, admin, api requests
  if (request.method !== 'GET') return;
  if (url.origin !== self.location.origin) return;
  if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/api')) return;

  if (request.destination === 'document') {
    // Network first for pages
    e.respondWith(
      fetch(request).catch(() => caches.match('/'))
    );
  } else {
    // Cache first for static assets
    e.respondWith(
      caches.match(request).then(cached => cached || fetch(request).then(res => {
        if (res.ok) {
          const clone = res.clone();
          caches.open(CACHE).then(c => c.put(request, clone));
        }
        return res;
      }))
    );
  }
});
