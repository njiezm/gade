// GADÉ — Sinéma lakay : scripts (Bootstrap à la carte, sans jQuery)
import '@fontsource/young-serif/latin-400.css';
import '@fontsource-variable/figtree/wght.css';
import '@fontsource/kalam/latin-700.css';
import Collapse from 'bootstrap/js/dist/collapse';
import { initRangees } from './rangees';
import { initListe, importerAncienneMemoire, initMenuProfil } from './memoire';
import { initProfils } from './profils';
import { initLecteur } from './lecteur';
import { initPwa } from './pwa';
import { initSurLaTele, initModeTv } from './tv';

document.documentElement.classList.add('js');

// --- En-tête fixe : hauteur réelle réservée (body padding-top) ---
const header = document.querySelector('[data-header]');
const setHeaderHeight = () => {
    if (header) document.documentElement.style.setProperty('--header-h', `${header.offsetHeight}px`);
};
setHeaderHeight();
window.addEventListener('resize', setHeaderHeight, { passive: true });
window.addEventListener('load', setHeaderHeight);
const onScroll = () => header?.classList.toggle('is-scrolled', window.scrollY > 8);
onScroll();
window.addEventListener('scroll', onScroll, { passive: true });

// Menu mobile : se referme après un clic sur un lien
document.querySelectorAll('.site-header .nav-link').forEach((link) => {
    link.addEventListener('click', () => {
        const menu = document.getElementById('menu-principal');
        if (menu?.classList.contains('show')) Collapse.getOrCreateInstance(menu).hide();
    });
});
// Bouton loupe (mobile) : ouvre le menu et place le curseur dans la recherche
document.querySelector('[data-ouvrir-recherche]')?.addEventListener('click', () => {
    const menu = document.getElementById('menu-principal');
    if (!menu) return;
    const input = menu.querySelector('input[type="search"]');
    if (menu.classList.contains('show')) { input?.focus(); return; }
    menu.addEventListener('shown.bs.collapse', () => input?.focus(), { once: true });
    Collapse.getOrCreateInstance(menu).show();
});

// --- À la une : choix du programme mis en avant ---
const une = document.querySelector('[data-une]');
if (une) {
    const slides = [...une.querySelectorAll('[data-une-slide]')];
    const boutons = [...une.querySelectorAll('[data-une-choix]')];
    const montrer = (i) => {
        slides.forEach((s, k) => s.classList.toggle('is-active', k === i));
        boutons.forEach((b, k) => b.setAttribute('aria-pressed', k === i ? 'true' : 'false'));
    };
    boutons.forEach((b, i) => b.addEventListener('click', () => montrer(i)));
}

// --- Fiche : onglets des saisons ---
document.querySelectorAll('[data-saisons]').forEach((bloc) => {
    const onglets = [...bloc.querySelectorAll('[role="tab"]')];
    const panneaux = [...bloc.querySelectorAll('[role="tabpanel"]')];
    const choisir = (i, focus = false) => {
        onglets.forEach((o, k) => { o.setAttribute('aria-selected', k === i ? 'true' : 'false'); o.tabIndex = k === i ? 0 : -1; });
        panneaux.forEach((p, k) => { p.hidden = k !== i; });
        if (focus) onglets[i].focus();
    };
    onglets.forEach((o, i) => {
        o.addEventListener('click', () => choisir(i));
        o.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') choisir((i + 1) % onglets.length, true);
            if (e.key === 'ArrowLeft') choisir((i - 1 + onglets.length) % onglets.length, true);
        });
    });
});

// --- Parcourir : les listes déroulantes filtrent tout de suite ---
document.querySelectorAll('[data-filtres]').forEach((form) => {
    form.querySelectorAll('select, input[type="checkbox"]').forEach((el) => el.addEventListener('change', () => form.requestSubmit()));
});

// Messages flash
document.querySelectorAll('[data-flash]').forEach((el) => setTimeout(() => el.remove(), 6500));

initRangees();
initListe();
initMenuProfil();
initProfils();
importerAncienneMemoire();
initLecteur();
initPwa();
initSurLaTele();
initModeTv();
