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
        Schema::create('reunion_ordre_jours', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reunion_id');
            $table->integer('ordre');
            $table->string('titre', 200);
            $table->text('description');
            $table->enum('type', ['SUJET_SPECIFIQUE', 'POINT_DIVERS', 'SUIVI_PROJETS']);
            $table->integer('duree_estimee_minutes');
            $table->unsignedBigInteger('entite_proposante_id')->nullable();
            $table->unsignedBigInteger('responsable_id')->nullable();
            $table->unsignedBigInteger('projet_id')->nullable();
            $table->enum('statut', ['PLANIFIE', 'EN_COURS', 'TERMINE', 'REPORTE']);
            $table->enum('niveau_detail_requis', ['SIMPLE', 'DETAILLE']);
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent();
            $table->unsignedBigInteger('creer_par');
            $table->unsignedBigInteger('modifier_par');

            // Index
            $table->index('reunion_id');
            $table->index('ordre');
            $table->index('type');
            $table->index('statut');
            $table->index('entite_proposante_id');
            $table->index('responsable_id');
            $table->index('projet_id');

            // Foreign keys
            $table->foreign('reunion_id')->references('id')->on('reunions')->onDelete('cascade');
            $table->foreign('entite_proposante_id')->references('id')->on('entites')->onDelete('set null');
            $table->foreign('responsable_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('projet_id')->references('id')->on('projets')->onDelete('set null');
            $table->foreign('creer_par')->references('id')->on('users');
            $table->foreign('modifier_par')->references('id')->on('users');

            // Unique constraint
            $table->unique(['reunion_id', 'ordre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reunion_ordre_jours');
    }
};
