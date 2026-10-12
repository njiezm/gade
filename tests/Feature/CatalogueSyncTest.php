<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Programme;
use App\Models\Progression;
use App\Services\CatalogueSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CatalogueSyncTest extends TestCase
{
    use RefreshDatabase;

    private string $dossier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dossier = storage_path('framework/testing/sync-'.uniqid());
        File::ensureDirectoryExists($this->dossier.'/ajouts');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dossier);
        parent::tearDown();
    }

    private function ecrire(string $fichier, array $programmes): void
    {
        File::put($this->dossier.'/'.$fichier, json_encode(['genere_le' => '2026-10-11', 'programmes' => $programmes]));
    }

    private function serie(array $episodes, array $extra = []): array
    {
        return $extra + ['slug' => 'bron', 'titre' => 'Bron', 'format' => 'serie', 'univers' => 'series', 'genres' => ['policier'],
            'tout_public' => false, 'description' => 'Un pont, un corps.', 'episodes' => $episodes];
    }

    public function test_sync_ajoute_fusionne_les_fichiers_et_reste_idempotente(): void
    {
        $this->ecrire('catalogue.json', [
            $this->serie([['saison' => 1, 'numero' => 1, 'titre' => 'S1E1', 'youtube_id' => 'AAAAAAAAAA1']]),
            ['slug' => 'film', 'titre' => 'Film', 'format' => 'unique', 'univers' => 'films', 'genres' => ['drame'], 'tout_public' => true, 'youtube_id' => 'FFFFFFFFFF1'],
        ]);
        $this->ecrire('ajouts/01-bron.json', [
            ['slug' => 'bron', 'episodes' => [['saison' => 2, 'numero' => 1, 'titre' => 'S2E1', 'youtube_id' => 'AAAAAAAAAA2']]],
            ['slug' => 'kassav', 'titre' => 'Kassav’', 'format' => 'unique', 'univers' => 'caraibes', 'genres' => ['concerts'], 'tout_public' => true, 'youtube_id' => 'KKKKKKKKKK1'],
        ]);

        $sync = app(CatalogueSync::class);
        $s = $sync->synchroniser($this->dossier);
        $this->assertSame(3, $s['programmes_crees']);
        $this->assertSame(2, $s['episodes_crees']);
        $this->assertSame(2, Programme::where('slug', 'bron')->first()->episodes()->count());

        // Deuxième passage : rien de plus
        $s = $sync->synchroniser($this->dossier);
        $this->assertSame(0, $s['programmes_crees'] + $s['episodes_crees'] + $s['programmes_completes'] + $s['episodes_completes']);
        $this->assertSame(3, Programme::count());
        $this->assertSame(2, Episode::count());
    }

    public function test_sync_ne_supprime_rien_et_ne_touche_ni_aux_profils_ni_aux_reglages_des_coulisses(): void
    {
        ['film' => $film] = $this->catalogue();
        $profil = $this->profil();
        $profil->liste()->attach($film->id);
        Progression::create(['profil_id' => $profil->id, 'programme_id' => $film->id, 'episode' => '', 'position' => 600, 'duree' => 5400]);
        $film->update(['masque' => true, 'masque_raison' => 'Masqué dans les coulisses', 'ordre' => 77, 'description' => 'Texte de N’jie']);

        $this->ecrire('catalogue.json', [
            ['slug' => 'film-policier', 'titre' => 'Film policier', 'univers' => 'films', 'youtube_id' => 'AAAAAAAAAA1', 'description' => 'Texte du JSON',
                'annee' => 1999, 'a_la_une' => false, 'ordre' => 0],
        ]);
        app(CatalogueSync::class)->synchroniser($this->dossier);

        $film->refresh();
        $this->assertSame(3, Programme::count(), 'Les programmes absents des fichiers restent');
        $this->assertTrue($film->masque);
        $this->assertSame(77, $film->ordre);
        $this->assertTrue($film->a_la_une);
        $this->assertSame('Texte de N’jie', $film->description, 'Un champ rempli n’est pas écrasé sans « maj »');
        $this->assertSame(1999, $film->annee, 'Un champ vide est complété');
        $this->assertSame(1, $profil->liste()->count());
        $this->assertSame(600, Progression::first()->position);

        // « maj » ciblé : on réécrit la description, pas les réglages des coulisses
        $this->ecrire('ajouts/01.json', [['slug' => 'film-policier', 'description' => 'Nouvelle description', 'maj' => ['description']]]);
        app(CatalogueSync::class)->synchroniser($this->dossier);
        $film->refresh();
        $this->assertSame('Nouvelle description', $film->description);
        $this->assertTrue($film->masque);
    }

    public function test_la_commande_tourne_et_les_nouveautes_passent_devant(): void
    {
        $this->artisan('gade:catalogue:sync', ['--simuler' => true])->assertSuccessful();
        $this->assertSame(0, Programme::count(), 'La simulation n’écrit rien');

        $this->artisan('gade:catalogue:sync')->expectsOutputToContain('Programmes créés')->assertSuccessful();
        $this->assertGreaterThan(100, Programme::count());
        $this->assertSame(0, Programme::whereNull('univers')->count());
    }
}
