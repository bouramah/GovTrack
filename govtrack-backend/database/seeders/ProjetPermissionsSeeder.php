<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Carbon\Carbon;

class ProjetPermissionsSeeder extends Seeder
{
    /**
     * Ajout des permissions pour les projets et leurs onglets
     */
    public function run(): void
    {
        $now = Carbon::now();
        $creator = 'projet_permissions_seeder@govtrack.system';

        $this->command->info('🚀 Ajout des permissions pour les projets et leurs onglets...');

        // Permissions principales pour les projets
        $projetPermissions = [
            // Permissions de base pour les projets
            'view_projects_list' => 'Voir la liste des projets',
            'create_project' => 'Créer un projet',
            'edit_project' => 'Modifier un projet',
            'delete_project' => 'Supprimer un projet',
            'view_project_details' => 'Voir les détails d\'un projet',
            'update_project_execution_level' => 'Mettre à jour le niveau d\'exécution d\'un projet',
            'change_project_status' => 'Changer le statut d\'un projet',

            // Permissions pour les tâches
            'view_project_tasks' => 'Voir les tâches d\'un projet',
            'create_project_task' => 'Créer une tâche d\'un projet',
            'edit_project_task' => 'Modifier une tâche d\'un projet',
            'delete_project_task' => 'Supprimer une tâche d\'un projet',
            'view_project_task_details' => 'Voir les détails d\'une tâche d\'un projet',

            // Permissions pour les pièces jointes
            'add_project_attachment' => 'Ajouter une pièce jointe à un projet',
            'view_project_attachments' => 'Voir les pièces jointes d\'un projet',
            'download_project_attachment' => 'Télécharger une pièce jointe d\'un projet',
            'edit_project_attachment' => 'Modifier une pièce jointe d\'un projet',
            'delete_project_attachment' => 'Supprimer une pièce jointe d\'un projet',

            // Permissions pour l'historique
            'view_project_history' => 'Voir l\'historique d\'un projet',

            // Permissions pour les commentaires
            'add_project_comment' => 'Ajouter un commentaire à un projet',
            'view_project_comments' => 'Voir les commentaires d\'un projet',
            'edit_project_comment' => 'Modifier un commentaire d\'un projet',
            'delete_project_comment' => 'Supprimer un commentaire d\'un projet',
            'view_project_comment_stats' => 'Voir les statistiques des commentaires d\'un projet',
        ];

        // Ajouter les permissions si elles n'existent pas
        foreach ($projetPermissions as $nom => $description) {
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
            // Admin : toutes les permissions
            foreach ($projetPermissions as $nom => $description) {
                $permission = Permission::where('nom', $nom)->first();
                if ($permission && !$roleAdmin->permissions->contains($permission->id)) {
                    $roleAdmin->permissions()->attach($permission->id, ['date_creation' => $now]);
                    $this->command->info("✅ Permission '$nom' attribuée au rôle Administrateur");
                }
            }
        }

        if ($roleDirecteur) {
            // Directeur : toutes les permissions sauf suppression
            $directeurPermissions = [
                'view_projects_list', 'create_project', 'edit_project', 'view_project_details',
                'update_project_execution_level', 'change_project_status',
                'view_project_tasks', 'create_project_task', 'edit_project_task', 'view_project_task_details',
                'add_project_attachment', 'view_project_attachments', 'download_project_attachment', 'edit_project_attachment',
                'view_project_history',
                'add_project_comment', 'view_project_comments', 'edit_project_comment', 'view_project_comment_stats'
            ];

            foreach ($directeurPermissions as $nom) {
                $permission = Permission::where('nom', $nom)->first();
                if ($permission && !$roleDirecteur->permissions->contains($permission->id)) {
                    $roleDirecteur->permissions()->attach($permission->id, ['date_creation' => $now]);
                    $this->command->info("✅ Permission '$nom' attribuée au rôle Directeur");
                }
            }
        }

        if ($roleEmployee) {
            // Employé : permissions limitées
            $employeePermissions = [
                'view_projects_list', 'view_project_details',
                'view_project_tasks', 'view_project_task_details',
                'view_project_attachments', 'download_project_attachment',
                'view_project_history',
                'view_project_comments', 'view_project_comment_stats'
            ];

            foreach ($employeePermissions as $nom) {
                $permission = Permission::where('nom', $nom)->first();
                if ($permission && !$roleEmployee->permissions->contains($permission->id)) {
                    $roleEmployee->permissions()->attach($permission->id, ['date_creation' => $now]);
                    $this->command->info("✅ Permission '$nom' attribuée au rôle Employé");
                }
            }
        }

        $this->command->info('🎉 Toutes les permissions des projets ont été ajoutées avec succès !');
        $this->command->info('📋 Résumé des niveaux de permissions :');
        $this->command->info('   🔓 ADMIN : Toutes les permissions');
        $this->command->info('   🏢 DIRECTEUR : Toutes les permissions sauf suppression');
        $this->command->info('   👤 EMPLOYÉ : Permissions de lecture uniquement');
    }
}
