<?php

namespace App\Services;

use App\Models\Episode;
use App\Models\Programme;
use App\Models\Proposition;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Synchronise le catalogue avec database/data/catalogue.json + database/data/ajouts/*.json (même format).
 *
 * - Upsert des programmes par slug (ou par ID de vidéo YouTube) et des épisodes par ID de vidéo.
 * - Ne supprime rien : un programme absent des fichiers reste tel quel.
 * - Ne touche jamais aux profils, positions, Ma liste, Déjà vu ni au compte.
 * - Programme ou épisode déjà en base : seuls les champs vides sont complétés, sauf les champs listés
 *   dans « maj » (true = tous les champs de contenu) ou avec l'option --maj. Les réglages des coulisses
 *   (à la une, ordre, masqué, statut de vérification) ne sont jamais écrasés.
 * - Un même slug présent dans plusieurs fichiers est fusionné : champs du dernier fichier, épisodes cumulés.
 */
class CatalogueSync
{
    /** Champs de contenu d'un programme, pilotés par les fichiers JSON. */
    public const CHAMPS_PROGRAMME = ['titre', 'format', 'univers', 'genres', 'tout_public', 'youtube_id', 'chaine_nom', 'chaine_id',
        'chaine_url', 'ayant_droit', 'duree_secondes', 'annee', 'description', 'vignette', 'affiche', 'disponible_jusqu_au', 'publie_le'];

    /** Réglages initiaux : utilisés à la création seulement (ensuite, ils appartiennent aux coulisses). */
    public const CHAMPS_CREATION = ['a_la_une', 'ordre'];

    public const CHAMPS_EPISODE = ['saison', 'numero', 'titre', 'duree_secondes', 'vignette', 'description', 'disponible_jusqu_au'];

    /** @var array<int,string> */
    public array $avertissements = [];

    /** @return array<int,string> fichiers lus, dans l'ordre (catalogue.json puis ajouts/ par nom) */
    public static function fichiers(?string $dossier = null): array
    {
        $dossier ??= database_path('data');
        $fichiers = File::exists($dossier.'/catalogue.json') ? [$dossier.'/catalogue.json'] : [];
        $ajouts = glob($dossier.'/ajouts/*.json') ?: [];
        sort($ajouts, SORT_NATURAL);

        return array_merge($fichiers, $ajouts);
    }

    /**
     * @return array{programmes_crees:int,programmes_completes:int,episodes_crees:int,episodes_completes:int,propositions_validees:int,fichiers:int,slugs_crees:array<int,string>}
     */
    public function synchroniser(?string $dossier = null, bool $maj = false, bool $simuler = false): array
    {
        $this->avertissements = [];
        $entrees = $this->lire(self::fichiers($dossier));
        $genres = array_keys((array) config('gade.genres'));
        $univers = array_keys((array) config('gade.univers'));

        $stats = ['programmes_crees' => 0, 'programmes_completes' => 0, 'episodes_crees' => 0, 'episodes_completes' => 0,
            'propositions_validees' => 0, 'fichiers' => count(self::fichiers($dossier)), 'slugs_crees' => []];

        // « Nouveautés » : les nouveaux programmes des derniers fichiers passent devant, puis par date de publication
        $nouveaux = collect($entrees)->filter(fn ($e) => ! $this->trouver($e))
            ->sortBy([fn ($a, $b) => $b['_fichier'] <=> $a['_fichier'], fn ($a, $b) => ($b['publie_le'] ?? '0000') <=> ($a['publie_le'] ?? '0000')])
            ->pluck('slug')->values()->flip();
        $maintenant = now();

        $travail = function () use ($entrees, $maj, $genres, $univers, $nouveaux, $maintenant, &$stats) {
            foreach ($entrees as $e) {
                $inconnus = array_diff($e['genres'] ?? [], $genres);
                if ($inconnus) {
                    $this->avertissements[] = "{$e['slug']} : genre(s) inconnu(s) dans config/gade.php : ".implode(', ', $inconnus);
                }
                if (isset($e['univers']) && ! in_array($e['univers'], $univers, true)) {
                    $this->avertissements[] = "{$e['slug']} : univers inconnu « {$e['univers']} »";
                }

                $verifie = Carbon::parse($e['_genere_le'] ?? 'now');
                $programme = $this->trouver($e);
                $forcer = $maj ? self::CHAMPS_PROGRAMME : ($e['maj'] === true ? self::CHAMPS_PROGRAMME : (array) ($e['maj'] ?? []));

                if (! $programme) {
                    if (empty($e['titre']) || empty($e['univers'])) {
                        $this->avertissements[] = "{$e['slug']} : titre ou univers manquant, programme ignoré";

                        continue;
                    }
                    $programme = new Programme(['slug' => $e['slug']]);
                    foreach (array_merge(self::CHAMPS_PROGRAMME, self::CHAMPS_CREATION) as $champ) {
                        if (array_key_exists($champ, $e)) {
                            $programme->{$champ} = $e[$champ];
                        }
                    }
                    $programme->format ??= empty($e['episodes']) ? 'unique' : 'serie';
                    $programme->genres ??= [];
                    $programme->statut_verif = 'ok';
                    $programme->verifie_le = $verifie;
                    $quand = $maintenant->copy()->subMinutes(($nouveaux[$e['slug']] ?? 0) * 7);
                    $programme->created_at = $quand;
                    $programme->updated_at = $quand;
                    $programme->save();
                    $stats['programmes_crees']++;
                    $stats['slugs_crees'][] = $programme->slug;
                } else {
                    $modifie = false;
                    foreach (self::CHAMPS_PROGRAMME as $champ) {
                        if (! array_key_exists($champ, $e)) {
                            continue;
                        }
                        $actuel = $programme->getAttribute($champ);
                        $vide = $actuel === null || $actuel === '' || $actuel === [];
                        if (($vide || in_array($champ, $forcer, true)) && $this->different($programme, $champ, $e[$champ])) {
                            $programme->{$champ} = $e[$champ];
                            $modifie = true;
                        }
                    }
                    if ($modifie) {
                        $programme->save();
                        $stats['programmes_completes']++;
                    }
                }

                $forcerEp = $maj || ($e['maj'] ?? null) === true || in_array('episodes', (array) ($e['maj'] ?? []), true);
                foreach ($e['episodes'] ?? [] as $ep) {
                    $r = $this->episode($programme, $ep, $forcerEp, $verifie);
                    if ($r) {
                        $stats[$r]++;
                    }
                }

                // Une proposition en attente pour la même vidéo est considérée comme validée
                $ids = array_filter(array_merge([$programme->youtube_id], array_column($e['episodes'] ?? [], 'youtube_id')));
                if ($ids) {
                    $stats['propositions_validees'] += Proposition::whereIn('youtube_id', $ids)->where('statut', 'en_attente')
                        ->update(['statut' => 'validee', 'traitee_le' => now(), 'programme_id' => $programme->id]);
                }
            }
        };

        if ($simuler) {
            DB::beginTransaction();
            try {
                $travail();
            } finally {
                DB::rollBack();
            }
        } else {
            DB::transaction($travail);
        }

        return $stats;
    }

    /** Lit et fusionne les fichiers (même slug = fusion, épisodes cumulés et dédoublonnés par ID). */
    private function lire(array $fichiers): array
    {
        $entrees = [];
        foreach ($fichiers as $i => $f) {
            $data = json_decode((string) file_get_contents($f), true);
            if (! is_array($data) || ! isset($data['programmes']) || ! is_array($data['programmes'])) {
                $this->avertissements[] = basename($f).' : JSON illisible ou sans clé « programmes », fichier ignoré';

                continue;
            }
            foreach ($data['programmes'] as $p) {
                if (empty($p['slug'])) {
                    $this->avertissements[] = basename($f).' : programme sans slug ignoré';

                    continue;
                }
                $p['_fichier'] = $i;
                $p['_genere_le'] = $data['genere_le'] ?? null;
                $p['maj'] ??= null;
                if (isset($entrees[$p['slug']])) {
                    $ancien = $entrees[$p['slug']];
                    $eps = collect(array_merge($ancien['episodes'] ?? [], $p['episodes'] ?? []))->keyBy('youtube_id')->values()->all();
                    $p = array_merge($ancien, $p, ['episodes' => $eps, '_fichier' => $ancien['_fichier']]);
                }
                $entrees[$p['slug']] = $p;
            }
        }

        return array_values($entrees);
    }

    private function trouver(array $e): ?Programme
    {
        $p = Programme::where('slug', $e['slug'])->first();
        if (! $p && ! empty($e['youtube_id'])) {
            $p = Programme::where('youtube_id', $e['youtube_id'])->first();
            if ($p) {
                $this->avertissements[] = "{$e['slug']} : vidéo {$e['youtube_id']} déjà au catalogue sous « {$p->slug} », programme complété";
            }
        }

        return $p;
    }

    private function different(Programme|Episode $m, string $champ, mixed $valeur): bool
    {
        $copie = $m->replicate();
        $copie->{$champ} = $valeur;

        return $copie->getAttributes()[$champ] !== $m->getAttributes()[$champ];
    }

    /** @return string|null clé de statistique */
    private function episode(Programme $programme, array $ep, bool $forcer, Carbon $verifie): ?string
    {
        if (empty($ep['youtube_id'])) {
            $this->avertissements[] = "{$programme->slug} : épisode sans youtube_id ignoré";

            return null;
        }
        $existant = Episode::where('youtube_id', $ep['youtube_id'])->first();
        if (! $existant) {
            if (Programme::where('youtube_id', $ep['youtube_id'])->exists()) {
                $this->avertissements[] = "{$programme->slug} : la vidéo {$ep['youtube_id']} est déjà un programme à part entière, épisode ignoré";

                return null;
            }
            $episode = new Episode(['youtube_id' => $ep['youtube_id'], 'saison' => 1, 'numero' => 1]);
            foreach (self::CHAMPS_EPISODE as $champ) {
                if (array_key_exists($champ, $ep)) {
                    $episode->{$champ} = $ep[$champ];
                }
            }
            $episode->titre ??= 'Épisode '.$episode->numero;
            $episode->statut_verif = 'ok';
            $episode->verifie_le = $verifie;
            $programme->episodes()->save($episode);

            return 'episodes_crees';
        }
        if ($existant->programme_id !== $programme->id) {
            $this->avertissements[] = "{$programme->slug} : l'épisode {$ep['youtube_id']} appartient déjà à un autre programme, ignoré";

            return null;
        }
        $modifie = false;
        foreach (self::CHAMPS_EPISODE as $champ) {
            if (! array_key_exists($champ, $ep)) {
                continue;
            }
            $actuel = $existant->getAttribute($champ);
            if (($forcer || $actuel === null || $actuel === '') && $this->different($existant, $champ, $ep[$champ])) {
                $existant->{$champ} = $ep[$champ];
                $modifie = true;
            }
        }
        if ($modifie) {
            $existant->save();

            return 'episodes_completes';
        }

        return null;
    }
}
