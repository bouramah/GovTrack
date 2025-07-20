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
        Schema::create('reunion_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('decision_id');
            $table->string('titre', 200);
            $table->text('description');
            $table->unsignedBigInteger('responsable_id');
            $table->date('date_limite');
            $table->enum('statut', ['A_FAIRE', 'EN_COURS', 'TERMINEE']);
            $table->text('commentaire')->nullable();
            $table->json('pieces_jointes')->nullable();
            $table->enum('priorite', ['FAIBLE', 'NORMALE', 'ELEVEE', 'CRITIQUE']);
            $table->integer('progression')->default(0); // Pourcentage de progression (0-100)
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent();
            $table->unsignedBigInteger('creer_par');
            $table->unsignedBigInteger('modifier_par');

            // Index
            $table->index('decision_id');
            $table->index('responsable_id');
            $table->index('statut');
            $table->index('priorite');
            $table->index('date_limite');
            $table->index('progression');

            // Foreign keys
            $table->foreign('decision_id')->references('id')->on('reunion_decisions')->onDelete('cascade');
            $table->foreign('responsable_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('creer_par')->references('id')->on('users');
            $table->foreign('modifier_par')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reunion_actions');
    }
};
