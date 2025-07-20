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
        Schema::create('reunion_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reunion_id');
            $table->enum('type', ['CONFIRMATION_PRESENCE', 'RAPPEL_24H', 'RAPPEL_1H', 'RAPPEL_15MIN', 'PV_DISPONIBLE', 'RAPPEL_ACTIONS']);
            $table->unsignedBigInteger('envoye_a');
            $table->timestamp('envoye_le');
            $table->enum('statut', ['ENVOYE', 'LU', 'ERREUR']);
            $table->text('contenu_email');
            $table->json('configuration_type');
            $table->timestamp('date_creation')->useCurrent();

            // Index
            $table->index('reunion_id');
            $table->index('type');
            $table->index('envoye_a');
            $table->index('statut');
            $table->index('envoye_le');

            // Foreign keys
            $table->foreign('reunion_id')->references('id')->on('reunions')->onDelete('cascade');
            $table->foreign('envoye_a')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reunion_notifications');
    }
};
