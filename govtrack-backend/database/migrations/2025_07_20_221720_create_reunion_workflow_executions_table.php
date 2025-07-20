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
        Schema::create('reunion_workflow_executions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reunion_id');
            $table->unsignedBigInteger('workflow_config_id');
            $table->integer('etape_actuelle');
            $table->enum('statut_global', ['EN_COURS', 'TERMINE', 'BLOQUE']);
            $table->timestamp('date_debut');
            $table->timestamp('date_fin')->nullable();
            $table->json('historique_etapes');
            $table->text('commentaire')->nullable();
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent();

            // Index
            $table->index('reunion_id');
            $table->index('workflow_config_id');
            $table->index('statut_global');
            $table->index('etape_actuelle');
            $table->index('date_debut');

            // Foreign keys
            $table->foreign('reunion_id')->references('id')->on('reunions')->onDelete('cascade');
            $table->foreign('workflow_config_id')->references('id')->on('reunion_workflow_configs')->onDelete('cascade');

            // Unique constraint
            $table->unique(['reunion_id', 'workflow_config_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reunion_workflow_executions');
    }
};
