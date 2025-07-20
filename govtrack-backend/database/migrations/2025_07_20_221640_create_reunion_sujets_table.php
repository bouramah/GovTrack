<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reunion_sujets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reunion_ordre_jour_id');
            $table->string('titre', 200);
            $table->text('description');
            $table->text('difficulte_globale')->nullable();
            $table->text('recommandation')->nullable();
            $table->enum('statut', ['RESOLU', 'EN_COURS_DE_RESOLUTION', 'BLOQUE', 'AVIS', 'APPROUVE', 'REJETE', 'EN_ATTENTE']);
            $table->text('commentaire')->nullable();
            $table->json('pieces_jointes')->nullable();
            $table->unsignedBigInteger('projet_id')->nullable();
            $table->unsignedBigInteger('entite_id')->nullable();
            $table->enum('niveau_detail', ['SIMPLE', 'DETAILLE']);
            $table->boolean('objectifs_actifs')->default(false);
            $table->boolean('difficultes_actives')->default(false);
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent();
            $table->unsignedBigInteger('creer_par');
            $table->unsignedBigInteger('modifier_par');

            // Index
            $table->index('reunion_ordre_jour_id');
            $table->index('statut');
            $table->index('projet_id');
            $table->index('entite_id');
            $table->index('objectifs_actifs');
            $table->index('difficultes_actives');

            // Foreign keys
            $table->foreign('reunion_ordre_jour_id')->references('id')->on('reunion_ordre_jours')->onDelete('cascade');
            $table->foreign('projet_id')->references('id')->on('projets')->onDelete('set null');
            $table->foreign('entite_id')->references('id')->on('entites')->onDelete('set null');
            $table->foreign('creer_par')->references('id')->on('users');
            $table->foreign('modifier_par')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reunion_sujets');
    }
};
