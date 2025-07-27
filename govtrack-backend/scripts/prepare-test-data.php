<?php

/**
 * Script de préparation des données de test pour le module Réunions
 *
 * Ce script crée les utilisateurs, entités et données de base nécessaires
 * pour exécuter le scénario de test complet.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Entite;
use App\Models\TypeEntite;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Poste;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

// Initialiser Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🚀 Début de la préparation des données de test...\n\n";

try {
    DB::beginTransaction();

    // ========================================
    // 1. CRÉATION DES TYPES D'ENTITÉS
    // ========================================
    echo "📋 Création des types d'entités...\n";

                $typeEntite1 = TypeEntite::firstOrCreate([
        'nom' => 'Direction'
    ], [
        'description' => 'Direction générale',
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 1
    ]);

    $typeEntite2 = TypeEntite::firstOrCreate([
        'nom' => 'Service'
    ], [
        'description' => 'Service opérationnel',
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 1
    ]);

    echo "✅ Types d'entités créés\n\n";

    // ========================================
    // 2. CRÉATION DES ENTITÉS
    // ========================================
    echo "🏢 Création des entités...\n";

                $entite1 = Entite::firstOrCreate([
        'nom' => 'Direction Générale'
    ], [
        'type_entite_id' => $typeEntite1->id,
        'description' => 'Direction générale de l\'organisation',
        'actif' => true,
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 1
    ]);

    $entite2 = Entite::firstOrCreate([
        'nom' => 'Service Informatique'
    ], [
        'type_entite_id' => $typeEntite2->id,
        'description' => 'Service informatique et digital',
        'actif' => true,
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 1
    ]);

    echo "✅ Entités créées\n\n";

    // ========================================
    // 3. CRÉATION DES POSTES
    // ========================================
    echo "👔 Création des postes...\n";

                $poste1 = Poste::firstOrCreate([
        'nom' => 'Directeur Général'
    ], [
        'description' => 'Directeur général de l\'organisation',
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 1
    ]);

    $poste2 = Poste::firstOrCreate([
        'nom' => 'Chef de Projet'
    ], [
        'description' => 'Chef de projet informatique',
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 1
    ]);

    $poste3 = Poste::firstOrCreate([
        'nom' => 'Analyste'
    ], [
        'description' => 'Analyste fonctionnel',
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 1
    ]);

    echo "✅ Postes créés\n\n";

    // ========================================
    // 4. CRÉATION DES PERMISSIONS
    // ========================================
    echo "🔐 Création des permissions...\n";

    $permissions = [
        'view_reunion_types',
        'create_reunion_types',
        'update_reunion_types',
        'delete_reunion_types',
        'view_reunions',
        'create_reunions',
        'update_reunions',
        'delete_reunions',
        'view_reunion_pv',
        'create_reunion_pv',
        'update_reunion_pv',
        'validate_reunion_pv',
        'view_reunion_series',
        'create_reunion_series',
        'update_reunion_series',
        'delete_reunion_series',
        'view_reunion_ordre_jour',
        'create_reunion_ordre_jour',
        'update_reunion_ordre_jour',
        'delete_reunion_ordre_jour',
        'view_reunion_decisions',
        'create_reunion_decisions',
        'update_reunion_decisions',
        'delete_reunion_decisions',
        'view_reunion_actions',
        'create_reunion_actions',
        'update_reunion_actions',
        'delete_reunion_actions',
        'view_reunion_sujets',
        'create_reunion_sujets',
        'update_reunion_sujets',
        'delete_reunion_sujets',
        'view_reunion_objectifs',
        'create_reunion_objectifs',
        'update_reunion_objectifs',
        'delete_reunion_objectifs',
        'view_reunion_difficultes',
        'create_reunion_difficultes',
        'update_reunion_difficultes',
        'delete_reunion_difficultes',
        'view_reunion_workflows',
        'create_reunion_workflow',
        'start_reunion_workflow',
        'validate_reunion_workflow',
        'cancel_reunion_workflow',
        'send_reunion_notifications',
        'view_notifications',
        'view_analytics',
        'export_analytics',
        'view_reunions',
        'view_all_reunions',
        'view_my_entity_reunions'
    ];

    foreach ($permissions as $permissionName) {
        Permission::firstOrCreate([
            'nom' => $permissionName
        ], [
            'description' => 'Permission pour ' . $permissionName,
            'date_creation' => now(),
            'date_modification' => now(),
            'creer_par' => 1
        ]);
    }

    echo "✅ Permissions créées\n\n";

    // ========================================
    // 5. CRÉATION DES RÔLES
    // ========================================
    echo "🎭 Création des rôles...\n";

                    $roleAdmin = Role::firstOrCreate([
        'nom' => 'Administrateur'
    ], [
        'description' => 'Administrateur système avec tous les droits',
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 1
    ]);

    $roleDirecteur = Role::firstOrCreate([
        'nom' => 'Directeur'
    ], [
        'description' => 'Directeur avec droits étendus',
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 1
    ]);

    $roleChefProjet = Role::firstOrCreate([
        'nom' => 'Chef de Projet'
    ], [
        'description' => 'Chef de projet avec droits de gestion',
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 1
    ]);

    $roleAnalyste = Role::firstOrCreate([
        'nom' => 'Analyste'
    ], [
        'description' => 'Analyste avec droits de consultation',
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 1
    ]);

    echo "✅ Rôles créés\n\n";

    // ========================================
    // 6. ASSIGNATION DES PERMISSIONS AUX RÔLES
    // ========================================
    echo "🔗 Assignment des permissions aux rôles...\n";

            // Administrateur - Toutes les permissions
    $adminPermissionIds = Permission::all()->pluck('id')->mapWithKeys(function($id) {
        return [$id => ['date_creation' => now()]];
    })->toArray();
    $roleAdmin->permissions()->syncWithoutDetaching($adminPermissionIds);

    // Directeur - Permissions étendues
    $directeurPermissionIds = Permission::whereIn('nom', [
        'view_all_reunions',
        'create_reunions',
        'update_reunions',
        'view_reunion_pv',
        'validate_reunion_pv',
        'view_reunion_series',
        'create_reunion_series',
        'view_reunion_ordre_jour',
        'create_reunion_ordre_jour',
        'view_reunion_decisions',
        'create_reunion_decisions',
        'view_reunion_actions',
        'create_reunion_actions',
        'view_reunion_sujets',
        'create_reunion_sujets',
        'view_reunion_objectifs',
        'create_reunion_objectifs',
        'view_reunion_difficultes',
        'create_reunion_difficultes',
        'view_analytics',
        'view_notifications'
    ])->pluck('id')->mapWithKeys(function($id) {
        return [$id => ['date_creation' => now()]];
    })->toArray();
    $roleDirecteur->permissions()->syncWithoutDetaching($directeurPermissionIds);

    // Chef de Projet - Permissions de gestion
    $chefProjetPermissionIds = Permission::whereIn('nom', [
        'view_my_entity_reunions',
        'create_reunions',
        'update_reunions',
        'view_reunion_pv',
        'create_reunion_pv',
        'view_reunion_ordre_jour',
        'create_reunion_ordre_jour',
        'view_reunion_decisions',
        'create_reunion_decisions',
        'view_reunion_actions',
        'create_reunion_actions',
        'view_reunion_sujets',
        'create_reunion_sujets',
        'view_reunion_objectifs',
        'create_reunion_objectifs',
        'view_reunion_difficultes',
        'create_reunion_difficultes',
        'view_notifications'
    ])->pluck('id')->mapWithKeys(function($id) {
        return [$id => ['date_creation' => now()]];
    })->toArray();
    $roleChefProjet->permissions()->syncWithoutDetaching($chefProjetPermissionIds);

    // Analyste - Permissions de consultation
    $analystePermissionIds = Permission::whereIn('nom', [
        'view_my_entity_reunions',
        'view_reunions',
        'view_reunion_pv',
        'view_reunion_ordre_jour',
        'view_reunion_decisions',
        'view_reunion_actions',
        'view_reunion_sujets',
        'view_reunion_objectifs',
        'view_reunion_difficultes',
        'view_notifications'
    ])->pluck('id')->mapWithKeys(function($id) {
        return [$id => ['date_creation' => now()]];
    })->toArray();
    $roleAnalyste->permissions()->syncWithoutDetaching($analystePermissionIds);

    echo "✅ Permissions assignées aux rôles\n\n";

    // ========================================
    // 7. CRÉATION DES UTILISATEURS
    // ========================================
    echo "👥 Création des utilisateurs de test...\n";

    $users = [
        [
            'email' => 'admin@govtrack.com',
            'password' => 'password123',
            'nom' => 'Admin',
            'prenom' => 'Système',
            'poste_id' => $poste1->id,
            'role' => $roleAdmin
        ],
        [
            'email' => 'directeur@govtrack.com',
            'password' => 'password123',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'poste_id' => $poste1->id,
            'role' => $roleDirecteur
        ],
        [
            'email' => 'chef-projet@govtrack.com',
            'password' => 'password123',
            'nom' => 'Martin',
            'prenom' => 'Sophie',
            'poste_id' => $poste2->id,
            'role' => $roleChefProjet
        ],
        [
            'email' => 'analyste@govtrack.com',
            'password' => 'password123',
            'nom' => 'Bernard',
            'prenom' => 'Pierre',
            'poste_id' => $poste3->id,
            'role' => $roleAnalyste
        ]
    ];

    foreach ($users as $userData) {
        $role = $userData['role'];
        unset($userData['role']);

        $user = User::firstOrCreate([
            'email' => $userData['email']
        ], [
            'nom' => $userData['nom'],
            'prenom' => $userData['prenom'],
            'name' => $userData['prenom'] . ' ' . $userData['nom'],
            'matricule' => 'MAT' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'password' => Hash::make($userData['password']),
            'poste_id' => $userData['poste_id'],
            'actif' => true,
            'date_creation' => now(),
            'date_modification' => now(),
            'creer_par' => 1
        ]);

        // Assigner le rôle
        $user->roles()->syncWithoutDetaching([$role->id => ['date_creation' => now()]]);

        // Assigner à une entité (sauf admin)
        if ($userData['email'] !== 'admin@govtrack.com') {
            $entiteId = ($userData['email'] === 'directeur@govtrack.com') ? $entite1->id : $entite2->id;

            DB::table('utilisateur_entite_histories')->insert([
                'user_id' => $user->id,
                'service_id' => $entiteId,
                'poste_id' => $userData['poste_id'],
                'date_debut' => now(),
                'date_fin' => null,
                'statut' => true,
                'creer_par' => 1,
                'date_creation' => now(),
                'date_modification' => now()
            ]);
        }

        echo "✅ Utilisateur créé : {$userData['email']}\n";
    }

    echo "\n✅ Tous les utilisateurs créés\n\n";

    // ========================================
    // 8. CRÉATION D'UN PROJET DE TEST
    // ========================================
    echo "📊 Création d'un projet de test...\n";

    // Créer un type de projet
    $typeProjet = \App\Models\TypeProjet::firstOrCreate([
        'nom' => 'Infrastructure'
    ], [
        'description' => 'Projets d\'infrastructure',
        'date_creation' => now(),
        'date_modification' => now(),
        'creer_par' => 1
    ]);

    // Récupérer l'admin comme donneur d'ordre
    $admin = User::where('email', 'admin@govtrack.com')->first();

    $projet = \App\Models\Projet::firstOrCreate([
        'titre' => 'Infrastructure Numérique'
    ], [
        'description' => 'Projet de modernisation de l\'infrastructure numérique',
        'type_projet_id' => $typeProjet->id,
        'donneur_ordre_id' => $admin->id,
        'date_debut_previsionnelle' => '2025-01-01',
        'date_fin_previsionnelle' => '2025-06-30',
        'statut' => 'EN_COURS',
        'priorite' => 'ELEVEE',
        'creer_par' => 1,
        'date_creation' => now(),
        'date_modification' => now()
    ]);

    echo "✅ Projet de test créé\n\n";

    DB::commit();

    echo "🎉 Préparation des données de test terminée avec succès !\n\n";

    echo "📋 Récapitulatif des données créées :\n";
    echo "- Types d'entités : 2\n";
    echo "- Entités : 2\n";
    echo "- Postes : 3\n";
    echo "- Permissions : " . count($permissions) . "\n";
    echo "- Rôles : 4\n";
    echo "- Utilisateurs : 4\n";
    echo "- Projet de test : 1\n\n";

    echo "🔑 Identifiants des utilisateurs créés :\n";
    $createdUsers = User::whereIn('email', [
        'admin@govtrack.com',
        'directeur@govtrack.com',
        'chef-projet@govtrack.com',
        'analyste@govtrack.com'
    ])->get();

    foreach ($createdUsers as $user) {
        echo "- {$user->email} (ID: {$user->id})\n";
    }

    echo "\n🚀 Vous pouvez maintenant exécuter le scénario de test dans Postman !\n";

} catch (Exception $e) {
    DB::rollBack();
    echo "❌ Erreur lors de la préparation des données : " . $e->getMessage() . "\n";
    echo "Stack trace : " . $e->getTraceAsString() . "\n";
}
