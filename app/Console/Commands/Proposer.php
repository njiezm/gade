<?php

namespace App\Console\Commands;

use App\Services\Selection;
use App\Support\Duree;
use Illuminate\Console\Command;
use RuntimeException;

/** Dépose une vidéo dans la file « Propositions à valider » des coulisses. */
class Proposer extends Command
{
    protected $signature = 'gade:proposer
        {url : URL (ou identifiant) de la vidéo YouTube}
        {--univers= : documentaires | films | series | caraibes | spectacles}
        {--genre=* : un ou plusieurs genres (voir config/gade.php : comedie, policier, histoire, humour, concerts, petits, cuisine, sport, true-crime, cru…)}
        {--tout-public : programme adapté à toute la famille}
        {--duree= : durée (ex. 1:32:10, 95 ou 1h35)}
        {--titre= : titre à afficher (sinon celui de YouTube)}
        {--description= : courte description}
        {--motif= : pourquoi cette proposition}
        {--source=claude : claude | cli}';

    protected $description = 'Ajoute une proposition (vérifiée par oEmbed) dans la file « Propositions à valider »';

    public function handle(Selection $selection): int
    {
        $univers = $this->option('univers');
        if ($univers && ! array_key_exists($univers, config('gade.univers'))) {
            $this->error('Univers inconnu : '.$univers);

            return self::INVALID;
        }
        $genres = array_values(array_intersect((array) $this->option('genre'), array_keys(config('gade.genres'))));

        try {
            $p = $selection->proposer($this->argument('url'), array_filter([
                'titre' => $this->option('titre'),
                'univers' => $univers,
                'genres' => $genres,
                'tout_public' => (bool) $this->option('tout-public'),
                'duree_secondes' => Duree::versSecondes($this->option('duree')),
                'description' => $this->option('description'),
                'motif' => $this->option('motif'),
            ], fn ($v) => $v !== null && $v !== ''), in_array($this->option('source'), ['claude', 'cli'], true) ? $this->option('source') : 'cli');
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Proposition n°{$p->id} ajoutée : « {$p->titre} » ({$p->chaine_nom}).");
        if ($p->motif && str_starts_with($p->motif, '⚠')) {
            $this->warn('Attention : chaîne hors de la liste de confiance (docs/SOURCES.md).');
        }
        $this->line('À valider dans les coulisses : '.route('coulisses.index').'#propositions');

        return self::SUCCESS;
    }
}
