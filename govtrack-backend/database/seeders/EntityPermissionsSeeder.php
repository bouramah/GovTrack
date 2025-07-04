<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Carbon\Carbon;

class EntityPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $creator = 'system';

        // Permissions spécifiques aux entités
        $entityPermissions = [
            ['nom' => 'view_entities_list', 'description' => 'Voir la liste des entités'],
            ['nom' => 'create_entity', 'description' => 'Créer une entité'],
            ['nom' => 'edit_entity', 'description' => 'Modifier une entité'],
            ['nom' => 'delete_entity', 'description' => 'Supprimer une entité'],
            ['nom' => 'view_entity_details', 'description' => 'Voir les détails d\'une entité'],
            ['nom' => 'view_entity_hierarchy', 'description' => 'Voir la hiérarchie des entités'],
            ['nom' => 'view_entity_users', 'description' => 'Voir les utilisateurs d\'une entité'],
            ['nom' => 'manage_entity_assignments', 'description' => 'Gérer les affectations d\'une entité'],
            ['nom' => 'view_entity_chief_history', 'description' => 'Voir l\'historique des chefs d\'entité'],
        ];

        // Créer les nouvelles permissions
        foreach ($entityPermissions as $permission) {
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
            $newPermissions = Permission::whereIn('nom', array_column($entityPermissions, 'nom'))->get();

            // Attacher les nouvelles permissions au rôle Administrateur
            foreach ($newPermissions as $permission) {
                if (!$roleAdmin->permissions()->where('permissions.id', $permission->id)->exists()) {
                    $roleAdmin->permissions()->attach($permission->id, ['date_creation' => $now]);
                }
            }
        }

        $this->command->info('✅ Permissions entités créées avec succès !');
        $this->command->info('🔐 Nouvelles permissions ajoutées :');
        foreach ($entityPermissions as $permission) {
            $this->command->info("   - {$permission['nom']}: {$permission['description']}");
        }
    }
}
