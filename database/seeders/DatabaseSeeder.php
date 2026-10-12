<?php

namespace Database\Seeders;

use App\Models\Episode;
use App\Models\Programme;
use App\Models\Proposition;
use App\Services\CatalogueSync;
use Illuminate\Database\Seeder;

/**
 * Remplit GADÉ avec la sélection vérifiée (database/data/catalogue.json + database/data/ajouts/*.json) :
 * vidéos intégrables (oEmbed 200), en français, longues, publiées par des chaînes officielles.
 * Les vignettes WebP correspondantes sont déjà dans public/vignettes et public/affiches.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Catalogue complet : catalogue.json + database/data/ajouts/*.json (même logique que « php artisan gade:catalogue:sync »)
        $sync = app(CatalogueSync::class);
        $stats = $sync->synchroniser();
        $this->command?->info("Catalogue : {$stats['programmes_crees']} programmes et {$stats['episodes_crees']} épisodes.");
        foreach ($sync->avertissements as $a) {
            $this->command?->warn('  ! '.$a);
        }

        // Quelques propositions de Claude en attente, pour montrer la file « à valider »
        $propositions = [
            ['G8YKHGtoU4A', 'République dominicaine, couleur Caraïbes', 'Echappées belles', 'https://www.youtube.com/channel/UC-HX7z7qJlbuYvhTa3VhGKQ', 'caraibes', ['voyage'], true, 5270,
                'Pour compléter la rangée Caraïbes avec notre grande voisine hispanophone.'],
            ['zVjwrbHrzhk', 'Ils ont fait Mars 17', 'Guyane la 1ère', 'https://www.youtube.com/channel/UCdijnb4OugCB6KUl2aN3qJQ', 'caraibes', ['societe', 'histoire'], false, 1630,
                "Documentaire sur le grand mouvement social guyanais de mars 2017 : un pan d'histoire récente de la Guyane."],
            ['CoVRXn8RAP4', "L'Archipel du Goulag, le courage de la vérité", 'LCP - Assemblée nationale', 'https://www.youtube.com/channel/UCHGFbA0KWBgf6gMbyUCZeCQ', 'documentaires', ['histoire'], false, 3205,
                'Complète « Goulag, une histoire soviétique » avec le portrait de Soljenitsyne.'],
            ['a8XZA0nit7M', 'La catastrophe de Tchernobyl', 'Nota Bene', 'https://www.youtube.com/channel/UCP46_MXP_WG_auH88FnfS1A', 'documentaires', ['histoire', 'sciences'], true, 1595,
                'Un format court (27 min) et très clair de Nota Bene, idéal pour la rangée « Moins de 30 min ».'],
        ];
        foreach ($propositions as [$id, $titre, $chaine, $url, $univers, $genres, $famille, $duree, $motif]) {
            // Déjà ajoutée au catalogue par un lot : plus rien à valider
            if (Programme::where('youtube_id', $id)->exists() || Episode::where('youtube_id', $id)->exists()) {
                continue;
            }
            Proposition::create([
                'youtube_id' => $id,
                'url' => 'https://www.youtube.com/watch?v='.$id,
                'titre' => $titre,
                'chaine_nom' => $chaine,
                'chaine_url' => $url,
                'vignette_url' => "https://i.ytimg.com/vi/{$id}/hqdefault.jpg",
                'univers' => $univers,
                'genres' => $genres,
                'tout_public' => $famille,
                'duree_secondes' => $duree,
                'motif' => $motif,
                'source' => 'claude',
            ]);
        }

        $this->call(FamilleSeeder::class);
    }
}
