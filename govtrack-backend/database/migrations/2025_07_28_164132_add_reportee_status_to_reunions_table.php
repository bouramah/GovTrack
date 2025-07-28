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
        Schema::table('reunions', function (Blueprint $table) {
            // Modifier l'énumération statut pour inclure REPORTEE
            $table->enum('statut', ['PLANIFIEE', 'EN_COURS', 'TERMINEE', 'ANNULEE', 'REPORTEE'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reunions', function (Blueprint $table) {
            // Revenir à l'énumération originale
            $table->enum('statut', ['PLANIFIEE', 'EN_COURS', 'TERMINEE', 'ANNULEE'])->change();
        });
    }
};
