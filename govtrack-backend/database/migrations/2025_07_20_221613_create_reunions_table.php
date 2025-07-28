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
        Schema::create('reunions', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 200);
            $table->text('description');
            $table->unsignedBigInteger('type_reunion_id');
            $table->enum('niveau_complexite_actuel', ['SIMPLE', 'INTERMEDIAIRE', 'COMPLEXE']);
            $table->timestamp('date_debut');
            $table->timestamp('date_fin')->nullable(); // Changé en nullable
            $table->string('lieu', 200);
            $table->enum('type_lieu', ['PHYSIQUE', 'VIRTUEL', 'HYBRIDE']);
            $table->string('lien_virtuel', 500)->nullable();
            $table->enum('periodicite', ['PONCTUELLE', 'HEBDOMADAIRE', 'BIHEBDOMADAIRE', 'MENSUELLE']);
            $table->unsignedBigInteger('serie_id')->nullable();
            $table->boolean('suspendue')->default(false);
            $table->timestamp('reprogrammee_le')->nullable();
            $table->json('fonctionnalites_actives');
            $table->integer('quorum_minimum')->nullable();
            $table->enum('ordre_du_jour_type', ['EXPLICITE', 'IMPLICITE', 'HYBRIDE']);
            $table->enum('statut', ['PLANIFIEE', 'EN_COURS', 'TERMINEE', 'ANNULEE', 'REPORTEE']);
            $table->unsignedBigInteger('pv_valide_par_id')->nullable();
            $table->timestamp('pv_valide_le')->nullable();
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent();
            $table->unsignedBigInteger('creer_par');
            $table->unsignedBigInteger('modifier_par');

            // Index
            $table->index('type_reunion_id');
            $table->index('serie_id');
            $table->index('niveau_complexite_actuel');
            $table->index('date_debut');
            $table->index('statut');
            $table->index('suspendue');

            // Foreign keys
            $table->foreign('type_reunion_id')->references('id')->on('type_reunions')->onDelete('cascade');
            $table->foreign('serie_id')->references('id')->on('reunion_series')->onDelete('set null');
            $table->foreign('pv_valide_par_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('creer_par')->references('id')->on('users');
            $table->foreign('modifier_par')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reunions');
    }
};
