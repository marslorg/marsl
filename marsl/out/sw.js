const cacheName = 'music2web';

self.addEventListener('install', event => {
    event.waitUntil(
        caches.delete(cacheName)
    );
});

self.addEventListener('fetch', event => {
    const req = event.request;
    event.respondWith(networkFirst(req));
});

self.addEventListener('push', event => {
    const notification = event.data.json();
    self.registration.showNotification(notification.title, notification);
});

self.addEventListener('notificationclick', event => {
    let url = event.notification.data;
    event.notification.close();
    event.waitUntil(
        clients.matchAll({type: 'window'}).then(windowClients => {
            for (var i = 0; i < windowClients.length; i++) {
                var client = windowClients[i];
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
})

async function networkFirst(req) {
    const cache = await caches.open(cacheName);
    try {
        const fresh = await fetch(req);
        if (fresh.ok) {
            cache.put(req, fresh.clone());
        }
        return fresh;
    }
    catch (e) {
        const cachedResponse = await cache.match(req);
        return cachedResponse;
    }
}