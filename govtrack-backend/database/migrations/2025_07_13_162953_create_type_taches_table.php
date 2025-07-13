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
        Schema::create('type_taches', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->text('description')->nullable();
            $table->string('couleur', 7)->default('#3B82F6'); // Code couleur hexadécimal
            $table->boolean('actif')->default(true);
            $table->integer('ordre')->default(0); // Pour l'ordre d'affichage
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->nullable();
            $table->string('creer_par');
            $table->string('modifier_par')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_taches');
    }
};
