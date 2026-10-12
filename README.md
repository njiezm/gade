# GADÉ — Sinéma lakay

Micro-Netflix **légal** pour regarder lakay : une sélection organisée de films, séries et documentaires **en français**, publiés gratuitement sur YouTube **par leurs ayants droit** (ARTE, France Télévisions, réseau La 1ère, INA, TV5MONDE, LCP…), lus avec le lecteur intégré youtube-nocookie.
Concept NJIEZM.FR (N'jie ZAMON, Le Robert, Martinique).

- Site : http://gade.test (Apache Laragon) — ou `php artisan serve --port=8130`
- **Accès réservé à la famille** : connexion par e-mail et mot de passe, puis choix du profil (« Ki moun ka gadé ? »).
- Coulisses : http://gade.test/coulisses — **profil de N'jie** seulement (son code PIN est redemandé s'il en a un).

## Connexion, profils et gestion

- **Compte unique de la famille** : e-mail et mot de passe dans le `.env` (`GADE_COMPTE_EMAIL`, `GADE_COMPTE_PASSWORD`, lus via `config/gade.php`). Le seeder `FamilleSeeder` crée le compte avec le mot de passe **haché** ; il n'est écrit nulle part ailleurs. Si le mot de passe contient un `$`, le mettre entre **guillemets simples** dans le `.env`.
- Connexion `/connexion` : « Rester connecté », **5 essais par minute** (par adresse IP), session régénérée. Déconnexion dans le menu du profil. Mot de passe oublié : page « contactez N'jie ».
- **Profils** (8 au maximum) créés d'office : **N'jie** (profil « mère », gestionnaire), Jie'n, Jade, Capucine, Christopher. Chaque profil : prénom, avatar, couleur, option **profil enfant** (uniquement le « tout public », rangées Policier / Romance / Nouveaux riches / True crime / CRU masquées), **code PIN** facultatif à 4 chiffres (haché, 5 essais puis blocage 15 min).
- **Seule N'jie** crée, renomme, supprime les profils, change leurs avatars, règle « enfant » et PIN (`/profils/gerer`), voit l'activité (`/gestion`), change le mot de passe (`/compte`) et entre dans les coulisses. Chaque profil peut changer **son propre avatar** (`/profils/{id}/avatar`).
- **Données par profil, en base** : « Kontinyé gadé » (position envoyée par le lecteur toutes les 10 s, et par `sendBeacon` à la fermeture), « Ma liste », « Déjà vu ». Les anciennes données du navigateur (localStorage) sont importées une fois dans le premier profil actif, puis effacées du navigateur.
- PIN oublié : `php artisan gade:pin` (retire le PIN du profil gestionnaire), `php artisan gade:pin Jade`, `--debloquer` pour seulement lever le blocage.
- L'ancien mot de passe `COULISSES_PASSWORD` est **supprimé** : compte connecté + profil de N'jie + son PIN suffisent, sans troisième secret à retenir.
- Site privé : `robots.txt` interdit tout, plus de sitemap, `X-Robots-Tag: noindex`, pages connectées en `Cache-Control: no-store`.
- Avatars : 31 dessins SVG originaux dans `public/images/avatars/` (4 thèmes : Bèt péyi, Fwi épi légim, Sinéma, Fantézi), générés par `npm run avatars` (`resources/avatars/avatars.mjs`, planche d'aperçu `docs/captures/avatars-planche.png`).

## Lancer le projet

```powershell
$env:Path="C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64;C:\laragon\bin\composer;C:\laragon\bin\nodejs\node-v22;$env:Path"
cd C:\laragon\www\prospects\projets\gade
php artisan migrate:fresh --seed --force   # INSTALLATION NEUVE SEULEMENT : efface tout, puis remplit le catalogue complet (catalogue.json + ajouts/)
npm run build
php artisan optimize:clear
php artisan config:cache                     # OBLIGATOIRE en fin de travail (mod_php Laragon)
```

> Après toute modification de `.env` ou de `config/*.php` (dont `config/gade.php`) : relancer `php artisan config:cache`.
> Avant `php artisan test` : `php artisan config:clear` (les tests refusent de tourner avec une configuration en cache, pour ne jamais toucher la vraie base).
> Pas de `view:cache` (verrous de fichiers sous Windows).

## Pages

| URL | Contenu |
|---|---|
| `/` | Sans session : page publique « accès famille ». Avec un profil : À la une + rangées : Kontinyé gadé, Ma liste, Nouveautés, Policier, Caraïbes & Antilles, Sitcoms années 90, Zouk & biguine en live, Humour & spectacles, Nouveaux riches, Comédies, Pour les petits, Romance, True crime (adultes), CRU (adultes), Documentaires nature, Séries, Histoire, Théâtre & télé rétro, En famille, Films complets, Science-fiction & fantastique, Moins de 30 min, Sciences, Voyages, Cuisine, Sport, Déjà vu |
| `/connexion`, `/mot-de-passe-oublie` | Connexion (publique) |
| `/profils` | « Ki moun ka gadé ? » (+ PIN `/profils/{id}/pin`) |
| `/profils/gerer`, `/gestion`, `/compte` | Gérer les profils, activité, compte et mot de passe (N'jie) |
| `/parcourir` | Recherche + filtres (univers, genre, durée, tout public, tri) |
| `/programme/{slug}` | Fiche : description, durée, chaîne officielle (lien), lecture / reprise, Ma liste, épisodes par saison, similaires |
| `/regarder/{slug}[/{idVidéo}]` | Lecteur (API IFrame, youtube-nocookie), plein écran, reprise, épisode suivant automatique |
| `/ma-liste` | Ma liste + Déjà vu du profil |
| `/a-propos` | Comment ça marche + chaînes officielles utilisées |
| `/mentions-legales` | Mentions & confidentialité |
| `/coulisses` | Profil de N'jie : ajout par URL (oEmbed), une, masquer, classer, vérifier, propositions à valider |
| `/robots.txt` | `Disallow: /` (site privé, pas de sitemap) |

## Mettre à jour la sélection (avec Claude)

### Ajouter des programmes sans toucher aux données de la famille : `gade:catalogue:sync`

Le site est utilisé par la famille (profils, Ma liste, Kontinyé gadé, Déjà vu) : **ne jamais** lancer `migrate:fresh`, `migrate:refresh`, `db:wipe` ni `db:seed` sur la vraie base. Pour enrichir le catalogue :

1. sauvegarder la base : `copy database\database.sqlite databaseackup-avant-ajouts-AAAAMMJJ-HHMM.sqlite` ;
2. déposer un fichier par lot dans `database/data/ajouts/` (même format que `catalogue.json`, ex. `01-bron-sandhamn.json`, `02-kassav-concerts.json`…) ;
3. `php artisan gade:catalogue:sync` (`--simuler` pour voir sans écrire), puis `php artisan gade:verifier --programme=slug1,slug2` et `php artisan config:cache`.

La commande lit `catalogue.json` puis `ajouts/*.json` dans l'ordre des noms, et fait des **upserts** de programmes (par slug, ou par ID de vidéo) et d'épisodes (par ID de vidéo). Elle est idempotente et **ne supprime rien** : un programme absent des fichiers reste en place. Elle ne touche jamais aux profils, au compte, aux positions, à Ma liste ni au Déjà vu, ni aux réglages des coulisses (à la une, ordre, masqué).
Un programme déjà en base n'est que **complété** (champs vides) ; pour réécrire un champ, l'entrée porte `"maj": ["description"]` (ou `true`), ou on lance `--maj`. Un même slug dans plusieurs fichiers est fusionné : on peut ajouter des épisodes à une série existante avec `{"slug": "bron", "episodes": [...]}`. Les nouveaux programmes apparaissent dans « Nouveautés ». Une proposition « à valider » pour la même vidéo passe en « validée ».
Le seeder (`migrate:fresh --seed`, installation neuve) utilise la même logique.
Sources non officielles acceptées par N'jie : `ayant_droit` commence par « Source non officielle, validée par N'jie le 11/10/2026 » (`source_officielle: false`, `note_source`).

### Propositions

```bash
# Claude dépose une proposition (vérifiée par oEmbed) dans la file « à valider »
php artisan gade:proposer "https://www.youtube.com/watch?v=XXXXXXXXXXX" --univers=documentaires --genre=histoire --duree=52:10 --tout-public --motif="Pourquoi"
# Revérifier toutes les vidéos : masque automatiquement celles devenues indisponibles, rapport dans storage/app/rapports/
php artisan gade:verifier            # options : --programme=slug  --sans-masquer  --pause=150
```

N'jie valide ou refuse dans **Coulisses → Propositions à valider** (« Valider… » ouvre la fiche pré-remplie).
Sources de confiance et règles de choix : `docs/SOURCES.md`.

## Où modifier quoi

| Quoi | Où |
|---|---|
| Univers, genres, rangées des genres (titre, accroche créole et française), tranches de durée, chaînes de confiance, compte, profils de départ, couleurs, PIN, rangées masquées aux enfants | `config/gade.php` (+ `.env`) |
| Connexion, profils, gestion | `app/Http/Controllers/Auth/*`, `ProfilsController`, `GestionController`, `MoiController` ; middlewares `ProfilChoisi`, `ProfilGestionnaire`, `CoulissesAuth` |
| Catalogue (seeders et ajouts) | `database/data/catalogue.json` + `database/data/ajouts/*.json` → `app/Services/CatalogueSync.php` (commande `gade:catalogue:sync`, `DatabaseSeeder`) |
| Vignettes WebP | `public/vignettes/` (480×270), `public/affiches/` (1280×720) |
| Rangées de l'accueil | `app/Support/Catalogue.php` |
| Lecteur, reprise, Ma liste | `resources/js/lecteur.js`, `resources/js/memoire.js` |
| Styles | `resources/scss/` (palette dans `_variables.scss`) |
| Logo | `npm run logo` (`resources/brand/logo.mjs`) — voir `docs/IDENTITE.md` |
| CSP | `app/Http/Middleware/EnTetesSecurite.php` |

## Tests

`php artisan config:clear && php artisan test` — connexion (CSRF, throttle, « Rester connecté », mot de passe), profils (PIN, droits N'jie / autres, max 8), données isolées par profil, profil enfant, import localStorage, coulisses réservées, pages, filtres, ajout par URL avec oEmbed simulé, `gade:verifier` et `gade:proposer` avec réponses simulées.

## Installer GADÉ

GADÉ est une **PWA** : elle s'installe comme une application (icône, plein écran, sans barre d'adresse).

- **Android (Chrome)** : menu → bouton **« Installer GADÉ »** (icône ⬇ dans le menu du site) ou ⋮ → *Installer l'application*.
- **iPhone / iPad (Safari)** : bouton **Partager** → **« Sur l'écran d'accueil »** (le bouton « Installer GADÉ » du menu affiche cette aide).
- **Ordinateur (Chrome, Edge)** : icône d'installation dans la barre d'adresse, ou bouton ⬇ de l'en-tête.

L'installation et le service worker exigent **HTTPS** (ou `localhost`) : utiliser https://gade.test (certificat Laragon) ou, pour tester en HTTP, Chrome lancé avec `--unsafely-treat-insecure-origin-as-secure=http://gade.test`.

| Quoi | Où |
|---|---|
| Manifeste (nom, couleurs, icônes) | `public/manifest.webmanifest` (+ type MIME dans `public/.htaccess`) |
| Icônes 192 / 512 / maskable / Apple | `public/images/pwa/` (générées depuis `public/images/brand/signe-gade.svg`) |
| Service worker | route `/sw.js` → `PwaController` + `resources/views/pwa/sw.blade.php` (pré-cache lu dans `public/build/manifest.json`, version auto à chaque build) |
| Page hors connexion « Pa ni rézo… » | `/hors-ligne` (`resources/views/pwa/hors-ligne.blade.php`) |
| Enregistrement, bandeau « Nouvelle version », bouton Installer, aide iOS | `resources/js/pwa.js`, `resources/scss/_pwa.scss` |

Stratégie du service worker : pages HTML **réseau d'abord, jamais mises en cache** (rien ne fuit d'un profil à l'autre) avec repli sur `/hors-ligne` ; vignettes et affiches en **cache d'abord** (300 max) ; build, polices, logo en cache ; jamais de cache pour les POST, `/coulisses`, `/regarder`, `/moi/*`, `/profils*`, ni YouTube.

## Regarder sur la télé

Bouton **📺 Sur la télé** sur le lecteur et les fiches (`resources/views/partials/sur-la-tele.blade.php`, `resources/js/tv.js`) :

- **Android / iPhone** : « Ouvrir dans l'app YouTube puis caster » ouvre la vidéo **au timecode actuel** (Android : intent vers l'app YouTube, repli sur youtube.com). La position est enregistrée dans le profil avant de partir. Dans YouTube, toucher l'icône **Cast**.
- **Ordinateur (Chrome, Edge)** : menu ⋮ → **Caster…** → *Caster l'onglet*, puis **Mode TV plein écran** (le lecteur seul, en plein écran).
- **Smart TV** : ouvrir gade.test (bientôt l'adresse en ligne) dans le navigateur de la TV, puis activer **Mode TV** dans le menu.
- **App YouTube de la TV** : Paramètres → *Associer à la TV avec un code*.

**Mode TV** (`?tv=1` ou bouton 📺 du menu, mémorisé sur l'appareil, `?tv=0` pour le couper) : grands éléments, focus jaune « projecteur », en-tête simplifié, **navigation à la télécommande ou au clavier** — flèches entre les cartes et les rangées (défilement automatique), **OK/Entrée** ouvre, **Retour/Échap/Backspace** revient, touches **lecture/pause** transmises au lecteur YouTube. Fonctionne sur l'accueil, les fiches, le lecteur et l'écran des profils (`[data-tv-focus]` pour tout élément à rendre focalisable). Styles : `resources/scss/_tv.scss` (respecte `prefers-reduced-motion`).

Tests : `php artisan test --filter=PwaTest` ; navigateur : `python tests/Navigateur/tv_pwa.py` (Mode TV, liens YouTube avec `t=`, SW actif, pré-cache, page hors ligne ; captures `docs/captures/tv-*.png`, `pwa-*.png`).

## Phase 2 : bouton Cast natif

Le *Default Media Receiver* de Google Cast ne lit pas les vidéos YouTube (flux protégé, pas d'URL de média). Un vrai bouton Cast dans GADÉ demande :

1. un **receiver Web personnalisé** (page HTML qui charge le lecteur IFrame YouTube sur la TV), hébergé en **HTTPS public** ;
2. son **inscription dans la Google Cast SDK Developer Console** (5 $ une fois, App ID) et l'enregistrement des appareils de test ;
3. côté GADÉ, le **Cast Web Sender SDK** (`cast_sender.js`, à autoriser dans la CSP) qui envoie au receiver l'ID YouTube, le timecode et le profil.

À faire après la mise en ligne. En attendant : app YouTube + Cast, onglet casté depuis Chrome, ou navigateur de la TV en Mode TV.

## [À compléter]

- Mentions légales : SIRET, adresse de contact et hébergeur lors d'une mise en ligne publique.
- Les droits ARTE sont limités dans le temps : lancer `php artisan gade:verifier` régulièrement (une fois par semaine).
"# gade" 
