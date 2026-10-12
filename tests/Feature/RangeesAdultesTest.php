<?php

namespace Tests\Feature;

use App\Models\Programme;
use App\Services\CatalogueSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Sections réservées aux adultes (CRU, True crime) et nouvelles rangées de genres. */
class RangeesAdultesTest extends TestCase
{
    use RefreshDatabase;

    private function sections(): void
    {
        $this->catalogue();
        Programme::create(['slug' => 'cru-les-100', 'titre' => 'CRU · Les 100', 'format' => 'unique', 'univers' => 'documentaires',
            'genres' => ['cru', 'societe'], 'tout_public' => false, 'youtube_id' => 'CRUCRUCRU01', 'chaine_nom' => 'CRU', 'duree_secondes' => 1200,
            'description' => 'Témoignages bruts.']);
        Programme::create(['slug' => 'faites-entrer-l-accuse', 'titre' => 'Faites entrer l’accusé', 'format' => 'unique', 'univers' => 'documentaires',
            'genres' => ['true-crime'], 'tout_public' => false, 'youtube_id' => 'FEAFEAFEA01', 'chaine_nom' => 'FEA', 'duree_secondes' => 4500,
            'description' => 'Grandes affaires criminelles.']);
        Programme::create(['slug' => 'bluey', 'titre' => 'Bluey', 'format' => 'unique', 'univers' => 'series',
            'genres' => ['petits'], 'tout_public' => true, 'youtube_id' => 'BLUEYBLUEY1', 'chaine_nom' => 'Bluey', 'duree_secondes' => 3600,
            'description' => 'Dessin animé.']);
    }

    public function test_cru_et_true_crime_sont_invisibles_sur_un_profil_enfant(): void
    {
        $this->sections();
        $this->entrer($this->profil(['prenom' => 'Christopher', 'enfant' => true]));

        $this->get('/')->assertOk()->assertDontSee('rangee-cru', false)->assertDontSee('rangee-true-crime', false)
            ->assertDontSee('CRU · Les 100')->assertSee('rangee-petits', false);
        $this->get('/parcourir?genre=cru')->assertOk()->assertDontSee('CRU · Les 100');
        $this->get('/parcourir?q=CRU')->assertOk()->assertDontSee('CRU · Les 100');
        $this->get('/parcourir?q=accus')->assertOk()->assertDontSee('Faites entrer');
        $this->get('/parcourir')->assertOk()->assertDontSee('CRU · Les 100')->assertDontSee('Faites entrer');
        $this->get('/programme/cru-les-100')->assertNotFound();
        $this->get('/regarder/cru-les-100')->assertNotFound();
        $this->get('/programme/faites-entrer-l-accuse')->assertNotFound();
        $this->get('/regarder/faites-entrer-l-accuse')->assertNotFound();
    }

    public function test_un_profil_adulte_voit_les_rangees_cru_et_true_crime_avec_leur_accroche(): void
    {
        $this->sections();
        $this->entrer();

        $this->get('/')->assertOk()->assertSee('rangee-cru', false)->assertSee('rangee-true-crime', false)
            ->assertSee(config('gade.rangees_genres.cru.creole'));
        $this->get('/parcourir?genre=cru')->assertOk()->assertSee('CRU · Les 100')->assertSee(config('gade.rangees_genres.cru.francais'));
        $this->get('/programme/cru-les-100')->assertOk();
    }

    public function test_les_fichiers_du_catalogue_ne_marquent_jamais_cru_ou_true_crime_tout_public(): void
    {
        $adultes = ['cru', 'true-crime'];
        foreach (CatalogueSync::fichiers() as $f) {
            foreach (json_decode(file_get_contents($f), true)['programmes'] as $p) {
                if (array_intersect($adultes, $p['genres'] ?? [])) {
                    $this->assertFalse($p['tout_public'] ?? false, basename($f).' : '.$p['slug'].' doit rester réservé aux adultes');
                }
                foreach ($p['genres'] ?? [] as $g) {
                    $this->assertArrayHasKey($g, config('gade.genres'), basename($f).' : genre inconnu '.$g);
                }
            }
        }
        foreach ($adultes as $g) {
            $this->assertContains($g, config('gade.profils.rangees_adultes'));
        }
    }
}
