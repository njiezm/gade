// Mémoire du profil actif, côté serveur : « Ma liste » et « Kontinyé gadé » (positions de lecture).
// Chaque profil a ses propres données (base de données). Les anciennes données du navigateur
// (localStorage, versions précédentes de GADÉ) sont importées une fois dans le profil actif.

const meta = () => document.querySelector('meta[name="gade-profil"]');
const jeton = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

export const fmtTemps = (s) => {
    s = Math.max(0, Math.floor(s));
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    const sec = String(s % 60).padStart(2, '0');
    return h ? `${h}:${String(m).padStart(2, '0')}:${sec}` : `${m}:${sec}`;
};

const formulaire = (donnees) => {
    const f = new FormData();
    f.append('_token', jeton());
    Object.entries(donnees).forEach(([k, v]) => { if (v !== null && v !== undefined) f.append(k, v); });
    return f;
};

// ---------------- Progression ----------------
export const estTermine = (p) => p && p.t >= p.d - 60;
export const estEnCours = (p) => p && p.t > 30 && !estTermine(p);

/** Position mémorisée pour la vidéo du lecteur (fournie par le serveur dans data-reprise). */
export function lireProgression() {
    const salle = document.querySelector('[data-lecteur]');
    const t = parseInt(salle?.dataset.reprise || '', 10);
    const d = parseInt(salle?.dataset.repriseDuree || '', 10);
    return Number.isNaN(t) || Number.isNaN(d) ? null : { t, d };
}

let dernierEnvoi = { cle: '', t: -1, a: 0 };
/**
 * Envoie la position au serveur (profil actif). Anti-rafale : au plus une fois toutes les 8 s
 * sauf si la position a sauté. `beacon` : envoi fiable à la fermeture de la page (sendBeacon).
 */
export function sauverProgression(slug, ep, t, d, beacon = false) {
    const url = meta()?.dataset.progression;
    if (!url || !d || d < 60) return;
    t = Math.floor(t); d = Math.floor(d);
    const cle = `${slug}/${ep || ''}`;
    const maintenant = Date.now();
    const fin = t >= d - 60;
    if (cle === dernierEnvoi.cle && t === dernierEnvoi.t) return;
    if (!beacon && !fin && cle === dernierEnvoi.cle && Math.abs(t - dernierEnvoi.t) < 30 && maintenant - dernierEnvoi.a < 8000) return;
    dernierEnvoi = { cle, t, a: maintenant };
    const corps = formulaire({ slug, ep: ep || null, t, d });
    if (beacon && navigator.sendBeacon) {
        navigator.sendBeacon(url, corps);
        return;
    }
    fetch(url, { method: 'POST', body: corps, credentials: 'same-origin', keepalive: true, headers: { Accept: 'application/json' } }).catch(() => {});
}

// ---------------- Ma liste ----------------
function majBoutonListe(btn, actif) {
    btn.setAttribute('aria-pressed', actif ? 'true' : 'false');
    const label = btn.querySelector('[data-liste-label]');
    if (label) label.textContent = actif ? 'Dans ma liste' : 'Ma liste';
    const icone = btn.querySelector('[data-liste-icone]');
    if (icone) icone.innerHTML = actif
        ? '<path d="M5 12.5l4.2 4.2L19 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>'
        : '<path d="M12 5v14M5 12h14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>';
}

export function initListe(root = document) {
    const base = meta()?.dataset.liste;
    root.querySelectorAll('[data-liste]').forEach((btn) => {
        if (btn.dataset.pret || !base) return;
        btn.dataset.pret = '1';
        btn.addEventListener('click', async () => {
            const slug = btn.dataset.liste;
            const freres = document.querySelectorAll(`[data-liste="${CSS.escape(slug)}"]`);
            const voulu = btn.getAttribute('aria-pressed') !== 'true';
            freres.forEach((b) => majBoutonListe(b, voulu)); // affichage immédiat
            try {
                const r = await fetch(`${base}/${encodeURIComponent(slug)}`, { method: 'POST', body: formulaire({}), credentials: 'same-origin', headers: { Accept: 'application/json' } });
                if (!r.ok) throw new Error(String(r.status));
                const { dans_liste: dans } = await r.json();
                freres.forEach((b) => majBoutonListe(b, dans));
            } catch {
                freres.forEach((b) => majBoutonListe(b, !voulu));
            }
        });
    });
}

// ---------------- Import unique des anciennes données du navigateur ----------------
const CLE_LISTE = 'gade:liste';
const CLE_PROGRESSION = 'gade:progression';
const lire = (cle) => { try { return JSON.parse(localStorage.getItem(cle)); } catch { return null; } };

export async function importerAncienneMemoire() {
    const m = meta();
    if (!m?.dataset.import) return;
    const liste = lire(CLE_LISTE);
    const progression = lire(CLE_PROGRESSION);
    const items = progression && typeof progression === 'object' ? Object.values(progression) : [];
    if (!(Array.isArray(liste) && liste.length) && !items.length) return;
    try {
        const r = await fetch(m.dataset.import, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': jeton() },
            body: JSON.stringify({ liste: Array.isArray(liste) ? liste.slice(0, 300) : [], progression: items.slice(0, 300) }),
        });
        if (!r.ok) return;
        const { importes } = await r.json();
        // Importé une seule fois : on vide l'ancien stockage du navigateur
        localStorage.removeItem(CLE_LISTE);
        localStorage.removeItem(CLE_PROGRESSION);
        if (importes > 0) {
            const flash = document.createElement('div');
            flash.className = 'flash';
            flash.setAttribute('role', 'status');
            flash.textContent = `Anciennes données de ce navigateur importées dans le profil ${m.dataset.prenom} (${importes}).`;
            document.getElementById('contenu')?.prepend(flash);
            setTimeout(() => flash.remove(), 6500);
        }
    } catch { /* hors ligne : on réessaiera à la prochaine page */ }
}

// ---------------- Menu du profil (en-tête) ----------------
export function initMenuProfil() {
    const bouton = document.querySelector('[data-menu-profil]');
    const menu = bouton && document.getElementById(bouton.getAttribute('aria-controls'));
    if (!menu) return;
    const liens = () => [...menu.querySelectorAll('a, button')].filter((el) => !el.hidden && el.offsetParent !== null);
    const ouvrir = (oui, focus = false) => {
        bouton.setAttribute('aria-expanded', oui ? 'true' : 'false');
        menu.hidden = !oui;
        if (oui && focus) liens()[0]?.focus();
    };
    bouton.addEventListener('click', () => ouvrir(menu.hidden, false));
    bouton.addEventListener('keydown', (e) => { if (e.key === 'ArrowDown') { e.preventDefault(); ouvrir(true, true); } });
    menu.addEventListener('keydown', (e) => {
        const l = liens();
        const i = l.indexOf(document.activeElement);
        if (e.key === 'Escape') { ouvrir(false); bouton.focus(); }
        if (e.key === 'ArrowDown') { e.preventDefault(); l[(i + 1) % l.length]?.focus(); }
        if (e.key === 'ArrowUp') { e.preventDefault(); l[(i - 1 + l.length) % l.length]?.focus(); }
    });
    document.addEventListener('click', (e) => { if (!menu.hidden && !menu.contains(e.target) && !bouton.contains(e.target)) ouvrir(false); });
}
