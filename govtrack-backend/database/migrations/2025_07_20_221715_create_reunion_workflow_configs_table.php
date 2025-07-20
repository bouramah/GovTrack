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
        Schema::create('reunion_workflow_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_reunion_id');
            $table->string('nom_workflow', 100);
            $table->json('etapes');
            $table->boolean('actif')->default(true);
            $table->boolean('obligatoire')->default(true);
            $table->json('configuration');
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent();
            $table->unsignedBigInteger('creer_par');
            $table->unsignedBigInteger('modifier_par');

            // Index
            $table->index('type_reunion_id');
            $table->index('actif');
            $table->index('obligatoire');

            // Foreign keys
            $table->foreign('type_reunion_id')->references('id')->on('type_reunions')->onDelete('cascade');
            $table->foreign('creer_par')->references('id')->on('users');
            $table->foreign('modifier_par')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reunion_workflow_configs');
    }
};
