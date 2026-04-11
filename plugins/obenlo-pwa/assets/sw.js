const CACHE_NAME = 'obenlo-cache-v1.6.8';
const ASSETS_TO_CACHE = [
  '/',
  '/?utm_source=pwa',
  '/wp-content/themes/obenlo/style.css',
  '/wp-content/themes/obenlo/assets/images/logo-social-profile-192.png',
  'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap'
];

// Install Event
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('Obenlo PWA: Caching core assets');
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
  self.skipWaiting(); // Force new service worker to become active
});

// Activate Event
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cache) => {
          if (cache !== CACHE_NAME) {
            console.log('Obenlo PWA: Clearing old cache');
            return caches.delete(cache);
          }
        })
      );
    })
  );
  return self.clients.claim(); // Take control of all clients immediately
});

// Fetch Event - Network First for HTML, Cache First for Assets
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);

  // For navigation requests (pages), try network first
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).catch(() => {
        return caches.match('/');
      })
    );
    return;
  }

  // For static assets, try cache first
  event.respondWith(
    caches.match(event.request).then((response) => {
      return response || fetch(event.request);
    })
  );
});

// Push Notification Support
self.addEventListener('push', (event) => {
  let data = { title: 'Obenlo', body: 'You have a new update!', url: '/messages' };
  if (event.data) {
    try {
      data = event.data.json();
    } catch (e) {
      data.body = event.data.text();
    }
  }

  const options = {
    body: data.body,
    icon: '/wp-content/themes/obenlo/assets/images/logo-social-profile-192.png',
    badge: '/wp-content/themes/obenlo/assets/images/logo-social-profile-192.png',
    vibrate: [100, 50, 100],
    data: { url: data.url || '/messages' },
    actions: [
      { action: 'open', title: 'View Now' }
    ]
  };

  event.waitUntil(
    self.registration.showNotification(data.title || 'Obenlo', options)
  );
});

// Notification Click
self.addEventListener('notificationclick', (event) => {
  const notification = event.notification;
  const url = notification.data ? notification.data.url : '/messages';

  notification.close();

  if (event.action === 'close') return;

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
      // Check if there is already a window/tab open with the target URL
      for (var i = 0; i < windowClients.length; i++) {
        var client = windowClients[i];
        if (client.url === url && 'focus' in client) {
          return client.focus();
        }
      }
      // If no window/tab is open, open a new one
      if (clients.openWindow) {
        return clients.openWindow(url);
      }
    })
  );
});
