<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoulissesAccesTest extends TestCase
{
    use RefreshDatabase;

    public function test_coulisses_refusees_sans_connexion_et_aux_autres_profils(): void
    {
        $this->get('/coulisses')->assertRedirect(route('connexion'));
        $this->post('/coulisses/programmes', [])->assertRedirect(route('connexion'));

        $this->entrer($this->profil(['prenom' => 'Jie\'n']));
        $this->get('/coulisses')->assertForbidden();
        $this->get('/coulisses/ajouter')->assertForbidden();
        $this->post('/coulisses/programmes', [])->assertForbidden();
        $this->get('/')->assertDontSee(route('coulisses.index'), false);
    }

    public function test_njie_sans_pin_entre_directement(): void
    {
        $this->entrerNjie();
        $this->get('/coulisses')->assertOk()->assertSee('Propositions à valider');
        $this->get('/coulisses/connexion')->assertRedirect(route('coulisses.index'));
    }

    public function test_njie_avec_pin_le_pin_est_redemande(): void
    {
        $this->entrerNjie(['pin' => '2468']);
        $this->get('/coulisses')->assertRedirect(route('coulisses.connexion'));
        $this->post('/coulisses/programmes', [])->assertRedirect(route('coulisses.connexion'));
        $this->get('/coulisses/connexion')->assertOk()->assertSee('Entrer dans les coulisses');

        $this->post('/coulisses/connexion', ['pin' => '1111'])->assertSessionHasErrors('pin');
        $this->post('/coulisses/connexion', ['pin' => '2468'])->assertRedirect(route('coulisses.index'));
        $this->get('/coulisses')->assertOk();

        // Quitter les coulisses : le PIN sera redemandé
        $this->post('/coulisses/deconnexion')->assertRedirect(route('accueil'));
        $this->get('/coulisses')->assertRedirect(route('coulisses.connexion'));

        // Confirmation expirée après quelques heures
        $this->post('/coulisses/connexion', ['pin' => '2468']);
        $this->travel(3)->hours();
        $this->get('/coulisses')->assertRedirect(route('coulisses.connexion'));
    }
}
