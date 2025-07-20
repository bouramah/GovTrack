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
        Schema::create('type_reunions', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->text('description');
            $table->string('couleur', 7); // Couleur hexadécimale
            $table->string('icone', 50);
            $table->boolean('actif')->default(true);
            $table->integer('ordre');
            $table->enum('niveau_complexite', ['SIMPLE', 'INTERMEDIAIRE', 'COMPLEXE']);
            $table->json('fonctionnalites_actives');
            $table->json('configuration_notifications');
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent();
            $table->unsignedBigInteger('creer_par');
            $table->unsignedBigInteger('modifier_par');

            // Index
            $table->index('actif');
            $table->index('ordre');
            $table->index('niveau_complexite');

            // Foreign keys
            $table->foreign('creer_par')->references('id')->on('users');
            $table->foreign('modifier_par')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_reunions');
    }
};
