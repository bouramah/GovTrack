<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Carbon\Carbon;

class ProjectPermissionsSeeder extends Seeder
{
    /**
     * Ajout des nouvelles permissions pour la visualisation des projets
     */
    public function run(): void
    {
        $now = Carbon::now();
        $creator = 'project_permissions_seeder@govtrack.system';

        $this->command->info('🚀 Ajout des nouvelles permissions pour les projets...');

        // Nouvelles permissions à ajouter
        $permissionsToAdd = [
            'view_my_projects' => 'Voir mes projets (où je suis responsable/porteur ou ai des tâches)',
            'view_all_projects' => 'Voir tous les projets avec filtres complets',
            'view_my_entity_projects' => 'Voir les projets de mon entité'
        ];

        // Ajouter les permissions si elles n'existent pas
        foreach ($permissionsToAdd as $nom => $description) {
            $permission = Permission::where('nom', $nom)->first();
            
            if (!$permission) {
                Permission::create([
                    'nom' => $nom,
                    'description' => $description,
                    'date_creation' => $now,
                    'date_modification' => $now,
                    'creer_par' => $creator,
                ]);
                $this->command->info("✅ Permission '$nom' ajoutée");
            } else {
                $this->command->warn("⚠️  Permission '$nom' existe déjà");
            }
        }

        $this->command->info('🔗 Attribution des permissions aux rôles...');

        // Attribution aux rôles existants
        $roleAdmin = Role::where('nom', 'Administrateur')->first();
        $roleDirecteur = Role::where('nom', 'Directeur')->first();
        $roleEmployee = Role::where('nom', 'Employé')->first();

        if ($roleAdmin) {
            // Admin : toutes les nouvelles permissions
            foreach ($permissionsToAdd as $nom => $description) {
                $permission = Permission::where('nom', $nom)->first();
                if ($permission && !$roleAdmin->permissions->contains($permission->id)) {
                    $roleAdmin->permissions()->attach($permission->id, ['date_creation' => $now]);
                    $this->command->info("✅ Permission '$nom' attribuée au rôle Administrateur");
                }
            }
        }

        if ($roleDirecteur) {
            // Directeur : view_my_entity_projects et view_my_projects
            $directeurPermissions = ['view_my_entity_projects', 'view_my_projects'];
            foreach ($directeurPermissions as $nom) {
                $permission = Permission::where('nom', $nom)->first();
                if ($permission && !$roleDirecteur->permissions->contains($permission->id)) {
                    $roleDirecteur->permissions()->attach($permission->id, ['date_creation' => $now]);
                    $this->command->info("✅ Permission '$nom' attribuée au rôle Directeur");
                }
            }
        }

        if ($roleEmployee) {
            // Employé : view_my_projects seulement
            $permission = Permission::where('nom', 'view_my_projects')->first();
            if ($permission && !$roleEmployee->permissions->contains($permission->id)) {
                $roleEmployee->permissions()->attach($permission->id, ['date_creation' => $now]);
                $this->command->info("✅ Permission 'view_my_projects' attribuée au rôle Employé");
            }
        }

        $this->command->info('🎉 Toutes les permissions ont été ajoutées avec succès !');
        $this->command->info('📋 Résumé des niveaux de permissions :');
        $this->command->info('   🔓 ADMIN (view_all_projects) : Voir tous les projets');
        $this->command->info('   🏢 DIRECTEUR (view_my_entity_projects) : Projets de son entité');
        $this->command->info('   👤 EMPLOYÉ (view_my_projects) : Ses projets personnels');
    }
}
