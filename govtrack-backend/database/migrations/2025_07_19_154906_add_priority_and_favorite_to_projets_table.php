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
        Schema::table('projets', function (Blueprint $table) {
            // Niveau de priorité (1=Faible, 2=Normale, 3=Élevée, 4=Critique)
            $table->enum('priorite', ['faible', 'normale', 'elevee', 'critique'])
                  ->default('normale')
                  ->after('niveau_execution');

            // Favoris (pour chaque utilisateur)
            $table->boolean('est_favori')
                  ->default(false)
                  ->after('priorite');

            // Index pour optimiser les requêtes
            $table->index(['priorite', 'est_favori']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projets', function (Blueprint $table) {
            $table->dropIndex(['priorite', 'est_favori']);
            $table->dropColumn(['priorite', 'est_favori']);
        });
    }
};
