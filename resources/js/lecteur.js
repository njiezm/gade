// Lecteur GADÉ : API IFrame YouTube en mode youtube-nocookie.
// Enregistre la position (currentTime) dans le navigateur, propose de reprendre,
// et enchaîne automatiquement l'épisode suivant d'une série.
import { sauverProgression, lireProgression, estEnCours, fmtTemps } from './memoire';

let apiPromesse = null;
function chargerApi() {
    if (window.YT && window.YT.Player) return Promise.resolve(window.YT);
    if (!apiPromesse) {
        apiPromesse = new Promise((resolve, reject) => {
            const precedent = window.onYouTubeIframeAPIReady;
            window.onYouTubeIframeAPIReady = () => { precedent?.(); resolve(window.YT); };
            const s = document.createElement('script');
            s.src = 'https://www.youtube.com/iframe_api';
            s.async = true;
            s.onerror = reject;
            document.head.appendChild(s);
        });
    }
    return apiPromesse;
}

export function initLecteur() {
    const salle = document.querySelector('[data-lecteur]');
    if (!salle) return;

    const { video, slug, episode, suivant, suivantTitre } = salle.dataset;
    const ecran = salle.querySelector('[data-ecran]');
    const cible = salle.querySelector('[data-ecran-lecteur]');
    const voileReprise = salle.querySelector('[data-voile-reprise]');
    const voileSuivant = salle.querySelector('[data-voile-suivant]');
    const voileErreur = salle.querySelector('[data-voile-erreur]');
    const params = new URLSearchParams(location.search);
    const tParam = parseInt(params.get('t') || '', 10);
    const auto = params.get('auto') === '1';
    const memo = lireProgression(slug, episode);

    let player = null;
    let minuterie = null;
    let compte = null;

    // Position envoyée au serveur (profil actif) ; beacon = true à la fermeture de la page (sendBeacon)
    const sauver = (beacon = false) => {
        if (!player || typeof player.getCurrentTime !== 'function') return;
        const t = player.getCurrentTime();
        const d = player.getDuration();
        if (t > 3) sauverProgression(slug, episode, t, d, beacon === true);
    };
    // Accès pour « Sur la télé » et le Mode TV (tv.js) : timecode, sauvegarde, lecture/pause
    window.gadeLecteur = { player: () => player, sauver, ecran: () => ecran, temps: () => (player?.getCurrentTime?.() ?? (tParam > 0 ? tParam : 0)) };

    const demarrer = (debut, lancer = true) => {
        voileReprise.hidden = true;
        salle.querySelector('[data-attente]')?.remove();
        chargerApi().then((YT) => {
            player = new YT.Player(cible, {
                host: 'https://www.youtube-nocookie.com',
                videoId: video,
                width: '100%',
                height: '100%',
                playerVars: {
                    autoplay: lancer ? 1 : 0,
                    start: Math.max(0, Math.floor(debut || 0)),
                    rel: 0,
                    playsinline: 1,
                    modestbranding: 1,
                    iv_load_policy: 3,
                    hl: 'fr',
                    cc_lang_pref: 'fr',
                    origin: location.origin,
                },
                events: {
                    onReady: (e) => { if (lancer) e.target.playVideo(); },
                    onStateChange: (e) => {
                        if (e.data === YT.PlayerState.PLAYING) {
                            clearInterval(minuterie);
                            minuterie = setInterval(() => sauver(), 10000);
                        } else {
                            clearInterval(minuterie);
                            sauver();
                        }
                        if (e.data === YT.PlayerState.ENDED) {
                            const d = player.getDuration();
                            sauverProgression(slug, episode, d, d);
                            if (suivant) lancerCompteARebours();
                        }
                    },
                    onError: () => { voileErreur.hidden = false; },
                },
            });
        }).catch(() => { voileErreur.hidden = false; });
    };

    function lancerCompteARebours() {
        let n = 10;
        const chiffre = voileSuivant.querySelector('[data-compte]');
        voileSuivant.hidden = false;
        chiffre.textContent = n;
        compte = setInterval(() => {
            n -= 1;
            chiffre.textContent = n;
            if (n <= 0) { clearInterval(compte); location.href = `${suivant}?auto=1`; }
        }, 1000);
    }
    voileSuivant?.querySelector('[data-annuler]')?.addEventListener('click', () => { clearInterval(compte); voileSuivant.hidden = true; });
    voileSuivant?.querySelector('[data-maintenant]')?.addEventListener('click', () => { location.href = `${suivant}?auto=1`; });

    // Démarrage : ?t= (lien « reprendre »), reprise proposée, ou lecture depuis le début
    if (!Number.isNaN(tParam) && tParam > 0) {
        demarrer(tParam, true);
    } else if (estEnCours(memo)) {
        voileReprise.querySelector('[data-reprise-temps]').textContent = fmtTemps(memo.t);
        voileReprise.hidden = false;
        voileReprise.querySelector('[data-reprendre-ici]').addEventListener('click', () => demarrer(memo.t, true));
        voileReprise.querySelector('[data-depuis-debut]').addEventListener('click', () => demarrer(0, true));
        voileReprise.querySelector('[data-reprendre-ici]').focus({ preventScroll: true });
    } else if (auto) {
        demarrer(0, true);
    } else {
        // Affiche + bouton : le lecteur YouTube ne se charge qu'au clic
        salle.querySelector('[data-lancer]')?.addEventListener('click', () => demarrer(0, true));
    }

    // Plein écran sur le cadre (le lecteur YouTube a aussi son propre bouton)
    salle.querySelector('[data-plein-ecran]')?.addEventListener('click', () => {
        if (document.fullscreenElement) document.exitFullscreen();
        else ecran.requestFullscreen?.().catch(() => {});
    });

    window.addEventListener('pagehide', () => sauver(true));
    document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'hidden') sauver(true); });

    if (suivantTitre) voileSuivant?.querySelector('[data-suivant-titre]')?.replaceChildren(document.createTextNode(suivantTitre));
}
