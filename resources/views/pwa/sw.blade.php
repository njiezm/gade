/* GADÉ — Sinéma lakay : service worker (généré par PwaController, ne pas mettre en cache HTTP). */
const VERSION = @json($version);
const SHELL = @json($shell);
const CACHE_SHELL = `gade-shell-${VERSION}`;
const CACHE_IMAGES = 'gade-images-v1';
const MAX_IMAGES = 300;
const HORS_LIGNE = '/hors-ligne';

// Jamais de cache : coulisses, lecture, positions, profils, connexion, données JSON, le SW lui-même.
const JAMAIS = [/^\/coulisses/, /^\/regarder/, /^\/position/, /^\/positions/, /^\/profil/, /^\/connexion/, /^\/deconnexion/, /^\/ma-liste/, /^\/moi\//, /^\/compte/, /^\/gestion/, /^\/catalogue\.json/, /^\/sw\.js/, /^\/api\//];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_SHELL).then((cache) =>
            // Les fichiers sont chargés un par un : un fichier manquant ne bloque pas l'installation.
            Promise.all(SHELL.map((url) => fetch(url, { credentials: 'omit', cache: 'reload' })
                .then((r) => (r.ok ? cache.put(url, r) : null))
                .catch(() => null)))
        )
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((cles) => Promise.all(cles
                .filter((c) => c.startsWith('gade-') && c !== CACHE_SHELL && c !== CACHE_IMAGES)
                .map((c) => caches.delete(c))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') self.skipWaiting();
});

async function limiter(nomCache, max) {
    const cache = await caches.open(nomCache);
    const cles = await cache.keys();
    if (cles.length > max) await Promise.all(cles.slice(0, cles.length - max).map((k) => cache.delete(k)));
}

async function cacheDabord(request, nomCache, max = 0) {
    const trouve = await caches.match(request);
    if (trouve) return trouve;
    const reponse = await fetch(request);
    if (reponse.ok && reponse.type === 'basic') {
        const cache = await caches.open(nomCache);
        await cache.put(request, reponse.clone());
        if (max) limiter(nomCache, max);
    }
    return reponse;
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;                       // POST, PUT… : jamais interceptés
    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;            // YouTube, ytimg… : réseau direct
    if (JAMAIS.some((re) => re.test(url.pathname)) && request.mode !== 'navigate') return;

    // Pages HTML : réseau d'abord, jamais mises en cache (pages propres à chaque profil),
    // repli sur la page « hors ligne » pré-cachée.
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(async () => (await caches.match(HORS_LIGNE)) || new Response(
            '<!doctype html><meta charset="utf-8"><title>Hors ligne</title><p>Pa ni rézo… Il faut Internet pour regarder.</p>',
            { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } })));
        return;
    }

    // Vignettes et affiches : cache d'abord, ~300 entrées max.
    if (url.pathname.startsWith('/vignettes/') || url.pathname.startsWith('/affiches/')) {
        event.respondWith(cacheDabord(request, CACHE_IMAGES, MAX_IMAGES));
        return;
    }

    // Build Vite (noms hachés), polices, logo, icônes : cache d'abord.
    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/images/brand/') || url.pathname.startsWith('/images/pwa/')) {
        event.respondWith(cacheDabord(request, CACHE_SHELL));
    }
});
