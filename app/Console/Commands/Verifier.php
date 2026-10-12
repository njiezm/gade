<?php

namespace App\Console\Commands;

use App\Models\Episode;
use App\Models\Programme;
use App\Models\Verification;
use App\Services\YouTube;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Revérifie chaque vidéo de la sélection via l'oEmbed public de YouTube.
 * 401/403/404 = non intégrable ou supprimée : la vidéo est masquée automatiquement.
 * Une vidéo masquée automatiquement qui redevient disponible est réaffichée.
 */
class Verifier extends Command
{
    protected $signature = 'gade:verifier
        {--programme= : un ou plusieurs programmes (slugs séparés par des virgules)}
        {--pause=150 : pause entre deux appels, en millisecondes}
        {--sans-masquer : produire le rapport sans rien modifier}';

    protected $description = 'Revérifie toutes les vidéos (oEmbed), masque celles devenues indisponibles et produit un rapport';

    private const RAISON_AUTO = 'Indisponible sur YouTube';

    public function handle(YouTube $yt): int
    {
        $q = Programme::query()->with('episodes')->orderBy('titre');
        if ($slug = $this->option('programme')) {
            $q->whereIn('slug', array_filter(array_map('trim', explode(',', $slug))));
        }
        $programmes = $q->get();
        if ($programmes->isEmpty()) {
            $this->warn('Aucun programme à vérifier.');

            return self::SUCCESS;
        }

        $modifier = ! $this->option('sans-masquer');
        $pause = max(0, (int) $this->option('pause')) * 1000;
        $stats = ['total' => 0, 'ok' => 0, 'indisponibles' => 0, 'erreurs' => 0];
        $lignesKo = [];
        $lignesRetour = [];
        $lignesErreur = [];
        $expirent = [];

        $verifier = function (string $id) use ($yt, $pause, &$stats) {
            $stats['total']++;
            $r = $yt->oembed($id);
            if ($pause) {
                usleep($pause);
            }
            match ($r['statut']) {
                'ok' => $stats['ok']++,
                'indisponible' => $stats['indisponibles']++,
                default => $stats['erreurs']++,
            };

            return $r;
        };

        $barre = $this->output->createProgressBar($programmes->sum(fn ($p) => $p->estSerie() ? $p->episodes->count() : 1));
        $barre->start();

        foreach ($programmes as $p) {
            if ($p->estSerie()) {
                $okSerie = 0;
                foreach ($p->episodes as $e) {
                    $r = $verifier($e->youtube_id);
                    $barre->advance();
                    $this->appliquer($e, $r, $modifier, "{$p->titre} — {$e->etiquette()} {$e->titre}", $lignesKo, $lignesRetour, $lignesErreur);
                    if ($r['statut'] !== 'indisponible') {
                        $okSerie++;
                    }
                    if ($e->disponible_jusqu_au && $e->disponible_jusqu_au->lte(now()->addDays(21))) {
                        $expirent[] = "{$p->titre} — {$e->etiquette()} : annoncé jusqu'au {$e->disponible_jusqu_au->format('d/m/Y')}";
                    }
                }
                // Une série dont plus aucun épisode n'est lisible est masquée elle aussi
                if ($modifier) {
                    if ($okSerie === 0 && $p->episodes->isNotEmpty()) {
                        $p->forceFill(['statut_verif' => 'indisponible', 'masque' => true, 'masque_raison' => self::RAISON_AUTO.' (aucun épisode lisible)'])->save();
                    } elseif ($p->statut_verif === 'indisponible' && str_starts_with((string) $p->masque_raison, self::RAISON_AUTO)) {
                        $p->forceFill(['statut_verif' => 'ok', 'masque' => false, 'masque_raison' => null])->save();
                    }
                    $p->forceFill(['verifie_le' => now()])->save();
                }
            } elseif ($p->youtube_id) {
                $r = $verifier($p->youtube_id);
                $barre->advance();
                $this->appliquer($p, $r, $modifier, $p->titre, $lignesKo, $lignesRetour, $lignesErreur);
                if ($p->disponible_jusqu_au && $p->disponible_jusqu_au->lte(now()->addDays(21))) {
                    $expirent[] = "{$p->titre} : annoncé jusqu'au {$p->disponible_jusqu_au->format('d/m/Y')}";
                }
            }
        }
        $barre->finish();
        $this->newLine(2);

        $rapport = $this->rapport($stats, $lignesKo, $lignesRetour, $lignesErreur, $expirent, $modifier);
        Verification::create($stats + ['rapport' => $rapport]);
        File::ensureDirectoryExists(storage_path('app/rapports'));
        $fichier = storage_path('app/rapports/verification-'.now()->format('Y-m-d-His').'.md');
        File::put($fichier, $rapport);

        $this->table(['Vidéos vérifiées', 'Disponibles', 'Indisponibles', 'Erreurs réseau'], [[$stats['total'], $stats['ok'], $stats['indisponibles'], $stats['erreurs']]]);
        foreach ($lignesKo as $l) {
            $this->line('  <fg=red>✗</> '.$l);
        }
        foreach ($lignesRetour as $l) {
            $this->line('  <fg=green>↺</> '.$l);
        }
        foreach ($lignesErreur as $l) {
            $this->line('  <fg=yellow>?</> '.$l);
        }
        $this->info('Rapport : '.$fichier);

        return self::SUCCESS;
    }

    private function appliquer(Programme|Episode $m, array $r, bool $modifier, string $nom, array &$ko, array &$retour, array &$erreurs): void
    {
        $id = $m->youtube_id;
        if ($r['statut'] === 'indisponible') {
            $ko[] = "{$nom} ({$id}) : oEmbed {$r['code']}".($modifier ? ' → masqué' : '');
            if ($modifier) {
                $m->forceFill(['statut_verif' => 'indisponible', 'masque' => true, 'verifie_le' => now()]
                    + ($m instanceof Programme ? ['masque_raison' => self::RAISON_AUTO." (oEmbed {$r['code']}, ".now()->format('d/m/Y').')'] : []))->save();
            }

            return;
        }
        if ($r['statut'] === 'ok') {
            if ($modifier) {
                $etait = $m->statut_verif === 'indisponible';
                $data = ['statut_verif' => 'ok', 'verifie_le' => now()];
                // On ne réaffiche que ce qui avait été masqué automatiquement
                if ($etait && ($m instanceof Episode || str_starts_with((string) $m->masque_raison, self::RAISON_AUTO))) {
                    $data['masque'] = false;
                    if ($m instanceof Programme) {
                        $data['masque_raison'] = null;
                    }
                    $retour[] = "{$nom} ({$id}) : de nouveau disponible → réaffiché";
                }
                $m->forceFill($data)->save();
            }

            return;
        }
        $erreurs[] = "{$nom} ({$id}) : pas de réponse exploitable (code {$r['code']}), rien n'est modifié";
    }

    private function rapport(array $s, array $ko, array $retour, array $erreurs, array $expirent, bool $modifier): string
    {
        $l = [];
        $l[] = '# Vérification GADÉ — '.now()->format('d/m/Y H:i');
        $l[] = '';
        $l[] = "- Vidéos vérifiées : {$s['total']}";
        $l[] = "- Disponibles : {$s['ok']}";
        $l[] = "- Indisponibles : {$s['indisponibles']}".($modifier ? ' (masquées automatiquement)' : ' (mode --sans-masquer : rien modifié)');
        $l[] = "- Erreurs réseau : {$s['erreurs']}";
        $sections = [
            'Devenues indisponibles' => $ko,
            'De nouveau disponibles' => $retour,
            'Sans réponse (à revérifier)' => $erreurs,
            'Fin de droits annoncée dans les 3 semaines' => $expirent,
        ];
        foreach ($sections as $titre => $items) {
            $l[] = '';
            $l[] = "## {$titre}";
            $l[] = $items ? implode("\n", array_map(fn ($x) => '- '.$x, $items)) : '- Rien à signaler.';
        }

        return implode("\n", $l)."\n";
    }
}
