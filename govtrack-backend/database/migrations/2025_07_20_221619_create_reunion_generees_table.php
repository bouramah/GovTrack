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
        Schema::create('reunion_generees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('serie_id');
            $table->unsignedBigInteger('reunion_id');
            $table->timestamp('genere_le');
            $table->enum('statut_generation', ['SUCCES', 'ERREUR']);
            $table->text('message_erreur')->nullable();
            $table->json('configuration_utilisee');
            $table->timestamp('date_creation')->useCurrent();

            // Index
            $table->index('serie_id');
            $table->index('reunion_id');
            $table->index('statut_generation');
            $table->index('genere_le');

            // Foreign keys
            $table->foreign('serie_id')->references('id')->on('reunion_series')->onDelete('cascade');
            $table->foreign('reunion_id')->references('id')->on('reunions')->onDelete('cascade');

            // Unique constraint
            $table->unique(['serie_id', 'reunion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reunion_generees');
    }
};
