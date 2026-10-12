<?php

namespace Database\Seeders;

use App\Models\Profil;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Compte unique de la famille (identifiants lus dans config/gade.php -> .env : GADE_COMPTE_EMAIL, GADE_COMPTE_PASSWORD)
 * et profils de départ : N'jie (gestionnaire), Jie'n, Jade, Capucine, Christopher.
 * Le mot de passe n'est jamais affiché : il est seulement haché en base.
 */
class FamilleSeeder extends Seeder
{
    public function run(): void
    {
        $email = mb_strtolower(trim((string) config('gade.compte.email')));
        $motDePasse = (string) config('gade.compte.password');

        if ($email === '' || $motDePasse === '') {
            $this->command?->warn('GADE_COMPTE_EMAIL / GADE_COMPTE_PASSWORD absents du .env : compte de la famille non créé.');

            return;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = 'Famille';
        $user->password = Hash::make($motDePasse);
        $user->save();

        foreach (config('gade.profils.defaut') as $i => $p) {
            $profil = Profil::firstOrNew(['user_id' => $user->id, 'prenom' => $p['prenom']]);
            $profil->user_id = $user->id;
            $profil->avatar = $p['avatar'];
            $profil->couleur = $p['couleur'];
            $profil->gestionnaire = (bool) ($p['gestionnaire'] ?? false);
            $profil->enfant = false;
            $profil->ordre = $i;
            $profil->save();
        }

        $this->command?->info('Compte de la famille et '.count(config('gade.profils.defaut')).' profils prêts.');
    }
}
