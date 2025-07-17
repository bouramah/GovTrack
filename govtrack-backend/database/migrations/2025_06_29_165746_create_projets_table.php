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
        Schema::create('projets', function (Blueprint $table) {
            $table->id();
            $table->string('titre')->comment('Titre du projet/instruction');
            $table->text('description')->comment('Description détaillée');

            // Relations
            $table->foreignId('type_projet_id')->constrained('type_projets')->comment('Type d\'instruction');
            $table->foreignId('porteur_id')->constrained('users')->comment('Utilisateur porteur principal');
            $table->foreignId('donneur_ordre_id')->constrained('users')->comment('Utilisateur ayant donné l\'ordre');

            // Statut et progression
            $table->enum('statut', [
                'a_faire',
                'en_cours',
                'bloque',
                'demande_de_cloture',
                'termine'
            ])->default('a_faire')->comment('Statut actuel de l\'instruction');
            $table->integer('niveau_execution')->default(0)->comment('Niveau d\'exécution en %');

            // Dates prévisionnelles
            $table->date('date_debut_previsionnelle')->comment('Date début prévisionnelle');
            $table->date('date_fin_previsionnelle')->comment('Date fin prévisionnelle');

            // Dates réelles
            $table->date('date_debut_reelle')->nullable()->comment('Date début réelle');
            $table->date('date_fin_reelle')->nullable()->comment('Date fin réelle');

            // Justification des modifications
            $table->text('justification_modification_dates')->nullable()->comment('Raison de modification des dates');

            // Champs d'audit
            $table->datetime('date_creation');
            $table->datetime('date_modification');
            $table->string('creer_par')->comment('Utilisateur qui a créé');
            $table->string('modifier_par')->nullable()->comment('Utilisateur qui a modifié');

            // Index pour les recherches et performances
            $table->index('statut');
            $table->index('niveau_execution');
            $table->index(['porteur_id', 'statut']);
            $table->index(['donneur_ordre_id', 'statut']);
            $table->index('date_fin_previsionnelle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projets');
    }
};
