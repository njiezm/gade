<?php

namespace App\Http\Middleware;

use App\Models\Profil;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Coulisses : réservées au profil gestionnaire (N'jie), compte connecté.
 * Si ce profil a un PIN, il est redemandé à l'entrée des coulisses (confirmation valable quelques heures).
 */
class CoulissesAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $profil = Profil::actif();
        abort_unless($profil?->gestionnaire, 403, 'Les coulisses sont réservées au profil de N’jie.');

        if ($profil->aUnPin()) {
            $ok = $request->session()->get('coulisses_pin');
            $valide = is_array($ok) && ($ok['profil'] ?? null) === $profil->id
                && ($ok['a'] ?? 0) > now()->subMinutes((int) config('gade.coulisses_pin_minutes', 120))->timestamp;
            if (! $valide) {
                return redirect()->guest(route('coulisses.connexion'));
            }
        }

        return $next($request);
    }
}
