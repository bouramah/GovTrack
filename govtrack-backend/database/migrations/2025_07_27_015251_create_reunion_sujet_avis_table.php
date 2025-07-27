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
        Schema::create('reunion_sujet_avis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reunion_sujet_id');
            $table->unsignedBigInteger('participant_id');
            $table->enum('type_avis', ['FAVORABLE', 'DEFAVORABLE', 'RESERVE', 'NEUTRE']);
            $table->text('commentaire')->nullable();
            $table->enum('statut', ['EN_ATTENTE', 'SOUMIS', 'MODIFIE']);
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent();
            $table->unsignedBigInteger('creer_par');
            $table->unsignedBigInteger('modifier_par');

            // Index
            $table->index('reunion_sujet_id');
            $table->index('participant_id');
            $table->index('type_avis');
            $table->index('statut');

            // Foreign keys
            $table->foreign('reunion_sujet_id')->references('id')->on('reunion_sujets')->onDelete('cascade');
            $table->foreign('participant_id')->references('id')->on('reunion_participants')->onDelete('cascade');
            $table->foreign('creer_par')->references('id')->on('users');
            $table->foreign('modifier_par')->references('id')->on('users');

            // Unique constraint - un participant ne peut avoir qu'un avis par sujet
            $table->unique(['reunion_sujet_id', 'participant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reunion_sujet_avis');
    }
};
