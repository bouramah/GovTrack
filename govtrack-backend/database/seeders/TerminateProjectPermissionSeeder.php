<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Carbon\Carbon;

class TerminateProjectPermissionSeeder extends Seeder
{
    /**
     * Ajout de la permission terminate_project
     */
    public function run(): void
    {
        $now = Carbon::now();
        $creator = 'terminate_project_seeder@govtrack.system';

        $this->command->info('🚀 Ajout de la permission terminate_project...');

        // Vérifier si la permission existe déjà
        $permission = Permission::where('nom', 'terminate_project')->first();

        if (!$permission) {
            $permission = Permission::create([
                'nom' => 'terminate_project',
                'description' => 'Terminer un projet (changer le statut à terminé)',
                'date_creation' => $now,
                'date_modification' => $now,
                'creer_par' => $creator,
            ]);
            $this->command->info("✅ Permission 'terminate_project' créée");
        } else {
            $this->command->warn("⚠️  Permission 'terminate_project' existe déjà");
        }

        $this->command->info('🔗 Attribution de la permission aux rôles...');

        // Attribution aux rôles existants
        $roleAdmin = Role::where('nom', 'Administrateur')->first();
        $roleDirecteur = Role::where('nom', 'Directeur')->first();
        $roleEmployee = Role::where('nom', 'Employé')->first();

        if ($roleAdmin) {
            // Admin : toutes les permissions
            if (!$roleAdmin->permissions->contains($permission->id)) {
                $roleAdmin->permissions()->attach($permission->id, ['date_creation' => $now]);
                $this->command->info("✅ Permission 'terminate_project' attribuée au rôle Administrateur");
            } else {
                $this->command->warn("⚠️  L'Administrateur a déjà la permission 'terminate_project'");
            }
        }

        if ($roleDirecteur) {
            // Directeur : peut terminer les projets de son entité
            if (!$roleDirecteur->permissions->contains($permission->id)) {
                $roleDirecteur->permissions()->attach($permission->id, ['date_creation' => $now]);
                $this->command->info("✅ Permission 'terminate_project' attribuée au rôle Directeur");
            } else {
                $this->command->warn("⚠️  Le Directeur a déjà la permission 'terminate_project'");
            }
        }

        if ($roleEmployee) {
            // Employé : N'A PAS la permission de terminer les projets
            if ($roleEmployee->permissions->contains($permission->id)) {
                $roleEmployee->permissions()->detach($permission->id);
                $this->command->info("✅ Permission 'terminate_project' retirée du rôle Employé");
            } else {
                $this->command->info("ℹ️  L'Employé n'a pas la permission 'terminate_project' (comportement attendu)");
            }
        }

        $this->command->info('🎉 Configuration terminée !');
        $this->command->info('📋 Résumé des permissions de terminaison :');
        $this->command->info('   🔓 ADMIN : Peut terminer tous les projets');
        $this->command->info('   🏢 DIRECTEUR : Peut terminer les projets de son entité');
        $this->command->info('   👤 EMPLOYÉ : Ne peut PAS terminer les projets');
    }
}