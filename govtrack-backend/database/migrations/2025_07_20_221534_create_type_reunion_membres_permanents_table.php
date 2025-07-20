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
        Schema::create('type_reunion_membres_permanents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_reunion_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('role_defaut', ['PRESIDENT', 'SECRETAIRE', 'PARTICIPANT', 'OBSERVATEUR']);
            $table->boolean('actif')->default(true);
            $table->json('notifications_par_defaut');
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_modification')->useCurrent();
            $table->unsignedBigInteger('creer_par');
            $table->unsignedBigInteger('modifier_par');

            // Index
            $table->index('type_reunion_id');
            $table->index('user_id');
            $table->index('role_defaut');
            $table->index('actif');

            // Foreign keys
            $table->foreign('type_reunion_id')->references('id')->on('type_reunions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('creer_par')->references('id')->on('users');
            $table->foreign('modifier_par')->references('id')->on('users');

            // Unique constraint
            $table->unique(['type_reunion_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_reunion_membres_permanents');
    }
};
