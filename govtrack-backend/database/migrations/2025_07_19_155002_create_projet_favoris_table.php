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
        Schema::create('projet_favoris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('projet_id')->constrained()->onDelete('cascade');
            $table->timestamp('date_ajout')->useCurrent();

            // Un utilisateur ne peut avoir qu'un seul favori par projet
            $table->unique(['user_id', 'projet_id']);

            // Index pour optimiser les requêtes
            $table->index(['user_id', 'projet_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projet_favoris');
    }
};
