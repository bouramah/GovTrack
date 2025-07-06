<?php

/**
 * Script de test pour les notifications de tâches
 *
 * Ce script teste le système de notifications par email pour les tâches :
 * - Création de tâche
 * - Changement de statut
 * - Mise à jour du niveau d'exécution
 *
 * Utilisation :
 * php test_notifications_taches.php
 */

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Projet;
use App\Models\Tache;
use App\Events\TacheCreated;
use App\Events\TacheStatusChanged;
use App\Events\TacheExecutionLevelUpdated;

// Démarrer l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🧪 Test des notifications de tâches\n";
echo "=====================================\n\n";

try {
    // Configuration pour les tests
    config(['mail.default' => 'log']); // Utiliser le driver log pour les tests

    // Récupérer des utilisateurs de test
    $users = User::take(3)->get();

    if ($users->count() < 3) {
        echo "❌ Erreur : Il faut au moins 3 utilisateurs dans la base de données pour les tests\n";
        exit(1);
    }

    $creator = $users[0];
    $porteur = $users[1];
    $responsable = $users[2];

    echo "👥 Utilisateurs de test :\n";
    echo "   - Créateur : {$creator->prenom} {$creator->nom} ({$creator->email})\n";
    echo "   - Porteur : {$porteur->prenom} {$porteur->nom} ({$porteur->email})\n";
    echo "   - Responsable : {$responsable->prenom} {$responsable->nom} ({$responsable->email})\n\n";

    // Créer un projet de test
    echo "📋 Création d'un projet de test...\n";
    $projet = Projet::create([
        'titre' => 'Projet de test pour notifications',
        'description' => 'Projet créé pour tester les notifications de tâches',
        'type_projet_id' => 1, // Assurez-vous que ce type existe
        'porteur_id' => $porteur->id,
        'donneur_ordre_id' => $creator->id,
        'date_debut_previsionnelle' => now()->addDays(7),
        'date_fin_previsionnelle' => now()->addDays(30),
        'statut' => 'en_cours',
        'niveau_execution' => 25,
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => $creator->email,
        'modifier_par' => $creator->email,
    ]);

    echo "✅ Projet créé : {$projet->titre} (ID: {$projet->id})\n\n";

    // Test 1 : Création de tâche
    echo "🎯 Test 1 : Création de tâche\n";
    echo "-----------------------------\n";

    $tache = Tache::create([
        'titre' => 'Tâche de test pour notifications',
        'description' => 'Tâche créée pour tester les notifications',
        'projet_id' => $projet->id,
        'responsable_id' => $responsable->id,
        'date_debut_previsionnelle' => now()->addDays(1),
        'date_fin_previsionnelle' => now()->addDays(7),
        'statut' => 'a_faire',
        'niveau_execution' => 0,
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => $creator->email,
        'modifier_par' => $creator->email,
    ]);

    echo "✅ Tâche créée : {$tache->titre} (ID: {$tache->id})\n";

    // Déclencher l'événement de création
    event(new TacheCreated($tache, $creator));
    echo "📧 Événement TacheCreated déclenché\n\n";

    // Test 2 : Changement de statut
    echo "🔄 Test 2 : Changement de statut\n";
    echo "-------------------------------\n";

    $ancienStatut = $tache->statut;
    $nouveauStatut = 'en_cours';

    $tache->update([
        'statut' => $nouveauStatut,
        'date_modification' => now(),
        'modifier_par' => $responsable->email,
    ]);

    echo "✅ Statut changé : {$ancienStatut} → {$nouveauStatut}\n";

    // Déclencher l'événement de changement de statut
    event(new TacheStatusChanged($tache, $responsable, $ancienStatut, $nouveauStatut, 'Début du travail sur la tâche'));
    echo "📧 Événement TacheStatusChanged déclenché\n\n";

    // Test 3 : Mise à jour du niveau d'exécution
    echo "📈 Test 3 : Mise à jour du niveau d'exécution\n";
    echo "--------------------------------------------\n";

    $ancienNiveau = $tache->niveau_execution;
    $nouveauNiveau = 50;

    $tache->update([
        'niveau_execution' => $nouveauNiveau,
        'date_modification' => now(),
        'modifier_par' => $responsable->email,
    ]);

    echo "✅ Niveau d'exécution mis à jour : {$ancienNiveau}% → {$nouveauNiveau}%\n";

    // Déclencher l'événement de mise à jour du niveau d'exécution
    event(new TacheExecutionLevelUpdated($tache, $responsable, $ancienNiveau, $nouveauNiveau, 'Progression significative réalisée'));
    echo "📧 Événement TacheExecutionLevelUpdated déclenché\n\n";

    // Test 4 : Changement de statut vers terminé
    echo "✅ Test 4 : Changement de statut vers terminé\n";
    echo "--------------------------------------------\n";

    $ancienStatut = $tache->statut;
    $nouveauStatut = 'termine';

    $tache->update([
        'statut' => $nouveauStatut,
        'niveau_execution' => 100,
        'date_modification' => now(),
        'modifier_par' => $porteur->email,
    ]);

    echo "✅ Statut changé : {$ancienStatut} → {$nouveauStatut}\n";

    // Déclencher l'événement de changement de statut
    event(new TacheStatusChanged($tache, $porteur, $ancienStatut, $nouveauStatut, 'Tâche terminée avec succès'));
    echo "📧 Événement TacheStatusChanged déclenché\n\n";

    echo "🎉 Tests terminés avec succès !\n\n";

    // Afficher les informations de debug
    echo "📋 Informations de debug :\n";
    echo "   - Projet ID : {$projet->id}\n";
    echo "   - Tâche ID : {$tache->id}\n";
    echo "   - Logs d'email disponibles dans : storage/logs/laravel.log\n";
    echo "   - Vérifiez les logs pour voir les notifications envoyées\n\n";

    // Nettoyer les données de test
    echo "🧹 Nettoyage des données de test...\n";
    $tache->delete();
    $projet->delete();
    echo "✅ Données de test supprimées\n";

} catch (Exception $e) {
    echo "❌ Erreur lors des tests : " . $e->getMessage() . "\n";
    echo "Stack trace :\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "✅ Script de test terminé avec succès !\n";
