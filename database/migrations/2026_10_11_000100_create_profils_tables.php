<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Profils « façon Netflix » et données par profil : reprise de lecture (Kontinyé gadé, Déjà vu) et Ma liste. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profils', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('prenom', 40);
            $table->string('avatar', 40);
            $table->string('couleur', 7)->default('#f2b53a');
            $table->boolean('gestionnaire')->default(false);
            $table->boolean('enfant')->default(false);
            $table->string('pin')->nullable();               // haché
            $table->unsignedTinyInteger('pin_echecs')->default(0);
            $table->timestamp('pin_bloque_jusqua')->nullable();
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();
        });

        Schema::create('progressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_id')->constrained('profils')->cascadeOnDelete();
            $table->foreignId('programme_id')->constrained()->cascadeOnDelete();
            $table->string('episode', 11)->default('');      // identifiant YouTube de l'épisode ('' pour un programme unique)
            $table->unsignedInteger('position')->default(0); // secondes
            $table->unsignedInteger('duree')->default(0);
            $table->timestamp('termine_le')->nullable();
            $table->timestamps();
            $table->unique(['profil_id', 'programme_id', 'episode']);
            $table->index(['profil_id', 'updated_at']);
        });

        Schema::create('profil_liste', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_id')->constrained('profils')->cascadeOnDelete();
            $table->foreignId('programme_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['profil_id', 'programme_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_liste');
        Schema::dropIfExists('progressions');
        Schema::dropIfExists('profils');
    }
};
