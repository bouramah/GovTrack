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
        Schema::create('reunion_sujet_objectifs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reunion_sujet_id');
            $table->string('titre', 200);
            $table->text('description');
            $table->text('cible');
            $table->integer('taux_realisation'); // Pourcentage de réalisation (0-100)
            $table->decimal('pourcentage_decaissement', 5, 2);
            $table->date('date_objectif');
            $table->enum('statut', ['EN_COURS', 'ATTEINT', 'EN_RETARD']);
            $table->integer('ordre');
            $table->boolean('actif')->default(true);
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent();
            $table->unsignedBigInteger('creer_par');
            $table->unsignedBigInteger('modifier_par');

            // Index
            $table->index('reunion_sujet_id');
            $table->index('statut');
            $table->index('ordre');
            $table->index('actif');
            $table->index('date_objectif');

            // Foreign keys
            $table->foreign('reunion_sujet_id')->references('id')->on('reunion_sujets')->onDelete('cascade');
            $table->foreign('creer_par')->references('id')->on('users');
            $table->foreign('modifier_par')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reunion_sujet_objectifs');
    }
};
