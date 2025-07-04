<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Carbon\Carbon;

class UserPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $creator = 'system';

        // Nouvelles permissions spécifiques aux utilisateurs
        $userPermissions = [
            ['nom' => 'view_users_list', 'description' => 'Voir la liste des utilisateurs'],
            ['nom' => 'create_user', 'description' => 'Créer un utilisateur'],
            ['nom' => 'edit_user', 'description' => 'Modifier un utilisateur'],
            ['nom' => 'delete_user', 'description' => 'Supprimer un utilisateur'],
            ['nom' => 'view_user_details', 'description' => 'Voir les détails d\'un utilisateur'],
            ['nom' => 'manage_user_assignments', 'description' => 'Gérer les affectations d\'un utilisateur'],
            ['nom' => 'manage_user_roles', 'description' => 'Gérer les rôles d\'un utilisateur'],
            ['nom' => 'view_user_stats', 'description' => 'Voir les statistiques des utilisateurs'],
        ];

        // Créer les nouvelles permissions
        foreach ($userPermissions as $permission) {
            // Vérifier si la permission existe déjà
            $existingPermission = Permission::where('nom', $permission['nom'])->first();

            if (!$existingPermission) {
                Permission::create([
                    'nom' => $permission['nom'],
                    'description' => $permission['description'],
                    'date_creation' => $now,
                    'date_modification' => $now,
                    'creer_par' => $creator,
                ]);
            }
        }

        // Récupérer le rôle Administrateur
        $roleAdmin = Role::where('nom', 'Administrateur')->first();

        if ($roleAdmin) {
            // Récupérer toutes les nouvelles permissions
            $newPermissions = Permission::whereIn('nom', array_column($userPermissions, 'nom'))->get();

            // Attacher les nouvelles permissions au rôle Administrateur
            foreach ($newPermissions as $permission) {
                if (!$roleAdmin->permissions()->where('permissions.id', $permission->id)->exists()) {
                    $roleAdmin->permissions()->attach($permission->id, ['date_creation' => $now]);
                }
            }
        }

        $this->command->info('✅ Permissions utilisateurs créées avec succès !');
        $this->command->info('🔐 Nouvelles permissions ajoutées :');
        foreach ($userPermissions as $permission) {
            $this->command->info("   - {$permission['nom']}: {$permission['description']}");
        }
    }
}
