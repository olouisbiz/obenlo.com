const CACHE_NAME = 'obenlo-v1.1.3';
const OFFLINE_URL = '/';

const ASSETS_TO_CACHE = [
  '/',
  '/wp-content/themes/obenlo/style.css',
  '/wp-content/themes/obenlo/assets/images/logo-wordmark.svg',
  '/wp-content/themes/obenlo/assets/images/logo-social-profile.png',
  '/wp-content/themes/obenlo/assets/images/obenlo-logo-social-profile.svg'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Strategy separation: Network First for HTML (dynamic pages with nonces), Stale-While-Revalidate for Assets
self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;
  
  // Do not cache the manifest or the service worker itself
  if (event.request.url.includes('manifest.json') || event.request.url.includes('sw.js')) {
    return;
  }

  // Network-First Strategy for HTML Navigation (Fixes Stale WP Nonces)
  if (event.request.mode === 'navigate' || (event.request.headers.get('accept') && event.request.headers.get('accept').includes('text/html'))) {
    event.respondWith(
      fetch(event.request).then((networkResponse) => {
        return caches.open(CACHE_NAME).then((cache) => {
          if (networkResponse.status === 200) {
            cache.put(event.request, networkResponse.clone());
          }
          return networkResponse;
        });
      }).catch(() => {
        return caches.match(event.request).then((cachedResponse) => {
          return cachedResponse || caches.match(OFFLINE_URL);
        });
      })
    );
    return;
  }

  // Stale-While-Revalidate Strategy for Static Assets (Images, CSS, JS)
  event.respondWith(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.match(event.request).then((cachedResponse) => {
        const fetchedResponse = fetch(event.request).then((networkResponse) => {
          if (networkResponse.status === 200) {
            cache.put(event.request, networkResponse.clone());
          }
          return networkResponse;
        }).catch(() => {
          // Silent catch for offline asset loads
        });

        return cachedResponse || fetchedResponse;
      });
    })
  );
});

self.addEventListener('push', function (event) {
  if (event.data) {
    const data = event.data.json();
    const options = {
      body: data.body,
      icon: data.icon || '/wp-content/themes/obenlo/assets/images/obenlo-logo-social-profile.svg',
      badge: '/wp-content/themes/obenlo/assets/images/obenlo-logo-social-profile.svg',
      vibrate: [100, 50, 100],
      data: {
        url: data.url || '/'
      }
    };
    event.waitUntil(
      self.registration.showNotification(data.title, options)
    );
  }
});

self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
      for (var i = 0; i < clientList.length; i++) {
        var client = clientList[i];
        if (client.url == event.notification.data.url && 'focus' in client)
          return client.focus();
      }
      if (clients.openWindow)
        return clients.openWindow(event.notification.data.url);
    })
  );
});
