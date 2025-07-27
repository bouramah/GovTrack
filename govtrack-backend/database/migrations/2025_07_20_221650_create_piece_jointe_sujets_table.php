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
        Schema::create('piece_jointe_sujets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reunion_sujet_id');
            $table->unsignedBigInteger('user_id');
            $table->string('fichier_path');
            $table->string('nom_original');
            $table->string('mime_type');
            $table->unsignedBigInteger('taille');
            $table->text('description')->nullable();
            $table->enum('type_document', ['rapport', 'justificatif', 'piece_jointe', 'documentation', 'autre'])->default('piece_jointe');
            $table->timestamp('date_creation')->useCurrent();

            // Index
            $table->index('reunion_sujet_id');
            $table->index('user_id');
            $table->index('type_document');
            $table->index('date_creation');

            // Foreign keys
            $table->foreign('reunion_sujet_id')->references('id')->on('reunion_sujets')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piece_jointe_sujets');
    }
};
