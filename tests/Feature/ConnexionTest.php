<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ConnexionTest extends TestCase
{
    use RefreshDatabase;

    public function test_connexion_regenere_la_session_et_mene_aux_profils(): void
    {
        $this->profil();
        $this->get('/connexion')->assertOk()->assertSee('Rester connecté');
        $avant = session()->getId();

        $this->post('/connexion', ['email' => 'Famille@Example.test', 'password' => 'mot-de-passe-de-test'])
            ->assertRedirect(route('profils'));
        $this->assertAuthenticated();
        $this->assertNotSame($avant, session()->getId());
        $this->get('/profils')->assertOk()->assertSee('Ki moun ka gadé ?')->assertSee('Jade');
        // Déjà connecté : la page de connexion renvoie aux profils
        $this->get('/connexion')->assertRedirect(route('profils'));
    }

    public function test_mauvais_mot_de_passe_refuse(): void
    {
        $this->compte();
        $this->from('/connexion')->post('/connexion', ['email' => 'famille@example.test', 'password' => 'faux'])
            ->assertRedirect('/connexion')->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_rester_connecte_pose_le_cookie_de_connexion(): void
    {
        $this->compte();
        $r = $this->post('/connexion', ['email' => 'famille@example.test', 'password' => 'mot-de-passe-de-test', 'remember' => '1']);
        $this->assertTrue(collect($r->headers->getCookies())->contains(fn ($c) => str_starts_with($c->getName(), 'remember_web_')));
    }

    public function test_limite_de_5_essais_par_minute(): void
    {
        $this->compte();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/connexion', ['email' => 'famille@example.test', 'password' => 'faux'.$i])->assertSessionHasErrors('email');
        }
        // 6e essai, même avec le bon mot de passe : bloqué
        $this->post('/connexion', ['email' => 'famille@example.test', 'password' => 'mot-de-passe-de-test'])
            ->assertSessionHasErrors('email');
        $this->assertStringContainsString('Trop d’essais', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_deconnexion(): void
    {
        $this->catalogue();
        $this->entrer();
        $this->post('/deconnexion')->assertRedirect(route('accueil'));
        $this->assertGuest();
        $this->get('/parcourir')->assertRedirect(route('connexion'));
    }

    public function test_csrf_exige_sur_la_connexion(): void
    {
        $this->compte();
        // Les tests désactivent la vérification CSRF : on la réactive le temps de cet essai
        $this->withMiddleware()->app->instance('env', 'production');
        $r = $this->call('POST', '/connexion', ['email' => 'famille@example.test', 'password' => 'mot-de-passe-de-test']);
        $this->assertSame(419, $r->getStatusCode());
        $this->assertGuest();
    }

    public function test_seule_njie_change_le_mot_de_passe(): void
    {
        $this->catalogue();
        $njie = $this->profil(['prenom' => "N'jie", 'gestionnaire' => true]);
        $jade = $this->entrer($this->profil(['prenom' => 'Jade']));

        $this->get('/compte')->assertOk()->assertSee('Seule N’jie peut changer', false);
        $this->put('/gestion/mot-de-passe', ['actuel' => 'mot-de-passe-de-test', 'password' => 'nouveau-mot-de-passe', 'password_confirmation' => 'nouveau-mot-de-passe'])
            ->assertForbidden();

        $this->withSession(['profil_id' => $njie->id]);
        $this->put('/gestion/mot-de-passe', ['actuel' => 'mauvais', 'password' => 'nouveau-mot-de-passe', 'password_confirmation' => 'nouveau-mot-de-passe'])
            ->assertSessionHasErrors('actuel');
        $this->put('/gestion/mot-de-passe', ['actuel' => 'mot-de-passe-de-test', 'password' => 'nouveau-mot-de-passe', 'password_confirmation' => 'nouveau-mot-de-passe'])
            ->assertRedirect(route('compte'));
        $this->assertTrue(Hash::check('nouveau-mot-de-passe', $jade->user->fresh()->password));
    }
}
