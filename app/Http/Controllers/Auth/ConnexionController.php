<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Connexion au compte de la famille (e-mail + mot de passe), 5 essais par minute. */
class ConnexionController extends Controller
{
    public const ESSAIS = 5;

    public function formulaire(): View
    {
        return view('auth.connexion');
    }

    public function connecter(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'email' => ['required', 'string', 'email', 'max:190'],
            'password' => ['required', 'string', 'max:200'],
        ]);

        $cle = 'connexion:'.$request->ip();
        if (RateLimiter::tooManyAttempts($cle, self::ESSAIS)) {
            $s = RateLimiter::availableIn($cle);
            throw ValidationException::withMessages(['email' => "Trop d’essais. Réessayez dans {$s} secondes."])->status(429);
        }

        if (! Auth::attempt(['email' => mb_strtolower(trim($donnees['email'])), 'password' => $donnees['password']], $request->boolean('remember'))) {
            RateLimiter::hit($cle, 60);
            throw ValidationException::withMessages(['email' => 'E-mail ou mot de passe incorrect.']);
        }

        RateLimiter::clear($cle);
        $request->session()->regenerate();
        $request->session()->forget(['profil_id', 'coulisses_pin']);

        return redirect()->route('profils');
    }

    public function deconnecter(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('accueil')->with('status', 'À bientôt lakay !');
    }

    public function oublie(): View
    {
        return view('auth.oublie');
    }
}
