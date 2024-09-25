const CACHE_NAME = 'ekilie-cache';
const OFFLINE_URL = '/offline.html'; 

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll([
                '/',
                '/index.html',
                'https://sense.ekilie.com/console/assets/css/style.css',
                '/main.js',
                '/icons/lgo.png',
                OFFLINE_URL // Caching the offline page
            ]);
        })
    );
});

self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request).then((response) => {
            // If the cached response is found, return it. Otherwise, fetch it from the network.
            return response || fetch(event.request).catch(() => {
                // If the fetch fails (e.g., when offline), return the offline page
                return caches.match(OFFLINE_URL);
            });
        })
    );
});
