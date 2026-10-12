# GADÉ — Analyse du concept

## Le problème

- YouTube regorge de films, séries et documentaires **en accès libre et légal**, publiés par ARTE, France Télévisions, le réseau La 1ère, l'INA, TV5MONDE… mais ils sont noyés au milieu des extraits, des vidéos courtes et des réuploads douteux.
- Chercher « film complet en français » mène surtout vers des chaînes anonymes qui republient des films sans en avoir les droits.
- L'interface YouTube pousse à l'enchaînement de recommandations : difficile de « choisir un film pour ce soir » tranquillement, à deux, sur le canapé.
- Les contenus antillais (Martinique la 1ère, Guadeloupe la 1ère, Guyane la 1ère, Échappées belles en Caraïbe…) existent mais ne sont jamais rassemblés.

## La solution GADÉ

| Besoin | Réponse |
|---|---|
| Ne regarder que du légal | Uniquement des chaînes officielles (liste de confiance), chaque vidéo vérifiée par oEmbed |
| Ne pas se perdre | Accueil façon plateforme : à la une, rangées thématiques, fiches claires, recherche et filtres |
| Regarder en plusieurs fois | « Kontinyé gadé » : la position est mémorisée, reprise en un clic, épisode suivant automatique |
| Garder pour plus tard | « Ma liste », sans compte |
| Regarder avec les enfants | Rangée et filtre « En famille » (programmes tout public) |
| Se sentir chez soi | Ambiance salle de cinéma créole, rangée Caraïbes & Antilles, microcopie en créole |
| Une sélection vivante | Coulisses (ajout par URL, une, masquage, classement), file de propositions de Claude, vérification automatique |
| Rester honnête | Pied de page clair : vidéos hébergées sur YouTube, propriété des ayants droit, GADÉ n'est qu'une sélection |

## Points techniques

- Lecteur : API IFrame YouTube en mode **youtube-nocookie**, plein écran, reprise, enchaînement.
- `php artisan gade:verifier` : revérifie toutes les vidéos et masque celles qui ont disparu (et réaffiche celles qui reviennent).
- CSP stricte : seuls `www.youtube-nocookie.com`, `www.youtube.com` et `i.ytimg.com` sont autorisés en plus du site.
- Aucune donnée personnelle côté serveur : liste et progression restent dans le navigateur.
