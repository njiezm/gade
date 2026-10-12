# GADÉ — Sources de confiance

Liste des **chaînes YouTube officielles** repérées pour la sélection (vérifiée le 10/10/2026).
Règle d'or : on ne met que des vidéos **publiées par les ayants droit eux-mêmes**. En cas de doute, on ne met pas la vidéo.

Pour chaque nouvelle vidéo :
1. la chaîne est dans cette liste (ou on vérifie qu'elle est bien l'ayant droit : diffuseur, producteur, distributeur, institution, auteur) ;
2. `https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v=ID&format=json` répond **200** (401/404 = non intégrable ou supprimée → on n'ajoute pas) ;
3. version **française** (VF ou film français), **complète** (pas d'extrait ni de bande-annonce), **plus de 20 minutes** ;
4. pas de restriction d'âge (« connexion requise ») : le lecteur intégré ne pourrait pas la lire.

Flux RSS d'une chaîne (15 dernières vidéos) : `https://www.youtube.com/feeds/videos.xml?channel_id=IDENTIFIANT`.
La même liste (identifiant → nom exact) est dans `config/gade.php` (`chaines_de_confiance`) : `gade:proposer` signale toute chaîne qui n'y figure pas.

## Utilisées dans la sélection actuelle

| Chaîne | Ayant droit | Ce qu'on y trouve | URL |
|---|---|---|---|
| ARTE | ARTE France | Grands documentaires (histoire, sciences, nature), reportages. **Droits limités dans le temps** (« disponible jusqu'au… » en description) | https://www.youtube.com/channel/UCwI-JbGNsojunnHbFAc0M4Q |
| ARTE Cinema | ARTE | Films complets (souvent jusqu'à une date donnée), documentaires sur le cinéma | https://www.youtube.com/channel/UClo03hULFynpoX3w1Jv7fhw |
| ARTE Séries | ARTE | Séries en VF, épisodes ou saisons intégrales (Bron, Meurtres à Sandhamn, Life on Mars, De grâce…) | https://www.youtube.com/channel/UCzaf-8cAEiXfynukcmV5MXw |
| ARTE Évasion | ARTE | Voyages, nature, cuisine du monde (« Voyage en cuisine ») | https://www.youtube.com/channel/UCXV7LUI7G1MaKRN18yQ5y7Q |
| Films & Séries – FranceTV Distribution | France Télévisions Distribution | Téléfilms et films complets en français, séries (Maigret avec Bruno Cremer, La Famille Katz…) — une nouveauté par semaine environ | https://www.youtube.com/channel/UCDEv1GHWqmyyinOSHf09__w |
| DOCS Voyage & Nature – FranceTV Distribution | France Télévisions Distribution | Documentaires animaliers, voyages, « Les Chemins de l'école », « Les 3 Guyanes » | https://www.youtube.com/channel/UCwAl5jBlk6rdxVi9QdKWZqQ |
| DOCS Histoire & Culture – FranceTV Distribution | France Télévisions Distribution | Archéologie, Seconde Guerre mondiale, cultures | https://www.youtube.com/channel/UCWaFdLxqxYscunr4P7uKxkw |
| Échappées belles | France Télévisions (France 5) | Émissions de voyage complètes (1 h 30), nombreuses îles de la Caraïbe | https://www.youtube.com/channel/UC-HX7z7qJlbuYvhTa3VhGKQ |
| Des Racines et des Ailes | France Télévisions (France 3) | Émissions intégrales patrimoine (dont Antilles, Caraïbes) | https://www.youtube.com/channel/UCRWhwFtif1T5mpFokTUnu6g |
| C'est pas sorcier (officiel) | France Télévisions | Épisodes complets de 26 min, parfait pour « En famille » | https://www.youtube.com/channel/UCENv8pH4LkzvuSV_qHIcslg |
| Martinique la 1ère | France Télévisions – réseau La 1ère | Magazine « Caraïbes », documentaires (ex. « Mensonges & vérité ») | https://www.youtube.com/channel/UCgA9V57lQlZFXkDfhVGTWSg |
| Guadeloupe la 1ère | France Télévisions – réseau La 1ère | « Lakou Mizik Sésyon » (live acoustique), documentaires | https://www.youtube.com/channel/UCGp0ZbEM4j6RA4jufMgG3FA |
| Guyane la 1ère | France Télévisions – réseau La 1ère | Documentaires (« De canne, de sucre et de rhum »), « Mo Kaz », « Fawaka », « Kazadok » | https://www.youtube.com/channel/UCdijnb4OugCB6KUl2aN3qJQ |
| TV5MONDE | TV5MONDE | « Destination Francophonie », séries africaines (« Allô Tribunal »), documentaires | https://www.youtube.com/channel/UCJsZHPR1jqKu-soDmKNMBFg |
| INA Histoire | INA | Archives télévisées par année (série « 1939-1945 ») | https://www.youtube.com/channel/UCFdEMYmiimq2uKmOMaBfPLg |
| INA Société | INA | Reportages d'archives sur la société française | https://www.youtube.com/channel/UCCTuFofVWD9uORhQqgn-d7Q |
| Ina Voyages | INA | Reportages de voyage d'archives | https://www.youtube.com/channel/UCnl6WhqDxlqN3BPTaZvC4UQ |
| LCP – Assemblée nationale | LCP | « Documentaire complet LCP », parfois des fictions historiques (La Séparation) | https://www.youtube.com/channel/UCHGFbA0KWBgf6gMbyUCZeCQ |
| M6+ Films & Séries | Groupe M6 | Téléfilms complets en VF (thrillers, romances) | https://www.youtube.com/channel/UCKJeH6nKtpmu-zHxbjacvHg |
| Factoris Films Officiel | Factoris Films (distributeur) | Quelques films complets en VF | https://www.youtube.com/channel/UC1rMCFD6Sxnu66sJZd44-Yg |
| Noob & Olydri | Olydri Studio (producteur) | Web-série NOOB en intégrale, le film | https://www.youtube.com/channel/UCdHXMcWxwZr2qQpdCCOcSkw |
| Il était une fois… (Hello Maestro) | Hello Maestro (producteur) | « Il était une fois… la Vie / l'Homme / les Explorateurs… » épisodes complets | https://www.youtube.com/channel/UCMsbW8TRs5i3R8K7Z9fUIXw |
| Les Mystérieuses Cités d'or – Officielle | chaîne officielle de la série | Épisodes regroupés par 3 (« marathons ») | https://www.youtube.com/channel/UC0Wy5l5KuDCd7j4s27POrKw |
| Nota Bene | Benjamin Brillaud (auteur) | Histoire racontée, formats de 20 à 40 min | https://www.youtube.com/channel/UCP46_MXP_WG_auH88FnfS1A |
| Histoires Crépues | auteurs de la chaîne | Histoire coloniale, Antilles (Mai 67, abolitions) | https://www.youtube.com/channel/UCVuMMUfqEI448VKbkpuNPHQ |
| Neoxi (@Ne0xi) | Neoxi, créateur français (auteur de ses vidéos) | Documentaires narrés de 20 min à 1 h 30 : catastrophes, accidents aériens et maritimes, affaires criminelles, histoires vraies. Public adulte. Certaines vidéos contiennent un passage sponsorisé annoncé (VPN, banque en ligne…) | https://www.youtube.com/channel/UC_446tDNo7UckPX78hM0Nlg |
| M6+ Mag | Groupe M6 | Reportages intégraux de Capital, Zone interdite, 66 Minutes, Enquête exclusive | https://www.youtube.com/channel/UCcJ3zI6dI519TecN5nnsVOA |
| Envoyé Spécial | France Télévisions (France 2) | Reportages intégraux du magazine | https://www.youtube.com/channel/UCCauEjHzPXxGer8af3u4x5g |
| Outre-mer La 1ère | France Télévisions | « Génération entrepreneurs » avec Kelly Massol, magazines Outre-mer | https://www.youtube.com/channel/UCpaOmN-F1fiS19Qh9vI8gAw |
| DOCS Enquêtes & Société – FranceTV Distribution | France Télévisions Distribution | Documentaires société (Tupperware…), enquêtes environnement | https://www.youtube.com/channel/UCXUSLU65KYElvyrrbQA1KIw |
| Contact-Entreprises Martinique | association des entreprises martiniquaises | Portraits d'entreprises martiniquaises, témoignages | https://www.youtube.com/channel/UCHCepbDwJELAO-68Kvl1NqA |
| Banque de France | Banque de France | Conférences d'éducation financière (Semaine de l'éducation financière, « Mes questions d'argent ») | https://www.youtube.com/channel/UCiL5cr7kZyfhQmfspVPCdOQ |

## Ajoutées le 11/10/2026 (lots `database/data/ajouts/`, chaînes officielles 🟢)

Toutes figurent dans `chaines_de_confiance` (`config/gade.php`).

| Chaîne | Ayant droit | Ce qu'on y a pris | URL |
|---|---|---|---|
| KASSAV' | le groupe Kassav' | 10 concerts complets (1985 → 40 ans à l'Arena) | https://www.youtube.com/channel/UCFRsJSL9aFLh0fniWEoq9sQ |
| North Sea Jazz Archive | North Sea Jazz Festival | Kassav' au North Sea Jazz 2016 | https://www.youtube.com/channel/UCHH_fkg_q8fu-AdEzIczzYQ |
| ARTE Concert | ARTE | Gaël Faye au Louvre, Catherine Ringer chante les Rita Mitsouko | https://www.youtube.com/channel/UC-smeLB9AnOTeypr1YyjJ3A |
| Marrakech du rire | festival (Jamel Debbouze) | Galas 2011, 2012, 2018, 2019, 2022 | https://www.youtube.com/channel/UCZIrjm7b2VvKwrIdlp5j73g |
| Montreux Comedy | Montreux Comedy Festival | Gala « Le temps d'en rire » (2023) | https://www.youtube.com/channel/UCPHJfLg1VeNBsfzzTszpmmQ |
| Jamel TV | Jamel Debbouze | Jamel en scène (1999), Jamel Comedy Club au Casino de Paris (2007) | https://www.youtube.com/channel/UCStN50pL6nWeNlP3oyDTzYw |
| Gilles Saint-Louis | GSL, humoriste martiniquais | « Comme jamais » à La Cigale (version télé) | https://www.youtube.com/channel/UCprV8LHZ29grbCjvs8RWjTQ |
| Titeuf Saison 4 [Officiel] | chaîne officielle de la série | 10 compilations d'épisodes complets | https://www.youtube.com/channel/UCi1tP-Yo8zQhS9Jm-eWU2Sw |
| Lou! La Série [Officiel] | chaîne officielle de la série | 10 compilations d'épisodes complets | https://www.youtube.com/channel/UCvYqgo9dGBPVvaa4paVIhwQ |
| Bluey - Français Chaîne Officielle | Ludo Studio / BBC Studios | 3 compilations d'épisodes complets | https://www.youtube.com/channel/UCrGR1w7FATM8OiK_EzN3GAw |
| PETIT POILU OFFICIEL | chaîne officielle de la série | 8 compilations | https://www.youtube.com/channel/UCSJjzZmKpDFdUFKjgtdzp2A |
| Cartoon Network France | Cartoon Network | Gumball, saison 1 en compilation | https://www.youtube.com/channel/UCO-sJY43ksC5ir_Jf1YJDew |
| Faites entrer l'accusé - Officielle | France Télévisions / producteur | 16 numéros (public adulte) | https://www.youtube.com/channel/UCrjIfq-eWK9BTUhPgQ2IFFg |
| CRU | média indépendant français (depuis 2019, 267 k abonnés, non certifié) | 28 témoignages : Tout ce que…, Les 100, Les 10, Talks, 30 jours (**public adulte**) | https://www.youtube.com/channel/UCDVsRG3knGOYJ4iJn6vScWQ |
| LCJ Editions et Productions | LCJ (éditeur) | Les Jumeaux (Jean Lefebvre, 1991) | https://www.youtube.com/channel/UCNsz8N5hi7x6F8kKWqEMy7g |
| INA madelen | INA | Au théâtre ce soir ×2, 1er épisode de Commissaire Moulin | https://www.youtube.com/channel/UCpsq1ZAGtJYLZsSpN9AAENw |
| INA Culte | INA | Droit de réponse (1981), L'École des fans (1982) | https://www.youtube.com/channel/UCPjBZT0aJPgJ3e1wAftV6TQ |
| INA Reportages | INA / CAPA | Baumettes 2000, Gennevilliers 1990 | https://www.youtube.com/channel/UCinR2u6FaxYr-acnjUfT_nw |
| CANAL+ Sport | CANAL+ | Dembélé, la « nouvelle vie » de Zidane | https://www.youtube.com/channel/UC8ggH3zU61XO0nMskSQwZdA |
| RMC Sport | RMC Sport | Gaba, le nouveau monde | https://www.youtube.com/channel/UCR5V8BTvlkyYtw6T_9swNEw |
| BFMTV | BFMTV | Zidane, la fabrique d'une légende | https://www.youtube.com/channel/UCXwDLMDV86ldKoFVc_g8P0g |
| Un gars une fille, Officiel | chaîne officielle de la série | 18 compilations d'1 h environ | https://www.youtube.com/channel/UCYS9w8hrXKvq9iSeAhSjDDA |
| Scènes de ménages - M6+ | Groupe M6 | 99 compilations, saisons 1 à 7 | https://www.youtube.com/channel/UCgL4BYu0PhQqCbQiIqXs30Q |
| Fais pas ci, Fais pas ça | chaîne officielle (France Télévisions) | 7 compilations thématiques | https://www.youtube.com/channel/UCXnO_LAy-Lz2eKs5O8nNBkw |
| Samantha oups ! | chaîne officielle | 3 intégrales, Marrakech, 10 « au gîte » | https://www.youtube.com/channel/UC3HcGh7434WBFgGaSfMRH-A |
| SODA | chaîne officielle (M6) | Saisons 1 à 3 en compilations + 2 téléfilms (saison 4) | https://www.youtube.com/channel/UCye2vnvzJ0451SldydbNtIw |
| Plus Belle la Vie | France Télévisions / Telfrance | Saison 1 intégrale (260 épisodes) | https://www.youtube.com/channel/UCDq87sGhSDfAb_gQW2ouAzQ |
| Instant Saga | chaîne officielle des téléfilms et séries (certifiée) | La Calanque, Mary Lester, Cut (saison 1, 70 ép.), Alerte à Malibu VF (saisons 1-2) | https://www.youtube.com/channel/UCL5IikHOTwC9P9kuPIYvgTA |
| Génération Sitcoms | AB Productions (certifiée) | 11 sitcoms AB en intégrale (≈ 2 000 épisodes) | https://www.youtube.com/channel/UCVVubp6nL6vP8b6ls-Ybk-g |
| JLA Groupe | JLA Productions (producteur) | Les Vacances de l'amour, épisodes 100 à 123 | https://www.youtube.com/channel/UCbempPd_O_7wj0_CUy8HD-g |

Également utilisées (déjà dans la liste) : Films & Séries FranceTV Distribution (5 films), M6+ Films & Séries (2 romances), Échappées belles (4), Guyane la 1ère (Mars 17), INA Histoire (après-guerre 1946-1951, Alain Decaux), C'est pas sorcier (banques, monnaie, or), Hello Maestro (Il était une fois… la Vie), M6+ Mag (Franchise), ARTE Séries (Bron S2-S4, Meurtres à Sandhamn S2 et S5-S11), ARTE Évasion (Voyage en cuisine), Neoxi (Kerviel).

## Sources non officielles acceptées (exceptions 🟠)

| Chaîne | Statut | Contenu | URL |
|---|---|---|---|
| Malcolm Wagner | 🟠 **Non officielle, validée par N'jie le 11/10/2026** | *Ma famille d'abord* (My Wife and Kids) en VF : 56 épisodes des saisons 1 à 3. Ce sont des enregistrements de diffusion M6, recadrés dans un décor coloré. Ils sont en ligne depuis 2015. Les ayants droit (Touchstone/ABC, Disney) ne publient pas la série sur YouTube. | https://www.youtube.com/channel/UCUdgyzFgZotK6rVytgGmvtg |
| Stream TV - Films complets | 🟠 **Non officielle, validée par N'jie le 11/10/2026** | Commissaire Moulin (27 épisodes des saisons 5-8 ; le 1er épisode vient d'INA madelen, officiel), Brigade des mineurs (6), La Commune (8), Madame la proviseur (13), Frank Riva (6). Chaîne certifiée qui publie avec un code de producteur (PM, Tetra, TM) ; licence non prouvée. | https://www.youtube.com/channel/UCKqmnQFF2PgokPDdX-L2fwQ |
| HOVERMAN | 🟠 **Non officielle, validée par N'jie le 11/10/2026** | Les Cordier, juge et flic : pilote + saisons 2 à 14 (60 téléfilms). Chaîne de particulier active depuis 2011. | https://www.youtube.com/channel/UCSkMvoIlrM0s_KhUb0PIyyA |
| My Digital Channel | 🟠 **Non officielle, validée par N'jie le 11/10/2026** | 5 films de science-fiction indépendants en VF (2048, Rubikon, Saturne V, The Thaw, Le Dernier Résistant). Distributeur My Digital Company, droits non prouvés. | https://www.youtube.com/channel/UCZFGH22U_Iv6C5_zNdsxLXQ |
| Food Story | 🟠 **Non officielle, validée par N'jie le 11/10/2026** | Saveurs d'outre-mer (Martinique, Guyane) et Cuisine créole : ils bousculent la haute gastronomie. Chaîne certifiée de reportages, producteur non confirmé. | https://www.youtube.com/channel/UCzyN_JSs95aDgDFrfOxagEw |

Dans les données, ces programmes ont un `ayant_droit` qui commence par « Source non officielle, validée par N'jie le 11/10/2026 », `source_officielle: false` et `note_source`. La fiche affiche alors « Voir la chaîne » (et non « Voir la chaîne officielle »).

Ces chaînes **ne sont pas** dans la liste de confiance de `config/gade.php`. Ce sont des exceptions décidées par N'jie pour des programmes précis, pas des sources à réutiliser librement. Si les vidéos disparaissent, `gade:verifier` masquera les épisodes concernés. *Ma famille d'abord* : deux titres d'épisodes sont à confirmer (S2E4 et S3E22, mal numérotés par la chaîne).

## Repérées, pas encore utilisées (fiables)

| Chaîne | Intérêt | URL |
|---|---|---|
| Public Sénat | Documentaires et débats (beaucoup d'actualité politique : trier) | https://www.youtube.com/channel/UCTa1bPArjpvNMgSHAb_pflA |
| BFM Business | Émissions économiques (beaucoup d'actualité et de plateaux : trier ; éviter les émissions crypto et bourse au jour le jour) | https://www.youtube.com/channel/UCUsBMOIUl_ad6JUOC16DpmQ |
| Bpifrance | Webinaires pratiques pour créer, financer ou reprendre une entreprise | https://www.youtube.com/channel/UCZ1adoWtdSQGI79Uq9zaGzQ |
| slash (France Télévisions) | Web-séries dont **SKAM France** (épisodes intégraux, ~20-30 min) | https://www.youtube.com/channel/UCguxKRCUwOq97ouGaBMtrQQ |
| FRANCE 24 | Reportages et documentaires d'actualité | https://www.youtube.com/channel/UCCCPCZNChQdGa9EkATeye4g |
| TV5MONDE Info | Reportages | https://www.youtube.com/channel/UCzKH70qfN_yuXq3s91fdwmg |
| CNRS | Conférences et films scientifiques (peu de longs formats récents) | https://www.youtube.com/channel/UCRHBpvcASQs852H0t4mt1zA |
| INA Paris Vintage | Archives sur Paris | https://www.youtube.com/channel/UC8rLuu2Xi0aUvcbaZt6flRg |
| Collège de France | Cours et conférences (pour les curieux) | https://www.youtube.com/channel/UCzZiy3EANVAx7h2XYqXsVbw |

## Écartées (doute sur les droits)

Chaînes de « films complets » sans ayant droit identifié ou republiant le travail d'autres : *Stream TV* (sauf les 5 séries acceptées en 🟠 ci-dessus), *CINE FEEL*, *ICON TV – Polars*, *Best T.V Police*, *Cinéma Cinémas*, *Ciné Club – Films Complets*, *Boxoffice | …*, *Cap Nature* (réupload de documentaires ARTE), *Terra Nauta*, *Cultur'Îles*, *100% DOCS*, *Regards sur le monde*, *Voyages du bout du monde*, chaînes non officielles « Il était une fois… l'Homme » ou « Les cités d'or ytb 3.0 ». *imineo Documentaires* (plateforme VOD) : à vérifier avant usage.

## Section « Nouveaux riches » : critère de choix

Sous-titre : « Réussir, entreprendre, bien gérer son argent ». On y met des parcours d'entrepreneurs (Antilles-Guyane, Afrique, diaspora en priorité), des histoires de marques et de grandes fortunes, de l'éducation financière (budget, épargne, investir prudemment, immobilier) et l'état d'esprit (discipline, persévérance).
**Exclus d'office** : gourous de l'argent facile, trading « miracle », promotion de cryptomonnaies, vente pyramidale ou multiniveau, et toute vidéo qui vend une formation ou un coaching. Seules des sources sérieuses (diffuseurs, institutions comme la Banque de France, magazines d'enquête) sont retenues ; le contenu doit inspirer, pas faire miroiter.

## Demandes non satisfaites

- **Malcolm (Malcolm in the Middle, VF)** — refus confirmé par N'jie le 11/10/2026 — octobre 2026 : aucune chaîne officielle (M6, 6ter, M6+ Films & Séries, Disney/Fox France) ne publie les épisodes sur YouTube. Ne circulent que des réuploads non officiels (« luka gaming 2026 », « Florian », « vdm777 », « N-Movies-Games-Series », « Malcolm - In the Midle », « Menu Maxi Best Of ») : écartés. À revérifier lors des prochaines mises à jour.

## Points d'attention

- **ARTE** indique souvent une date de fin de droits : elle est saisie dans `disponible_jusqu_au` et affichée sur la fiche quand elle approche ; `gade:verifier` la signale 3 semaines avant.
- Certaines vidéos ARTE sont **non intégrables** (oEmbed 401) ou **soumises à une limite d'âge** : on les écarte.
- Les vidéos « en direct » (lives rediffusés) sont écartées.
- Les droits ARTE des saisons 2 à 4 de **Bron** se terminent aussi le **17/11/2026** (comme la saison 1) ; Meurtres à Sandhamn le 28/12/2026.
- **Écartées lors des ajouts du 11/10/2026** : « Faites entrer l'accusé – L'assassinat du petit Grégory » (`NFB4nDiR_l0`, limite d'âge : connexion requise), le pilote des Cordier (`6JrhazAlU3M`) et Les Garçons de la plage ép. 26 (`8wz25i565EQ`), eux aussi soumis à une limite d'âge ; doublons (« Kidnapping » de Commissaire Moulin publié deux fois, compilations Bluey identiques, épisodes Guadeloupe de Voyage en cuisine déjà au catalogue) ; compilations d'extraits de Gumball (pas des épisodes entiers) ; replay de match de L'Équipe (éphémère). Les 5 vidéos non intégrables de PROPOSITIONS-2026-10 restent exclues.
- Une vidéo marquée « conçue pour les enfants » n'est pas lisible par yt-dlp : on la contrôle par la page (état de lecture, `playableInEmbed`).
- Vérifier des centaines de vidéos d'affilée déclenche une limite de YouTube (429, « confirmez que vous n'êtes pas un robot ») : contrôler les longues séries par oEmbed, puis les pages lentement (2 requêtes à la fois, pause).
