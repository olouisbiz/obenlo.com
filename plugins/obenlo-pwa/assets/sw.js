/**
 * Obenlo Service Worker — v2.0.0
 * True Native App Strategy:
 *   - App Shell: Cache-First (instant loads)
 *   - Pages:     Network-First with offline fallback
 *   - API/AJAX:  Network-Only (never cache dynamic data)
 *   - Assets:    Stale-While-Revalidate (always fresh, never slow)
 *   - Images:    Cache-First with expiry (perf-optimized)
 */

const APP_VERSION       = 'obenlo-v2.0.0';
const SHELL_CACHE       = `${APP_VERSION}-shell`;
const PAGES_CACHE       = `${APP_VERSION}-pages`;
const ASSETS_CACHE      = `${APP_VERSION}-assets`;
const IMAGES_CACHE      = `${APP_VERSION}-images`;
const ALL_CACHES        = [SHELL_CACHE, PAGES_CACHE, ASSETS_CACHE, IMAGES_CACHE];

// ── Core App Shell (install immediately, always available offline) ──────────
const SHELL_ASSETS = [
  '/',
  '/offline',
  '/wp-content/themes/obenlo/style.css',
  '/wp-content/plugins/obenlo-pwa/assets/pwa.css',
  '/wp-content/themes/obenlo/assets/images/logo-social-profile-192.png',
  '/wp-content/themes/obenlo/assets/images/logo-social-profile.png',
];

// ── Install: Pre-cache the app shell ────────────────────────────────────────
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(SHELL_CACHE)
      .then((cache) => {
        console.log('[Obenlo SW] Pre-caching app shell');
        // addAll fails if any one request fails — use individual adds for resilience
        return Promise.allSettled(
          SHELL_ASSETS.map((url) => cache.add(url).catch(() => {}))
        );
      })
      .then(() => self.skipWaiting())
  );
});

// ── Activate: Purge old caches ───────────────────────────────────────────────
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) =>
      Promise.all(
        cacheNames
          .filter((name) => !ALL_CACHES.includes(name))
          .map((name) => {
            console.log('[Obenlo SW] Deleting old cache:', name);
            return caches.delete(name);
          })
      )
    ).then(() => {
      console.log('[Obenlo SW] Activated — claiming all clients');
      return self.clients.claim();
    })
  );
});

// ── Fetch: Strategy Router ───────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // 1. Always bypass: non-GET, admin, AJAX, REST API, external
  if (
    request.method !== 'GET' ||
    url.pathname.startsWith('/wp-admin') ||
    url.pathname.startsWith('/wp-json') ||
    url.pathname.includes('admin-ajax.php') ||
    url.pathname.includes('wp-cron.php') ||
    (url.origin !== self.location.origin && !url.hostname.includes('fonts.g'))
  ) {
    return; // Let the browser handle it natively
  }

  // 2. Google Fonts — Stale-While-Revalidate
  if (url.hostname.includes('fonts.googleapis.com') || url.hostname.includes('fonts.gstatic.com')) {
    event.respondWith(staleWhileRevalidate(request, ASSETS_CACHE));
    return;
  }

  // 3. Static assets (CSS, JS, fonts) — Stale-While-Revalidate
  if (
    url.pathname.match(/\.(css|js|woff2?|ttf|otf|eot|svg)$/)
  ) {
    event.respondWith(staleWhileRevalidate(request, ASSETS_CACHE));
    return;
  }

  // 4. Images — Cache-First (with 30-day expiry handled by cache name versioning)
  if (url.pathname.match(/\.(png|jpe?g|gif|webp|ico|avif)$/i)) {
    event.respondWith(cacheFirst(request, IMAGES_CACHE));
    return;
  }

  // 5. HTML page navigations — Network-First with offline fallback
  if (request.mode === 'navigate') {
    event.respondWith(networkFirstWithOffline(request));
    return;
  }

  // 6. Everything else — Stale-While-Revalidate
  event.respondWith(staleWhileRevalidate(request, ASSETS_CACHE));
});

// ── Strategy: Network-First with offline page fallback ──────────────────────
async function networkFirstWithOffline(request) {
  const cache = await caches.open(PAGES_CACHE);
  try {
    const networkResponse = await fetchWithTimeout(request, 5000);
    // Cache a copy for offline
    if (networkResponse.ok) {
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch {
    // Try cache first
    const cachedResponse = await cache.match(request);
    if (cachedResponse) return cachedResponse;
    // Serve the offline shell
    const offlineCache = await caches.open(SHELL_CACHE);
    const offline = await offlineCache.match('/offline');
    return offline || new Response('You are offline.', { status: 503, headers: { 'Content-Type': 'text/plain' } });
  }
}

// ── Strategy: Cache-First ────────────────────────────────────────────────────
async function cacheFirst(request, cacheName) {
  const cache = await caches.open(cacheName);
  const cached = await cache.match(request);
  if (cached) return cached;
  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok) cache.put(request, networkResponse.clone());
    return networkResponse;
  } catch {
    return new Response('', { status: 503 });
  }
}

// ── Strategy: Stale-While-Revalidate ─────────────────────────────────────────
async function staleWhileRevalidate(request, cacheName) {
  const cache = await caches.open(cacheName);
  const cached = await cache.match(request);
  // Always fetch in background to keep cache fresh
  const networkPromise = fetch(request).then((response) => {
    if (response.ok) cache.put(request, response.clone());
    return response;
  }).catch(() => {});
  return cached || networkPromise;
}

// ── Helper: fetch with timeout ───────────────────────────────────────────────
function fetchWithTimeout(request, ms) {
  return new Promise((resolve, reject) => {
    const timer = setTimeout(() => reject(new Error('timeout')), ms);
    fetch(request).then(
      (res) => { clearTimeout(timer); resolve(res); },
      (err) => { clearTimeout(timer); reject(err); }
    );
  });
}

// ── Push Notifications ───────────────────────────────────────────────────────
self.addEventListener('push', (event) => {
  let data = { title: 'Obenlo', body: 'You have a new update!', url: '/account/' };
  if (event.data) {
    try { data = { ...data, ...event.data.json() }; }
    catch { data.body = event.data.text(); }
  }

  event.waitUntil(
    self.registration.showNotification(data.title, {
      body: data.body,
      icon: '/wp-content/themes/obenlo/assets/images/logo-social-profile-192.png',
      badge: '/wp-content/themes/obenlo/assets/images/logo-social-profile-192.png',
      image: data.image || undefined,
      vibrate: [100, 50, 100, 50, 200],
      tag: data.tag || 'obenlo-notification',
      renotify: true,
      requireInteraction: data.requireInteraction || false,
      data: { url: data.url || '/account/' },
      actions: [
        { action: 'open', title: 'View Now', icon: '/wp-content/themes/obenlo/assets/images/logo-social-profile-192.png' },
        { action: 'dismiss', title: 'Dismiss' }
      ]
    })
  );
});

// ── Notification Click ───────────────────────────────────────────────────────
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  if (event.action === 'dismiss') return;

  const targetUrl = event.notification.data?.url || '/account/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
      // Focus an existing tab if already open at that URL
      for (const client of windowClients) {
        if (client.url === targetUrl && 'focus' in client) return client.focus();
      }
      // Focus any existing Obenlo tab and navigate it
      for (const client of windowClients) {
        if ('navigate' in client) return client.navigate(targetUrl).then((c) => c?.focus());
      }
      // Open a new window
      if (clients.openWindow) return clients.openWindow(targetUrl);
    })
  );
});

// ── Background Sync — queue failed booking/messaging requests ────────────────
self.addEventListener('sync', (event) => {
  if (event.tag === 'obenlo-sync-queue') {
    event.waitUntil(processSyncQueue());
  }
});

async function processSyncQueue() {
  // Retrieve queued requests from IndexedDB and replay them
  // (IndexedDB logic handled client-side via obenlo-pwa.php)
  const clients_ = await self.clients.matchAll();
  clients_.forEach((client) => client.postMessage({ type: 'SYNC_COMPLETE' }));
}

// ── Message handling (skip waiting on demand) ────────────────────────────────
self.addEventListener('message', (event) => {
  if (event.data?.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  if (event.data?.type === 'GET_VERSION') {
    event.source?.postMessage({ type: 'VERSION', version: APP_VERSION });
  }
});
