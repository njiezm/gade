<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** Page « Compte » (tous les profils) et changement du mot de passe (profil de N'jie seulement). */
class CompteController extends Controller
{
    public function index(Request $request): View
    {
        return view('compte.index', ['user' => $request->user()]);
    }

    public function changerMotDePasse(Request $request): RedirectResponse
    {
        $request->validate([
            'actuel' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'confirmed', Password::min(10)],
        ], [
            'actuel.current_password' => 'Le mot de passe actuel ne correspond pas.',
        ], ['password' => 'nouveau mot de passe', 'actuel' => 'mot de passe actuel']);

        $user = $request->user();
        $user->password = Hash::make($request->input('password'));
        $user->setRememberToken(Str::random(60)); // « Rester connecté » coupé sur les autres appareils
        $user->save();
        $request->session()->regenerate();

        return redirect()->route('compte')->with('status', 'Mot de passe changé. Pensez à le donner à la famille !');
    }
}
