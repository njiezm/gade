<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FiltresTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entrer();
    }

    public function test_filtre_par_univers(): void
    {
        $this->catalogue();

        $this->get('/parcourir?univers=caraibes')
            ->assertSee('Volcans de Martinique')
            ->assertDontSee('Film policier')
            ->assertSee('1 programme');
    }

    public function test_filtre_par_genre(): void
    {
        $this->catalogue();

        $this->get('/parcourir?genre=policier')->assertSee('Film policier')->assertDontSee('Série comique');
    }

    public function test_filtre_par_duree(): void
    {
        $this->catalogue();

        $this->get('/parcourir?duree=court')
            ->assertSee('Volcans de Martinique')
            ->assertSee('Série comique')   // épisodes de 25 min
            ->assertDontSee('Film policier');
        $this->get('/parcourir?duree=long')->assertSee('Film policier')->assertDontSee('Volcans de Martinique');
    }

    public function test_filtre_tout_public(): void
    {
        $this->catalogue();

        $this->get('/parcourir?famille=1')->assertSee('Volcans de Martinique')->assertDontSee('Film policier');
    }

    public function test_recherche_sans_accents_dans_titres_descriptions_et_episodes(): void
    {
        $this->catalogue();

        $this->get('/parcourir?q=pelee')->assertSee('Volcans de Martinique')->assertDontSee('Film policier');
        $this->get('/parcourir?q=suite')->assertSee('Série comique');
        $this->get('/parcourir?q=introuvable')->assertSee('rien trouvé');
    }

    public function test_valeurs_inconnues_ignorees(): void
    {
        $this->catalogue();

        $this->get('/parcourir?univers=pirate&genre=xxx&duree=zzz')->assertOk()->assertSee('3 programmes');
    }
}
