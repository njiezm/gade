<?php

namespace App\Console\Commands;

use App\Services\CatalogueSync;
use Illuminate\Console\Command;

/**
 * Synchronise le catalogue (catalogue.json + ajouts/*.json) sans jamais toucher aux données de la famille.
 * Idempotente : la relancer ne crée rien de plus.
 */
class CatalogueSyncCommand extends Command
{
    protected $signature = 'gade:catalogue:sync
        {--maj : réécrire les champs de contenu depuis les fichiers (titres, descriptions…), sans toucher aux réglages des coulisses}
        {--simuler : tout calculer puis annuler (aucune écriture)}';

    protected $description = 'Ajoute ou complète programmes et épisodes depuis database/data/catalogue.json et database/data/ajouts/*.json (ne supprime rien)';

    public function handle(CatalogueSync $sync): int
    {
        $s = $sync->synchroniser(null, (bool) $this->option('maj'), (bool) $this->option('simuler'));

        $this->table(['Fichiers', 'Programmes créés', 'Programmes complétés', 'Épisodes créés', 'Épisodes complétés', 'Propositions validées'],
            [[$s['fichiers'], $s['programmes_crees'], $s['programmes_completes'], $s['episodes_crees'], $s['episodes_completes'], $s['propositions_validees']]]);
        if ($s['slugs_crees']) {
            $this->line('Nouveaux : '.implode(', ', $s['slugs_crees']));
        }
        foreach ($sync->avertissements as $a) {
            $this->warn('  ! '.$a);
        }
        if ($this->option('simuler')) {
            $this->info('Simulation : rien n\'a été écrit.');
        }

        return self::SUCCESS;
    }
}
