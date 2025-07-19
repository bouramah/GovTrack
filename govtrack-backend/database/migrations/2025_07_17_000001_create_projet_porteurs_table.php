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
        Schema::create('projet_porteurs', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('projet_id')->constrained('projets')->onDelete('cascade')->comment('Référence vers le projet');
            $table->foreignId('user_id')->constrained('users')->comment('Utilisateur porteur');

            // Métadonnées d'assignation
            $table->datetime('date_assignation')->comment('Date d\'assignation comme porteur');
            $table->datetime('date_fin_assignation')->nullable()->comment('Date de fin d\'assignation');
            $table->boolean('statut')->default(true)->comment('Si l\'assignation est active');
            $table->text('commentaire')->nullable()->comment('Commentaire sur l\'assignation');

            // Contrainte d'unicité
            $table->unique(['projet_id', 'user_id'], 'unique_projet_user');

            // Index pour les performances
            $table->index(['projet_id', 'statut']);
            $table->index(['user_id', 'statut']);
            $table->index('date_assignation');
            $table->index('date_fin_assignation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projet_porteurs');
    }
};
