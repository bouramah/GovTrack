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
        Schema::create('reunion_notification_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_reunion_id');
            $table->enum('type_notification', ['CONFIRMATION_PRESENCE', 'RAPPEL', 'PV_DISPONIBLE', 'RAPPEL_ACTIONS']);
            $table->boolean('actif')->default(true);
            $table->integer('delai_jours')->nullable(); // Délai en jours pour les rappels
            $table->string('template_email', 100);
            $table->json('destinataires_par_defaut');
            $table->json('configuration_avancee');
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent();
            $table->unsignedBigInteger('creer_par');
            $table->unsignedBigInteger('modifier_par');

            // Index
            $table->index('type_reunion_id');
            $table->index('type_notification');
            $table->index('actif');

            // Foreign keys
            $table->foreign('type_reunion_id')->references('id')->on('type_reunions')->onDelete('cascade');
            $table->foreign('creer_par')->references('id')->on('users');
            $table->foreign('modifier_par')->references('id')->on('users');

            // Unique constraint avec nom court
            $table->unique(['type_reunion_id', 'type_notification'], 'reunion_notif_config_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reunion_notification_configs');
    }
};
