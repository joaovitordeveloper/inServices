const CACHE_NAME = 'inservices-estatico-v2';
const ARQUIVOS_ESTATICOS = [
    '/offline.html',
    '/manifest.webmanifest',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/maskable-512.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.addAll(ARQUIVOS_ESTATICOS)));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(caches.keys().then((keys) => Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))));
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    if (event.request.method !== 'GET' || url.pathname.startsWith('/admin') || url.pathname.startsWith('/painel') || url.pathname.includes('/horarios')) {
        return;
    }

    event.respondWith(fetch(event.request).catch(() => caches.match('/offline.html')));
});

self.addEventListener('push', (event) => {
    const dados = event.data ? event.data.json() : {};
    event.waitUntil(self.registration.showNotification(dados.titulo || 'Nova notificacao', {
        body: dados.corpo || 'Abra o inServices para ver os detalhes.',
        data: { url: dados.url || '/' }
    }));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(clients.openWindow(event.notification.data.url || '/'));
});
