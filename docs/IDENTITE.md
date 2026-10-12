# GADÉ — Sinéma lakay : identité

**GADÉ** (« regarde ! » en créole martiniquais) + **Sinéma lakay** (« le cinéma à la maison »).
L'idée : une petite salle de cinéma chaleureuse, dans le salon, un soir de semaine. Velours, lumière de projecteur, une touche de madras. Rien de futuriste.

## Le logo

- **Le signe** : un écran-velours aux coins arrondis (bordeaux de rideau de cinéma), avec un **œil** qui regarde (« gadé ! ») dont la pupille est un **bouton lecture**. En bas, une **bande de madras** rouge, jaune et vert, comme une tête attachée.
- **Le mot** : « GADÉ » en *Young Serif* (empattements doux, esprit affiche de cinéma d'autrefois), vectorisé.
- **La signature** : « Sinéma lakay » en *Kalam* (écriture manuscrite), jaune madras.

Fichiers (générés par `npm run logo` → `resources/brand/logo.mjs`) :

| Fichier | Usage |
|---|---|
| `docs/logo-gade.png` | Logo PNG 1200 px sur fond brun nuit |
| `docs/logo-gade-clair.png` | Version pour fond clair |
| `public/images/brand/logo-gade.svg` | Logo vectoriel (fond sombre) |
| `public/images/brand/logo-gade-clair.svg` | Logo vectoriel (fond clair) |
| `public/images/brand/signe-gade.svg` | Le signe seul |
| `public/images/brand/favicon.svg`, `public/favicon.ico`, `favicon-180/192/512.png` | Favicons (version simplifiée sans madras) |
| `public/images/brand/og-gade.jpg` (+ `docs/og-gade.png`) | Image de partage 1200 × 630 : rideaux de velours, accroche « Ka ou lé gadé jodi-a ? » |
| `resources/views/partials/signe.blade.php`, `mot-gade.blade.php` | Signe et mot en SVG dans l'en-tête |

## Couleurs

| Nom | Hex | Rôle |
|---|---|---|
| Brun nuit | `#160E0B` | Fond (salle éteinte) |
| Velours | `#2B1613` | Cartes, panneaux |
| Bordeaux rideau | `#6E1C27` | Signe, accents, rideaux |
| Rouge madras | `#C8322B` | Madras, alertes, barre de progression |
| Jaune madras | `#F2B53A` | Bouton « Gadé », liens, lumière de projecteur |
| Vert créole | `#2F7D4A` | Madras, pastille « Tout public » |
| Crème | `#F7ECD9` | Texte principal |
| Sable | `#CDB89A` | Texte secondaire |

Contrastes : crème et sable sur brun nuit dépassent largement 4,5:1 ; le bouton jaune porte un texte brun nuit.

## Typographies (auto-hébergées via @fontsource, licence SIL OFL)

- **Young Serif** : titres, logo.
- **Figtree** (variable) : textes et interface, très lisible sur écran sombre.
- **Kalam** : petites phrases en créole (« Ka ou lé gadé jodi-a ? », « Kontinyé gadé », « Pou pita… »).

## Motifs et détails

- **Bande de madras** (CSS `@mixin madras`) : sous l'en-tête, avant le pied de page, devant le titre « Caraïbes & Antilles », soulignement du menu actif.
- **Écran de projection** : le lecteur est posé dans un cadre bordeaux avec un halo doré.
- Cartes à coins arrondis, légère élévation et halo jaune au survol, comme une affiche éclairée.

## Microcopie créole

| Créole | Où | Sens |
|---|---|---|
| Ka ou lé gadé jodi-a ? | accueil, image OG | Que veux-tu regarder aujourd'hui ? |
| Kontinyé gadé | rangée de reprise, bouton de fiche, lecteur | Continuer à regarder |
| Ka ou ka chèché ? | recherche | Que cherches-tu ? |
| Pou pita… / Ou poko mété ayen ! | Ma liste | Pour plus tard / Tu n'as encore rien mis ! |
| Lakay nou | rangée Caraïbes | Chez nous |
| Woy… pa ni ayen la. | page 404 | Oh… il n'y a rien ici. |
| Dèyè rido-a | coulisses | Derrière le rideau |
| Bon film lakay ou ! | pied de page | Bon film chez toi ! |
