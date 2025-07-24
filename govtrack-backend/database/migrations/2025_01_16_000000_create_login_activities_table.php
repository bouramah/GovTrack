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
        Schema::create('login_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('action', ['login', 'logout', 'failed_login', 'password_reset', 'session_expired']);
            $table->string('ip_address', 45)->nullable(); // IPv6 peut être jusqu'à 45 caractères
            $table->text('user_agent')->nullable();
            $table->string('location')->nullable(); // Pays/Ville si disponible
            $table->string('device_type')->nullable(); // desktop, mobile, tablet
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->text('session_id')->nullable();
            // $table->timestamps(); // Ajoute created_at et updated_at
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_activities');
    }
};
