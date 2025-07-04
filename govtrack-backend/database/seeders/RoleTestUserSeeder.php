<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class RoleTestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $creator = 'system';

        // Créer un rôle "Lecteur Rôles" avec permissions limitées
        $roleLecteurRoles = Role::firstOrCreate(
            ['nom' => 'Lecteur Rôles'],
            [
                'description' => 'Peut voir les rôles et permissions mais ne peut pas les modifier',
                'date_creation' => $now,
                'date_modification' => $now,
                'creer_par' => $creator,
            ]
        );

        // Permissions pour le rôle Lecteur Rôles
        $permissionsLecteur = [
            'view_roles_list',
            'view_role_details',
            'view_role_users',
            'view_role_stats',
            'view_permissions_list',
            'view_permission_details',
            'view_permission_users',
            'view_permission_roles',
            'view_permission_stats',
        ];

        // Attacher les permissions au rôle
        foreach ($permissionsLecteur as $permissionName) {
            $permission = Permission::where('nom', $permissionName)->first();
            if ($permission && !$roleLecteurRoles->permissions()->where('permissions.id', $permission->id)->exists()) {
                $roleLecteurRoles->permissions()->attach($permission->id, ['date_creation' => $now]);
            }
        }

        // Créer un utilisateur de test
        $testUser = User::firstOrCreate(
            ['email' => 'lecteur.roles@govtrack.test'],
            [
                'name' => 'Lecteur Rôles',
                'nom' => 'Lecteur',
                'prenom' => 'Rôles',
                'matricule' => 'ROL001',
                'email' => 'lecteur.roles@govtrack.test',
                'password' => Hash::make('password123'),
                'date_creation' => $now,
                'date_modification' => $now,
                'creer_par' => $creator,
            ]
        );

        // Attacher le rôle à l'utilisateur
        if (!$testUser->roles()->where('roles.id', $roleLecteurRoles->id)->exists()) {
            $testUser->roles()->attach($roleLecteurRoles->id, ['date_creation' => $now]);
        }

        $this->command->info('✅ Utilisateur de test pour les rôles et permissions créé avec succès !');
        $this->command->info('👤 Utilisateur: lecteur.roles@govtrack.test');
        $this->command->info('🔑 Mot de passe: password123');
        $this->command->info('🔐 Rôle: Lecteur Rôles');
        $this->command->info('📋 Permissions:');
        foreach ($permissionsLecteur as $permission) {
            $this->command->info("   - {$permission}");
        }
    }
}
