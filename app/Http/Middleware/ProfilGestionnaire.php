<?php

namespace App\Http\Middleware;

use App\Models\Profil;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Réservé au profil gestionnaire (N'jie). À placer après ProfilChoisi. */
class ProfilGestionnaire
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(Profil::actif()?->gestionnaire, 403, 'Réservé au profil de N’jie.');

        return $next($request);
    }
}
