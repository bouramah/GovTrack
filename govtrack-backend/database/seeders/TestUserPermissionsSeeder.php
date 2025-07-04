<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class TestUserPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $creator = 'system';

        // Créer ou récupérer un rôle "Lecteur" avec permissions limitées
        $roleLecteur = Role::firstOrCreate(
            ['nom' => 'Lecteur'],
            [
                'description' => 'Utilisateur avec permissions de lecture limitées',
                'date_creation' => $now,
                'date_modification' => $now,
                'creer_par' => $creator,
            ]
        );

        // Permissions pour le rôle Lecteur (seulement voir les détails)
        $lecteurPermissions = Permission::whereIn('nom', [
            'view_user_details',  // Peut voir les détails d'un utilisateur
            'view_my_projects',   // Peut voir ses propres projets
        ])->pluck('id');

        // Attacher les permissions seulement si elles ne sont pas déjà attachées
        foreach ($lecteurPermissions as $permissionId) {
            if (!$roleLecteur->permissions()->where('permissions.id', $permissionId)->exists()) {
                $roleLecteur->permissions()->attach($permissionId, ['date_creation' => $now]);
            }
        }

        // Créer un utilisateur test avec le rôle Lecteur
        $testUser = User::create([
            'matricule' => 'TEST001',
            'nom' => 'Test',
            'prenom' => 'Utilisateur',
            'name' => 'Test Utilisateur',
            'email' => 'test.user@govtrack.gov',
            'telephone' => '+221 77 999 99 99',
            'adresse' => 'Dakar, Sénégal',
            'statut' => true,
            'password' => Hash::make('password'),
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        // Attribuer le rôle Lecteur à l'utilisateur test
        $testUser->roles()->attach($roleLecteur->id, ['date_creation' => $now]);

        $this->command->info('✅ Utilisateur test créé avec succès !');
        $this->command->info('👤 Utilisateur test :');
        $this->command->info('   - test.user@govtrack.gov (password: password)');
        $this->command->info('   - Rôle: Lecteur (permissions limitées)');
        $this->command->info('   - Permissions: view_user_details, view_my_projects');

        // Créer un rôle "Lecteur" avec permissions limitées
        $roleLecteur = Role::create([
            'nom' => 'Lecteur',
            'description' => 'Utilisateur avec permissions de lecture limitées',
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        // Permissions pour le rôle Lecteur (seulement voir les détails)
        $lecteurPermissions = Permission::whereIn('nom', [
            'view_user_details',  // Peut voir les détails d'un utilisateur
            'view_my_projects',   // Peut voir ses propres projets
        ])->pluck('id');

        $roleLecteur->permissions()->attach($lecteurPermissions, ['date_creation' => $now]);

        // Créer un utilisateur test avec le rôle Lecteur
        $testUser = User::create([
            'matricule' => 'TEST001',
            'nom' => 'Test',
            'prenom' => 'Utilisateur',
            'name' => 'Test Utilisateur',
            'email' => 'test.user@govtrack.gov',
            'telephone' => '+221 77 999 99 99',
            'adresse' => 'Dakar, Sénégal',
            'statut' => true,
            'password' => Hash::make('password'),
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $creator,
        ]);

        // Attribuer le rôle Lecteur à l'utilisateur test
        $testUser->roles()->attach($roleLecteur->id, ['date_creation' => $now]);
    }
}
