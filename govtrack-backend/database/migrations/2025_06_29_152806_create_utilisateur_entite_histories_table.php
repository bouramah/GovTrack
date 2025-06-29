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
        Schema::create('utilisateur_entite_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('poste_id')->constrained('postes')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('entites')->onDelete('cascade');
            $table->boolean('statut')->default(true);
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->datetime('date_creation');
            $table->datetime('date_modification');
            $table->string('creer_par');
            $table->string('modifier_par')->nullable();

            $table->index(['user_id', 'statut']);
            $table->index(['service_id', 'statut']);
            $table->index('date_debut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateur_entite_histories');
    }
};
