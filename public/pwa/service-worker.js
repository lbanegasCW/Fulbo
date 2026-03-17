const CACHE_NAME = 'fulbo-cache-v6-20260317-logo-refresh';
const BASE_PATH = new URL(self.registration.scope).pathname.replace(/\/$/, '');
const OFFLINE_URL = `${BASE_PATH}/pwa/offline.html`;

const ASSETS = [
    `${BASE_PATH}/`,
    `${BASE_PATH}/login`,
    `${BASE_PATH}/assets/css/app.css`,
    `${BASE_PATH}/assets/js/main.js`,
    `${BASE_PATH}/assets/js/modules/pwa.js`,
    `${BASE_PATH}/assets/img/fulbo-logo.svg`,
    `${BASE_PATH}/assets/img/logo_fulbo.png`,
    `${BASE_PATH}/assets/img/logo_fulbo-192.png?v=20260317-icon3`,
    `${BASE_PATH}/assets/img/logo_fulbo-512.png?v=20260317-icon3`,
    OFFLINE_URL
];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS)));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))))
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                const copy = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
                return response;
            })
            .catch(async () => {
                const cached = await caches.match(event.request);
                return cached || caches.match(OFFLINE_URL);
            })
    );
});
