<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Programme;
use App\Models\Proposition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CoulissesTest extends TestCase
{
    use RefreshDatabase;

    private function connecte(): static
    {
        if (! \App\Models\Profil::where('gestionnaire', true)->exists()) {
            $this->entrerNjie();
        }

        return $this;
    }

    private function fauxYouTube(int $code = 200, string $auteur = 'ARTE'): void
    {
        Http::fake([
            'www.youtube.com/oembed*' => $code === 200
                ? Http::response(['title' => 'Un grand documentaire', 'author_name' => $auteur, 'author_url' => 'https://www.youtube.com/@arte', 'thumbnail_url' => 'https://i.ytimg.com/vi/EEEEEEEEEE5/hqdefault.jpg'])
                : Http::response('Unauthorized', $code),
            'i.ytimg.com/*' => Http::response('', 404),
        ]);
    }

    public function test_ajout_par_url_preremplit_grace_a_oembed(): void
    {
        $this->fauxYouTube();

        $this->connecte()->get('/coulisses/ajouter?url='.urlencode('https://youtu.be/EEEEEEEEEE5'))
            ->assertOk()
            ->assertSee('Un grand documentaire')
            ->assertSee('ARTE')
            ->assertSee('Chaîne de la liste de confiance');
    }

    public function test_ajout_refuse_une_video_non_integrable(): void
    {
        $this->fauxYouTube(401);

        $this->connecte()->get('/coulisses/ajouter?url='.urlencode('https://www.youtube.com/watch?v=EEEEEEEEEE5'))
            ->assertRedirect(route('coulisses.index'))
            ->assertSessionHasErrors('url');
    }

    public function test_enregistrer_un_programme_avec_duree_saisie(): void
    {
        $this->fauxYouTube();

        $this->connecte()->post('/coulisses/programmes', [
            'youtube_id' => 'EEEEEEEEEE5', 'titre' => 'Un grand documentaire', 'univers' => 'documentaires',
            'genres' => ['sciences', 'histoire'], 'tout_public' => '1', 'duree' => '1:32:10', 'annee' => 2024,
            'description' => 'Ma description.', 'chaine_nom' => 'ARTE', 'chaine_url' => 'https://www.youtube.com/@arte', 'a_la_une' => '1',
        ])->assertRedirect(route('coulisses.index'));

        $p = Programme::where('youtube_id', 'EEEEEEEEEE5')->firstOrFail();
        $this->assertSame(5530, $p->duree_secondes);
        $this->assertTrue($p->tout_public);
        $this->assertTrue($p->a_la_une);
        $this->assertSame(['sciences', 'histoire'], $p->genres);
        $this->get('/programme/'.$p->slug)->assertOk()->assertSee('Ma description.');
    }

    public function test_ajouter_un_episode_a_une_serie(): void
    {
        ['serie' => $serie] = $this->catalogue();
        $this->fauxYouTube();

        $this->connecte()->post('/coulisses/programmes', [
            'youtube_id' => 'EEEEEEEEEE5', 'titre' => 'Le final', 'serie_id' => $serie->id, 'saison' => 1, 'numero' => 3, 'duree' => '26',
        ])->assertRedirect();

        $this->assertSame(3, $serie->episodes()->count());
        $this->assertSame(1560, Episode::where('youtube_id', 'EEEEEEEEEE5')->value('duree_secondes'));
    }

    public function test_doublon_refuse(): void
    {
        $this->catalogue();
        $this->fauxYouTube();

        $this->connecte()->get('/coulisses/ajouter?url=https://www.youtube.com/watch?v=AAAAAAAAAA1')
            ->assertRedirect(route('coulisses.index'))->assertSessionHasErrors('url');
    }

    public function test_classer_mettre_a_la_une_et_masquer(): void
    {
        ['doc' => $doc] = $this->catalogue();

        $this->connecte()->post("/coulisses/programmes/{$doc->id}/une")->assertRedirect();
        $this->connecte()->post("/coulisses/programmes/{$doc->id}/classer", ['sens' => 'haut'])->assertRedirect();
        $doc->refresh();
        $this->assertTrue($doc->a_la_une);
        $this->assertSame(10, $doc->ordre);

        $this->connecte()->post("/coulisses/programmes/{$doc->id}/masque")->assertRedirect();
        $this->assertTrue($doc->fresh()->masque);
        $this->get('/programme/volcans-de-martinique')->assertNotFound();
    }

    public function test_file_des_propositions_valider_et_refuser(): void
    {
        $this->fauxYouTube(200, 'Une chaîne inconnue');

        $this->connecte()->post('/coulisses/propositions', ['url' => 'https://youtu.be/EEEEEEEEEE5', 'motif' => 'À voir'])->assertRedirect();
        $prop = Proposition::firstOrFail();
        $this->assertStringStartsWith('⚠', $prop->motif);   // chaîne hors liste de confiance signalée

        $this->connecte()->get('/coulisses')->assertSee('Un grand documentaire')->assertSee('Valider…');
        $this->connecte()->get('/coulisses/ajouter?proposition='.$prop->id)->assertOk()->assertSee('Valider une proposition');

        $this->connecte()->post('/coulisses/programmes', [
            'youtube_id' => 'EEEEEEEEEE5', 'titre' => 'Un grand documentaire', 'univers' => 'documentaires', 'proposition_id' => $prop->id,
        ])->assertRedirect();
        $this->assertSame('validee', $prop->fresh()->statut);

        $autre = Proposition::create(['youtube_id' => 'FFFFFFFFFF6', 'url' => 'https://youtu.be/FFFFFFFFFF6', 'titre' => 'Bof']);
        $this->connecte()->post("/coulisses/propositions/{$autre->id}/refuser")->assertRedirect();
        $this->assertSame('refusee', $autre->fresh()->statut);
    }
}
