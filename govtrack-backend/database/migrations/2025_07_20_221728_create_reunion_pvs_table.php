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
        Schema::create('reunion_pvs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reunion_id');
            $table->longText('contenu');
            $table->unsignedBigInteger('redige_par_id');
            $table->timestamp('redige_le');
            $table->timestamp('modifie_le')->nullable(); // Changé en nullable
            $table->integer('version')->default(1);
            $table->unsignedBigInteger('valide_par_id')->nullable();
            $table->timestamp('valide_le')->nullable();
            $table->enum('statut', ['BROUILLON', 'VALIDE', 'PUBLIE']);
            $table->text('commentaire_validation')->nullable();
            $table->boolean('notifications_envoyees')->default(false);
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent();

            // Index
            $table->index('reunion_id');
            $table->index('redige_par_id');
            $table->index('valide_par_id');
            $table->index('statut');
            $table->index('version');

            // Foreign keys
            $table->foreign('reunion_id')->references('id')->on('reunions')->onDelete('cascade');
            $table->foreign('redige_par_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('valide_par_id')->references('id')->on('users')->onDelete('set null');

            // Unique constraint
            $table->unique(['reunion_id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reunion_pvs');
    }
};
