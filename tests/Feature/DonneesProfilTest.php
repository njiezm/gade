<?php

namespace Tests\Feature;

use App\Models\Profil;
use App\Models\Progression;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonneesProfilTest extends TestCase
{
    use RefreshDatabase;

    public function test_progression_enregistree_par_profil_et_kontinye_gade(): void
    {
        $this->catalogue();
        $jade = $this->entrer();

        $this->postJson('/moi/progression', ['slug' => 'film-policier', 't' => 1200, 'd' => 5400])->assertOk()->assertJson(['ok' => true]);
        $this->postJson('/moi/progression', ['slug' => 'serie-comique', 'ep' => 'CCCCCCCCCC3', 't' => 1490, 'd' => 1500])->assertJson(['termine' => true]);

        $this->assertSame(2, $jade->progressions()->count());
        $this->get('/')->assertSee('rangee-kontinye', false)
            ->assertSee('Reprendre à 20:00')
            ->assertSee(route('regarder', 'film-policier').'?t=1200', false)
            ->assertSee('À suivre : S1 · É2 · La suite', false)
            ->assertSee('rangee-deja-vu', false);
        $this->get('/regarder/film-policier')->assertSee('data-reprise="1200"', false);
        $this->get('/programme/film-policier')->assertSee('Kontinyé gadé (20:00)');

        // Épisode étranger à la série, programme inconnu, durée trop courte
        $this->postJson('/moi/progression', ['slug' => 'serie-comique', 'ep' => 'AAAAAAAAAA1', 't' => 10, 'd' => 1500])->assertStatus(422);
        $this->postJson('/moi/progression', ['slug' => 'inconnu', 't' => 10, 'd' => 1500])->assertNotFound();
        $this->postJson('/moi/progression', ['slug' => 'film-policier', 't' => 10, 'd' => 30])->assertJson(['ok' => false]);
    }

    public function test_beacon_en_formdata_accepte(): void
    {
        $this->catalogue();
        $jade = $this->entrer();
        $this->post('/moi/progression', ['slug' => 'film-policier', 't' => '300', 'd' => '5400'])->assertOk();
        $this->assertSame(300, $jade->progressions()->first()->position);
    }

    public function test_ma_liste_par_profil(): void
    {
        $this->catalogue();
        $jade = $this->entrer();

        $this->postJson('/moi/liste/film-policier')->assertJson(['dans_liste' => true]);
        $this->get('/ma-liste')->assertSee('Film policier')->assertSee('1 programme gardé');
        $this->get('/programme/film-policier')->assertSee('Dans ma liste');
        $this->postJson('/moi/liste/film-policier')->assertJson(['dans_liste' => false]);
        $this->get('/ma-liste')->assertSee('Ou poko mété ayen');
    }

    public function test_un_profil_ne_voit_ni_ne_modifie_les_donnees_d_un_autre(): void
    {
        ['film' => $film, 'serie' => $serie] = $this->catalogue();
        $jade = $this->profil(['prenom' => 'Jade']);
        $capucine = $this->profil(['prenom' => 'Capucine', 'avatar' => 'colibri']);
        $capucine->liste()->attach($serie->id);
        Progression::enregistrer($capucine, $film, null, 2000, 5400);

        $this->entrer($jade);
        // Jade ne voit rien de Capucine
        $this->get('/')->assertDontSee('rangee-kontinye', false)->assertDontSee('rangee-ma-liste', false);
        $this->get('/ma-liste')->assertDontSee('Série comique');
        $this->get('/regarder/film-policier')->assertDontSee('data-reprise=', false);
        $this->get('/programme/serie-comique')->assertSee('aria-pressed="false"', false);

        // Les écritures de Jade ne touchent que Jade, même si elle tente de viser un autre profil
        $this->postJson('/moi/liste/serie-comique', ['profil_id' => $capucine->id])->assertJson(['dans_liste' => true]);
        $this->postJson('/moi/progression', ['slug' => 'film-policier', 't' => 100, 'd' => 5400, 'profil_id' => $capucine->id])->assertOk();
        $this->assertSame(2000, $capucine->progressions()->first()->position);
        $this->assertSame(1, $capucine->liste()->count());
        $this->assertTrue($jade->liste()->where('programmes.id', $serie->id)->exists());
        $this->assertSame(100, $jade->progressions()->first()->position);

        // La gestion (réservée à N'jie) n'est pas accessible à Jade
        $this->get('/gestion')->assertForbidden();
    }

    public function test_njie_voit_l_activite_de_chaque_profil(): void
    {
        ['film' => $film] = $this->catalogue();
        $capucine = $this->profil(['prenom' => 'Capucine', 'avatar' => 'colibri']);
        Progression::enregistrer($capucine, $film, null, 2000, 5400);
        $this->entrerNjie();

        $this->get('/gestion')->assertOk()->assertSee('Capucine')->assertSee('Film policier')->assertSee('37 %');
    }

    public function test_import_unique_du_localstorage(): void
    {
        $this->catalogue();
        $jade = $this->entrer();

        $this->postJson('/moi/importer', [
            'liste' => ['volcans-de-martinique', 'inconnu'],
            'progression' => [
                ['slug' => 'film-policier', 'ep' => null, 't' => 600, 'd' => 5400, 'at' => now()->subDay()->getTimestampMs()],
                ['slug' => 'serie-comique', 'ep' => 'DDDDDDDDDD4', 't' => 200, 'd' => 1520, 'at' => now()->subHour()->getTimestampMs()],
                ['slug' => 'serie-comique', 'ep' => 'PASUNEPISOD', 't' => 200, 'd' => 1520],
            ],
        ])->assertOk()->assertJson(['importes' => 3]);

        $this->assertSame(['volcans-de-martinique'], $jade->liste()->pluck('slug')->all());
        $this->assertSame(2, $jade->progressions()->count());
    }

    public function test_profil_enfant_limite_au_tout_public(): void
    {
        $this->catalogue();
        $this->entrer($this->profil(['prenom' => 'Christopher', 'enfant' => true]));

        $this->get('/')->assertOk()->assertSee('Volcans de Martinique')->assertDontSee('Film policier')
            ->assertDontSee('rangee-policier', false)->assertSee('Profil enfant');
        $this->get('/parcourir')->assertSee('Volcans de Martinique')->assertDontSee('Film policier')->assertDontSee('Série comique');
        $this->get('/parcourir?q=policier')->assertDontSee('Film policier');
        $this->get('/programme/film-policier')->assertNotFound();
        $this->get('/regarder/film-policier')->assertNotFound();
        $this->get('/programme/volcans-de-martinique')->assertOk();
        $this->postJson('/moi/liste/film-policier')->assertNotFound();
        $this->postJson('/moi/progression', ['slug' => 'film-policier', 't' => 100, 'd' => 5400])->assertNotFound();
    }

    public function test_supprimer_un_profil_efface_ses_donnees(): void
    {
        ['film' => $film] = $this->catalogue();
        $capucine = $this->profil(['prenom' => 'Capucine']);
        Progression::enregistrer($capucine, $film, null, 2000, 5400);
        $capucine->delete();
        $this->assertSame(0, Progression::count());
        $this->assertSame(0, Profil::count());
    }
}
