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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Informations sur l'action
            $table->string('action'); // 'delete', 'restore', etc.
            $table->string('table_name'); // Nom de la table affectée
            $table->unsignedBigInteger('record_id'); // ID de l'enregistrement supprimé
            $table->string('record_type'); // Type d'enregistrement (User, Entite, Projet, etc.)

            // Données de l'enregistrement supprimé (JSON)
            $table->json('deleted_data')->nullable(); // Données complètes avant suppression
            $table->text('deleted_data_summary')->nullable(); // Résumé lisible des données supprimées

            // Informations sur l'utilisateur qui a effectué l'action
            $table->unsignedBigInteger('user_id')->nullable(); // ID de l'utilisateur
            $table->string('user_name')->nullable(); // Nom de l'utilisateur (backup)
            $table->string('user_email')->nullable(); // Email de l'utilisateur (backup)

            // Informations sur le contexte
            $table->string('ip_address')->nullable(); // Adresse IP
            $table->text('user_agent')->nullable(); // User agent du navigateur
            $table->string('request_url')->nullable(); // URL de la requête
            $table->string('request_method')->nullable(); // Méthode HTTP (DELETE, POST, etc.)

            // Informations supplémentaires
            $table->text('reason')->nullable(); // Raison de la suppression (si fournie)
            $table->json('metadata')->nullable(); // Données supplémentaires en JSON

            // Timestamps
            $table->timestamps();

            // Index pour les performances
            $table->index(['action', 'table_name']);
            $table->index(['user_id']);
            $table->index(['record_type', 'record_id']);
            $table->index(['created_at']);

            // Contrainte de clé étrangère pour user_id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
