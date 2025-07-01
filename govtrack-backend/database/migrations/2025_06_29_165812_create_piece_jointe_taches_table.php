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
        Schema::create('piece_jointe_taches', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('tache_id')->constrained('taches')->onDelete('cascade')->comment('Référence vers la tâche');
            $table->foreignId('user_id')->constrained('users')->comment('Utilisateur ayant ajouté le fichier');

            // Informations du fichier
            $table->string('fichier_path')->comment('Chemin vers le fichier stocké');
            $table->string('nom_original')->comment('Nom original du fichier');
            $table->string('mime_type')->nullable()->comment('Type MIME du fichier');
            $table->bigInteger('taille')->nullable()->comment('Taille du fichier en octets');
            $table->text('description')->nullable()->comment('Description de la pièce jointe');

            // Métadonnées
            $table->boolean('est_justificatif')->default(false)->comment('Si c\'est un justificatif obligatoire');
            $table->string('type_document')->nullable()->comment('Type de document (rapport, justificatif, etc.)');

            // Audit
            $table->datetime('date_creation')->comment('Date d\'ajout');

            // Index pour les recherches
            $table->index(['tache_id', 'date_creation']);
            $table->index(['user_id', 'date_creation']);
            $table->index('est_justificatif');
            $table->index('type_document');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piece_jointe_taches');
    }
};
