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
        Schema::create('reunion_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reunion_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('role', ['PRESIDENT', 'SECRETAIRE', 'PARTICIPANT', 'OBSERVATEUR', 'VALIDATEUR_PV']);
            $table->enum('type', ['PERMANENT', 'INVITE']);
            $table->enum('statut_presence', ['CONFIRME', 'ABSENT', 'EN_ATTENTE']);
            $table->timestamp('present_le')->nullable();
            $table->timestamp('absent_le')->nullable();
            $table->text('commentaire_absence')->nullable();
            $table->boolean('notifie_absence')->default(false);
            $table->json('notifications_actives');
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent();
            $table->unsignedBigInteger('creer_par');
            $table->unsignedBigInteger('modifier_par');

            // Index
            $table->index('reunion_id');
            $table->index('user_id');
            $table->index('role');
            $table->index('type');
            $table->index('statut_presence');

            // Foreign keys
            $table->foreign('reunion_id')->references('id')->on('reunions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('creer_par')->references('id')->on('users');
            $table->foreign('modifier_par')->references('id')->on('users');

            // Unique constraint
            $table->unique(['reunion_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reunion_participants');
    }
};
