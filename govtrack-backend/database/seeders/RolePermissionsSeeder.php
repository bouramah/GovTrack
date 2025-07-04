<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Carbon\Carbon;

class RolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $creator = 'system';

        // Permissions pour les rôles
        $rolePermissions = [
            ['nom' => 'view_roles_list', 'description' => 'Voir la liste des rôles'],
            ['nom' => 'create_role', 'description' => 'Créer un nouveau rôle'],
            ['nom' => 'edit_role', 'description' => 'Modifier un rôle existant'],
            ['nom' => 'delete_role', 'description' => 'Supprimer un rôle'],
            ['nom' => 'view_role_details', 'description' => 'Voir les détails d\'un rôle'],
            ['nom' => 'assign_permissions_to_role', 'description' => 'Assigner des permissions à un rôle'],
            ['nom' => 'remove_permissions_from_role', 'description' => 'Retirer des permissions d\'un rôle'],
            ['nom' => 'view_role_users', 'description' => 'Voir les utilisateurs d\'un rôle'],
            ['nom' => 'assign_role_to_user', 'description' => 'Assigner un rôle à un utilisateur'],
            ['nom' => 'remove_role_from_user', 'description' => 'Retirer un rôle d\'un utilisateur'],
            ['nom' => 'view_role_stats', 'description' => 'Voir les statistiques d\'un rôle'],
        ];

        // Permissions pour les permissions
        $permissionPermissions = [
            ['nom' => 'view_permissions_list', 'description' => 'Voir la liste des permissions'],
            ['nom' => 'create_permission', 'description' => 'Créer une nouvelle permission'],
            ['nom' => 'edit_permission', 'description' => 'Modifier une permission existante'],
            ['nom' => 'delete_permission', 'description' => 'Supprimer une permission'],
            ['nom' => 'view_permission_details', 'description' => 'Voir les détails d\'une permission'],
            ['nom' => 'view_permission_users', 'description' => 'Voir les utilisateurs d\'une permission'],
            ['nom' => 'view_permission_roles', 'description' => 'Voir les rôles d\'une permission'],
            ['nom' => 'view_permission_stats', 'description' => 'Voir les statistiques d\'une permission'],
        ];

        // Créer toutes les permissions
        $allPermissions = array_merge($rolePermissions, $permissionPermissions);

        foreach ($allPermissions as $permissionData) {
            Permission::firstOrCreate(
                ['nom' => $permissionData['nom']],
                [
                    'description' => $permissionData['description'],
                    'date_creation' => $now,
                    'date_modification' => $now,
                    'creer_par' => $creator,
                ]
            );
        }

        $this->command->info('✅ Permissions pour les rôles et permissions créées avec succès !');
        $this->command->info('📋 Permissions créées :');
        foreach ($allPermissions as $permission) {
            $this->command->info("   - {$permission['nom']}: {$permission['description']}");
        }
    }
}
