<?php

require_once 'vendor/autoload.php';

use App\Models\Projet;
use App\Models\Tache;
use App\Models\User;
use App\Models\TypeProjet;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST NIVEAU D'EXÉCUTION AUTOMATIQUE ===\n\n";

try {
    // 1. Créer un projet sans tâches
    echo "1. Test projet sans tâches (mode manuel)\n";
    $projet = Projet::first();
    if (!$projet) {
        echo "Aucun projet trouvé dans la base de données\n";
        exit;
    }

    echo "Projet: {$projet->titre}\n";
    echo "Niveau actuel: {$projet->niveau_execution}%\n";
    echo "A des tâches: " . ($projet->aDesTaches() ? 'Oui' : 'Non') . "\n";
    echo "Mode automatique: " . ($projet->niveau_execution_automatique ? 'Oui' : 'Non') . "\n\n";

    // 2. Ajouter une tâche et vérifier le calcul automatique
    echo "2. Test ajout de tâche (passage en mode automatique)\n";

    // Créer une tâche
    $tache = new Tache();
    $tache->titre = "Tâche de test";
    $tache->description = "Tâche pour tester le niveau d'exécution automatique";
    $tache->projet_id = $projet->id;
    $tache->responsable_id = User::first()->id;
    $tache->statut = 'en_cours';
    $tache->niveau_execution = 50;
    $tache->date_creation = now();
    $tache->date_modification = now();
    $tache->creer_par = 'test@example.com';
    $tache->save();

    echo "Tâche créée avec niveau: 50%\n";

    // Recharger le projet
    $projet->refresh();
    echo "Niveau projet après ajout tâche: {$projet->niveau_execution}%\n";
    echo "A des tâches: " . ($projet->aDesTaches() ? 'Oui' : 'Non') . "\n";
    echo "Mode automatique: " . ($projet->niveau_execution_automatique ? 'Oui' : 'Non') . "\n\n";

    // 3. Modifier le niveau de la tâche
    echo "3. Test modification niveau de tâche\n";
    $tache->niveau_execution = 75;
    $tache->save();

    $projet->refresh();
    echo "Niveau projet après modification tâche: {$projet->niveau_execution}%\n\n";

    // 4. Ajouter une deuxième tâche
    echo "4. Test ajout d'une deuxième tâche\n";
    $tache2 = new Tache();
    $tache2->titre = "Deuxième tâche de test";
    $tache2->description = "Deuxième tâche pour tester la moyenne";
    $tache2->projet_id = $projet->id;
    $tache2->responsable_id = User::first()->id;
    $tache2->statut = 'en_cours';
    $tache2->niveau_execution = 25;
    $tache2->date_creation = now();
    $tache2->date_modification = now();
    $tache2->creer_par = 'test@example.com';
    $tache2->save();

    $projet->refresh();
    echo "Niveau projet après ajout deuxième tâche: {$projet->niveau_execution}%\n";
    echo "Moyenne attendue: " . round((75 + 25) / 2) . "%\n\n";

    // 5. Supprimer une tâche
    echo "5. Test suppression de tâche\n";
    $tache->delete();

    $projet->refresh();
    echo "Niveau projet après suppression tâche: {$projet->niveau_execution}%\n";
    echo "Niveau de la tâche restante: {$tache2->niveau_execution}%\n\n";

    // 6. Supprimer la dernière tâche (retour en mode manuel)
    echo "6. Test suppression dernière tâche (retour mode manuel)\n";
    $tache2->delete();

    $projet->refresh();
    echo "Niveau projet après suppression dernière tâche: {$projet->niveau_execution}%\n";
    echo "A des tâches: " . ($projet->aDesTaches() ? 'Oui' : 'Non') . "\n";
    echo "Mode automatique: " . ($projet->niveau_execution_automatique ? 'Oui' : 'Non') . "\n\n";

    echo "=== TEST TERMINÉ AVEC SUCCÈS ===\n";

} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . "\n";
    echo "Ligne: " . $e->getLine() . "\n";
}
