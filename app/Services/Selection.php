<?php

namespace App\Services;

use App\Models\Episode;
use App\Models\Programme;
use App\Models\Proposition;
use Illuminate\Support\Str;
use RuntimeException;

/** Ajout de programmes et de propositions dans la sélection (coulisses et ligne de commande). */
class Selection
{
    public function __construct(private YouTube $yt) {}

    /** L'identifiant est-il déjà dans la sélection (programme, épisode ou proposition en attente) ? */
    public function dejaPresent(string $id): ?string
    {
        if ($p = Programme::where('youtube_id', $id)->first()) {
            return "déjà dans la sélection (« {$p->titre} »)";
        }
        if ($e = Episode::where('youtube_id', $id)->with('programme')->first()) {
            return "déjà dans la sélection (épisode de « {$e->programme?->titre} »)";
        }
        if (Proposition::enAttente()->where('youtube_id', $id)->exists()) {
            return 'déjà dans la file des propositions';
        }

        return null;
    }

    public static function chaineDeConfiance(?string $nom): bool
    {
        $n = self::normaliser($nom);

        return $n !== '' && collect(config('gade.chaines_de_confiance'))->contains(fn ($c) => self::normaliser($c) === $n);
    }

    private static function normaliser(?string $s): string
    {
        return preg_replace('/[^a-z0-9]/', '', Str::lower(Str::ascii((string) $s)));
    }

    /**
     * Dépose une proposition dans la file « à valider ».
     *
     * @throws RuntimeException si l'URL est invalide, la vidéo indisponible ou déjà présente
     */
    public function proposer(string $url, array $infos = [], string $source = 'claude'): Proposition
    {
        $id = YouTube::idDepuisUrl($url) ?? throw new RuntimeException('URL YouTube non reconnue.');
        if ($raison = $this->dejaPresent($id)) {
            throw new RuntimeException("Cette vidéo est {$raison}.");
        }
        $o = $this->yt->oembed($id);
        if ($o['statut'] === 'indisponible') {
            throw new RuntimeException("Vidéo non intégrable ou supprimée (oEmbed {$o['code']}) : on ne peut pas la proposer.");
        }
        if ($o['statut'] !== 'ok') {
            throw new RuntimeException('YouTube ne répond pas pour le moment, réessayez plus tard.');
        }

        $motif = trim((string) ($infos['motif'] ?? ''));
        if (! self::chaineDeConfiance($o['chaine_nom'])) {
            $motif = trim("⚠ Chaîne hors de la liste de confiance : vérifier qu'il s'agit bien de l'ayant droit. ".$motif);
        }

        return Proposition::create([
            'youtube_id' => $id,
            'url' => 'https://www.youtube.com/watch?v='.$id,
            'titre' => $infos['titre'] ?? $o['titre'],
            'chaine_nom' => $o['chaine_nom'],
            'chaine_url' => $o['chaine_url'],
            'vignette_url' => "https://i.ytimg.com/vi/{$id}/hqdefault.jpg",
            'univers' => $infos['univers'] ?? null,
            'genres' => $infos['genres'] ?? [],
            'tout_public' => (bool) ($infos['tout_public'] ?? false),
            'duree_secondes' => $infos['duree_secondes'] ?? null,
            'description' => $infos['description'] ?? null,
            'motif' => $motif ?: null,
            'source' => $source,
        ]);
    }

    /**
     * Crée un programme (vidéo unique) ou ajoute un épisode à une série existante.
     * $d : titre, univers, genres[], tout_public, duree_secondes, annee, description, chaine_nom, chaine_url,
     *      a_la_une, serie_id (optionnel), saison, numero
     */
    public function ajouter(string $id, array $d): Programme
    {
        $vignette = $this->yt->enregistrerVignette($id);

        if (! empty($d['serie_id'])) {
            $serie = Programme::findOrFail($d['serie_id']);
            $serie->episodes()->create([
                'saison' => (int) ($d['saison'] ?? 1),
                'numero' => (int) ($d['numero'] ?? ($serie->episodes()->max('numero') + 1)),
                'titre' => $d['titre'],
                'youtube_id' => $id,
                'duree_secondes' => $d['duree_secondes'] ?? null,
                'vignette' => $vignette,
                'description' => $d['description'] ?? null,
                'verifie_le' => now(),
            ]);
            $serie->touch();

            return $serie;
        }

        return Programme::create([
            'slug' => $this->slugUnique($d['titre']),
            'titre' => $d['titre'],
            'format' => 'unique',
            'univers' => $d['univers'],
            'genres' => array_values($d['genres'] ?? []),
            'tout_public' => (bool) ($d['tout_public'] ?? false),
            'youtube_id' => $id,
            'chaine_nom' => $d['chaine_nom'] ?? null,
            'chaine_url' => $d['chaine_url'] ?? null,
            'ayant_droit' => $d['ayant_droit'] ?? ($d['chaine_nom'] ?? null),
            'duree_secondes' => $d['duree_secondes'] ?? null,
            'annee' => $d['annee'] ?? null,
            'description' => $d['description'] ?? null,
            'vignette' => $vignette,
            'affiche' => $vignette,
            'a_la_une' => (bool) ($d['a_la_une'] ?? false),
            'ordre' => (int) ($d['ordre'] ?? 0),
            'statut_verif' => 'ok',
            'verifie_le' => now(),
        ]);
    }

    public function slugUnique(string $titre): string
    {
        $base = Str::slug(Str::limit($titre, 70, '')) ?: 'programme';
        $slug = $base;
        $i = 2;
        while (Programme::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
