<?php

namespace Tests\Feature;

use App\Models\Profil;
use App\Models\User;
use App\Support\Avatars;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfilsTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_galerie_compte_au_moins_30_avatars_ranges_par_themes(): void
    {
        $this->assertGreaterThanOrEqual(30, count(Avatars::tous()));
        $this->assertGreaterThanOrEqual(4, count(Avatars::themes()));
        foreach (['manicou', 'colibri', 'iguane', 'tortue', 'mangouste', 'crabe', 'ananas', 'mangue', 'coco', 'christophine', 'popcorn', 'clap', 'bobine', 'robot', 'extraterrestre', 'monstre', 'pirate'] as $cle) {
            $this->assertTrue(Avatars::existe($cle), $cle);
            $this->assertFileExists(public_path("images/avatars/{$cle}.svg"));
        }
    }

    public function test_choisir_un_profil_sans_pin(): void
    {
        $this->catalogue();
        $jade = $this->profil();
        $this->actingAs($jade->user);

        $this->get('/profils')->assertOk()->assertSee('Ki moun ka gadé ?')->assertDontSee('Gérer les profils');
        $this->post("/profils/{$jade->id}/choisir")->assertRedirect(route('accueil'));
        $this->assertSame($jade->id, session('profil_id'));
        $this->get('/')->assertOk()->assertSee('Profil de Jade', false);
    }

    public function test_impossible_de_choisir_le_profil_d_un_autre_compte(): void
    {
        $autreCompte = User::create(['name' => 'Autre', 'email' => 'autre@example.test', 'password' => Hash::make('x-x-x-x-x-x')]);
        $intrus = $this->profil(['prenom' => 'Intrus'], $autreCompte);
        $this->entrer();

        $this->post("/profils/{$intrus->id}/choisir")->assertNotFound();
        $this->withSession(['profil_id' => $intrus->id])->get('/parcourir')->assertRedirect(route('profils'));
    }

    public function test_pin_hache_5_essais_puis_blocage(): void
    {
        $njie = $this->profil(['prenom' => "N'jie", 'gestionnaire' => true, 'pin' => '1234']);
        $this->actingAs($njie->user);
        $this->assertNotSame('1234', $njie->fresh()->pin);
        $this->assertTrue(Hash::check('1234', $njie->fresh()->pin));

        $this->post("/profils/{$njie->id}/choisir")->assertRedirect(route('profils.pin', $njie));
        $this->get("/profils/{$njie->id}/pin")->assertOk()->assertSee('Code PIN de N&#039;jie', false);
        $this->assertNull(session('profil_id'));

        for ($i = 1; $i <= 4; $i++) {
            $this->post("/profils/{$njie->id}/pin", ['pin' => '000'.$i])->assertSessionHasErrors('pin');
        }
        $this->post("/profils/{$njie->id}/pin", ['pin' => '9999'])->assertSessionHasErrors('pin');
        $this->assertTrue($njie->fresh()->pinBloque());
        // Bloqué : même le bon code est refusé
        $this->post("/profils/{$njie->id}/pin", ['pin' => '1234'])->assertSessionHasErrors('pin');
        $this->assertNull(session('profil_id'));

        $this->travel(16)->minutes();
        $this->post("/profils/{$njie->id}/pin", ['pin' => '1234'])->assertRedirect(route('accueil'));
        $this->assertSame($njie->id, session('profil_id'));
    }

    public function test_gerer_les_profils_passe_par_le_profil_de_njie(): void
    {
        $njie = $this->profil(['prenom' => "N'jie", 'gestionnaire' => true]);
        $this->actingAs($njie->user);
        $this->post("/profils/{$njie->id}/choisir", ['suite' => 'gerer'])->assertRedirect(route('profils.gerer'));
        $this->get('/profils/gerer')->assertOk()->assertSee('Ajouter un profil');
    }

    public function test_seule_njie_gere_les_profils(): void
    {
        $njie = $this->profil(['prenom' => "N'jie", 'gestionnaire' => true]);
        $jade = $this->entrer($this->profil(['prenom' => 'Jade']));
        $capucine = $this->profil(['prenom' => 'Capucine', 'avatar' => 'colibri']);

        $this->get('/profils/gerer')->assertForbidden();
        $this->get('/gestion')->assertForbidden();
        $this->get('/profils/nouveau')->assertForbidden();
        $this->post('/profils', ['prenom' => 'Pirate', 'couleur' => '#f2b53a'])->assertForbidden();
        $this->put("/profils/{$capucine->id}", ['prenom' => 'Kapi', 'couleur' => '#f2b53a'])->assertForbidden();
        $this->delete("/profils/{$capucine->id}")->assertForbidden();
        $this->put("/profils/{$njie->id}", ['prenom' => 'Moi', 'couleur' => '#f2b53a', 'pin' => '0000', 'pin_confirmation' => '0000'])->assertForbidden();
        $this->assertSame('Capucine', $capucine->fresh()->prenom);
        $this->assertNull($njie->fresh()->pin);
        $this->get('/')->assertDontSee('Gérer les profils');

        // Son propre avatar : oui ; celui d'un autre : non
        $this->get("/profils/{$jade->id}/avatar")->assertOk()->assertSee('Choisir un avatar')->assertSee('Bèt péyi');
        $this->put("/profils/{$jade->id}/avatar", ['avatar' => 'tortue'])->assertRedirect(route('accueil'));
        $this->assertSame('tortue', $jade->fresh()->avatar);
        $this->get("/profils/{$capucine->id}/avatar")->assertForbidden();
        $this->put("/profils/{$capucine->id}/avatar", ['avatar' => 'tortue'])->assertForbidden();
        $this->assertSame('colibri', $capucine->fresh()->avatar);
        $this->put("/profils/{$jade->id}/avatar", ['avatar' => 'inexistant'])->assertSessionHasErrors('avatar');
    }

    public function test_njie_cree_modifie_et_supprime_des_profils(): void
    {
        $this->catalogue();
        $njie = $this->entrerNjie();
        $capucine = $this->profil(['prenom' => 'Capucine']);
        $capucine->liste()->attach(\App\Models\Programme::first()->id);

        $this->get('/profils/gerer')->assertOk()->assertSee('Capucine');
        $this->post('/profils', ['prenom' => 'Mamie', 'couleur' => '#9b6ad6', 'enfant' => '0'])->assertRedirect();
        $mamie = Profil::where('prenom', 'Mamie')->firstOrFail();
        $this->assertTrue(Avatars::existe($mamie->avatar));

        $this->put("/profils/{$capucine->id}", ['prenom' => 'Capucine', 'couleur' => '#e0679a', 'enfant' => '1', 'pin' => '4321', 'pin_confirmation' => '4321'])
            ->assertRedirect(route('profils.gerer'));
        $this->assertTrue($capucine->fresh()->enfant);
        $this->assertTrue(Hash::check('4321', $capucine->fresh()->pin));
        $this->put("/profils/{$capucine->id}", ['prenom' => 'Capucine', 'couleur' => '#e0679a', 'pin' => '12', 'pin_confirmation' => '12'])->assertSessionHasErrors('pin');
        $this->put("/profils/{$capucine->id}", ['prenom' => 'Capucine', 'couleur' => '#e0679a', 'retirer_pin' => '1'])->assertRedirect();
        $this->assertNull($capucine->fresh()->pin);

        $this->put("/profils/{$capucine->id}/avatar", ['avatar' => 'pirate'])->assertRedirect(route('profils.gerer'));
        $this->assertSame('pirate', $capucine->fresh()->avatar);

        $this->delete("/profils/{$capucine->id}")->assertRedirect(route('profils.gerer'));
        $this->assertNull(Profil::find($capucine->id));
        $this->assertDatabaseMissing('profil_liste', ['profil_id' => $capucine->id]);

        // Le profil de N'jie ne peut pas être supprimé ni passer en profil enfant
        $this->delete("/profils/{$njie->id}");
        $this->assertNotNull($njie->fresh());
        $this->put("/profils/{$njie->id}", ['prenom' => "N'jie", 'couleur' => '#f2b53a', 'enfant' => '1']);
        $this->assertFalse($njie->fresh()->enfant);
    }

    public function test_maximum_8_profils(): void
    {
        $this->entrerNjie();
        for ($i = 0; $i < 7; $i++) {
            $this->profil(['prenom' => 'P'.$i]);
        }
        $this->post('/profils', ['prenom' => 'Neuvième', 'couleur' => '#f2b53a'])->assertSessionHasErrors('profil');
        $this->assertSame(8, Profil::count());
    }
}
