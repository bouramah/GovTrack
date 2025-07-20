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
        Schema::create('reunion_series', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->text('description');
            $table->unsignedBigInteger('type_reunion_id');
            $table->enum('periodicite', ['HEBDOMADAIRE', 'BIHEBDOMADAIRE', 'MENSUELLE']);
            $table->integer('jour_semaine')->nullable(); // Jour semaine (1-7) pour hebdomadaire
            $table->integer('jour_mois')->nullable(); // Jour du mois (1-31) pour mensuel
            $table->time('heure_debut');
            $table->integer('duree_minutes');
            $table->string('lieu_defaut', 200);
            $table->boolean('actif')->default(true);
            $table->date('date_debut_serie');
            $table->date('date_fin_serie');
            $table->boolean('suspendue')->default(false);
            $table->timestamp('prochaine_generation')->nullable();
            $table->json('configuration_recurrence');
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent();
            $table->unsignedBigInteger('creer_par');
            $table->unsignedBigInteger('modifier_par');

            // Index
            $table->index('type_reunion_id');
            $table->index('periodicite');
            $table->index('actif');
            $table->index('suspendue');
            $table->index('prochaine_generation');

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
        Schema::dropIfExists('reunion_series');
    }
};
