<?php

// GADÉ — Sinéma lakay : réglages de la sélection.
// Après toute modification de ce fichier ou du .env : php artisan config:cache

return [
    'name' => 'GADÉ',
    'tagline' => 'Sinéma lakay',
    'description' => 'GADÉ, le sinéma lakay : une sélection soignée de films, séries et documentaires en français, publiés gratuitement sur YouTube par leurs ayants droit.',

    // Compte unique de la famille : lu par le seeder (le mot de passe est haché en base, jamais affiché)
    'compte' => [
        'email' => env('GADE_COMPTE_EMAIL', ''),
        'password' => env('GADE_COMPTE_PASSWORD', ''),
    ],

    // Profils « façon Netflix »
    'profils' => [
        'max' => 8,
        // Profils créés d'office par le seeder (le premier est le profil « mère », gestionnaire)
        'defaut' => [
            ['prenom' => "N'jie", 'avatar' => 'clap', 'couleur' => '#f2b53a', 'gestionnaire' => true],
            ['prenom' => "Jie'n", 'avatar' => 'manicou', 'couleur' => '#c8322b'],
            ['prenom' => 'Jade', 'avatar' => 'iguane', 'couleur' => '#2f9e6a'],
            ['prenom' => 'Capucine', 'avatar' => 'colibri', 'couleur' => '#e0679a'],
            ['prenom' => 'Christopher', 'avatar' => 'christophine', 'couleur' => '#4f8fd6'],
        ],
        // Couleurs d'accent proposées
        'couleurs' => ['#f2b53a', '#c8322b', '#2f9e6a', '#e0679a', '#4f8fd6', '#9b6ad6', '#e98a2c', '#3fb8b0'],
        'pin_essais' => 5,          // essais de PIN avant blocage
        'pin_blocage_minutes' => 15,
        // Rangées de l'accueil masquées pour un profil enfant (en plus du filtre « tout public »)
        'rangees_adultes' => ['policier', 'romance', 'nouveaux-riches', 'true-crime', 'cru'],
    ],

    // Coulisses : confirmation du PIN du profil gestionnaire valable (minutes)
    'coulisses_pin_minutes' => 120,

    'univers' => [
        'documentaires' => ['label' => 'Documentaires', 'court' => 'Docs', 'texte' => 'Nature, océans, sciences, histoire, société et voyages.'],
        'films' => ['label' => 'Films complets', 'court' => 'Films', 'texte' => 'Comédies, polars, drames et grandes histoires, en version intégrale.'],
        'series' => ['label' => 'Séries & web-séries', 'court' => 'Séries', 'texte' => 'Des épisodes complets, rangés par saison, avec passage automatique au suivant.'],
        'caraibes' => ['label' => 'Caraïbes & Antilles', 'court' => 'Caraïbes', 'texte' => 'Martinique, Guadeloupe, Guyane et toute la Caraïbe : culture, musique, histoire, reportages.'],
        'spectacles' => ['label' => 'Spectacles & concerts', 'court' => 'Scène', 'texte' => 'Humour, concerts et théâtre filmés en entier.'],
    ],

    'genres' => [
        'comedie' => 'Comédie',
        'policier' => 'Policier & thriller',
        'histoire' => 'Histoire & guerre',
        'aventure-nature' => 'Aventure & nature',
        'sciences' => 'Sciences',
        'societe' => 'Société',
        'voyage' => 'Voyages',
        'musique' => 'Musique',
        'drame' => 'Drame',
        'romance' => 'Romance',
        'nouveaux-riches' => 'Nouveaux riches',
        'sitcoms-90' => 'Sitcoms années 90',
        'concerts' => 'Concerts & zouk en live',
        'humour' => 'Humour & spectacles',
        'petits' => 'Pour les petits',
        'sf-fantastique' => 'Science-fiction & fantastique',
        'cuisine' => 'Cuisine',
        'sport' => 'Sport',
        'true-crime' => 'True crime',
        'cru' => 'CRU',
        'theatre-retro' => 'Théâtre & télé rétro',
    ],

    // Rangées des nouveaux genres sur l'accueil (titre, sous-titre, accroche créole + traduction).
    // « true-crime » et « cru » sont réservés aux adultes : programmes jamais « tout public », rangées masquées aux profils enfants.
    'rangees_genres' => [
        'sitcoms-90' => ['titre' => 'Sitcoms années 90', 'sous_titre' => 'Les séries AB de notre jeunesse, en intégrale',
            'creole' => 'Ou ka sonjé, nou té ka gadé sa apré lékòl ?', 'francais' => 'Tu te souviens ? On regardait ça après l’école.'],
        'concerts' => ['titre' => 'Zouk & biguine en live', 'sous_titre' => 'Concerts complets : Kassav’ et les grandes scènes',
            'creole' => 'Zouk-la sé sèl médikaman nou ni.', 'francais' => 'Le zouk, c’est le seul médicament qu’on ait.'],
        'humour' => ['titre' => 'Humour & spectacles', 'sous_titre' => 'Galas et one-man-shows en entier',
            'creole' => 'Lè ou ka ri, lavi ka bèl.', 'francais' => 'Quand on rit, la vie est belle.'],
        'petits' => ['titre' => 'Pour les petits', 'sous_titre' => 'Dessins animés en compilations officielles',
            'creole' => 'Ti moun sé lajwa lakay.', 'francais' => 'Les enfants, c’est la joie de la maison.'],
        'true-crime' => ['titre' => 'True crime', 'sous_titre' => 'Grandes affaires criminelles, racontées sérieusement · adultes',
            'creole' => 'Sa ki fèt an nwè ka parèt an gran jou.', 'francais' => 'Ce qui se fait dans l’ombre finit au grand jour.'],
        'cru' => ['titre' => 'CRU', 'sous_titre' => 'Témoignages bruts, vies vécues · adultes',
            'creole' => 'Pawòl vré, san fawdaj.', 'francais' => 'Des paroles vraies, sans maquillage.'],
        'theatre-retro' => ['titre' => 'Théâtre & télé rétro', 'sous_titre' => 'Au théâtre ce soir, archives cultes de l’INA',
            'creole' => 'Lontan sé té lontan… men nou ka sonjé.', 'francais' => 'Autrefois, c’était autrefois… mais on se souvient.'],
        'sf-fantastique' => ['titre' => 'Science-fiction & fantastique', 'sous_titre' => 'Mondes futurs et films d’anticipation',
            'creole' => 'Lòt bò syèl-la, ni dòt lèmonn.', 'francais' => 'De l’autre côté du ciel, il y a d’autres mondes.'],
        'cuisine' => ['titre' => 'Cuisine', 'sous_titre' => 'Saveurs créoles et cuisines du monde',
            'creole' => 'Lanmou ka pasé pa kannari.', 'francais' => 'L’amour passe par la marmite.'],
        'sport' => ['titre' => 'Sport', 'sous_titre' => 'Champions, parcours et légendes',
            'creole' => 'Fòs pa ka vann, sé travay ki ka ba’w li.', 'francais' => 'La force ne s’achète pas : c’est le travail qui la donne.'],
    ],

    // Section « Nouveaux riches » : inspiration, entrepreneuriat, éducation financière (jamais d'argent facile)
    'nouveaux_riches' => [
        'sous_titre' => 'Réussir, entreprendre, bien gérer son argent',
        'creole' => 'Richès pa ka tonbé an syèl : sé travay, pasians épi bon jesyon.',
        'francais' => 'La richesse ne tombe pas du ciel : c’est du travail, de la patience et une bonne gestion.',
    ],

    // Tranches de durée pour les filtres (en minutes)
    'durees' => [
        'court' => ['label' => 'Moins de 30 min', 'min' => 0, 'max' => 30],
        'moyen' => ['label' => '30 à 60 min', 'min' => 30, 'max' => 60],
        'long' => ['label' => '1 h à 2 h', 'min' => 60, 'max' => 120],
        'tres-long' => ['label' => 'Plus de 2 h', 'min' => 120, 'max' => null],
    ],

    // Chaînes officielles de confiance (identifiant => nom exact sur YouTube) — même liste que docs/SOURCES.md
    'chaines_de_confiance' => [
        'UCwI-JbGNsojunnHbFAc0M4Q' => 'ARTE',
        'UClo03hULFynpoX3w1Jv7fhw' => 'ARTE Cinema',
        'UCzaf-8cAEiXfynukcmV5MXw' => 'ARTE Séries',
        'UCXV7LUI7G1MaKRN18yQ5y7Q' => 'ARTE Évasion',
        'UCDEv1GHWqmyyinOSHf09__w' => 'Films & Séries - FranceTV Distribution',
        'UCwAl5jBlk6rdxVi9QdKWZqQ' => 'DOCS Voyage & Nature - FranceTV Distribution',
        'UCWaFdLxqxYscunr4P7uKxkw' => 'DOCS Histoire & Culture - FranceTV Distribution',
        'UCXUSLU65KYElvyrrbQA1KIw' => 'DOCS Enquêtes & Société - FranceTV Distribution',
        'UC-HX7z7qJlbuYvhTa3VhGKQ' => 'Echappées belles',
        'UCRWhwFtif1T5mpFokTUnu6g' => 'Des Racines et des Ailes - France Télévisions',
        'UCENv8pH4LkzvuSV_qHIcslg' => "C'est pas sorcier",
        'UCgA9V57lQlZFXkDfhVGTWSg' => 'Martinique la 1ère',
        'UCGp0ZbEM4j6RA4jufMgG3FA' => 'Guadeloupe la 1ère',
        'UCdijnb4OugCB6KUl2aN3qJQ' => 'Guyane la 1ère',
        'UCpaOmN-F1fiS19Qh9vI8gAw' => 'Outre-mer La 1ère',
        'UCJsZHPR1jqKu-soDmKNMBFg' => 'TV5MONDE',
        'UCFdEMYmiimq2uKmOMaBfPLg' => 'INA Histoire',
        'UCCTuFofVWD9uORhQqgn-d7Q' => 'INA Société',
        'UCnl6WhqDxlqN3BPTaZvC4UQ' => 'Ina Voyages',
        'UCHGFbA0KWBgf6gMbyUCZeCQ' => 'LCP - Assemblée nationale',
        'UCTa1bPArjpvNMgSHAb_pflA' => 'Public Sénat',
        'UCKJeH6nKtpmu-zHxbjacvHg' => 'M6+ Films & Séries',
        'UC1rMCFD6Sxnu66sJZd44-Yg' => 'Factoris Films Officiel',
        'UCdHXMcWxwZr2qQpdCCOcSkw' => 'Noob & Olydri',
        'UCMsbW8TRs5i3R8K7Z9fUIXw' => 'Il était une fois... (Hello Maestro)',
        'UC0Wy5l5KuDCd7j4s27POrKw' => "Les mystérieuses cités d'or - Officielle",
        'UCP46_MXP_WG_auH88FnfS1A' => 'Nota Bene',
        'UCVuMMUfqEI448VKbkpuNPHQ' => 'Histoires Crépues',
        'UCCCPCZNChQdGa9EkATeye4g' => 'FRANCE 24',
        'UCguxKRCUwOq97ouGaBMtrQQ' => 'slash',
        'UC_446tDNo7UckPX78hM0Nlg' => 'Neoxi',
        'UCcJ3zI6dI519TecN5nnsVOA' => 'M6+ Mag',
        'UCCauEjHzPXxGer8af3u4x5g' => 'Envoyé Spécial',
        'UCHCepbDwJELAO-68Kvl1NqA' => 'Contact-Entreprises Martinique',
        'UCiL5cr7kZyfhQmfspVPCdOQ' => 'Banque de France',
        'UCUsBMOIUl_ad6JUOC16DpmQ' => 'BFM Business',
        'UCZ1adoWtdSQGI79Uq9zaGzQ' => 'Bpifrance',
        'UCFRsJSL9aFLh0fniWEoq9sQ' => "KASSAV'",
        'UCHH_fkg_q8fu-AdEzIczzYQ' => 'North Sea Jazz Archive',
        'UC-smeLB9AnOTeypr1YyjJ3A' => 'ARTE Concert',
        'UCZIrjm7b2VvKwrIdlp5j73g' => 'Marrakech du rire',
        'UCPHJfLg1VeNBsfzzTszpmmQ' => 'Montreux Comedy',
        'UCStN50pL6nWeNlP3oyDTzYw' => 'Jamel TV',
        'UCprV8LHZ29grbCjvs8RWjTQ' => 'Gilles Saint-Louis',
        'UCi1tP-Yo8zQhS9Jm-eWU2Sw' => 'Titeuf Saison 4 [Officiel]',
        'UCvYqgo9dGBPVvaa4paVIhwQ' => 'Lou! La Série [Officiel]',
        'UCrGR1w7FATM8OiK_EzN3GAw' => 'Bluey - Français Chaîne Officielle',
        'UCSJjzZmKpDFdUFKjgtdzp2A' => 'PETIT POILU OFFICIEL',
        'UCO-sJY43ksC5ir_Jf1YJDew' => 'Cartoon Network France',
        'UCrjIfq-eWK9BTUhPgQ2IFFg' => 'Faites entrer l’accusé - Officielle',
        'UCDVsRG3knGOYJ4iJn6vScWQ' => 'CRU',
        'UCNsz8N5hi7x6F8kKWqEMy7g' => 'LCJ Editions et Productions',
        'UCpsq1ZAGtJYLZsSpN9AAENw' => 'INA madelen',
        'UCPjBZT0aJPgJ3e1wAftV6TQ' => 'INA Culte',
        'UC8ggH3zU61XO0nMskSQwZdA' => 'CANAL+ Sport',
        'UCR5V8BTvlkyYtw6T_9swNEw' => 'RMC Sport',
        'UCXwDLMDV86ldKoFVc_g8P0g' => 'BFMTV',
        'UCinR2u6FaxYr-acnjUfT_nw' => 'INA Reportages',
        'UCYS9w8hrXKvq9iSeAhSjDDA' => 'Un gars une fille | Officiel',
        'UCgL4BYu0PhQqCbQiIqXs30Q' => 'Scènes de ménages - M6+',
        'UCXnO_LAy-Lz2eKs5O8nNBkw' => 'Fais pas ci, Fais pas ça',
        'UC3HcGh7434WBFgGaSfMRH-A' => 'Samantha oups !',
        'UCye2vnvzJ0451SldydbNtIw' => 'SODA',
        'UCDq87sGhSDfAb_gQW2ouAzQ' => 'Plus Belle la Vie',
        'UCL5IikHOTwC9P9kuPIYvgTA' => 'Instant Saga',
        'UCVVubp6nL6vP8b6ls-Ybk-g' => 'Génération Sitcoms',
        'UCbempPd_O_7wj0_CUy8HD-g' => 'JLA Groupe',
    ],
];
