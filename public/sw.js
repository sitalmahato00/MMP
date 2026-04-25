const CACHE_NAME = 'mmp-pwa-v5';
const OFFLINE_URL = '/offline.html';
const PRECACHE_URLS = [
    '/',
    '/notices',
    '/login',
    '/manifest.json?v=4',
    '/brand-logo',
    '/favicon.ico',
    OFFLINE_URL,
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => Promise.all(
            keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
        )).then(() => self.clients.claim())
    );
});

self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

self.addEventListener('fetch', event => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith((async () => {
            try {
                const response = await fetch(request);

                if (response && response.ok) {
                    const cache = await caches.open(CACHE_NAME);
                    cache.put(request, response.clone());
                }

                return response;
            } catch (error) {
                const cachedPage = await caches.match(request);
                return cachedPage || caches.match(OFFLINE_URL);
            }
        })());

        return;
    }

    const isStaticAsset = ['style', 'script', 'image', 'font'].includes(request.destination)
        || url.pathname.endsWith('.json')
        || url.pathname.endsWith('.webmanifest');

    if (!isStaticAsset) {
        return;
    }

    event.respondWith(
        caches.match(request).then(cachedResponse => {
            const networkResponse = fetch(request).then(response => {
                if (response && response.ok) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(request, responseClone));
                }

                return response;
            }).catch(() => cachedResponse);

            return cachedResponse || networkResponse;
        })
    );
});

self.addEventListener('push', event => {
    let payload = {};

    if (event.data) {
        try {
            payload = event.data.json();
        } catch (error) {
            payload = {
                title: 'MMP Academic App',
                body: event.data.text(),
            };
        }
    }

    const title = payload.title || 'MMP Academic App';
    const body = payload.body || 'You have a new update.';
    const clickAction = payload.click_action || payload.target_url || '/';
    const priority = payload.priority || 'medium';

    event.waitUntil(
        self.registration.showNotification(title, {
            body,
            icon: payload.icon || '/brand-logo',
            badge: payload.badge || '/brand-logo',
            tag: payload.tag || `mmp-${payload.type || 'general'}`,
            silent: priority === 'low',
            requireInteraction: priority === 'high',
            renotify: priority === 'high',
            data: {
                click_action: clickAction,
            },
        })
    );
});

self.addEventListener('notificationclick', event => {
    event.notification.close();

    const clickAction = event.notification.data?.click_action || '/';
    const targetUrl = new URL(clickAction, self.location.origin).toString();

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
            for (const client of windowClients) {
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }

            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }

            return undefined;
        })
    );
});
