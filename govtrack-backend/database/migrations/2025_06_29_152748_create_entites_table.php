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
        Schema::create('entites', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->foreignId('type_entite_id')->constrained('type_entites')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('entites')->onDelete('set null');
            $table->text('description')->nullable();
            $table->datetime('date_creation');
            $table->datetime('date_modification');
            $table->string('creer_par');
            $table->string('modifier_par')->nullable();

            $table->index('nom');
            $table->index('type_entite_id');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entites');
    }
};
