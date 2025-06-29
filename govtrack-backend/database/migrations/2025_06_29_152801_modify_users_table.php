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
        Schema::table('users', function (Blueprint $table) {
            // Ajouter les nouveaux champs
            $table->string('matricule')->unique()->after('id');
            $table->string('nom')->after('matricule');
            $table->string('prenom')->after('nom');
            $table->string('telephone')->nullable()->after('email');
            $table->text('adresse')->nullable()->after('telephone');
            $table->string('photo')->nullable()->after('adresse');
            $table->boolean('statut')->default(true)->after('photo');
            $table->datetime('date_creation')->after('updated_at');
            $table->datetime('date_modification')->after('date_creation');
            $table->string('creer_par')->after('date_modification');
            $table->string('modifier_par')->nullable()->after('creer_par');

            // Ajouter des index
            $table->index('matricule');
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['matricule']);
            $table->dropIndex(['statut']);
            $table->dropColumn([
                'matricule', 'nom', 'prenom', 'telephone', 'adresse',
                'photo', 'statut', 'date_creation', 'date_modification',
                'creer_par', 'modifier_par'
            ]);
        });
    }
};
