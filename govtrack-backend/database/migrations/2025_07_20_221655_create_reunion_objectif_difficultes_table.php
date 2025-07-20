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
        Schema::create('reunion_objectif_difficultes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('objectif_id');
            $table->unsignedBigInteger('entite_id');
            $table->text('description_difficulte');
            $table->enum('niveau_difficulte', ['FAIBLE', 'MOYEN', 'ELEVE', 'CRITIQUE']);
            $table->text('impact');
            $table->text('solution_proposee')->nullable();
            $table->enum('statut', ['IDENTIFIEE', 'EN_COURS_RESOLUTION', 'RESOLUE']);
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent();
            $table->unsignedBigInteger('creer_par');
            $table->unsignedBigInteger('modifier_par');

            // Index
            $table->index('objectif_id');
            $table->index('entite_id');
            $table->index('niveau_difficulte');
            $table->index('statut');

            // Foreign keys
            $table->foreign('objectif_id')->references('id')->on('reunion_sujet_objectifs')->onDelete('cascade');
            $table->foreign('entite_id')->references('id')->on('entites')->onDelete('cascade');
            $table->foreign('creer_par')->references('id')->on('users');
            $table->foreign('modifier_par')->references('id')->on('users');

            // Unique constraint
            $table->unique(['objectif_id', 'entite_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reunion_objectif_difficultes');
    }
};
