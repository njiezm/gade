<?php

namespace Tests;

use App\Models\Profil;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Sécurité : avec une configuration en cache, les tests viseraient la vraie base SQLite.
        if ($this->app->configurationIsCached() || config('database.connections.sqlite.database') !== ':memory:') {
            $this->fail('Configuration en cache : lancez « php artisan config:clear » avant « php artisan test ».');
        }
        // Identifiants factices : les tests n'utilisent jamais le vrai compte du .env
        config(['gade.compte.email' => 'famille@example.test', 'gade.compte.password' => 'mot-de-passe-de-test']);
    }

    /** Compte de test (e-mail et mot de passe factices). */
    protected function compte(): User
    {
        return User::firstOrCreate(['email' => 'famille@example.test'], ['name' => 'Famille', 'password' => Hash::make('mot-de-passe-de-test')]);
    }

    protected function profil(array $attrs = [], ?User $user = null): Profil
    {
        $p = new Profil(['prenom' => $attrs['prenom'] ?? 'Jade', 'avatar' => $attrs['avatar'] ?? 'iguane', 'couleur' => '#2f9e6a', 'enfant' => $attrs['enfant'] ?? false]);
        $p->user_id = ($user ?? $this->compte())->id;
        $p->gestionnaire = $attrs['gestionnaire'] ?? false;
        $p->save();
        if (! empty($attrs['pin'])) {
            $p->definirPin($attrs['pin']);
        }

        return $p;
    }

    /** Connecte le compte de test avec un profil choisi (session). */
    protected function entrer(?Profil $profil = null): Profil
    {
        $profil ??= $this->profil();
        $this->actingAs($profil->user)->withSession(['profil_id' => $profil->id]);

        return $profil;
    }

    protected function entrerNjie(array $attrs = []): Profil
    {
        return $this->entrer($this->profil(['prenom' => "N'jie", 'avatar' => 'clap', 'gestionnaire' => true] + $attrs));
    }

    /** Petit catalogue de test : un film, un documentaire tout public, une série de 2 épisodes. */
    protected function catalogue(): array
    {
        $film = Programme::create([
            'slug' => 'film-policier', 'titre' => 'Film policier', 'format' => 'unique', 'univers' => 'films',
            'genres' => ['policier'], 'tout_public' => false, 'youtube_id' => 'AAAAAAAAAA1', 'chaine_nom' => 'ARTE Cinema',
            'chaine_id' => 'UClo03hULFynpoX3w1Jv7fhw', 'duree_secondes' => 5400, 'description' => 'Une enquête sous la pluie.',
            'a_la_une' => true, 'ordre' => 10,
        ]);
        $doc = Programme::create([
            'slug' => 'volcans-de-martinique', 'titre' => 'Volcans de Martinique', 'format' => 'unique', 'univers' => 'caraibes',
            'genres' => ['aventure-nature'], 'tout_public' => true, 'youtube_id' => 'BBBBBBBBBB2', 'chaine_nom' => 'Martinique la 1ère',
            'duree_secondes' => 1560, 'description' => 'La montagne Pelée racontée aux enfants.',
        ]);
        $serie = Programme::create([
            'slug' => 'serie-comique', 'titre' => 'Série comique', 'format' => 'serie', 'univers' => 'series',
            'genres' => ['comedie'], 'tout_public' => false, 'chaine_nom' => 'TV5MONDE', 'duree_secondes' => 1500,
            'description' => 'Des épisodes drôles.',
        ]);
        $serie->episodes()->create(['saison' => 1, 'numero' => 1, 'titre' => 'Le début', 'youtube_id' => 'CCCCCCCCCC3', 'duree_secondes' => 1500]);
        $serie->episodes()->create(['saison' => 1, 'numero' => 2, 'titre' => 'La suite', 'youtube_id' => 'DDDDDDDDDD4', 'duree_secondes' => 1520]);

        return compact('film', 'doc', 'serie');
    }
}
