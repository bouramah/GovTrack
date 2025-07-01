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
        Schema::create('taches', function (Blueprint $table) {
            $table->id();
            $table->string('titre')->comment('Titre de la tâche');
            $table->text('description')->nullable()->comment('Description de la tâche');

            // Relations
            $table->foreignId('projet_id')->constrained('projets')->onDelete('cascade')->comment('Référence vers le projet');
            $table->foreignId('responsable_id')->nullable()->constrained('users')->comment('Utilisateur responsable de la tâche');

            // Statut et progression
            $table->enum('statut', [
                'a_faire',
                'en_cours',
                'bloque',
                'demande_de_cloture',
                'termine'
            ])->default('a_faire')->comment('Statut actuel de la tâche');
            $table->integer('niveau_execution')->default(0)->comment('Avancement en %');

            // Dates prévisionnelles
            $table->date('date_debut_previsionnelle')->nullable()->comment('Début prévisionnel');
            $table->date('date_fin_previsionnelle')->nullable()->comment('Fin prévisionnelle');

            // Dates réelles
            $table->date('date_debut_reelle')->nullable()->comment('Début réel');
            $table->date('date_fin_reelle')->nullable()->comment('Fin réelle');

            // Champs d'audit
            $table->datetime('date_creation');
            $table->datetime('date_modification');
            $table->string('creer_par')->comment('Utilisateur qui a créé');
            $table->string('modifier_par')->nullable()->comment('Utilisateur qui a modifié');

            // Index pour les recherches et performances
            $table->index(['projet_id', 'statut']);
            $table->index(['responsable_id', 'statut']);
            $table->index('statut');
            $table->index('niveau_execution');
            $table->index('date_fin_previsionnelle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taches');
    }
};
