<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class EntityTypeTestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Créer un rôle "Lecteur Types et Postes" avec permissions limitées
        $role = Role::firstOrCreate(
            ['nom' => 'Lecteur Types et Postes'],
            [
                'description' => 'Peut voir les types d\'entités et postes mais pas les modifier',
                'date_creation' => now(),
                'date_modification' => now(),
                'creer_par' => 'system',
                'modifier_par' => 'system'
            ]
        );

        // Permissions pour ce rôle (lecture seule)
        $permissions = [
            'view_entity_types_list',
            'view_entity_type_details',
            'view_posts_list',
            'view_post_details',
            'view_posts_stats',
            'view_organigramme',
            'view_organigramme_details'
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::where('nom', $permissionName)->first();
            if ($permission && !$role->permissions()->where('permission_id', $permission->id)->exists()) {
                $role->permissions()->attach($permission->id, [
                    'date_creation' => now()
                ]);
            }
        }

        // Créer un utilisateur de test
        $user = User::firstOrCreate(
            ['email' => 'lecteur.types@test.com'],
            [
                'name' => 'Lecteur Types',
                'nom' => 'Lecteur',
                'prenom' => 'Types',
                'matricule' => 'LT001',
                'email' => 'lecteur.types@test.com',
                'password' => bcrypt('password'),
                'statut' => 'actif'
            ]
        );

        // Assigner le rôle à l'utilisateur
        if (!$user->roles()->where('role_id', $role->id)->exists()) {
            $user->roles()->attach($role->id);
        }

        $this->command->info('Utilisateur de test "Lecteur Types et Postes" créé avec succès !');
        $this->command->info('Email: lecteur.types@test.com');
        $this->command->info('Mot de passe: password');
    }
}
