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
        Schema::create('reunion_decisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reunion_id');
            $table->unsignedBigInteger('reunion_sujet_id')->nullable();
            $table->text('texte_decision');
            $table->enum('type', ['PROVISOIRE', 'DEFINITIVE']);
            $table->json('responsables_ids'); // Tableau des IDs des responsables
            $table->date('date_limite');
            $table->enum('statut', ['EN_ATTENTE', 'EN_COURS', 'TERMINEE']);
            $table->enum('priorite', ['FAIBLE', 'NORMALE', 'ELEVEE', 'CRITIQUE']);
            $table->text('commentaire')->nullable();
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent();
            $table->unsignedBigInteger('creer_par');
            $table->unsignedBigInteger('modifier_par');

            // Index
            $table->index('reunion_id');
            $table->index('reunion_sujet_id');
            $table->index('type');
            $table->index('statut');
            $table->index('priorite');
            $table->index('date_limite');

            // Foreign keys
            $table->foreign('reunion_id')->references('id')->on('reunions')->onDelete('cascade');
            $table->foreign('reunion_sujet_id')->references('id')->on('reunion_sujets')->onDelete('set null');
            $table->foreign('creer_par')->references('id')->on('users');
            $table->foreign('modifier_par')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reunion_decisions');
    }
};
