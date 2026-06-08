# PWA Install Popup Design Specification

## Overview
This document describes the implementation of a PWA (Progressive Web App) install popup that appears after 10 mobile visits to the website. The popup will use the browser's default PWA install prompt to avoid interrupting app functionality while encouraging users to install the PWA for a native app-like experience.

## Requirements
- Show PWA install popup on mobile devices only
- Appear after exactly 10 visits (not on first visit)
- Use browser's default install prompt (not custom UI)
- Do not interrupt existing app functionality
- Work with existing website structure
- Track visits persistently across sessions

## Architecture
### Components
1. **Visit Tracking Module** - Handles counting and storing visit counts using localStorage
2. **PWA Detection Module** - Detects if device is mobile and if PWA install is available
3. **Install Trigger Module** - Shows browser's default PWA install prompt when conditions are met
4. **Manifest and Service Worker** - Standard PWA files for install capability

### Data Flow
1. On each page load, check if we're on a mobile device
2. If mobile, retrieve visit count from localStorage (default 0)
3. Increment visit count and store back to localStorage
4. If visit count equals 10 and PWA install is available, show browser's install prompt
5. After showing prompt, prevent further prompts by keeping count at 10+

### Files to Create/Modify
- `public_html/manifest.json` - PWA manifest file
- `public_html/sw.js` - Service worker file
- `public_html/pwa-install.js` - Visit tracking and install trigger logic
- Update `public_html/index.php` to include the PWA install script

## Implementation Details

### Visit Tracking Logic
```javascript
// pwa-install.js
(function() {
  // Check if running on mobile device
  function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
  }

  // Get visit count from localStorage
  function getVisitCount() {
    return parseInt(localStorage.getItem('pwaVisitCount')) || 0;
  }

  // Set visit count in localStorage
  function setVisitCount(count) {
    localStorage.setItem('pwaVisitCount', count);
  }

  // Show PWA install prompt
  function showInstallPrompt() {
    // The beforeinstallprompt event is typically captured elsewhere
    // This function would be called when we have a deferred prompt
    if (window.deferredPrompt) {
      window.deferredPrompt.prompt();
      window.deferredPrompt.userChoice.then(function(choiceResult) {
        // Optionally log the outcome
        console.log('User choice:', choiceResult.outcome);
        // Prevent future prompts by setting count high
        setVisitCount(999);
      });
      window.deferredPrompt = null;
    }
  }

  // Main initialization
  function initPWAInstall() {
    if (!isMobileDevice()) return;

    // Listen for beforeinstallprompt to capture the deferred prompt
    window.addEventListener('beforeinstallprompt', function(e) {
      // Prevent Chrome 67 and earlier from automatically showing the prompt
      e.preventDefault();
      // Stash the event so it can be triggered later
      window.deferredPrompt = e;
    });

    // Check and increment visit count
    var visitCount = getVisitCount();
    visitCount++;
    setVisitCount(visitCount);

    // Show install prompt on 10th visit
    if (visitCount === 10 && window.deferredPrompt) {
      showInstallPrompt();
    }
  }

  // Initialize on page load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPWAInstall);
  } else {
    initPWAInstall();
  }
})();
```

### Manifest File (public_html/manifest.json)
```json
{
  "name": "Phantom Smoking",
  "short_name": "PhantomSmoke",
  "description": "Premium tobacco products and accessories",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#8B0000",
  "icons": [
    {
      "src": "/assets/icons/icon-72x72.png",
      "sizes": "72x72",
      "type": "image/png"
    },
    {
      "src": "/assets/icons/icon-96x96.png",
      "sizes": "96x96",
      "type": "image/png"
    },
    {
      "src": "/assets/icons/icon-128x128.png",
      "sizes": "128x128",
      "type": "image/png"
    },
    {
      "src": "/assets/icons/icon-144x144.png",
      "sizes": "144x144",
      "type": "image/png"
    },
    {
      "src": "/assets/icons/icon-152x152.png",
      "sizes": "152x152",
      "type": "image/png"
    },
    {
      "src": "/assets/icons/icon-192x192.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "/assets/icons/icon-384x384.png",
      "sizes": "384x384",
      "type": "image/png"
    },
    {
      "src": "/assets/icons/icon-512x512.png",
      "sizes": "512x512",
      "type": "image/png"
    }
  ]
}
```

### Service Worker (public_html/sw.js)
```javascript
const CACHE_NAME = 'phantom-smoking-cache-v1';
const urlsToCache = [
  '/',
  '/index.php',
  '/assets/css/style.css',
  '/assets/js/app.js',
  // Add other essential assets here
];

// Install service worker
self.addEventListener('install', function(event) {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function(cache) {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
  );
});

// Cache and return requests
self.addEventListener('fetch', function(event) {
  event.respondWith(
    caches.match(event.request)
      .then(function(response) {
        // Cache hit - return response
        if (response) {
          return response;
        }
        return fetch(event.request);
      }
    )
  );
});

// Update service worker
self.addEventListener('activate', function(event) {
  var cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(function(cacheNames) {
      return Promise.all(
        cacheNames.map(function(cacheName) {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});
```

### Integration with index.php
Add the following line before the closing `</body>` tag in `public_html/index.php`:
```html
<script src="/pwa-install.js"></script>
<link rel="manifest" href="/manifest.json">
```

## Error Handling
- If localStorage is unavailable, visit tracking will not work (graceful degradation)
- If service worker registration fails, PWA install won't be offered (but site still works)
- All scripts wrapped in IIFE to avoid polluting global namespace
- Feature detection used before accessing browser APIs

## Testing Considerations
1. Test on actual mobile devices or device emulators
2. Verify visit count persists across browser sessions
3. Confirm install prompt appears exactly on 10th visit
4. Ensure no prompt appears after installation (or after count is set high)
5. Verify existing website functionality remains unaffected
6. Test clearing localStorage resets the counter

## Non-Goals
- Custom UI for install prompt (using browser's default)
- Forcing installation or blocking content until installed
- Cross-device visit tracking (this is device/browser specific)
- Background sync or push notifications (basic PWA only)

## Success Criteria
- [ ] Visit count accurately tracks and persists in localStorage
- [ ] PWA install prompt appears on mobile devices after exactly 10 visits
- [ ] Prompt does not appear on non-mobile devices
- [ ] Existing website functionality remains completely unaffected
- [ ] Users can successfully install and launch the PWA
- [ ] After installation, no further prompts appear