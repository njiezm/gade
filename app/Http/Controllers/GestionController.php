<?php

namespace App\Http\Controllers;

use App\Models\Profil;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Écran « Gestion » (profil de N'jie) : activité de chaque profil, liens vers profils, compte et coulisses. */
class GestionController extends Controller
{
    public function index(Request $request): View
    {
        $profils = $request->user()->profils()
            ->withCount(['liste', 'progressions as vus_count' => fn ($q) => $q->whereNotNull('termine_le')])
            ->get()
            ->each(function (Profil $p) {
                $p->setRelation('derniers', $p->progressions()->with('programme.episodes')->orderByDesc('updated_at')->take(6)->get());
            });

        return view('gestion.index', ['profils' => $profils]);
    }
}
