<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Programme;
use App\Models\Proposition;
use App\Models\Verification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CommandesTest extends TestCase
{
    use RefreshDatabase;

    /** Réponses oEmbed simulées : 401 pour BBBB (non intégrable), 404 pour DDDD (supprimée), 200 sinon. */
    private function fauxOembed(array $codes = []): void
    {
        Http::fake(function (Request $r) use ($codes) {
            parse_str((string) parse_url($r->url(), PHP_URL_QUERY), $q);
            $id = substr((string) ($q['url'] ?? ''), -11);
            $code = $codes[$id] ?? 200;

            return $code === 200
                ? Http::response(['title' => 'Titre '.$id, 'author_name' => 'ARTE', 'author_url' => 'https://www.youtube.com/@arte'])
                : Http::response('', $code);
        });
    }

    public function test_verifier_masque_les_videos_devenues_indisponibles_et_produit_un_rapport(): void
    {
        ['doc' => $doc, 'serie' => $serie] = $this->catalogue();
        $this->fauxOembed(['BBBBBBBBBB2' => 401, 'DDDDDDDDDD4' => 404]);

        $this->artisan('gade:verifier', ['--pause' => 0])
            ->expectsOutputToContain('Rapport')
            ->assertSuccessful();

        $doc->refresh();
        $this->assertTrue($doc->masque);
        $this->assertSame('indisponible', $doc->statut_verif);
        $this->assertStringStartsWith('Indisponible sur YouTube', $doc->masque_raison);

        $ep = Episode::where('youtube_id', 'DDDDDDDDDD4')->first();
        $this->assertTrue($ep->masque);
        $this->assertFalse($serie->fresh()->masque, 'La série garde un épisode lisible');
        $this->assertFalse(Programme::where('slug', 'film-policier')->first()->masque);

        $v = Verification::latest()->first();
        $this->assertSame(4, $v->total);
        $this->assertSame(2, $v->indisponibles);
        $this->assertStringContainsString('Volcans de Martinique', $v->rapport);
        $this->entrer();
        $this->get('/programme/volcans-de-martinique')->assertNotFound();
    }

    public function test_verifier_reaffiche_une_video_revenue_et_respecte_le_masquage_manuel(): void
    {
        ['doc' => $doc, 'film' => $film] = $this->catalogue();
        $doc->update(['masque' => true, 'statut_verif' => 'indisponible', 'masque_raison' => 'Indisponible sur YouTube (oEmbed 404)']);
        $film->update(['masque' => true, 'masque_raison' => 'Masqué dans les coulisses']);
        $this->fauxOembed();

        $this->artisan('gade:verifier', ['--pause' => 0])->assertSuccessful();

        $this->assertFalse($doc->fresh()->masque);
        $this->assertTrue($film->fresh()->masque, 'Un masquage manuel ne doit pas être annulé');
    }

    public function test_une_serie_sans_aucun_episode_lisible_est_masquee(): void
    {
        ['serie' => $serie] = $this->catalogue();
        $this->fauxOembed(['CCCCCCCCCC3' => 404, 'DDDDDDDDDD4' => 401]);

        $this->artisan('gade:verifier', ['--programme' => 'serie-comique', '--pause' => 0])->assertSuccessful();

        $this->assertTrue($serie->fresh()->masque);
    }

    public function test_erreur_reseau_ne_masque_rien(): void
    {
        ['film' => $film] = $this->catalogue();
        Http::fake(['*' => Http::response('', 503)]);

        $this->artisan('gade:verifier', ['--pause' => 0])->assertSuccessful();

        $this->assertFalse($film->fresh()->masque);
        $this->assertSame(4, Verification::latest()->first()->erreurs);
    }

    public function test_mode_sans_masquer(): void
    {
        ['doc' => $doc] = $this->catalogue();
        $this->fauxOembed(['BBBBBBBBBB2' => 404]);

        $this->artisan('gade:verifier', ['--pause' => 0, '--sans-masquer' => true])->assertSuccessful();

        $this->assertFalse($doc->fresh()->masque);
    }

    public function test_proposer_ajoute_une_proposition_verifiee(): void
    {
        $this->fauxOembed();

        $this->artisan('gade:proposer', [
            'url' => 'https://www.youtube.com/watch?v=GGGGGGGGGG7', '--univers' => 'documentaires', '--genre' => ['sciences'],
            '--duree' => '52:10', '--tout-public' => true, '--motif' => 'Très beau',
        ])->expectsOutputToContain('Proposition n°')->assertSuccessful();

        $p = Proposition::firstOrFail();
        $this->assertSame('GGGGGGGGGG7', $p->youtube_id);
        $this->assertSame('claude', $p->source);
        $this->assertSame(3130, $p->duree_secondes);
        $this->assertSame(['sciences'], $p->genres);
        $this->assertTrue($p->tout_public);
        $this->assertSame('en_attente', $p->statut);
    }

    public function test_proposer_refuse_une_video_non_integrable_ou_deja_presente(): void
    {
        $this->catalogue();
        $this->fauxOembed(['HHHHHHHHHH8' => 401]);

        $this->artisan('gade:proposer', ['url' => 'https://youtu.be/HHHHHHHHHH8'])->assertFailed();
        $this->artisan('gade:proposer', ['url' => 'https://youtu.be/AAAAAAAAAA1'])->assertFailed();
        $this->artisan('gade:proposer', ['url' => 'pas une url'])->assertFailed();
        $this->assertSame(0, Proposition::count());
    }
}
