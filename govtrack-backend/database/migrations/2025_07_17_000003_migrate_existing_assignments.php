<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Migrer les porteurs existants de projets
        DB::statement("
            INSERT INTO projet_porteurs (projet_id, user_id, date_assignation, statut)
            SELECT id, porteur_id, date_creation, TRUE
            FROM projets
            WHERE porteur_id IS NOT NULL
        ");

        // 2. Migrer les responsables existants de tâches
        DB::statement("
            INSERT INTO tache_responsables (tache_id, user_id, date_assignation, statut)
            SELECT id, responsable_id, date_creation, TRUE
            FROM taches
            WHERE responsable_id IS NOT NULL
        ");

        // 3. Renommer les colonnes existantes pour compatibilité
        Schema::table('projets', function (Blueprint $table) {
            $table->renameColumn('porteur_id', 'porteur_principal_id');
        });

        Schema::table('taches', function (Blueprint $table) {
            $table->renameColumn('responsable_id', 'responsable_principal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurer les noms de colonnes originaux
        Schema::table('projets', function (Blueprint $table) {
            $table->renameColumn('porteur_principal_id', 'porteur_id');
        });

        Schema::table('taches', function (Blueprint $table) {
            $table->renameColumn('responsable_principal_id', 'responsable_id');
        });

        // Supprimer les données migrées
        DB::statement("DELETE FROM projet_porteurs");
        DB::statement("DELETE FROM tache_responsables");
    }
};
