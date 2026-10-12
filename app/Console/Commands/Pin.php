<?php

namespace App\Console\Commands;

use App\Models\Profil;
use Illuminate\Console\Command;

/** Secours : retirer le code PIN d'un profil (ou le débloquer) si N'jie l'a oublié. */
class Pin extends Command
{
    protected $signature = 'gade:pin {prenom? : prénom du profil (par défaut : le profil gestionnaire)} {--debloquer : seulement lever le blocage après 5 erreurs}';

    protected $description = 'Retire le code PIN d’un profil GADÉ (ou le débloque)';

    public function handle(): int
    {
        $prenom = $this->argument('prenom');
        $profil = $prenom ? Profil::where('prenom', $prenom)->first() : Profil::where('gestionnaire', true)->first();
        if (! $profil) {
            $this->error('Profil introuvable.');

            return self::FAILURE;
        }

        if ($this->option('debloquer')) {
            $profil->forceFill(['pin_echecs' => 0, 'pin_bloque_jusqua' => null])->save();
            $this->info("Profil {$profil->prenom} débloqué.");
        } else {
            $profil->definirPin(null);
            $this->info("Code PIN retiré du profil {$profil->prenom}.");
        }

        return self::SUCCESS;
    }
}
