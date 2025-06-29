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
        Schema::create('entite_chef_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entite_id')->constrained('entites')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->datetime('date_creation');
            $table->datetime('date_modification');
            $table->string('creer_par');
            $table->string('modifier_par')->nullable();

            $table->index(['entite_id', 'date_fin']);
            $table->index(['user_id', 'date_fin']);
            $table->index('date_debut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entite_chef_histories');
    }
};
