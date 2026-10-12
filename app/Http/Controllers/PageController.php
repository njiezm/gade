<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Profil;
use App\Models\Programme;
use App\Support\Catalogue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    public function accueil(Request $request): View
    {
        // Sans session : page publique minimale « accès famille »
        if (! $request->user()) {
            return view('auth.bienvenue');
        }
        $profil = Profil::actif();
        $tous = Programme::visible()->pourProfil($profil)->with('episodes')->tri()->get();

        $une = $tous->where('a_la_une', true)->sortByDesc('ordre')->values()->take(5);
        if ($une->isEmpty()) {
            $une = $tous->take(1);
        }

        $rangees = Catalogue::rangees($tous, $profil);
        $kontinye = Catalogue::kontinye($profil);
        $maListe = Catalogue::maListe($profil)->take(20);
        $dejaVu = Catalogue::dejaVu($profil);
        $nbProgrammes = $tous->count();

        return view('pages.accueil', compact('une', 'rangees', 'kontinye', 'maListe', 'dejaVu', 'nbProgrammes'));
    }

    public function parcourir(Request $request): View
    {
        $filtres = [
            'q' => trim((string) $request->query('q', '')),
            'univers' => $request->query('univers'),
            'genre' => $request->query('genre'),
            'duree' => $request->query('duree'),
            'famille' => $request->boolean('famille'),
            'tri' => $request->query('tri', 'selection'),
        ];

        $q = Programme::visible()->pourProfil()->with('episodes');

        if ($filtres['univers'] && array_key_exists($filtres['univers'], config('gade.univers'))) {
            $q->where('univers', $filtres['univers']);
        } else {
            $filtres['univers'] = null;
        }
        if ($filtres['genre'] && array_key_exists($filtres['genre'], config('gade.genres'))) {
            $q->genre($filtres['genre']);
        } else {
            $filtres['genre'] = null;
        }
        if ($filtres['duree'] && ($d = config('gade.durees.'.$filtres['duree']))) {
            $q->dureeEntre($d['min'], $d['max']);
        } else {
            $filtres['duree'] = null;
        }
        if ($filtres['famille']) {
            $q->where('tout_public', true);
        }
        if ($filtres['q'] !== '') {
            $mots = preg_split('/\s+/', Str::lower(Str::ascii($filtres['q'])));
            $programmes = $q->get()->filter(function (Programme $p) use ($mots) {
                $texte = Str::lower(Str::ascii($p->titre.' '.$p->description.' '.$p->chaine_nom.' '.implode(' ', $p->genresLabels()).' '.$p->universLabel()
                    .' '.$p->episodes->pluck('titre')->implode(' ')));

                return collect($mots)->every(fn ($m) => str_contains($texte, $m));
            });
        } else {
            $programmes = $q->get();
        }

        $programmes = match ($filtres['tri']) {
            'titre' => $programmes->sortBy(fn ($p) => Str::ascii($p->titre)),
            'duree' => $programmes->sortBy('duree_secondes'),
            'recents' => $programmes->sortByDesc('created_at'),
            default => $programmes->sortByDesc(fn ($p) => [(int) $p->a_la_une, $p->ordre, $p->created_at?->timestamp]),
        };

        $titre = $this->titreParcourir($filtres);

        return view('pages.parcourir', ['programmes' => $programmes->values(), 'filtres' => $filtres, 'titre' => $titre]);
    }

    private function titreParcourir(array $f): string
    {
        if ($f['q'] !== '') {
            return 'Résultats pour « '.$f['q'].' »';
        }
        $parts = [];
        if ($f['univers']) {
            $parts[] = config("gade.univers.{$f['univers']}.label");
        }
        if ($f['genre']) {
            $parts[] = config("gade.genres.{$f['genre']}");
        }
        if ($f['duree']) {
            $parts[] = config("gade.durees.{$f['duree']}.label");
        }
        if ($f['famille']) {
            $parts[] = 'En famille';
        }

        return $parts ? implode(' · ', $parts) : 'Toute la sélection';
    }

    public function programme(Programme $programme): View
    {
        abort_unless($programme->accessiblePour(), 404);
        $programme->load('episodes');
        $profil = Profil::actif();

        return view('pages.programme', [
            'p' => $programme,
            'reprise' => Catalogue::repriseFiche($profil, $programme),
            'dansListe' => $profil->aDansSaListe($programme),
            'saisons' => $programme->saisons(),
            'similaires' => $programme->similaires(),
        ]);
    }

    public function regarder(Programme $programme, ?string $episode = null): View
    {
        abort_unless($programme->accessiblePour(), 404);
        $profil = Profil::actif();
        $programme->load('episodes');

        $ep = null;
        $suivant = null;
        if ($programme->estSerie()) {
            $liste = $programme->episodesVisibles();
            abort_if($liste->isEmpty(), 404);
            $ep = $episode ? $liste->firstWhere('youtube_id', $episode) : $liste->first();
            abort_if(! $ep, 404);
            $idx = $liste->search(fn (Episode $e) => $e->id === $ep->id);
            $suivant = $liste->get($idx + 1);
            $videoId = $ep->youtube_id;
        } else {
            abort_if($episode !== null, 404);
            $videoId = $programme->youtube_id;
        }

        $memo = $profil->progressions()->where('programme_id', $programme->id)->where('episode', $ep?->youtube_id ?? '')->first();

        return view('pages.regarder', [
            'p' => $programme,
            'memo' => $memo?->estEnCours() ? $memo : null,
            'dansListe' => $profil->aDansSaListe($programme),
            'ep' => $ep,
            'suivant' => $suivant,
            'videoId' => $videoId,
            'saisons' => $programme->estSerie() ? $programme->saisons() : collect(),
            'similaires' => $programme->similaires(10),
        ]);
    }

    public function maListe(): View
    {
        $profil = Profil::actif();

        return view('pages.ma-liste', ['programmes' => Catalogue::maListe($profil), 'dejaVu' => Catalogue::dejaVu($profil)]);
    }

    public function aPropos(): View
    {
        $chaines = Programme::visible()->pourProfil()->whereNotNull('chaine_nom')->get()
            ->groupBy('chaine_nom')->map(fn ($g) => ['n' => $g->count(), 'url' => $g->first()->chaineUrl(), 'ayant_droit' => $g->first()->ayant_droit])
            ->sortByDesc('n');

        return view('pages.a-propos', compact('chaines'));
    }

    public function mentions(): View
    {
        return view('pages.mentions');
    }

    public function robots(): Response
    {
        // Site privé (accès famille) : aucun robot, aucun sitemap.
        $txt = "User-agent: *\nDisallow: /\n";

        return response($txt)->header('Content-Type', 'text/plain');
    }
}
