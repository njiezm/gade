<?php

namespace App\Http\Middleware;

use App\Models\Profil;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/** Exige un profil choisi (stocké en session, côté serveur) appartenant au compte connecté. */
class ProfilChoisi
{
    /** @param  string|null  $mode  « invite » : laisse passer les visiteurs non connectés (page publique « / »). */
    public function handle(Request $request, Closure $next, ?string $mode = null): Response
    {
        if ($mode === 'invite' && ! $request->user()) {
            return $next($request);
        }
        $id = $request->session()->get('profil_id');
        $profil = $id ? Profil::where('user_id', $request->user()?->id)->find($id) : null;

        if (! $profil) {
            $request->session()->forget('profil_id');
            if ($request->expectsJson() || ! $request->isMethod('GET')) {
                abort(409, 'Choisissez un profil.');
            }

            return redirect()->route('profils');
        }

        app()->instance('profil.actif', $profil);
        View::share('profilActif', $profil);

        return $next($request);
    }
}
