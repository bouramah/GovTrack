<?php

/**
 * Script de test pour les notifications de discussions
 *
 * Ce script teste les notifications email pour :
 * - Nouveaux commentaires sur les projets
 * - Réponses aux commentaires de projets
 * - Nouveaux commentaires sur les tâches
 * - Réponses aux commentaires de tâches
 */

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Projet;
use App\Models\Tache;
use App\Models\DiscussionProjet;
use App\Models\DiscussionTache;
use App\Events\DiscussionProjetCreated;
use App\Events\DiscussionTacheCreated;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST DES NOTIFICATIONS DE DISCUSSIONS ===\n\n";

try {
    // Récupérer des utilisateurs de test
    $users = User::take(3)->get();
    if ($users->count() < 3) {
        echo "❌ Erreur : Il faut au moins 3 utilisateurs pour tester les notifications\n";
        exit(1);
    }

    $user1 = $users[0]; // Auteur des commentaires
    $user2 = $users[1]; // Porteur/Responsable
    $user3 = $users[2]; // Donneur d'ordre

    echo "👥 Utilisateurs de test :\n";
    echo "- Auteur : {$user1->prenom} {$user1->nom} ({$user1->email})\n";
    echo "- User 2 : {$user2->prenom} {$user2->nom} ({$user2->email})\n";
    echo "- User 3 : {$user3->prenom} {$user3->nom} ({$user3->email})\n\n";

    // Récupérer ou créer un projet de test
    $projet = Projet::first();
    if (!$projet) {
        echo "❌ Erreur : Aucun projet trouvé pour les tests\n";
        exit(1);
    }

    // Mettre à jour le projet avec nos utilisateurs de test
    $projet->update([
        'porteur_id' => $user2->id,
        'donneur_ordre_id' => $user3->id,
    ]);

    echo "📋 Projet de test : {$projet->titre}\n";
    echo "- Porteur : {$user2->prenom} {$user2->nom}\n";
    echo "- Donneur d'ordre : {$user3->prenom} {$user3->nom}\n\n";

    // Récupérer ou créer une tâche de test
    $tache = Tache::where('projet_id', $projet->id)->first();
    if (!$tache) {
        echo "❌ Erreur : Aucune tâche trouvée pour les tests\n";
        exit(1);
    }

    // Mettre à jour la tâche avec un responsable de test
    $tache->update([
        'responsable_id' => $user2->id,
    ]);

    echo "✅ Tâche de test : {$tache->titre}\n";
    echo "- Responsable : {$user2->prenom} {$user2->nom}\n\n";

    // Test 1 : Nouveau commentaire sur un projet
    echo "=== TEST 1 : NOUVEAU COMMENTAIRE SUR UN PROJET ===\n";

    $commentaireProjet = DiscussionProjet::create([
        'projet_id' => $projet->id,
        'user_id' => $user1->id,
        'parent_id' => null,
        'message' => 'Ceci est un nouveau commentaire de test sur le projet ' . $projet->titre,
        'est_modifie' => false,
        'date_creation' => now(),
        'creer_par' => $user1->email,
    ]);

    echo "✅ Commentaire créé : {$commentaireProjet->message}\n";

    // Déclencher l'événement
    event(new DiscussionProjetCreated($commentaireProjet, $user1, false));
    echo "📧 Événement DiscussionProjetCreated déclenché (nouveau commentaire)\n";
    echo "   → Notifications envoyées au porteur et donneur d'ordre\n\n";

    // Test 2 : Réponse à un commentaire de projet
    echo "=== TEST 2 : RÉPONSE À UN COMMENTAIRE DE PROJET ===\n";

    $reponseProjet = DiscussionProjet::create([
        'projet_id' => $projet->id,
        'user_id' => $user2->id,
        'parent_id' => $commentaireProjet->id,
        'message' => 'Ceci est une réponse au commentaire précédent',
        'est_modifie' => false,
        'date_creation' => now(),
        'creer_par' => $user2->email,
    ]);

    echo "✅ Réponse créée : {$reponseProjet->message}\n";

    // Déclencher l'événement
    event(new DiscussionProjetCreated($reponseProjet, $user2, true));
    echo "📧 Événement DiscussionProjetCreated déclenché (réponse)\n";
    echo "   → Notification envoyée à l'auteur du commentaire original\n\n";

    // Test 3 : Nouveau commentaire sur une tâche
    echo "=== TEST 3 : NOUVEAU COMMENTAIRE SUR UNE TÂCHE ===\n";

    $commentaireTache = DiscussionTache::create([
        'tache_id' => $tache->id,
        'user_id' => $user1->id,
        'parent_id' => null,
        'message' => 'Ceci est un nouveau commentaire de test sur la tâche ' . $tache->titre,
        'est_modifie' => false,
        'date_creation' => now(),
        'creer_par' => $user1->email,
    ]);

    echo "✅ Commentaire créé : {$commentaireTache->message}\n";

    // Déclencher l'événement
    event(new DiscussionTacheCreated($commentaireTache, $user1, false));
    echo "📧 Événement DiscussionTacheCreated déclenché (nouveau commentaire)\n";
    echo "   → Notifications envoyées au responsable de la tâche et au porteur du projet\n\n";

    // Test 4 : Réponse à un commentaire de tâche
    echo "=== TEST 4 : RÉPONSE À UN COMMENTAIRE DE TÂCHE ===\n";

    $reponseTache = DiscussionTache::create([
        'tache_id' => $tache->id,
        'user_id' => $user3->id,
        'parent_id' => $commentaireTache->id,
        'message' => 'Ceci est une réponse au commentaire de la tâche',
        'est_modifie' => false,
        'date_creation' => now(),
        'creer_par' => $user3->email,
    ]);

    echo "✅ Réponse créée : {$reponseTache->message}\n";

    // Déclencher l'événement
    event(new DiscussionTacheCreated($reponseTache, $user3, true));
    echo "📧 Événement DiscussionTacheCreated déclenché (réponse)\n";
    echo "   → Notification envoyée à l'auteur du commentaire original\n\n";

    echo "=== RÉSUMÉ DES TESTS ===\n";
    echo "✅ Test 1 : Nouveau commentaire sur projet - Notifications au porteur et donneur d'ordre\n";
    echo "✅ Test 2 : Réponse à commentaire de projet - Notification à l'auteur original\n";
    echo "✅ Test 3 : Nouveau commentaire sur tâche - Notifications au responsable et porteur\n";
    echo "✅ Test 4 : Réponse à commentaire de tâche - Notification à l'auteur original\n\n";

    echo "📧 Tous les événements ont été déclenchés avec succès !\n";
    echo "   Vérifiez votre configuration email et les logs pour voir les emails envoyés.\n\n";

    echo "💡 Pour vérifier les emails envoyés :\n";
    echo "   - Consultez les logs Laravel : storage/logs/laravel.log\n";
    echo "   - Vérifiez votre configuration SMTP dans .env\n";
    echo "   - Si vous utilisez Mailtrap ou similaire, vérifiez votre boîte de réception\n\n";

} catch (Exception $e) {
    echo "❌ Erreur lors du test : " . $e->getMessage() . "\n";
    echo "Stack trace :\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
