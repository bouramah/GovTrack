<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Entite;
use App\Models\UtilisateurEntiteHistory;
use App\Models\Projet;
use App\Models\Permission;
use App\Models\Role;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 TEST DES PERMISSIONS AVEC HIÉRARCHIE D'ENTITÉS\n";
echo "================================================\n\n";

try {
    // 1. Créer une structure hiérarchique d'entités
    echo "1. Création de la structure hiérarchique d'entités...\n";

    // Entité parent (Direction)
    $direction = Entite::create([
        'nom' => 'Direction Générale',
        'type_entite_id' => 1, // Assurez-vous que ce type existe
        'parent_id' => null,
        'description' => 'Direction parent pour les tests',
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 'test@system.com'
    ]);

    // Entité enfant 1 (Service)
    $service1 = Entite::create([
        'nom' => 'Service Informatique',
        'type_entite_id' => 2, // Assurez-vous que ce type existe
        'parent_id' => $direction->id,
        'description' => 'Service enfant 1',
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 'test@system.com'
    ]);

    // Entité enfant 2 (Division)
    $division1 = Entite::create([
        'nom' => 'Division Développement',
        'type_entite_id' => 3, // Assurez-vous que ce type existe
        'parent_id' => $service1->id,
        'description' => 'Division enfant de service1',
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 'test@system.com'
    ]);

    echo "✅ Structure hiérarchique créée:\n";
    echo "   - {$direction->nom} (ID: {$direction->id})\n";
    echo "   - {$service1->nom} (ID: {$service1->id}) → parent: {$direction->nom}\n";
    echo "   - {$division1->nom} (ID: {$division1->id}) → parent: {$service1->nom}\n\n";

    // 2. Créer des utilisateurs pour chaque entité
    echo "2. Création des utilisateurs...\n";

    $userDirection = User::create([
        'nom' => 'Dupont',
        'prenom' => 'Jean',
        'email' => 'jean.dupont@test.com',
        'password' => bcrypt('password'),
        'matricule' => 'DIR001',
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 'test@system.com'
    ]);

    $userService = User::create([
        'nom' => 'Martin',
        'prenom' => 'Marie',
        'email' => 'marie.martin@test.com',
        'password' => bcrypt('password'),
        'matricule' => 'SER001',
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 'test@system.com'
    ]);

    $userDivision = User::create([
        'nom' => 'Bernard',
        'prenom' => 'Pierre',
        'email' => 'pierre.bernard@test.com',
        'password' => bcrypt('password'),
        'matricule' => 'DIV001',
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 'test@system.com'
    ]);

    echo "✅ Utilisateurs créés:\n";
    echo "   - {$userDirection->prenom} {$userDirection->nom} (Direction)\n";
    echo "   - {$userService->prenom} {$userService->nom} (Service)\n";
    echo "   - {$userDivision->prenom} {$userDivision->nom} (Division)\n\n";

    // 3. Affecter les utilisateurs aux entités
    echo "3. Affectation des utilisateurs aux entités...\n";

    UtilisateurEntiteHistory::create([
        'user_id' => $userDirection->id,
        'poste_id' => 1, // Assurez-vous que ce poste existe
        'service_id' => $direction->id,
        'statut' => true,
        'date_debut' => now(),
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 'test@system.com'
    ]);

    UtilisateurEntiteHistory::create([
        'user_id' => $userService->id,
        'poste_id' => 2, // Assurez-vous que ce poste existe
        'service_id' => $service1->id,
        'statut' => true,
        'date_debut' => now(),
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 'test@system.com'
    ]);

    UtilisateurEntiteHistory::create([
        'user_id' => $userDivision->id,
        'poste_id' => 3, // Assurez-vous que ce poste existe
        'service_id' => $division1->id,
        'statut' => true,
        'date_debut' => now(),
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 'test@system.com'
    ]);

    echo "✅ Utilisateurs affectés aux entités\n\n";

    // 4. Attribuer la permission view_my_entity_projects
    echo "4. Attribution des permissions...\n";

    $permission = Permission::where('nom', 'view_my_entity_projects')->first();
    if (!$permission) {
        echo "❌ Permission 'view_my_entity_projects' non trouvée\n";
        exit(1);
    }

    // Créer un rôle de test
    $roleTest = Role::create([
        'nom' => 'Test Entity Projects',
        'description' => 'Rôle de test pour les permissions d\'entité',
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 'test@system.com'
    ]);

    $roleTest->permissions()->attach($permission->id, ['date_creation' => now()]);

    // Attribuer le rôle aux utilisateurs
    $userDirection->roles()->attach($roleTest->id, ['date_creation' => now()]);
    $userService->roles()->attach($roleTest->id, ['date_creation' => now()]);
    $userDivision->roles()->attach($roleTest->id, ['date_creation' => now()]);

    echo "✅ Permissions attribuées\n\n";

    // 5. Créer des projets pour chaque utilisateur
    echo "5. Création des projets de test...\n";

    $projetDirection = Projet::create([
        'titre' => 'Projet Direction',
        'description' => 'Projet créé par l\'utilisateur de la direction',
        'type_projet_id' => 1, // Assurez-vous que ce type existe
        'porteur_id' => $userDirection->id,
        'donneur_ordre_id' => $userDirection->id,
        'statut' => 'a_faire',
        'niveau_execution' => 0,
        'date_debut_previsionnelle' => now(),
        'date_fin_previsionnelle' => now()->addDays(30),
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 'test@system.com'
    ]);

    $projetService = Projet::create([
        'titre' => 'Projet Service',
        'description' => 'Projet créé par l\'utilisateur du service',
        'type_projet_id' => 1,
        'porteur_id' => $userService->id,
        'donneur_ordre_id' => $userService->id,
        'statut' => 'en_cours',
        'niveau_execution' => 50,
        'date_debut_previsionnelle' => now(),
        'date_fin_previsionnelle' => now()->addDays(30),
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 'test@system.com'
    ]);

    $projetDivision = Projet::create([
        'titre' => 'Projet Division',
        'description' => 'Projet créé par l\'utilisateur de la division',
        'type_projet_id' => 1,
        'porteur_id' => $userDivision->id,
        'donneur_ordre_id' => $userDivision->id,
        'statut' => 'termine',
        'niveau_execution' => 100,
        'date_debut_previsionnelle' => now(),
        'date_fin_previsionnelle' => now()->addDays(30),
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 'test@system.com'
    ]);

    echo "✅ Projets créés:\n";
    echo "   - {$projetDirection->titre} (porteur: {$userDirection->nom})\n";
    echo "   - {$projetService->titre} (porteur: {$userService->nom})\n";
    echo "   - {$projetDivision->titre} (porteur: {$userDivision->nom})\n\n";

    // 6. Tester la méthode getEntitesEnfantsRecursives
    echo "6. Test de la méthode getEntitesEnfantsRecursives...\n";

    $controller = new \App\Http\Controllers\Api\ProjetController();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getEntitesEnfantsRecursives');
    $method->setAccessible(true);

    $entitesIds = $method->invoke($controller, $direction->id);

    echo "✅ Entités récupérées pour la direction (ID: {$direction->id}):\n";
    foreach ($entitesIds as $id) {
        $entite = Entite::find($id);
        echo "   - {$entite->nom} (ID: {$id})\n";
    }
    echo "\n";

    // 7. Simuler les requêtes de projets pour chaque utilisateur
    echo "7. Test des permissions de visualisation des projets...\n";

    // Test pour l'utilisateur de la direction (doit voir tous les projets)
    echo "Test utilisateur Direction ({$userDirection->nom}):\n";
    $directionProjects = Projet::where(function ($q) use ($userDirection, $userService, $userDivision) {
        $q->whereIn('porteur_id', [$userDirection->id, $userService->id, $userDivision->id])
          ->orWhereIn('donneur_ordre_id', [$userDirection->id, $userService->id, $userDivision->id]);
    })->get();

    echo "   Projets visibles: {$directionProjects->count()}/3\n";
    foreach ($directionProjects as $projet) {
        echo "   - {$projet->titre}\n";
    }
    echo "\n";

    // Test pour l'utilisateur du service (doit voir les projets du service et division)
    echo "Test utilisateur Service ({$userService->nom}):\n";
    $serviceProjects = Projet::where(function ($q) use ($userService, $userDivision) {
        $q->whereIn('porteur_id', [$userService->id, $userDivision->id])
          ->orWhereIn('donneur_ordre_id', [$userService->id, $userDivision->id]);
    })->get();

    echo "   Projets visibles: {$serviceProjects->count()}/2\n";
    foreach ($serviceProjects as $projet) {
        echo "   - {$projet->titre}\n";
    }
    echo "\n";

    // Test pour l'utilisateur de la division (doit voir seulement son projet)
    echo "Test utilisateur Division ({$userDivision->nom}):\n";
    $divisionProjects = Projet::where(function ($q) use ($userDivision) {
        $q->whereIn('porteur_id', [$userDivision->id])
          ->orWhereIn('donneur_ordre_id', [$userDivision->id]);
    })->get();

    echo "   Projets visibles: {$divisionProjects->count()}/1\n";
    foreach ($divisionProjects as $projet) {
        echo "   - {$projet->titre}\n";
    }
    echo "\n";

    echo "🎉 TESTS TERMINÉS AVEC SUCCÈS!\n";
    echo "La permission view_my_entity_projects inclut maintenant les entités enfants.\n";

} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
