<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

class TypeProjetTestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un rôle "Lecteur Types de Projets" avec permissions limitées
        $role = Role::firstOrCreate(
            ['nom' => 'Lecteur Types de Projets'],
            [
                'nom' => 'Lecteur Types de Projets',
                'description' => 'Peut uniquement voir les types de projets et leurs statistiques'
            ]
        );

        // Permissions pour ce rôle (lecture seule)
        $permissions = [
            'view_type_projets_list',
            'view_type_projet_details',
            'view_type_projet_stats'
        ];

        // Assigner les permissions au rôle
        foreach ($permissions as $permissionName) {
            $permission = Permission::where('nom', $permissionName)->first();
            if ($permission && !$role->permissions()->where('permission_id', $permission->id)->exists()) {
                $role->permissions()->attach($permission->id);
            }
        }

        // Créer un utilisateur de test
        $user = User::firstOrCreate(
            ['email' => 'lecteur.typeprojets@govtrack.test'],
            [
                'nom' => 'Lecteur',
                'prenom' => 'TypeProjets',
                'email' => 'lecteur.typeprojets@govtrack.test',
                'password' => Hash::make('password123'),
                'statut' => 'actif',
                'date_creation' => now(),
                'derniere_connexion' => now()
            ]
        );

        // Assigner le rôle à l'utilisateur
        if (!$user->roles()->where('role_id', $role->id)->exists()) {
            $user->roles()->attach($role->id);
        }

        $this->command->info('Utilisateur de test "Lecteur Types de Projets" créé avec succès !');
        $this->command->info('Email: lecteur.typeprojets@govtrack.test');
        $this->command->info('Mot de passe: password123');
    }
}
