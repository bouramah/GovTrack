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
        Schema::create('discussion_taches', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('tache_id')->constrained('taches')->onDelete('cascade')->comment('Référence vers la tâche');
            $table->foreignId('user_id')->constrained('users')->comment('Utilisateur auteur du message');
            $table->foreignId('parent_id')->nullable()->constrained('discussion_taches')->onDelete('cascade')->comment('Réponse à un message (nullable)');

            // Contenu du message
            $table->text('message')->comment('Contenu du message');
            $table->boolean('est_modifie')->default(false)->comment('Si le message a été modifié');

            // Champs d'audit
            $table->datetime('date_creation')->comment('Date de création');
            $table->datetime('date_modification')->nullable()->comment('Date de dernière modification');
            $table->string('creer_par')->comment('Utilisateur qui a créé');
            $table->string('modifier_par')->nullable()->comment('Utilisateur qui a modifié');

            // Index pour les recherches et performances
            $table->index(['tache_id', 'date_creation']);
            $table->index(['user_id', 'date_creation']);
            $table->index(['parent_id', 'date_creation']);
            $table->index('date_creation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discussion_taches');
    }
};
