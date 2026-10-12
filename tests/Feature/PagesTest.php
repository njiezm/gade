<?php

namespace Tests\Feature;

use App\Models\Profil;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_sans_session_seules_les_pages_publiques_repondent(): void
    {
        $this->catalogue();

        $this->get('/')->assertOk()->assertSee('Accès famille')->assertSee(route('connexion'), false)->assertDontSee('Film policier');
        $this->get('/connexion')->assertOk();
        $this->get('/mot-de-passe-oublie')->assertOk()->assertSee('contactez N’jie', false);
        $this->get('/mentions-legales')->assertOk();
        $this->get('/robots.txt')->assertOk()->assertSee("Disallow: /\n", false);
        $this->get('/sitemap.xml')->assertNotFound();
        $this->get('/catalogue.json')->assertNotFound();

        foreach (['/parcourir', '/programme/film-policier', '/regarder/film-policier', '/regarder/serie-comique/DDDDDDDDDD4',
            '/ma-liste', '/a-propos', '/profils', '/compte', '/gestion', '/coulisses'] as $url) {
            $this->get($url)->assertRedirect(route('connexion'));
        }
        $this->post('/moi/progression', ['slug' => 'film-policier', 't' => 100, 'd' => 5400])->assertRedirect(route('connexion'));
    }

    public function test_connecte_sans_profil_on_passe_par_ki_moun_ka_gade(): void
    {
        $this->catalogue();
        $profil = $this->profil();
        $this->actingAs($profil->user);

        $this->get('/')->assertRedirect(route('profils'));
        $this->get('/parcourir')->assertRedirect(route('profils'));
        $this->postJson('/moi/liste/film-policier')->assertStatus(409);
    }

    public function test_les_pages_principales_repondent_avec_un_profil(): void
    {
        $this->catalogue();
        $this->entrer();

        foreach (['/', '/parcourir', '/programme/film-policier', '/programme/serie-comique', '/regarder/film-policier',
            '/regarder/serie-comique', '/regarder/serie-comique/DDDDDDDDDD4', '/ma-liste', '/a-propos', '/mentions-legales',
            '/compte', '/profils'] as $url) {
            $this->get($url)->assertOk();
        }
        $r = $this->get('/');
        $this->assertStringContainsString('noindex', $r->headers->get('X-Robots-Tag'));
        $this->assertStringContainsString('no-store', $r->headers->get('Cache-Control'));
    }

    public function test_accueil_affiche_la_une_les_rangees_et_le_pied_de_page_legal(): void
    {
        $this->catalogue();
        $this->entrer();

        $this->get('/')
            ->assertSee('Film policier')
            ->assertSee('Ka ou lé gadé jodi-a, Jade ?')
            ->assertSee('Policier &amp; thriller', false)
            ->assertSee('En famille')
            ->assertSee('Moins de 30 min')
            ->assertSee('hébergées sur YouTube', false)
            ->assertSee('n’est qu’une sélection', false);
    }

    public function test_fiche_serie_liste_les_episodes_et_la_chaine_officielle(): void
    {
        $this->catalogue();
        $this->entrer();

        $this->get('/programme/serie-comique')
            ->assertSee('Le début')
            ->assertSee('La suite')
            ->assertSee('TV5MONDE')
            ->assertSee(route('regarder.episode', ['serie-comique', 'CCCCCCCCCC3']), false);
    }

    public function test_lecteur_utilise_youtube_nocookie_et_annonce_l_episode_suivant(): void
    {
        $this->catalogue();
        $this->entrer();

        $r = $this->get('/regarder/serie-comique/CCCCCCCCCC3');
        $r->assertOk()
            ->assertSee('data-video="CCCCCCCCCC3"', false)
            ->assertSee('data-suivant="'.route('regarder.episode', ['serie-comique', 'DDDDDDDDDD4']).'"', false)
            ->assertSee('name="csrf-token"', false);
        $this->assertStringContainsString('https://www.youtube-nocookie.com', $r->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString('https://i.ytimg.com', $r->headers->get('Content-Security-Policy'));
    }

    public function test_programme_masque_ou_indisponible_renvoie_404(): void
    {
        ['film' => $film, 'doc' => $doc] = $this->catalogue();
        $this->entrer();
        $film->update(['masque' => true]);
        $doc->update(['statut_verif' => 'indisponible']);

        $this->get('/programme/film-policier')->assertNotFound();
        $this->get('/regarder/volcans-de-martinique')->assertNotFound();
        $this->get('/regarder/serie-comique/ZZZZZZZZZZ9')->assertNotFound();
        $this->get('/')->assertDontSee('Film policier');
    }

    public function test_le_seeder_remplit_la_selection_et_cree_la_famille(): void
    {
        $this->seed();

        $this->assertGreaterThanOrEqual(119, Programme::count());
        $this->assertLessThanOrEqual(600, Programme::count());
        $this->assertGreaterThan(2500, \App\Models\Episode::count(),'Catalogue complet : catalogue.json + ajouts/');
        foreach (['documentaires', 'films', 'series', 'caraibes', 'spectacles'] as $u) {
            $this->assertTrue(Programme::where('univers', $u)->exists(), "Univers vide : {$u}");
        }
        $this->assertTrue(Programme::where('tout_public', true)->count() > 20);
        $this->assertSame(0, Programme::where('format', 'unique')->where('duree_secondes', '<', 1200)->count(), 'Programme de moins de 20 min');
        $this->assertGreaterThanOrEqual(10, Programme::genre('romance')->count());
        $this->assertGreaterThanOrEqual(12, Programme::genre('nouveaux-riches')->count());

        // Compte unique (identifiants factices des tests) et 5 profils, N'jie gestionnaire
        $user = User::sole();
        $this->assertSame('famille@example.test', $user->email);
        $this->assertTrue(Hash::check('mot-de-passe-de-test', $user->password));
        $this->assertSame(["N'jie", "Jie'n", 'Jade', 'Capucine', 'Christopher'], $user->profils()->pluck('prenom')->all());
        $this->assertSame(["N'jie"], Profil::where('gestionnaire', true)->pluck('prenom')->all());
        $this->assertCount(5, Profil::pluck('avatar')->unique());
        foreach (Profil::all() as $p) {
            $this->assertFileExists(public_path('images/avatars/'.$p->avatar.'.svg'));
            $this->assertNull($p->pin);
        }

        $this->entrer(Profil::where('prenom', 'Jade')->first());
        $this->get('/')->assertOk()->assertSee('Les enquêtes du commissaire Maigret')
            ->assertSee('rangee-romance', false)->assertSee('rangee-nouveaux-riches', false)
            ->assertSee('Réussir, entreprendre, bien gérer son argent');
        $this->get('/parcourir?genre=nouveaux-riches')->assertOk()->assertSee('Nouveaux riches')->assertSee('Génération entrepreneurs');
        $this->get('/parcourir?genre=romance')->assertOk()->assertSee('Les Mariés de l');
    }
}
