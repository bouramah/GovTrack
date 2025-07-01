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
        Schema::create('tache_historique_statuts', function (Blueprint $table) {
            $table->id();

            // Relations - colonnes séparées des contraintes
            $table->unsignedBigInteger('tache_id')->comment('Référence vers la tâche');
            $table->unsignedBigInteger('user_id')->comment('Utilisateur ayant fait le changement');

            // Statuts
            $table->enum('ancien_statut', [
                'a_faire',
                'en_cours',
                'bloque',
                'demande_de_cloture',
                'termine'
            ])->nullable()->comment('Ancien statut (null si création)');

            $table->enum('nouveau_statut', [
                'a_faire',
                'en_cours',
                'bloque',
                'demande_de_cloture',
                'termine'
            ])->comment('Nouveau statut');

            // Détails du changement
            $table->text('commentaire')->nullable()->comment('Commentaire du changement');
            $table->string('justificatif_path')->nullable()->comment('Chemin vers le fichier justificatif');
            $table->datetime('date_changement')->comment('Date et heure du changement');

            // Contraintes de clé étrangère
            $table->foreign('tache_id')->references('id')->on('taches')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');

            // Index pour les recherches et performances
            $table->index(['tache_id', 'date_changement']);
            $table->index(['user_id', 'date_changement']);
            $table->index('nouveau_statut');
            $table->index('date_changement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tache_historique_statuts');
    }
}; 