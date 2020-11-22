const cacheName = 'music2web';
const staticAssets =                 [
    './',
    './index.php',
    './styles/menu.css',
    './styles/mobile.css',
    './styles/portal.css',
    './styles/style.css',
    './includes/graphics/big_loader.gif',
    './includes/graphics/box.gif',
    './includes/graphics/cancel32.png',
    './includes/graphics/content-two-columns.gif',
    './includes/graphics/content-wrapper.gif',
    './includes/graphics/delete22.png',
    './includes/graphics/favicon.ico',
    './includes/graphics/footer-wrapper.gif',
    './includes/graphics/gradient-light.gif',
    './includes/graphics/header-wrapper-2.gif',
    './includes/graphics/header.gif',
    './includes/graphics/header.png',
    './includes/graphics/help.gif',
    './includes/graphics/icon_512x512.png',
    './includes/graphics/icon_1000x1000.png',
    './includes/graphics/logo.png',
    './includes/graphics/mail22.png',
    './includes/graphics/mobicon.png',
    './includes/graphics/navigation-arrow-2.gif',
    './includes/graphics/navigation-arrow.gif',
    './includes/graphics/navigation-wrapper-2.gif',
    './includes/graphics/navigation.gif',
    './includes/graphics/ok22.png',
    './includes/graphics/ok32.png',
    './includes/graphics/refresh.gif',
    './includes/graphics/rw8ogjem.bmp',
    './includes/graphics/selected-item.gif',
    './includes/graphics/selected-item.png',
    './includes/graphics/separator-vertical.gif',
    './includes/graphics/square.gif',
    './includes/graphics/subnav-wrapper-2.gif',
    './includes/graphics/subnav-wrapper.gif',
    './includes/graphics/transparent-bg.gif',
    './includes/graphics/transparent-bg.png',
    './includes/jscripts/jcrop/jquery.Jcrop.min.js',
    './includes/jscripts/jquery/jquery-ui.js',
    './includes/jscripts/jquery/jquery.js',
    './includes/jscripts/photoswipe/default-skin/default-skin.css',
    './includes/jscripts/photoswipe/default-skin/default-skin.png',
    './includes/jscripts/photoswipe/default-skin/default-skin.svg',
    './includes/jscripts/photoswipe/default-skin/preloader.gif',
    './includes/jscripts/photoswipe/photoswipe-ui-default.js',
    './includes/jscripts/photoswipe/photoswipe-ui-default.min.js',
    './includes/jscripts/photoswipe/photoswipe.css',
    './includes/jscripts/photoswipe/photoswipe.js',
    './includes/jscripts/photoswipe/photoswipe.min.js',
    './includes/socialcounters/facebook.php',
    './includes/socialcounters/twitter.php',
    './includes/socialcounters/assets/css/silicon-counters.css',
    './includes/socialcounters/assets/font-awesome/css/font-awesome.css',
    './includes/socialcounters/assets/font-awesome/css/font-awesome.min.css',
    './includes/socialcounters/assets/font-awesome/fonts/fontawesome-webfont.eot',
    './includes/socialcounters/assets/font-awesome/fonts/fontawesome-webfont.svg',
    './includes/socialcounters/assets/font-awesome/fonts/fontawesome-webfont.ttf',
    './includes/socialcounters/assets/font-awesome/fonts/fontawesome-webfont.woff',
    './includes/socialcounters/assets/font-awesome/fonts/fontawesome-webfont.woff2',
    './includes/socialcounters/assets/font-awesome/fonts/FontAwesome.otf',
    './includes/socialcounters/assets/js/admin.js',
    './includes/socialcounters/assets/js/silicon-counters.js'
];

self.addEventListener('install', async event => {
    const cache = await caches.open(cacheName);
    await cache.addAll (staticAssets);
});

self.addEventListener('fetch', event => {
    const req = event.request;
    event.respondWith(cacheFirst(req));
});

self.addEventListener('push', event => {
    const notification = event.data.json();
    self.registration.showNotification(notification.title, notification);
});

self.addEventListener('notificationClick', event => {
    event.notification.close();
    if (event.action === 'ok') {
        event.waitUntil(
            client.openWindow(event.notification.data.url)
        );
    }
})

async function cacheFirst(req) {
    const cache = await caches.open(cacheName);
    const cachedResponse = await cache.match(req);
    return cachedResponse || fetch(req);
}