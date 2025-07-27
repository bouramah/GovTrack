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
        Schema::table('reunion_objectif_difficultes', function (Blueprint $table) {
            // Supprimer la contrainte unique qui empêche plusieurs difficultés par objectif/entité
            $table->dropUnique(['objectif_id', 'entite_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reunion_objectif_difficultes', function (Blueprint $table) {
            // Recréer la contrainte unique si nécessaire (rollback)
            $table->unique(['objectif_id', 'entite_id']);
        });
    }
};
