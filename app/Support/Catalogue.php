<?php

namespace App\Support;

use App\Models\Episode;
use App\Models\Profil;
use App\Models\Programme;
use App\Models\Progression;
use Illuminate\Support\Collection;

class Catalogue
{
    /**
     * Rangées de l'accueil, façon plateforme.
     *
     * @param  Collection<int,Programme>  $tous  programmes visibles, déjà triés
     * @return array<int,array{id:string,titre:string,sous_titre:?string,lien:string,items:Collection}>
     */
    public static function rangees(Collection $tous, ?Profil $profil = null): array
    {
        $genre = fn (string $g) => $tous->filter(fn (Programme $p) => in_array($g, $p->genres ?? [], true));
        $univers = fn (string $u) => $tous->where('univers', $u);
        // Rangées des nouveaux genres (config/gade.php → rangees_genres), avec accroche créole
        $rg = function (string $g, ?callable $tri = null) use ($genre) {
            $c = (array) config("gade.rangees_genres.{$g}");
            $items = $genre($g);

            return ['id' => $g, 'titre' => $c['titre'] ?? config("gade.genres.{$g}", $g), 'sous_titre' => $c['sous_titre'] ?? null,
                'lien' => route('parcourir', ['genre' => $g]), 'items' => $tri ? $items->sortByDesc($tri) : $items,
                'accroche' => $c['creole'] ?? null, 'accroche_fr' => $c['francais'] ?? null];
        };

        $rangees = [
            ['id' => 'nouveautes', 'titre' => 'Nouveautés de la sélection', 'sous_titre' => 'Fraîchement ajoutés à GADÉ', 'lien' => route('parcourir', ['tri' => 'recents']),
                'items' => $tous->sortByDesc('created_at')->take(16)],
            ['id' => 'policier', 'titre' => 'Policier & thriller', 'sous_titre' => 'Enquêtes, suspense et commissaires', 'lien' => route('parcourir', ['genre' => 'policier']),
                'items' => $genre('policier')],
            ['id' => 'caraibes', 'titre' => 'Caraïbes & Antilles', 'sous_titre' => 'Lakay nou, et toute la Caraïbe', 'lien' => route('parcourir', ['univers' => 'caraibes']),
                'items' => $univers('caraibes'), 'madras' => true],
            $rg('sitcoms-90'),
            $rg('concerts', fn ($p) => $p->univers === 'caraibes' ? 1 : 0) + ['madras' => true],
            $rg('humour'),
            ['id' => 'nouveaux-riches', 'titre' => 'Nouveaux riches', 'sous_titre' => config('gade.nouveaux_riches.sous_titre'), 'lien' => route('parcourir', ['genre' => 'nouveaux-riches']),
                'items' => $genre('nouveaux-riches')->sortByDesc(fn ($p) => $p->univers === 'caraibes' ? 1 : 0), 'theme' => 'richesse',
                'accroche' => config('gade.nouveaux_riches.creole'), 'accroche_fr' => config('gade.nouveaux_riches.francais')],
            ['id' => 'comedies', 'titre' => 'Comédies', 'sous_titre' => 'Pour rire ensemble sur le canapé', 'lien' => route('parcourir', ['genre' => 'comedie']),
                'items' => $genre('comedie')],
            $rg('petits'),
            ['id' => 'romance', 'titre' => 'Romance', 'sous_titre' => 'Histoires d’amour et comédies romantiques, pour un soir à deux', 'lien' => route('parcourir', ['genre' => 'romance']),
                'items' => $genre('romance')],
            $rg('true-crime'),
            $rg('cru'),
            ['id' => 'nature', 'titre' => 'Documentaires nature', 'sous_titre' => 'Océans, volcans, forêts et animaux', 'lien' => route('parcourir', ['univers' => 'documentaires', 'genre' => 'aventure-nature']),
                'items' => $tous->filter(fn (Programme $p) => in_array($p->univers, ['documentaires', 'caraibes'], true) && in_array('aventure-nature', $p->genres ?? [], true))],
            ['id' => 'series', 'titre' => 'Séries', 'sous_titre' => 'Des épisodes complets, saison par saison', 'lien' => route('parcourir', ['univers' => 'series']),
                'items' => $univers('series')],
            ['id' => 'histoire', 'titre' => 'Histoire', 'sous_titre' => "Grandes époques, guerres et mémoires", 'lien' => route('parcourir', ['genre' => 'histoire']),
                'items' => $genre('histoire')],
            $rg('theatre-retro'),
            ['id' => 'famille', 'titre' => 'En famille', 'sous_titre' => 'Pour tous les âges, sans mauvaise surprise', 'lien' => route('parcourir', ['famille' => 1]),
                'items' => $tous->where('tout_public', true)->sortByDesc(fn ($p) => [$p->univers === 'series' ? 1 : 0, $p->ordre])],
            ['id' => 'films', 'titre' => 'Films complets', 'sous_titre' => 'Un film entier, ce soir', 'lien' => route('parcourir', ['univers' => 'films']),
                'items' => $univers('films')],
            $rg('sf-fantastique'),
            ['id' => 'court', 'titre' => 'Moins de 30 min', 'sous_titre' => 'Le temps d’un café… ou avant de dormir', 'lien' => route('parcourir', ['duree' => 'court']),
                'items' => $tous->filter(fn (Programme $p) => $p->duree_secondes && $p->duree_secondes < 1800)],
            ['id' => 'sciences', 'titre' => 'Sciences & découvertes', 'sous_titre' => 'Comprendre le monde, sans prise de tête', 'lien' => route('parcourir', ['genre' => 'sciences']),
                'items' => $genre('sciences')],
            ['id' => 'voyages', 'titre' => 'Voyages', 'sous_titre' => 'Partir sans quitter le salon', 'lien' => route('parcourir', ['genre' => 'voyage']),
                'items' => $genre('voyage')],
            $rg('cuisine'),
            $rg('sport'),
        ];

        // Profil enfant : on masque aussi les rangées « adultes » (le catalogue est déjà limité au tout public)
        $masquees = $profil?->enfant ? (array) config('gade.profils.rangees_adultes', []) : [];

        return array_values(array_filter(array_map(function ($r) {
            $r['items'] = $r['items']->values()->take(20);

            return $r;
        }, $rangees), fn ($r) => $r['items']->isNotEmpty() && ! in_array($r['id'], $masquees, true)));
    }

    // ---------------- Données du profil ----------------

    public static function temps(int $s): string
    {
        $h = intdiv($s, 3600);
        $m = intdiv($s % 3600, 60);
        $sec = str_pad((string) ($s % 60), 2, '0', STR_PAD_LEFT);

        return $h ? $h.':'.str_pad((string) $m, 2, '0', STR_PAD_LEFT).':'.$sec : $m.':'.$sec;
    }

    /**
     * « Kontinyé gadé » : programmes en cours (reprise à la seconde près) ou épisode suivant d'une série.
     *
     * @return Collection<int,array{p:Programme,url:string,vignette:string,meta:string,pourcent:int}>
     */
    public static function kontinye(Profil $profil, int $max = 14): Collection
    {
        $items = collect();
        $vus = [];
        $progs = $profil->progressions()->with('programme.episodes')->orderByDesc('updated_at')->take(150)->get();
        foreach ($progs as $pr) {
            $p = $pr->programme;
            if (! $p || isset($vus[$p->id]) || ! $p->accessiblePour($profil)) {
                continue;
            }
            $vus[$p->id] = true;
            $ep = $pr->episode !== '' ? $p->episodes->firstWhere('youtube_id', $pr->episode) : null;
            if ($pr->episode !== '' && (! $ep || $ep->masque)) {
                continue;
            }
            if ($pr->estEnCours()) {
                $items->push([
                    'p' => $p, 'url' => $p->urlLecture($ep).'?t='.$pr->position,
                    'vignette' => $ep ? $ep->vignetteUrl() : $p->vignetteUrl(),
                    'meta' => $ep ? $ep->etiquette().' · reprendre à '.self::temps($pr->position) : 'Reprendre à '.self::temps($pr->position),
                    'pourcent' => $pr->pourcent(),
                ]);
            } elseif ($pr->estTermine() && $ep) {
                $liste = $p->episodesVisibles()->values();
                $i = $liste->search(fn (Episode $e) => $e->id === $ep->id);
                $suivant = $i === false ? null : $liste->get($i + 1);
                if ($suivant) {
                    $items->push([
                        'p' => $p, 'url' => $p->urlLecture($suivant), 'vignette' => $suivant->vignetteUrl(),
                        'meta' => 'À suivre : '.$suivant->etiquette().' · '.$suivant->titre, 'pourcent' => 0,
                    ]);
                }
            }
            if ($items->count() >= $max) {
                break;
            }
        }

        return $items;
    }

    /** « Ma liste » du profil, du plus récent au plus ancien ajout. */
    public static function maListe(Profil $profil): Collection
    {
        return $profil->liste()->visible()->pourProfil($profil)->with('episodes')->orderByPivot('created_at', 'desc')->get();
    }

    /** « Déjà vu » : programmes (ou épisodes) regardés jusqu'au bout. */
    public static function dejaVu(Profil $profil, int $max = 20): Collection
    {
        return $profil->progressions()->whereNotNull('termine_le')->with('programme.episodes')->orderByDesc('termine_le')->get()
            ->pluck('programme')->filter(fn ($p) => $p && $p->accessiblePour($profil))->unique('id')->take($max)->values();
    }

    /**
     * Reprise sur une fiche : bouton principal et barres de progression des épisodes.
     *
     * @return array{bouton:?array{href:string,label:string},episodes:array<string,int>}
     */
    public static function repriseFiche(Profil $profil, Programme $p): array
    {
        $progs = $profil->progressions()->where('programme_id', $p->id)->orderByDesc('updated_at')->get();
        $episodes = $progs->filter(fn (Progression $x) => $x->episode !== '')->mapWithKeys(fn (Progression $x) => [$x->episode => $x->pourcent()])->all();
        $dernier = $progs->first();
        $bouton = null;
        if ($dernier) {
            $ep = $dernier->episode !== '' ? $p->episodesVisibles()->firstWhere('youtube_id', $dernier->episode) : null;
            if ($dernier->estEnCours() && ($ep || $dernier->episode === '')) {
                $bouton = ['href' => $p->urlLecture($ep).'?t='.$dernier->position,
                    'label' => 'Kontinyé gadé'.($ep ? ' · '.$ep->etiquette() : '').' ('.self::temps($dernier->position).')'];
            } elseif ($dernier->estTermine() && $ep) {
                $liste = $p->episodesVisibles()->values();
                $i = $liste->search(fn (Episode $e) => $e->id === $ep->id);
                if ($i !== false && ($suivant = $liste->get($i + 1))) {
                    $bouton = ['href' => $p->urlLecture($suivant), 'label' => 'Épisode suivant · '.$suivant->etiquette()];
                }
            }
        }

        return ['bouton' => $bouton, 'episodes' => $episodes];
    }
}
