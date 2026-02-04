const CACHE_NAME = 'tcc-breeze-v1';
const OFFLINE_URL = '/offline';

const ASSETS_TO_CACHE = [
    OFFLINE_URL,
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/icons/maskable-icon-512x512.png',
    '/favicon.ico'
];

// Install event: cache static assets and offline page
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
    self.skipWaiting();
});

// Activate event: cleanup old caches
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

// Fetch event: handle cache strategies
self.addEventListener('fetch', (event) => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // Strategy: Cache FIRST for static assets (CSS, JS, Fonts, Images)
    const isStaticAsset = 
        url.pathname.match(/\.(js|css|png|jpg|jpeg|gif|svg|woff|woff2|ttf|eot|ico)$/) ||
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/icons/');

    if (isStaticAsset) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                if (cachedResponse) return cachedResponse;
                
                return fetch(event.request).then((networkResponse) => {
                    return caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, networkResponse.clone());
                        return networkResponse;
                    });
                }).catch(() => caches.match('/icons/icon-192x192.png')); // Fallback for images
            })
        );
        return;
    }

    // Strategy: Network FIRST for pages and dynamic content
    event.respondWith(
        fetch(event.request)
            .then((networkResponse) => {
                // Don't cache if not a successful response or sensitive pages
                if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
                    return networkResponse;
                }

                // Avoid caching sensitive or authenticated-only routes if possible
                // However, for basic offline support of the shell, we can cache
                const isSensitive = url.pathname.includes('/login') || 
                                   url.pathname.includes('/register') || 
                                   url.pathname.includes('/dashboard') ||
                                   url.pathname.includes('/profile');

                if (!isSensitive) {
                    const responseToCache = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }

                return networkResponse;
            })
            .catch(() => {
                // Fallback to cache or offline page
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) return cachedResponse;
                    
                    if (event.request.mode === 'navigate') {
                        return caches.match(OFFLINE_URL);
                    }
                    return null;
                });
            })
    );
});
