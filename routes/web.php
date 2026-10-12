<?php

use App\Http\Controllers\Auth\CompteController;
use App\Http\Controllers\Auth\ConnexionController;
use App\Http\Controllers\CoulissesController;
use App\Http\Controllers\GestionController;
use App\Http\Controllers\MoiController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfilsController;
use App\Http\Middleware\CoulissesAuth;
use App\Http\Middleware\ProfilChoisi;
use App\Http\Middleware\ProfilGestionnaire;
use Illuminate\Support\Facades\Route;

// PWA (publiques, hors connexion : le service worker les charge sans cookies)
Route::get('/sw.js', [\App\Http\Controllers\PwaController::class, 'serviceWorker'])->name('pwa.sw');
Route::get('/hors-ligne', [\App\Http\Controllers\PwaController::class, 'horsLigne'])->name('pwa.hors-ligne');

// ---------- Public : accueil minimal « accès famille », connexion, mentions, robots ----------
// « / » : page publique sans session ; avec session, il faut un profil choisi.
Route::get('/', [PageController::class, 'accueil'])->middleware(ProfilChoisi::class.':invite')->name('accueil');
Route::get('/mentions-legales', [PageController::class, 'mentions'])->name('mentions');
Route::get('/robots.txt', [PageController::class, 'robots']);

Route::middleware('guest')->group(function () {
    Route::get('/connexion', [ConnexionController::class, 'formulaire'])->name('connexion');
    Route::post('/connexion', [ConnexionController::class, 'connecter'])->name('connexion.envoyer');
});
Route::get('/mot-de-passe-oublie', [ConnexionController::class, 'oublie'])->name('mot-de-passe.oublie');

// ---------- Compte connecté ----------
Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [ConnexionController::class, 'deconnecter'])->name('deconnexion');

    // « Ki moun ka gadé ? »
    Route::get('/profils', [ProfilsController::class, 'index'])->name('profils');
    Route::post('/profils/{profil}/choisir', [ProfilsController::class, 'choisir'])->name('profils.choisir');
    Route::get('/profils/{profil}/pin', [ProfilsController::class, 'pin'])->name('profils.pin');
    Route::post('/profils/{profil}/pin', [ProfilsController::class, 'verifierPin'])->name('profils.pin.verifier');

    // ---------- Profil choisi ----------
    Route::middleware(ProfilChoisi::class)->group(function () {
        Route::get('/parcourir', [PageController::class, 'parcourir'])->name('parcourir');
        Route::get('/programme/{programme}', [PageController::class, 'programme'])->name('programme');
        Route::get('/regarder/{programme}', [PageController::class, 'regarder'])->name('regarder');
        Route::get('/regarder/{programme}/{episode}', [PageController::class, 'regarder'])->name('regarder.episode')
            ->where('episode', '[A-Za-z0-9_-]{11}');
        Route::get('/ma-liste', [PageController::class, 'maListe'])->name('ma-liste');
        Route::get('/a-propos', [PageController::class, 'aPropos'])->name('a-propos');
        Route::get('/compte', [CompteController::class, 'index'])->name('compte');

        // Données du profil actif (CSRF, JSON)
        Route::post('/moi/progression', [MoiController::class, 'progression'])->middleware('throttle:60,1')->name('moi.progression');
        Route::post('/moi/liste/{programme}', [MoiController::class, 'basculerListe'])->name('moi.liste');
        Route::post('/moi/importer', [MoiController::class, 'importer'])->middleware('throttle:5,1')->name('moi.importer');

        // Avatar : le sien (ou tous, pour N'jie)
        Route::get('/profils/{profil}/avatar', [ProfilsController::class, 'avatar'])->name('profils.avatar');
        Route::put('/profils/{profil}/avatar', [ProfilsController::class, 'changerAvatar'])->name('profils.avatar.changer');

        // ---------- Profil gestionnaire (N'jie) ----------
        Route::middleware(ProfilGestionnaire::class)->group(function () {
            Route::get('/gestion', [GestionController::class, 'index'])->name('gestion');
            Route::put('/gestion/mot-de-passe', [CompteController::class, 'changerMotDePasse'])->name('gestion.mot-de-passe');
            Route::get('/profils/gerer', [ProfilsController::class, 'gerer'])->name('profils.gerer');
            Route::get('/profils/nouveau', [ProfilsController::class, 'creer'])->name('profils.creer');
            Route::post('/profils', [ProfilsController::class, 'enregistrer'])->name('profils.enregistrer');
            Route::get('/profils/{profil}/modifier', [ProfilsController::class, 'modifier'])->name('profils.modifier');
            Route::put('/profils/{profil}', [ProfilsController::class, 'mettreAJour'])->name('profils.mettre-a-jour');
            Route::delete('/profils/{profil}', [ProfilsController::class, 'supprimer'])->name('profils.supprimer');
        });
    });
});

// Coulisses : compte connecté + profil de N'jie (+ son PIN redemandé s'il en a un)
Route::prefix('coulisses')->name('coulisses.')->middleware(['auth', ProfilChoisi::class, ProfilGestionnaire::class])->group(function () {
    Route::get('/connexion', [CoulissesController::class, 'connexion'])->name('connexion');
    Route::post('/connexion', [CoulissesController::class, 'seConnecter'])->middleware('throttle:8,1')->name('se-connecter');

    Route::middleware(CoulissesAuth::class)->group(function () {
        Route::get('/', [CoulissesController::class, 'index'])->name('index');
        Route::post('/deconnexion', [CoulissesController::class, 'seDeconnecter'])->name('deconnexion');
        Route::get('/ajouter', [CoulissesController::class, 'ajouter'])->name('ajouter');
        Route::post('/programmes', [CoulissesController::class, 'enregistrer'])->name('enregistrer');
        Route::get('/programmes/{programme:id}/modifier', [CoulissesController::class, 'modifier'])->name('modifier');
        Route::put('/programmes/{programme:id}', [CoulissesController::class, 'mettreAJour'])->name('mettre-a-jour');
        Route::post('/programmes/{programme:id}/une', [CoulissesController::class, 'basculerUne'])->name('une');
        Route::post('/programmes/{programme:id}/masque', [CoulissesController::class, 'basculerMasque'])->name('masque');
        Route::post('/programmes/{programme:id}/classer', [CoulissesController::class, 'classer'])->name('classer');
        Route::post('/programmes/{programme:id}/verifier', [CoulissesController::class, 'verifierUn'])->name('verifier');
        Route::post('/propositions', [CoulissesController::class, 'proposer'])->name('proposer');
        Route::post('/propositions/{proposition}/refuser', [CoulissesController::class, 'refuser'])->name('refuser');
    });
});
