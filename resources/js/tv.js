// GADÉ — « Sur la télé » (panneau selon la plateforme) et Mode TV (navigation à la télécommande).
import { lireProgression, sauverProgression } from './memoire';

const CLE_TV = 'gade:tv';
const reduit = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// ---------------------------------------------------------------- Plateforme
export function plateforme() {
    const ua = navigator.userAgent || '';
    const ios = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    const android = /Android/i.test(ua);
    const tv = /SMART-TV|SmartTV|Tizen|Web0S|webOS|NetCast|BRAVIA|AFT\w|CrKey|GoogleTV|Android TV|HbbTV/i.test(ua);
    return { ios, android, tv, mobile: (ios || android) && !tv };
}

// ---------------------------------------------------------------- Lien YouTube
export function lienYoutube(id, t = 0) {
    const s = Math.max(0, Math.floor(t || 0));
    return `https://www.youtube.com/watch?v=${encodeURIComponent(id)}${s ? `&t=${s}s` : '&t=0s'}`;
}
export function intentAndroid(id, t = 0) {
    const web = lienYoutube(id, t);
    const chemin = web.replace(/^https:\/\//, '');
    return `intent://${chemin}#Intent;scheme=https;package=com.google.android.youtube;S.browser_fallback_url=${encodeURIComponent(web)};end`;
}
const fmt = (s) => {
    s = Math.floor(s);
    const h = Math.floor(s / 3600); const m = Math.floor((s % 3600) / 60); const sec = String(s % 60).padStart(2, '0');
    return h ? `${h}:${String(m).padStart(2, '0')}:${sec}` : `${m}:${sec}`;
};

// Vidéo + timecode à ouvrir : position du lecteur en cours, sinon reprise de la fiche (lien « Kontinyé » ?t=)
function cibleCourante(panneau) {
    let id = panneau.dataset.video;
    let t = 0;
    if (panneau.dataset.contexte === 'lecteur' && window.gadeLecteur) {
        t = window.gadeLecteur.temps() || lireProgression()?.t || 0;
    } else {
        const href = document.querySelector('[data-reprendre]')?.getAttribute('href') || '';
        const ep = href.match(/\/regarder\/[^/?#]+\/([A-Za-z0-9_-]{11})/);
        const tm = href.match(/[?&]t=(\d+)/);
        if (ep) id = ep[1];
        if (tm) t = parseInt(tm[1], 10);
    }
    return { id, t };
}

// Avant de quitter GADÉ pour l'app YouTube : pause + position envoyée au profil (sendBeacon, fiable)
function sauverAvantDepart() {
    try {
        const p = window.gadeLecteur?.player?.();
        const salle = document.querySelector('[data-lecteur]');
        if (p?.getCurrentTime && salle) {
            sauverProgression(salle.dataset.slug, salle.dataset.episode || null, p.getCurrentTime(), p.getDuration?.() || 0, true);
            p.pauseVideo?.();
        }
    } catch { /* rien */ }
    return Promise.resolve();
}

export function initSurLaTele() {
    const pf = plateforme();
    document.querySelectorAll('[data-tele]').forEach((panneau) => {
        const ouvrir = document.querySelector(`[data-tele-ouvrir="${panneau.id}"]`);
        const mobile = panneau.querySelector('[data-tele-plateforme="mobile"]');
        const ordi = panneau.querySelector('[data-tele-plateforme="ordi"]');
        if (mobile) mobile.hidden = !pf.mobile;
        if (ordi) ordi.hidden = pf.mobile;

        const majLiens = () => {
            const { id, t } = cibleCourante(panneau);
            panneau.querySelectorAll('[data-tele-youtube]').forEach((a) => {
                a.href = pf.android ? intentAndroid(id, t) : lienYoutube(id, t);
                a.dataset.web = lienYoutube(id, t);
            });
            const temps = panneau.querySelector('[data-tele-temps]');
            if (temps) temps.textContent = t > 5 ? `à ${fmt(t)}` : 'au début';
        };

        ouvrir?.addEventListener('click', () => {
            majLiens();
            if (typeof panneau.showModal === 'function') panneau.showModal(); else panneau.setAttribute('open', '');
            (panneau.querySelector('.tele-bloc:not([hidden]) .btn') || panneau.querySelector('[data-tele-fermer]'))?.focus();
        });
        panneau.querySelector('[data-tele-fermer]')?.addEventListener('click', () => panneau.close?.() ?? panneau.removeAttribute('open'));
        panneau.addEventListener('click', (e) => { if (e.target === panneau) panneau.close(); });

        panneau.querySelectorAll('[data-tele-youtube]').forEach((a) => {
            a.addEventListener('click', (e) => {
                majLiens();
                // On enregistre la position avant de quitter GADÉ pour l'app YouTube
                e.preventDefault();
                const href = a.href;
                sauverAvantDepart().finally(() => {
                    if (href.startsWith('intent:')) location.href = href;
                    else window.open(href, '_blank', 'noopener') || (location.href = href);
                });
            });
        });

        panneau.querySelector('[data-tele-mode-tv]')?.addEventListener('click', () => {
            activerTv(true);
            panneau.close?.();
            const ecran = window.gadeLecteur?.ecran?.();
            if (ecran && panneau.dataset.contexte === 'lecteur') {
                ecran.classList.add('is-tv-plein');
                ecran.requestFullscreen?.({ navigationUI: 'hide' }).catch(() => {});
                const lancer = document.querySelector('[data-attente] [data-lancer]');
                if (lancer) lancer.click(); else window.gadeLecteur?.player?.()?.playVideo?.();
            } else if (panneau.dataset.lecture) {
                location.href = `${panneau.dataset.lecture}${panneau.dataset.lecture.includes('?') ? '&' : '?'}tv=1`;
            }
        });
    });
    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement) document.querySelectorAll('.is-tv-plein').forEach((el) => el.classList.remove('is-tv-plein'));
    });
}

// ---------------------------------------------------------------- Mode TV
const estActif = () => document.body.classList.contains('mode-tv');

export function activerTv(actif) {
    document.body.classList.toggle('mode-tv', actif);
    try { if (actif) localStorage.setItem(CLE_TV, '1'); else localStorage.removeItem(CLE_TV); } catch { /* rien */ }
    document.querySelectorAll('[data-tv-interrupteur]').forEach((b) => b.setAttribute('aria-pressed', actif ? 'true' : 'false'));
    window.dispatchEvent(new Event('resize')); // hauteur d'en-tête recalculée
}

const SELECTEUR = '[data-tv-focus], a[href], button:not([disabled]), input:not([type="hidden"]), select, [tabindex]:not([tabindex="-1"])';

function visible(el) {
    if (el.closest('[hidden], [inert], .collapse:not(.show)')) return false;
    if (el.matches('[data-piste], iframe')) return false;
    if (el.closest('dialog:not([open])')) return false;
    if (typeof el.checkVisibility === 'function' && !el.checkVisibility({ opacityProperty: true, visibilityProperty: true })) return false;
    const r = el.getBoundingClientRect();
    return r.width > 2 && r.height > 2;
}

function candidats() {
    const dialogue = document.querySelector('dialog[open]');
    const racine = dialogue || document;
    return [...racine.querySelectorAll(SELECTEUR)].filter(visible);
}

// Les cartes d'une rangée débordent horizontalement : leur position « logique » est celle du rectangle réel
function voisin(depuis, dir) {
    const a = depuis.getBoundingClientRect();
    const ax = a.left + a.width / 2; const ay = a.top + a.height / 2;
    const rangeeA = depuis.closest('[data-piste]');
    let meilleur = null; let score = Infinity;
    for (const el of candidats()) {
        if (el === depuis || el.contains(depuis) || depuis.contains(el)) continue;
        const b = el.getBoundingClientRect();
        const bx = b.left + b.width / 2; const by = b.top + b.height / 2;
        const dx = bx - ax; const dy = by - ay;
        let principal; let lateral;
        if (dir === 'droite') { if (b.left < a.right - a.width * 0.5 || dx <= 0) continue; principal = dx; lateral = Math.abs(dy); }
        else if (dir === 'gauche') { if (b.right > a.left + a.width * 0.5 || dx >= 0) continue; principal = -dx; lateral = Math.abs(dy); }
        else if (dir === 'bas') { if (b.top < a.bottom - a.height * 0.5 || dy <= 0) continue; principal = dy; lateral = Math.abs(dx); }
        else { if (b.bottom > a.top + a.height * 0.5 || dy >= 0) continue; principal = -dy; lateral = Math.abs(dx); }
        // Gauche/droite : rester dans la même rangée si possible
        const memeRangee = rangeeA && el.closest('[data-piste]') === rangeeA;
        const s = principal + lateral * (dir === 'droite' || dir === 'gauche' ? 4 : 1.5) - (memeRangee ? 400 : 0);
        if (s < score) { score = s; meilleur = el; }
    }
    return meilleur;
}

function focaliser(el) {
    if (!el) return;
    el.focus({ preventScroll: true });
    const comportement = reduit() ? 'auto' : 'smooth';
    const li = el.closest('[data-piste] > li');
    if (li) li.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: comportement });
    el.scrollIntoView({ block: 'center', inline: 'nearest', behavior: comportement });
}

function premierElement() {
    const prefere = ['[data-tv-start]', '.ecran-voile:not([hidden]) .btn', '[data-attente] [data-lancer]', '[data-reprendre]', '[data-profil]', '.une-slide.is-active .btn-gade', 'main .carte', 'main a[href], main button'];
    for (const s of prefere) {
        const el = [...document.querySelectorAll(s)].find(visible);
        if (el) return el;
    }
    return candidats()[0];
}

function lecturePause() {
    const p = window.gadeLecteur?.player?.();
    if (p?.getPlayerState) {
        if (p.getPlayerState() === 1) p.pauseVideo(); else p.playVideo();
        return true;
    }
    const lancer = document.querySelector('[data-attente] [data-lancer]');
    if (lancer) { lancer.click(); return true; }
    return false;
}

function retour() {
    const dialogue = document.querySelector('dialog[open]');
    if (dialogue) { dialogue.close(); return; }
    if (document.fullscreenElement) { document.exitFullscreen().catch(() => {}); return; }
    const menu = document.getElementById('menu-principal');
    if (menu?.classList.contains('show')) { document.querySelector('[data-bs-target="#menu-principal"]')?.click(); return; }
    if (history.length > 1 && document.referrer && new URL(document.referrer).origin === location.origin) history.back();
    else if (location.pathname !== '/') location.href = '/';
}

const DIRECTIONS = { ArrowRight: 'droite', ArrowLeft: 'gauche', ArrowDown: 'bas', ArrowUp: 'haut' };

function surTouche(e) {
    if (!estActif() || e.altKey || e.ctrlKey || e.metaKey) return;
    const actif = document.activeElement;
    const saisie = actif && (actif.matches('input:not([type="checkbox"]):not([type="radio"]), textarea, select') || actif.isContentEditable);

    // Touches média (clavier, télécommandes LG/Samsung)
    if (['MediaPlayPause', 'MediaPlay', 'MediaPause'].includes(e.key) || [179, 415, 19, 10252].includes(e.keyCode)) {
        if (lecturePause()) e.preventDefault();
        return;
    }
    // Retour : Échap, Backspace (hors saisie), touche Retour des télécommandes (461 LG, 10009 Samsung)
    if (e.key === 'Escape' || e.key === 'GoBack' || e.key === 'BrowserBack' || e.keyCode === 461 || e.keyCode === 10009 || (e.key === 'Backspace' && !saisie)) {
        if (e.key === 'Escape' && document.querySelector('dialog[open]')) return; // le navigateur ferme le dialogue
        e.preventDefault();
        retour();
        return;
    }
    const dir = DIRECTIONS[e.key];
    if (dir) {
        if (saisie && (dir === 'gauche' || dir === 'droite')) return;
        e.preventDefault();
        e.stopPropagation();
        const depart = actif && actif !== document.body && visible(actif) ? actif : null;
        focaliser(depart ? (voisin(depart, dir) || depart) : premierElement());
        return;
    }
    if ((e.key === 'Enter' || e.key === ' ') && actif?.matches('[data-tv-focus]:not(a):not(button)')) {
        e.preventDefault();
        actif.click();
    }
}

export function initModeTv() {
    const params = new URLSearchParams(location.search);
    let actif = false;
    try { actif = localStorage.getItem(CLE_TV) === '1'; } catch { /* rien */ }
    if (params.get('tv') === '1') actif = true;
    if (params.get('tv') === '0') actif = false;
    activerTv(actif);

    document.querySelectorAll('[data-tv-interrupteur]').forEach((b) => {
        b.addEventListener('click', () => activerTv(!estActif()));
    });
    // Capture : passe avant les gestionnaires des rangées et des onglets
    document.addEventListener('keydown', surTouche, true);

    if (actif) {
        // Focus initial pour que la télécommande ait tout de suite un repère
        requestAnimationFrame(() => { if (document.activeElement === document.body) focaliser(premierElement()); });
    }
}
