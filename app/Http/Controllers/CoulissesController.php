<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Profil;
use App\Models\Programme;
use App\Models\Proposition;
use App\Models\Verification;
use App\Services\Selection;
use App\Services\YouTube;
use App\Support\Duree;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Espace « Coulisses » : mettre à jour la sélection ensemble. */
class CoulissesController extends Controller
{
    public function __construct(private Selection $selection, private YouTube $yt) {}

    /** Entrée des coulisses : le PIN du profil de N'jie est redemandé (s'il en a un). */
    public function connexion(Request $request): View|RedirectResponse
    {
        $profil = Profil::actif();
        if (! $profil->aUnPin() || $this->pinConfirme($request)) {
            return redirect()->route('coulisses.index');
        }

        return view('profils.pin', [
            'profil' => $profil,
            'action' => route('coulisses.se-connecter'),
            'titre' => 'Entrer dans les coulisses',
            'sous_titre' => 'Dèyè rido-a… Confirmez votre code PIN.',
            'retour' => route('accueil'),
        ]);
    }

    public function seConnecter(Request $request): RedirectResponse
    {
        $request->validate(['pin' => ['required', 'string', 'max:10']]);
        $profil = Profil::actif();

        $resultat = $profil->verifierPin((string) $request->input('pin'));
        if ($resultat !== 'ok') {
            return back()->withErrors(['pin' => ProfilsController::messagePin($profil, $resultat)]);
        }

        $request->session()->regenerate();
        $request->session()->put('coulisses_pin', ['profil' => $profil->id, 'a' => now()->timestamp]);

        return redirect()->intended(route('coulisses.index'));
    }

    /** Quitter les coulisses (le PIN sera redemandé à la prochaine entrée). */
    public function seDeconnecter(Request $request): RedirectResponse
    {
        $request->session()->forget('coulisses_pin');

        return redirect()->route('accueil');
    }

    private function pinConfirme(Request $request): bool
    {
        $ok = $request->session()->get('coulisses_pin');

        return is_array($ok) && ($ok['profil'] ?? null) === Profil::actif()->id
            && ($ok['a'] ?? 0) > now()->subMinutes((int) config('gade.coulisses_pin_minutes', 120))->timestamp;
    }

    public function index(Request $request): View
    {
        $filtre = $request->query('voir', 'tous');
        $q = Programme::query()->withCount('episodes')->tri();
        match ($filtre) {
            'une' => $q->where('a_la_une', true),
            'masques' => $q->where(fn ($w) => $w->where('masque', true)->orWhere('statut_verif', 'indisponible')),
            'series' => $q->where('format', 'serie'),
            default => null,
        };
        if ($univers = $request->query('univers')) {
            $q->where('univers', $univers);
        }

        return view('coulisses.index', [
            'programmes' => $q->get(),
            'propositions' => Proposition::enAttente()->latest()->get(),
            'historique' => Proposition::where('statut', '!=', 'en_attente')->latest('traitee_le')->take(8)->get(),
            'derniere' => Verification::latest()->first(),
            'stats' => [
                'programmes' => Programme::count(),
                'visibles' => Programme::visible()->count(),
                'episodes' => Episode::count(),
                'une' => Programme::where('a_la_une', true)->count(),
                'masques' => Programme::where('masque', true)->count(),
            ],
            'filtre' => $filtre,
        ]);
    }

    /** Étape 1 : coller une URL -> fiche pré-remplie grâce à oEmbed. Peut aussi partir d'une proposition. */
    public function ajouter(Request $request): View|RedirectResponse
    {
        $proposition = $request->query('proposition') ? Proposition::enAttente()->find($request->query('proposition')) : null;
        $url = $proposition?->url ?? (string) $request->query('url', '');

        if ($url === '') {
            return redirect()->route('coulisses.index');
        }
        $id = YouTube::idDepuisUrl($url);
        if (! $id) {
            return redirect()->route('coulisses.index')->withErrors(['url' => 'Cette adresse ne ressemble pas à une vidéo YouTube.'])->withInput(['url' => $url]);
        }
        if (! $proposition && ($raison = $this->selection->dejaPresent($id))) {
            return redirect()->route('coulisses.index')->withErrors(['url' => "Cette vidéo est {$raison}."])->withInput(['url' => $url]);
        }

        $o = $this->yt->oembed($id);
        if ($o['statut'] === 'indisponible') {
            return redirect()->route('coulisses.index')->withErrors(['url' => "YouTube indique que cette vidéo n'est pas intégrable ou a été supprimée (code {$o['code']}). On ne peut pas l'ajouter."])->withInput(['url' => $url]);
        }

        return view('coulisses.ajouter', [
            'id' => $id,
            'o' => $o,
            'proposition' => $proposition,
            'confiance' => Selection::chaineDeConfiance($o['chaine_nom'] ?? $proposition?->chaine_nom),
            'series' => Programme::where('format', 'serie')->orderBy('titre')->get(['id', 'titre']),
        ]);
    }

    /** Étape 2 : enregistrer le programme (ou l'épisode). */
    public function enregistrer(Request $request): RedirectResponse
    {
        $d = $request->validate([
            'youtube_id' => ['required', 'regex:/^[A-Za-z0-9_-]{11}$/'],
            'titre' => ['required', 'string', 'max:200'],
            'serie_id' => ['nullable', 'exists:programmes,id'],
            'saison' => ['nullable', 'integer', 'min:1', 'max:99'],
            'numero' => ['nullable', 'integer', 'min:1', 'max:999'],
            'univers' => ['required_without:serie_id', 'nullable', Rule::in(array_keys(config('gade.univers')))],
            'genres' => ['array'],
            'genres.*' => [Rule::in(array_keys(config('gade.genres')))],
            'tout_public' => ['boolean'],
            'duree' => ['nullable', 'string', 'max:20'],
            'annee' => ['nullable', 'integer', 'min:1890', 'max:2100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'chaine_nom' => ['nullable', 'string', 'max:200'],
            'chaine_url' => ['nullable', 'url', 'max:255'],
            'a_la_une' => ['boolean'],
            'proposition_id' => ['nullable', 'exists:propositions,id'],
        ]);

        $proposition = ! empty($d['proposition_id']) ? Proposition::find($d['proposition_id']) : null;
        if ($raison = $this->selection->dejaPresent($d['youtube_id'])) {
            if (! ($proposition && str_contains($raison, 'propositions'))) {
                return back()->withErrors(['youtube_id' => "Cette vidéo est {$raison}."])->withInput();
            }
        }

        $duree = Duree::versSecondes($d['duree'] ?? null);
        if (($d['duree'] ?? '') !== '' && ! $duree) {
            return back()->withErrors(['duree' => 'Durée non comprise : écrivez par exemple 1:32:10, 95 ou 1h35.'])->withInput();
        }

        $programme = $this->selection->ajouter($d['youtube_id'], [
            'titre' => $d['titre'],
            'serie_id' => $d['serie_id'] ?? null,
            'saison' => $d['saison'] ?? 1,
            'numero' => $d['numero'] ?? null,
            'univers' => $d['univers'] ?? null,
            'genres' => $d['genres'] ?? [],
            'tout_public' => $request->boolean('tout_public'),
            'duree_secondes' => $duree,
            'annee' => $d['annee'] ?? null,
            'description' => $d['description'] ?? null,
            'chaine_nom' => $d['chaine_nom'] ?? null,
            'chaine_url' => $d['chaine_url'] ?? null,
            'a_la_une' => $request->boolean('a_la_une'),
        ]);

        if ($proposition) {
            $proposition->update(['statut' => 'validee', 'traitee_le' => now(), 'programme_id' => $programme->id]);
        }

        return redirect()->route('coulisses.index')->with('status', empty($d['serie_id'])
            ? "« {$programme->titre} » est maintenant dans la sélection."
            : "Épisode ajouté à « {$programme->titre} ».");
    }

    public function modifier(Programme $programme): View
    {
        $programme->load('episodes');

        return view('coulisses.modifier', ['p' => $programme]);
    }

    public function mettreAJour(Request $request, Programme $programme): RedirectResponse
    {
        $d = $request->validate([
            'titre' => ['required', 'string', 'max:200'],
            'univers' => ['required', Rule::in(array_keys(config('gade.univers')))],
            'genres' => ['array'],
            'genres.*' => [Rule::in(array_keys(config('gade.genres')))],
            'duree' => ['nullable', 'string', 'max:20'],
            'annee' => ['nullable', 'integer', 'min:1890', 'max:2100'],
            'ordre' => ['nullable', 'integer', 'min:-999', 'max:999'],
            'description' => ['nullable', 'string', 'max:2000'],
            'masque_raison' => ['nullable', 'string', 'max:200'],
        ]);
        $duree = Duree::versSecondes($d['duree'] ?? null);

        $programme->update([
            'titre' => $d['titre'],
            'univers' => $d['univers'],
            'genres' => array_values($d['genres'] ?? []),
            'tout_public' => $request->boolean('tout_public'),
            'duree_secondes' => $duree ?? $programme->duree_secondes,
            'annee' => $d['annee'] ?? null,
            'ordre' => (int) ($d['ordre'] ?? 0),
            'description' => $d['description'] ?? null,
            'a_la_une' => $request->boolean('a_la_une'),
            'masque' => $request->boolean('masque'),
            'masque_raison' => $request->boolean('masque') ? ($d['masque_raison'] ?? 'Masqué dans les coulisses') : null,
        ]);

        return redirect()->route('coulisses.index')->with('status', "« {$programme->titre} » a été mis à jour.");
    }

    public function basculerUne(Programme $programme): RedirectResponse
    {
        $programme->update(['a_la_une' => ! $programme->a_la_une]);

        return back()->with('status', $programme->a_la_une ? "« {$programme->titre} » passe à la une." : "« {$programme->titre} » n'est plus à la une.");
    }

    public function basculerMasque(Programme $programme): RedirectResponse
    {
        $masque = ! $programme->masque;
        $programme->update(['masque' => $masque, 'masque_raison' => $masque ? 'Masqué dans les coulisses' : null]);

        return back()->with('status', $masque ? "« {$programme->titre} » est masqué." : "« {$programme->titre} » est de nouveau visible.");
    }

    /** Classer : monter / descendre dans la sélection (ordre plus grand = plus haut). */
    public function classer(Request $request, Programme $programme): RedirectResponse
    {
        $sens = $request->input('sens') === 'bas' ? -1 : 1;
        $programme->update(['ordre' => max(-999, min(999, $programme->ordre + $sens * 10))]);

        return back()->with('status', "Ordre de « {$programme->titre} » : {$programme->ordre}.");
    }

    public function verifierUn(Programme $programme): RedirectResponse
    {
        \Illuminate\Support\Facades\Artisan::call('gade:verifier', ['--programme' => $programme->slug, '--pause' => 0]);
        $programme->refresh();

        return back()->with('status', $programme->statut_verif === 'indisponible'
            ? "« {$programme->titre} » n'est plus disponible sur YouTube : il a été masqué."
            : "« {$programme->titre} » : vidéo(s) disponible(s), vérifié à l'instant.");
    }

    public function proposer(Request $request): RedirectResponse
    {
        $request->validate(['url' => ['required', 'string', 'max:300'], 'motif' => ['nullable', 'string', 'max:500']]);
        try {
            $p = $this->selection->proposer($request->input('url'), ['motif' => $request->input('motif')], 'coulisses');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['proposition' => $e->getMessage()])->withInput();
        }

        return back()->with('status', "« {$p->titre} » est dans la file des propositions.");
    }

    public function refuser(Proposition $proposition): RedirectResponse
    {
        $proposition->update(['statut' => 'refusee', 'traitee_le' => now()]);

        return back()->with('status', "Proposition « {$proposition->titre} » refusée.");
    }
}
