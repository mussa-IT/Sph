const CACHE_NAME = 'sph-v1';
const STATIC_CACHE_NAME = 'sph-static-v1';
const DYNAMIC_CACHE_NAME = 'sph-dynamic-v1';

const STATIC_ASSETS = [
  '/',
  '/dashboard',
  '/projects',
  '/tasks',
  '/manifest.json',
  '/css/app.css',
  '/js/app.js',
  '/icons/icon-192x192.png',
  '/icons/icon-512x512.png',
  '/offline.html'
];

const API_CACHE_TTL = 5 * 60 * 1000; // 5 minutes
const STATIC_CACHE_TTL = 24 * 60 * 60 * 1000; // 24 hours

// Install event - cache static assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE_NAME)
      .then((cache) => {
        return cache.addAll(STATIC_ASSETS);
      })
      .then(() => {
        return self.skipWaiting();
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            if (cacheName !== STATIC_CACHE_NAME && 
                cacheName !== DYNAMIC_CACHE_NAME && 
                cacheName !== CACHE_NAME) {
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        return self.clients.claim();
      })
  );
});

// Fetch event - serve from cache with network fallback
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }

  // Skip external requests (except for APIs)
  if (!url.origin.startsWith(self.location.origin) && !url.pathname.startsWith('/api')) {
    return;
  }

  // Handle API requests
  if (url.pathname.startsWith('/api')) {
    event.respondWith(handleApiRequest(request));
    return;
  }

  // Handle static assets
  if (isStaticAsset(request.url)) {
    event.respondWith(handleStaticAsset(request));
    return;
  }

  // Handle navigation requests
  if (request.mode === 'navigate') {
    event.respondWith(handleNavigationRequest(request));
    return;
  }

  // Handle other requests
  event.respondWith(handleDynamicRequest(request));
});

// Handle API requests with cache-first strategy
async function handleApiRequest(request) {
  const cache = await caches.open(DYNAMIC_CACHE_NAME);
  const cachedResponse = await cache.match(request);

  if (cachedResponse && !isExpired(cachedResponse, API_CACHE_TTL)) {
    // Return cached response and update in background
    fetchAndCache(request, cache);
    return cachedResponse;
  }

  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const responseClone = networkResponse.clone();
      await cache.put(request, responseClone);
    }
    
    return networkResponse;
  } catch (error) {
    // Return cached response if network fails
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Return offline fallback for API requests
    return new Response(
      JSON.stringify({ 
        error: 'Offline', 
        message: 'No network connection available' 
      }),
      {
        status: 503,
        headers: { 'Content-Type': 'application/json' }
      }
    );
  }
}

// Handle static assets with cache-first strategy
async function handleStaticAsset(request) {
  const cache = await caches.open(STATIC_CACHE_NAME);
  const cachedResponse = await cache.match(request);

  if (cachedResponse && !isExpired(cachedResponse, STATIC_CACHE_TTL)) {
    return cachedResponse;
  }

  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const responseClone = networkResponse.clone();
      await cache.put(request, responseClone);
    }
    
    return networkResponse;
  } catch (error) {
    // Return cached response if network fails
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Return offline fallback for static assets
    return new Response('Offline', { status: 503 });
  }
}

// Handle navigation requests
async function handleNavigationRequest(request) {
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(DYNAMIC_CACHE_NAME);
      const responseClone = networkResponse.clone();
      await cache.put(request, responseClone);
      return networkResponse;
    }
  } catch (error) {
    // Try to serve from cache
    const cache = await caches.open(DYNAMIC_CACHE_NAME);
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Return offline page
    return caches.match('/offline.html');
  }
}

// Handle dynamic requests
async function handleDynamicRequest(request) {
  const cache = await caches.open(DYNAMIC_CACHE_NAME);
  const cachedResponse = await cache.match(request);

  if (cachedResponse) {
    return cachedResponse;
  }

  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const responseClone = networkResponse.clone();
      await cache.put(request, responseClone);
    }
    
    return networkResponse;
  } catch (error) {
    // Return cached response if available
    if (cachedResponse) {
      return cachedResponse;
    }
    
    return new Response('Offline', { status: 503 });
  }
}

// Helper functions
function isStaticAsset(url) {
  return url.includes('/css/') || 
         url.includes('/js/') || 
         url.includes('/icons/') || 
         url.includes('/images/') ||
         url.endsWith('.css') ||
         url.endsWith('.js') ||
         url.endsWith('.png') ||
         url.endsWith('.jpg') ||
         url.endsWith('.jpeg') ||
         url.endsWith('.svg') ||
         url.endsWith('.ico');
}

function isExpired(response, ttl) {
  const dateHeader = response.headers.get('date');
  if (!dateHeader) return true;
  
  const responseDate = new Date(dateHeader);
  const now = new Date();
  return (now - responseDate) > ttl;
}

async function fetchAndCache(request, cache) {
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      await cache.put(request, networkResponse);
    }
  } catch (error) {
    // Silently fail background fetch
    console.log('Background fetch failed:', error);
  }
}

// Background sync for offline actions
self.addEventListener('sync', (event) => {
  if (event.tag === 'background-sync') {
    event.waitUntil(syncOfflineData());
  }
});

async function syncOfflineData() {
  try {
    const offlineActions = await getOfflineActions();
    
    for (const action of offlineActions) {
      try {
        await fetch(action.url, {
          method: action.method,
          headers: action.headers,
          body: action.body
        });
        
        // Remove successful action from offline storage
        await removeOfflineAction(action.id);
      } catch (error) {
        console.log('Failed to sync action:', action, error);
      }
    }
  } catch (error) {
    console.log('Background sync failed:', error);
  }
}

// Push notifications
self.addEventListener('push', (event) => {
  const options = {
    body: event.data.text(),
    icon: '/icons/icon-192x192.png',
    badge: '/icons/badge-72x72.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'Explore',
        icon: '/icons/checkmark.png'
      },
      {
        action: 'close',
        title: 'Close',
        icon: '/icons/xmark.png'
      }
    ]
  };

  event.waitUntil(
    self.registration.showNotification('SmartProjectHub', options)
  );
});

// Notification click handling
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  if (event.action === 'explore') {
    event.waitUntil(
      clients.openWindow('/dashboard')
    );
  } else if (event.action === 'close') {
    // Just close the notification
  } else {
    // Default action - open the app
    event.waitUntil(
      clients.openWindow('/')
    );
  }
});

// Message handling from main thread
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

// Periodic background sync (if supported)
self.addEventListener('periodicsync', (event) => {
  if (event.tag === 'sync-data') {
    event.waitUntil(syncOfflineData());
  }
});

// Helper functions for offline storage
async function getOfflineActions() {
  // This would integrate with IndexedDB or localStorage
  // For now, return empty array
  return [];
}

async function removeOfflineAction(id) {
  // This would integrate with IndexedDB or localStorage
  // For now, do nothing
  return Promise.resolve();
}
