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
        Schema::create('type_reunion_validateur_pvs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_reunion_id');
            $table->enum('role_validateur', ['SECRETAIRE', 'PRESIDENT', 'AUTRE']);
            $table->unsignedBigInteger('user_id')->nullable(); // Si validateur spécifique
            $table->integer('ordre_priorite');
            $table->boolean('actif')->default(true);
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent();
            $table->unsignedBigInteger('creer_par');
            $table->unsignedBigInteger('modifier_par');

            // Index
            $table->index('type_reunion_id');
            $table->index('role_validateur');
            $table->index('user_id');
            $table->index('ordre_priorite');
            $table->index('actif');

            // Foreign keys
            $table->foreign('type_reunion_id')->references('id')->on('type_reunions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('creer_par')->references('id')->on('users');
            $table->foreign('modifier_par')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_reunion_validateur_pvs');
    }
};
