<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Un programme = un film, un documentaire ou une série (dans ce cas, ses vidéos sont dans « episodes »)
        Schema::create('programmes', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('titre');
            $table->string('format', 20)->default('unique');        // unique | serie
            $table->string('univers', 30)->index();                  // documentaires | films | series | caraibes
            $table->json('genres')->nullable();                      // ["policier", "comedie", ...]
            $table->boolean('tout_public')->default(false)->index();
            $table->string('youtube_id', 20)->nullable()->unique();  // vidéo unique (null pour une série)
            $table->string('chaine_nom')->nullable();
            $table->string('chaine_id', 40)->nullable();
            $table->string('chaine_url')->nullable();
            $table->string('ayant_droit')->nullable();
            $table->unsignedInteger('duree_secondes')->nullable();
            $table->unsignedSmallInteger('annee')->nullable();
            $table->text('description')->nullable();
            $table->string('vignette')->nullable();                  // chemin public (vignettes/xxx.webp)
            $table->string('affiche')->nullable();                   // grande image (à la une / fiche)
            $table->boolean('a_la_une')->default(false)->index();
            $table->integer('ordre')->default(0)->index();
            $table->boolean('masque')->default(false)->index();
            $table->string('masque_raison')->nullable();
            $table->date('disponible_jusqu_au')->nullable();
            $table->string('statut_verif', 20)->default('ok');       // ok | indisponible | a_verifier
            $table->timestamp('verifie_le')->nullable();
            $table->timestamp('publie_le')->nullable();              // date de mise en ligne sur YouTube
            $table->timestamps();
        });

        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('saison')->default(1);
            $table->unsignedSmallInteger('numero')->default(1);
            $table->string('titre');
            $table->string('youtube_id', 20)->unique();
            $table->unsignedInteger('duree_secondes')->nullable();
            $table->string('vignette')->nullable();
            $table->text('description')->nullable();
            $table->boolean('masque')->default(false);
            $table->date('disponible_jusqu_au')->nullable();
            $table->string('statut_verif', 20)->default('ok');
            $table->timestamp('verifie_le')->nullable();
            $table->timestamps();
            $table->index(['programme_id', 'saison', 'numero']);
        });

        // File « Propositions à valider » : Claude (ou N'jie) y dépose des vidéos, on valide ou on refuse
        Schema::create('propositions', function (Blueprint $table) {
            $table->id();
            $table->string('youtube_id', 20)->index();
            $table->string('url');
            $table->string('titre')->nullable();
            $table->string('chaine_nom')->nullable();
            $table->string('chaine_url')->nullable();
            $table->string('vignette_url')->nullable();
            $table->string('univers', 30)->nullable();
            $table->json('genres')->nullable();
            $table->boolean('tout_public')->default(false);
            $table->unsignedInteger('duree_secondes')->nullable();
            $table->text('description')->nullable();
            $table->text('motif')->nullable();                       // pourquoi cette proposition
            $table->string('source', 20)->default('claude');         // claude | coulisses | cli
            $table->string('statut', 20)->default('en_attente')->index(); // en_attente | validee | refusee
            $table->timestamp('traitee_le')->nullable();
            $table->foreignId('programme_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // Historique des vérifications (commande gade:verifier)
        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('ok')->default(0);
            $table->unsignedInteger('indisponibles')->default(0);
            $table->unsignedInteger('erreurs')->default(0);
            $table->longText('rapport')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifications');
        Schema::dropIfExists('propositions');
        Schema::dropIfExists('episodes');
        Schema::dropIfExists('programmes');
    }
};
