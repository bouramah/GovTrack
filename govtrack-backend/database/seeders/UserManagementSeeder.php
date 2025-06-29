<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeEntite;
use App\Models\Entite;
use App\Models\Poste;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\UtilisateurEntiteHistory;
use App\Models\EntiteChefHistory;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $creator = 'system';

        // Création des types d'entités
        $typeDirection = TypeEntite::create([
            'nom' => 'Direction',
            'description' => 'Direction générale',
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        $typeService = TypeEntite::create([
            'nom' => 'Service',
            'description' => 'Service départemental',
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        $typeDivision = TypeEntite::create([
            'nom' => 'Division',
            'description' => 'Division spécialisée',
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        // Création des entités
        $dsi = Entite::create([
            'nom' => 'Direction des Systèmes d\'Information',
            'type_entite_id' => $typeDirection->id,
            'description' => 'Direction responsable des systèmes informatiques',
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        $drh = Entite::create([
            'nom' => 'Direction des Ressources Humaines',
            'type_entite_id' => $typeDirection->id,
            'description' => 'Direction responsable de la gestion du personnel',
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        $serviceDev = Entite::create([
            'nom' => 'Service Développement',
            'type_entite_id' => $typeService->id,
            'parent_id' => $dsi->id,
            'description' => 'Service de développement logiciel',
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        // Création des postes
        $posteDG = Poste::create([
            'nom' => 'Directeur Général',
            'description' => 'Poste de direction générale',
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        $posteChefService = Poste::create([
            'nom' => 'Chef de Service',
            'description' => 'Responsable d\'un service',
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        $posteDeveloppeur = Poste::create([
            'nom' => 'Développeur',
            'description' => 'Développeur informatique',
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        // Création des permissions
        $permissions = [
            ['nom' => 'create_instruction', 'description' => 'Créer une instruction'],
            ['nom' => 'edit_instruction', 'description' => 'Modifier une instruction'],
            ['nom' => 'validate_instruction', 'description' => 'Valider une instruction'],
            ['nom' => 'view_all_instructions', 'description' => 'Voir toutes les instructions'],
            ['nom' => 'manage_users', 'description' => 'Gérer les utilisateurs'],
            ['nom' => 'manage_entities', 'description' => 'Gérer les entités'],
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'nom' => $permission['nom'],
                'description' => $permission['description'],
                'date_creation' => $now,
                'date_modification' => $now,
                'creer_par' => $creator,
            ]);
        }

        // Création des rôles
        $roleAdmin = Role::create([
            'nom' => 'Administrateur',
            'description' => 'Administrateur système',
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        $roleDirecteur = Role::create([
            'nom' => 'Directeur',
            'description' => 'Directeur d\'entité',
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        $roleEmployee = Role::create([
            'nom' => 'Employé',
            'description' => 'Employé standard',
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        // Attribution des permissions aux rôles
        $allPermissions = Permission::all();
        $roleAdmin->permissions()->attach($allPermissions->pluck('id'), ['date_creation' => $now]);

        $roleDirecteur->permissions()->attach([1, 2, 3, 4], ['date_creation' => $now]);
        $roleEmployee->permissions()->attach([1, 2], ['date_creation' => $now]);

        // Création des utilisateurs
        $adminUser = User::create([
            'matricule' => 'ADM001',
            'nom' => 'Admin',
            'prenom' => 'Super',
            'name' => 'Super Admin',
            'email' => 'admin@govtrack.gov',
            'telephone' => '+221 77 123 45 67',
            'adresse' => 'Dakar, Sénégal',
            'statut' => true,
            'password' => Hash::make('password'),
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        $directeurDSI = User::create([
            'matricule' => 'DIR001',
            'nom' => 'Diop',
            'prenom' => 'Amadou',
            'name' => 'Amadou Diop',
            'email' => 'amadou.diop@govtrack.gov',
            'telephone' => '+221 77 234 56 78',
            'adresse' => 'Dakar, Sénégal',
            'statut' => true,
            'password' => Hash::make('password'),
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        $devUser = User::create([
            'matricule' => 'DEV001',
            'nom' => 'Fall',
            'prenom' => 'Fatou',
            'name' => 'Fatou Fall',
            'email' => 'fatou.fall@govtrack.gov',
            'telephone' => '+221 77 345 67 89',
            'adresse' => 'Dakar, Sénégal',
            'statut' => true,
            'password' => Hash::make('password'),
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        // Attribution des rôles aux utilisateurs
        $adminUser->roles()->attach($roleAdmin->id, ['date_creation' => $now]);
        $directeurDSI->roles()->attach($roleDirecteur->id, ['date_creation' => $now]);
        $devUser->roles()->attach($roleEmployee->id, ['date_creation' => $now]);

        // Affectations des utilisateurs aux entités
        UtilisateurEntiteHistory::create([
            'user_id' => $directeurDSI->id,
            'poste_id' => $posteDG->id,
            'service_id' => $dsi->id,
            'statut' => true,
            'date_debut' => $now->subMonths(6),
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        UtilisateurEntiteHistory::create([
            'user_id' => $devUser->id,
            'poste_id' => $posteDeveloppeur->id,
            'service_id' => $serviceDev->id,
            'statut' => true,
            'date_debut' => $now->subMonths(3),
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        // Définition des chefs d'entités
        EntiteChefHistory::create([
            'entite_id' => $dsi->id,
            'user_id' => $directeurDSI->id,
            'date_debut' => $now->subMonths(6),
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        $this->command->info('✅ Données de test créées avec succès !');
        $this->command->info('👤 Utilisateurs créés :');
        $this->command->info('   - admin@govtrack.gov (password: password)');
        $this->command->info('   - amadou.diop@govtrack.gov (password: password)');
        $this->command->info('   - fatou.fall@govtrack.gov (password: password)');
    }
}
