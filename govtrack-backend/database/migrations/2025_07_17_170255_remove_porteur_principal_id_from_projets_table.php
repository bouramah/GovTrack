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
            // Supprimer la contrainte de clé étrangère d'abord (nom correct de la contrainte)
            $table->dropForeign('projets_porteur_id_foreign');
            // Supprimer la colonne
            $table->dropColumn('porteur_principal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projets', function (Blueprint $table) {
            $table->foreignId('porteur_principal_id')->nullable()->constrained('users')->onDelete('set null');
        });
    }
};
