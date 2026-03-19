const CACHE_NAME = 'stockidea-crm-v2';
const OFFLINE_URL = '/offline';

const CACHE_ASSETS = [
    '/',
    '/dashboard',
    '/offline',
    '/favicon.ico',
    // We would list main CSS/JS bundles here, but Vite hashes them.
    // The service worker will dynamically cache CSS/JS as they are fetched.
];

// Install Event - cache the app shell
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Opened cache');
                return cache.addAll(CACHE_ASSETS);
            })
    );
    self.skipWaiting();
});

// Activate Event - clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cache => {
                    if (cache !== CACHE_NAME) {
                        console.log('Clearing Old Cache', cache);
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
    return self.clients.claim();
});

// Fetch Event - Stale While Revalidate for API/HTML, Network First for others
self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    const isHtml = request.mode === 'navigate' || (request.headers.get('accept') || '').includes('text/html');
    const isStatic =
        request.destination === 'style' ||
        request.destination === 'script' ||
        request.destination === 'image' ||
        request.destination === 'font' ||
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/assets/') ||
        url.pathname.startsWith('/js/') ||
        url.pathname === '/favicon.ico';

    // Never cache dynamic HTML to avoid storing sensitive CRM data offline.
    if (isHtml) {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Cache only static assets
    if (!isStatic) {
        event.respondWith(fetch(request));
        return;
    }

    event.respondWith(
        caches.match(request).then(cached => {
            if (cached) return cached;
            return fetch(request).then(response => {
                if (response && response.status === 200 && response.type === 'basic') {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(request, responseClone));
                }
                return response;
            });
        })
    );
});
