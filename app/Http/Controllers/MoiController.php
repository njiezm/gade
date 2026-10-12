<?php

namespace App\Http\Controllers;

use App\Models\Profil;
use App\Models\Programme;
use App\Models\Progression;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Données du profil actif : position de lecture (Kontinyé gadé, Déjà vu) et Ma liste.
 * Toujours le profil de la session : un profil ne peut ni lire ni modifier les données d'un autre.
 */
class MoiController extends Controller
{
    /** Le lecteur envoie la position toutes les 10 s (fetch) et à la fermeture (sendBeacon). */
    public function progression(Request $request): JsonResponse
    {
        $d = $request->validate([
            'slug' => ['required', 'string', 'max:190'],
            'ep' => ['nullable', 'string', 'regex:/^[A-Za-z0-9_-]{11}$/'],
            't' => ['required', 'numeric', 'min:0', 'max:86400'],
            'd' => ['required', 'numeric', 'min:0', 'max:86400'],
        ]);
        $profil = Profil::actif();
        $programme = Programme::where('slug', $d['slug'])->first();
        abort_unless($programme && $programme->accessiblePour($profil), 404);
        $ep = $this->episodeValide($programme, $d['ep'] ?? null);

        if ((int) $d['d'] < 60) {
            return response()->json(['ok' => false, 'raison' => 'trop court']);
        }
        $prog = Progression::enregistrer($profil, $programme, $ep, (int) $d['t'], (int) $d['d']);

        return response()->json(['ok' => true, 'position' => $prog->position, 'termine' => $prog->estTermine()]);
    }

    /** Ajoute ou retire un programme de Ma liste. */
    public function basculerListe(Programme $programme): JsonResponse
    {
        $profil = Profil::actif();
        abort_unless($programme->accessiblePour($profil), 404);

        if ($profil->aDansSaListe($programme)) {
            $profil->liste()->detach($programme->id);
            $dans = false;
        } else {
            $profil->liste()->attach($programme->id);
            $dans = true;
        }

        return response()->json(['dans_liste' => $dans]);
    }

    /** Import unique des anciennes données du navigateur (localStorage) dans le profil actif. */
    public function importer(Request $request): JsonResponse
    {
        $request->validate([
            'liste' => ['nullable', 'array', 'max:300'],
            'liste.*' => ['string', 'max:190'],
            'progression' => ['nullable', 'array', 'max:300'],
        ]);
        $profil = Profil::actif();
        $n = 0;

        $slugs = array_values(array_unique(array_filter((array) $request->input('liste', []), 'is_string')));
        $programmes = Programme::whereIn('slug', $slugs)->get()->keyBy('slug');
        foreach (array_reverse($slugs) as $slug) {
            $p = $programmes->get($slug);
            if ($p && $p->accessiblePour($profil) && ! $profil->aDansSaListe($p)) {
                $profil->liste()->attach($p->id);
                $n++;
            }
        }

        foreach ((array) $request->input('progression', []) as $item) {
            if (! is_array($item) || ! is_string($item['slug'] ?? null) || ! is_numeric($item['t'] ?? null) || ! is_numeric($item['d'] ?? null)) {
                continue;
            }
            $p = Programme::where('slug', $item['slug'])->first();
            if (! $p || ! $p->accessiblePour($profil) || (int) $item['d'] < 60) {
                continue;
            }
            $ep = $this->episodeValide($p, is_string($item['ep'] ?? null) ? $item['ep'] : null, false);
            if ($ep === false) {
                continue;
            }
            $quand = is_numeric($item['at'] ?? null) ? Carbon::createFromTimestampMs((int) $item['at'])->min(now()) : now();
            $existant = Progression::where(['profil_id' => $profil->id, 'programme_id' => $p->id, 'episode' => $ep ?: ''])->first();
            if ($existant && $existant->updated_at >= $quand) {
                continue; // la base est plus récente
            }
            Progression::enregistrer($profil, $p, $ep, (int) $item['t'], (int) $item['d'], $quand);
            $n++;
        }

        return response()->json(['ok' => true, 'importes' => $n]);
    }

    /** Identifiant d'épisode accepté seulement s'il appartient bien au programme. */
    private function episodeValide(Programme $programme, ?string $ep, bool $abort = true): string|null|false
    {
        if (! $programme->estSerie()) {
            return null;
        }
        $ok = $ep && $programme->episodes()->where('youtube_id', $ep)->exists();
        if (! $ok) {
            if ($abort) {
                abort(422, 'Épisode inconnu.');
            }

            return false;
        }

        return $ep;
    }
}
