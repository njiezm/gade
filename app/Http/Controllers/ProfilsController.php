<?php

namespace App\Http\Controllers;

use App\Models\Profil;
use App\Support\Avatars;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** « Ki moun ka gadé ? » : choix du profil, PIN, gestion des profils (N'jie) et choix de l'avatar. */
class ProfilsController extends Controller
{
    // ---------------- Choisir un profil ----------------

    public function index(Request $request): View
    {
        return view('profils.choisir', [
            'profils' => $request->user()->profils()->get(),
            'gestionnaire' => $request->user()->profils()->where('gestionnaire', true)->first(),
        ]);
    }

    public function choisir(Request $request, Profil $profil): RedirectResponse
    {
        $this->duCompte($request, $profil);
        $suite = $request->input('suite') === 'gerer' && $profil->gestionnaire ? 'gerer' : null;

        if ($profil->aUnPin()) {
            $request->session()->put('profil_suite', $suite);

            return redirect()->route('profils.pin', $profil);
        }

        return $this->activer($request, $profil, $suite);
    }

    public function pin(Request $request, Profil $profil): View
    {
        $this->duCompte($request, $profil);
        abort_unless($profil->aUnPin(), 404);

        return view('profils.pin', [
            'profil' => $profil,
            'action' => route('profils.pin.verifier', $profil),
            'titre' => 'Code PIN de '.$profil->prenom,
            'retour' => route('profils'),
        ]);
    }

    public function verifierPin(Request $request, Profil $profil): RedirectResponse
    {
        $this->duCompte($request, $profil);
        $request->validate(['pin' => ['required', 'string', 'max:10']]);

        $resultat = $profil->verifierPin((string) $request->input('pin'));
        if ($resultat !== 'ok') {
            return back()->withErrors(['pin' => self::messagePin($profil, $resultat)]);
        }

        return $this->activer($request, $profil, $request->session()->pull('profil_suite'));
    }

    public static function messagePin(Profil $profil, string $resultat): string
    {
        if ($resultat === 'bloque') {
            $min = max(1, (int) ceil(now()->diffInSeconds($profil->pin_bloque_jusqua, true) / 60));

            return "Trop d’erreurs : ce profil est bloqué pendant {$min} min.";
        }
        $n = $profil->essaisRestants();

        return 'Code incorrect. Encore '.$n.' essai'.($n > 1 ? 's' : '').'.';
    }

    private function activer(Request $request, Profil $profil, ?string $suite): RedirectResponse
    {
        $request->session()->regenerate();
        $request->session()->forget('coulisses_pin');
        $request->session()->put('profil_id', $profil->id);

        return $suite === 'gerer'
            ? redirect()->route('profils.gerer')
            : redirect()->route('accueil')->with('status', 'Bonswa '.$profil->prenom.' ! Bon film.');
    }

    // ---------------- Gérer les profils (N'jie) ----------------

    public function gerer(Request $request): View
    {
        return view('profils.gerer', ['profils' => $request->user()->profils()->get(), 'max' => (int) config('gade.profils.max', 8)]);
    }

    public function creer(Request $request): View|RedirectResponse
    {
        if ($this->complet($request)) {
            return redirect()->route('profils.gerer')->withErrors(['profil' => 'Maximum '.config('gade.profils.max').' profils.']);
        }

        return view('profils.formulaire', ['profil' => new Profil(['couleur' => $this->couleurLibre($request)])]);
    }

    public function enregistrer(Request $request): RedirectResponse
    {
        if ($this->complet($request)) {
            return redirect()->route('profils.gerer')->withErrors(['profil' => 'Maximum '.config('gade.profils.max').' profils.']);
        }
        $d = $this->valider($request);
        $profil = new Profil(['prenom' => $d['prenom'], 'couleur' => $d['couleur'], 'enfant' => $request->boolean('enfant')]);
        $profil->user_id = $request->user()->id;
        $profil->avatar = $this->avatarLibre($request);
        $profil->ordre = (int) $request->user()->profils()->max('ordre') + 1;
        $profil->save();
        if (! empty($d['pin'])) {
            $profil->definirPin($d['pin']);
        }

        return redirect()->route('profils.avatar', $profil)->with('status', 'Profil '.$profil->prenom.' créé. Choisissez son avatar !');
    }

    public function modifier(Request $request, Profil $profil): View
    {
        $this->duCompte($request, $profil);

        return view('profils.formulaire', ['profil' => $profil]);
    }

    public function mettreAJour(Request $request, Profil $profil): RedirectResponse
    {
        $this->duCompte($request, $profil);
        $d = $this->valider($request);
        $profil->prenom = $d['prenom'];
        $profil->couleur = $d['couleur'];
        $profil->enfant = $profil->gestionnaire ? false : $request->boolean('enfant');
        $profil->save();

        if ($request->boolean('retirer_pin')) {
            $profil->definirPin(null);
        } elseif (! empty($d['pin'])) {
            $profil->definirPin($d['pin']);
        }

        return redirect()->route('profils.gerer')->with('status', 'Profil '.$profil->prenom.' enregistré.');
    }

    public function supprimer(Request $request, Profil $profil): RedirectResponse
    {
        $this->duCompte($request, $profil);
        if ($profil->gestionnaire) {
            return back()->withErrors(['profil' => 'Le profil de N’jie ne peut pas être supprimé.']);
        }
        $prenom = $profil->prenom;
        $profil->delete(); // liste et historique supprimés en cascade

        return redirect()->route('profils.gerer')->with('status', "Profil {$prenom} supprimé.");
    }

    // ---------------- Avatar (le sien, ou tous pour N'jie) ----------------

    public function avatar(Request $request, Profil $profil): View
    {
        $this->peutChangerAvatar($request, $profil);

        return view('profils.avatar', ['profil' => $profil, 'themes' => Avatars::themes()]);
    }

    public function changerAvatar(Request $request, Profil $profil): RedirectResponse
    {
        $this->peutChangerAvatar($request, $profil);
        $request->validate(['avatar' => ['required', 'string', Rule::in(array_keys(Avatars::tous()))]]);
        $profil->avatar = $request->input('avatar');
        $profil->save();

        $actif = Profil::actif();

        return redirect()->route($actif->gestionnaire && ! $actif->is($profil) ? 'profils.gerer' : 'accueil')
            ->with('status', 'Nouvel avatar pour '.$profil->prenom.' : '.Avatars::nom($profil->avatar).' !');
    }

    // ---------------- Outils ----------------

    private function duCompte(Request $request, Profil $profil): void
    {
        abort_unless((int) $profil->user_id === (int) $request->user()->id, 404);
    }

    private function peutChangerAvatar(Request $request, Profil $profil): void
    {
        $this->duCompte($request, $profil);
        $actif = Profil::actif();
        abort_unless($actif && ($actif->gestionnaire || $actif->is($profil)), 403, 'Vous ne pouvez changer que votre propre avatar.');
    }

    private function valider(Request $request): array
    {
        return $request->validate([
            'prenom' => ['required', 'string', 'max:20'],
            'couleur' => ['required', Rule::in(config('gade.profils.couleurs'))],
            'pin' => ['nullable', 'digits:4', 'confirmed'],
        ], [
            'pin.digits' => 'Le code PIN doit comporter 4 chiffres.',
            'pin.confirmed' => 'Les deux codes PIN ne correspondent pas.',
        ], ['prenom' => 'prénom']);
    }

    private function complet(Request $request): bool
    {
        return $request->user()->profils()->count() >= (int) config('gade.profils.max', 8);
    }

    private function couleurLibre(Request $request): string
    {
        $prises = $request->user()->profils()->pluck('couleur')->all();

        return collect(config('gade.profils.couleurs'))->first(fn ($c) => ! in_array($c, $prises, true)) ?? config('gade.profils.couleurs.0');
    }

    private function avatarLibre(Request $request): string
    {
        $pris = $request->user()->profils()->pluck('avatar')->all();
        $libres = array_values(array_diff(array_keys(Avatars::tous()), $pris));

        return $libres ? $libres[array_rand($libres)] : 'popcorn';
    }
}
