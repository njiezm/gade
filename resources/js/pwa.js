// GADÉ — PWA : service worker, bandeau de mise à jour, bouton « Installer GADÉ », aide iOS.

const estStandalone = () => window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
const estIos = () => /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

function bandeau(texte, action, libelle) {
    if (document.querySelector('[data-pwa-bandeau]')) return;
    const div = document.createElement('div');
    div.className = 'pwa-bandeau';
    div.setAttribute('role', 'status');
    div.dataset.pwaBandeau = '';
    div.innerHTML = '<span></span><button type="button" class="btn btn-gade btn-sm"></button><button type="button" class="pwa-fermer" aria-label="Fermer">×</button>';
    div.querySelector('span').textContent = texte;
    const bouton = div.querySelector('.btn');
    bouton.textContent = libelle;
    bouton.addEventListener('click', action);
    div.querySelector('.pwa-fermer').addEventListener('click', () => div.remove());
    document.body.appendChild(div);
}

function surveillerMiseAJour(reg) {
    let rechargement = false;
    let demande = false; // ne recharger que si l'utilisateur a cliqué « Recharger » (pas à la 1re installation)
    const proposer = (worker) => bandeau('Nouvelle version disponible', () => { demande = true; worker.postMessage({ type: 'SKIP_WAITING' }); }, 'Recharger');
    if (reg.waiting && navigator.serviceWorker.controller) proposer(reg.waiting);
    reg.addEventListener('updatefound', () => {
        const nouveau = reg.installing;
        nouveau?.addEventListener('statechange', () => {
            if (nouveau.state === 'installed' && navigator.serviceWorker.controller) proposer(nouveau);
        });
    });
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (rechargement || !demande) return;
        rechargement = true;
        location.reload();
    });
}

function initInstallation() {
    const boutons = [...document.querySelectorAll('[data-pwa-installer]')];
    if (!boutons.length || estStandalone()) return;
    let invite = null;

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        invite = e;
        boutons.forEach((b) => { b.hidden = false; });
    });
    window.addEventListener('appinstalled', () => {
        invite = null;
        boutons.forEach((b) => { b.hidden = true; });
    });

    // iOS (Safari) : pas d'invite automatique, on explique le geste
    if (estIos()) boutons.forEach((b) => { b.hidden = false; });

    boutons.forEach((b) => b.addEventListener('click', async () => {
        if (invite) {
            invite.prompt();
            await invite.userChoice.catch(() => null);
            invite = null;
            boutons.forEach((x) => { x.hidden = true; });
        } else if (estIos()) {
            const aide = document.querySelector('[data-pwa-aide-ios]');
            if (aide) aide.hidden = !aide.hidden;
        }
    }));
}

export function initPwa() {
    if (estStandalone()) document.documentElement.classList.add('pwa-standalone');
    initInstallation();
    if (!('serviceWorker' in navigator) || !window.isSecureContext) return;
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then((reg) => {
                surveillerMiseAJour(reg);
                // Vérifie une nouvelle version quand on revient sur l'app
                document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') reg.update().catch(() => {}); });
            })
            .catch(() => { /* SW indisponible : le site fonctionne normalement */ });
    });
}
