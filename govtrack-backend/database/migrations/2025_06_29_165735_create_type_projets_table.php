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
        Schema::create('type_projets', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->comment('Nom du type d\'instruction');
            $table->text('description')->nullable()->comment('Description du type');

            // SLA (Service Level Agreement) - pour définir les délais par défaut
            $table->integer('duree_previsionnelle_jours')->default(30)->comment('Durée par défaut en jours');
            $table->text('description_sla')->nullable()->comment('Description du SLA pour ce type');

            // Champs d'audit
            $table->datetime('date_creation');
            $table->datetime('date_modification');
            $table->string('creer_par')->comment('Utilisateur qui a créé');
            $table->string('modifier_par')->nullable()->comment('Utilisateur qui a modifié');

            // Index pour les recherches
            $table->index('nom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_projets');
    }
};
