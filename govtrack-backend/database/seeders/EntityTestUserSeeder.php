<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class EntityTestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $creator = 'system';

        // Créer un rôle "Lecteur Entités" avec permissions limitées
        $roleLecteurEntites = Role::firstOrCreate(
            ['nom' => 'Lecteur Entités'],
            [
                'description' => 'Peut voir les entités mais ne peut pas les modifier',
                'date_creation' => $now,
                'date_modification' => $now,
                'creer_par' => $creator,
            ]
        );

        // Permissions pour le rôle Lecteur Entités
        $permissionsLecteur = [
            'view_entities_list',
            'view_entity_details',
            'view_entity_hierarchy',
        ];

        // Attacher les permissions au rôle
        foreach ($permissionsLecteur as $permissionName) {
            $permission = Permission::where('nom', $permissionName)->first();
            if ($permission && !$roleLecteurEntites->permissions()->where('permissions.id', $permission->id)->exists()) {
                $roleLecteurEntites->permissions()->attach($permission->id, ['date_creation' => $now]);
            }
        }

        // Créer un utilisateur de test
        $testUser = User::firstOrCreate(
            ['email' => 'lecteur.entites@govtrack.test'],
            [
                'name' => 'Lecteur Entités',
                'nom' => 'Lecteur',
                'prenom' => 'Entités',
                'matricule' => 'ENT001',
                'email' => 'lecteur.entites@govtrack.test',
                'password' => Hash::make('password123'),
                'date_creation' => $now,
                'date_modification' => $now,
                'creer_par' => $creator,
            ]
        );

        // Attacher le rôle à l'utilisateur
        if (!$testUser->roles()->where('roles.id', $roleLecteurEntites->id)->exists()) {
            $testUser->roles()->attach($roleLecteurEntites->id, ['date_creation' => $now]);
        }

        $this->command->info('✅ Utilisateur de test pour les entités créé avec succès !');
        $this->command->info('👤 Utilisateur: lecteur.entites@govtrack.test');
        $this->command->info('🔑 Mot de passe: password123');
        $this->command->info('🔐 Rôle: Lecteur Entités');
        $this->command->info('📋 Permissions:');
        foreach ($permissionsLecteur as $permission) {
            $this->command->info("   - {$permission}");
        }
    }
}
