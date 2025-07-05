<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Carbon\Carbon;

class TachePermissionsSeeder extends Seeder
{
    /**
     * Ajout des permissions pour les tâches et leurs onglets
     */
    public function run(): void
    {
        $now = Carbon::now();
        $creator = 'tache_permissions_seeder@govtrack.system';

        $this->command->info('🚀 Ajout des permissions pour les tâches et leurs onglets...');

        // Permissions principales pour les tâches
        $tachePermissions = [
            // Permissions de base pour les tâches
            'view_tasks_list' => 'Voir la liste des tâches',
            'create_task' => 'Créer une tâche',
            'edit_task' => 'Modifier une tâche',
            'delete_task' => 'Supprimer une tâche',
            'view_task_details' => 'Voir les détails d\'une tâche',
            'change_task_status' => 'Changer le statut d\'une tâche',
            'view_task_history' => 'Voir l\'historique d\'une tâche',

            // Permissions pour les pièces jointes des tâches
            'add_task_attachment' => 'Ajouter une pièce jointe à une tâche',
            'view_task_attachments' => 'Voir les pièces jointes d\'une tâche',
            'download_task_attachment' => 'Télécharger une pièce jointe d\'une tâche',
            'delete_task_attachment' => 'Supprimer une pièce jointe d\'une tâche',

            // Permissions pour les commentaires des tâches
            'add_task_comment' => 'Ajouter un commentaire à une tâche',
            'view_task_comments' => 'Voir les commentaires d\'une tâche',
            'edit_task_comment' => 'Modifier un commentaire d\'une tâche',
            'delete_task_comment' => 'Supprimer un commentaire d\'une tâche',
            'view_task_comment_stats' => 'Voir les statistiques des commentaires d\'une tâche',
        ];

        // Ajouter les permissions si elles n'existent pas
        foreach ($tachePermissions as $nom => $description) {
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
            foreach ($tachePermissions as $nom => $description) {
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
                'view_tasks_list', 'create_task', 'edit_task', 'view_task_details',
                'change_task_status', 'view_task_history',
                'add_task_attachment', 'view_task_attachments', 'download_task_attachment',
                'add_task_comment', 'view_task_comments', 'edit_task_comment', 'view_task_comment_stats'
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
                'view_tasks_list', 'view_task_details', 'view_task_history',
                'view_task_attachments', 'download_task_attachment',
                'view_task_comments', 'view_task_comment_stats'
            ];

            foreach ($employeePermissions as $nom) {
                $permission = Permission::where('nom', $nom)->first();
                if ($permission && !$roleEmployee->permissions->contains($permission->id)) {
                    $roleEmployee->permissions()->attach($permission->id, ['date_creation' => $now]);
                    $this->command->info("✅ Permission '$nom' attribuée au rôle Employé");
                }
            }
        }

        $this->command->info('🎉 Toutes les permissions des tâches ont été ajoutées avec succès !');
        $this->command->info('📋 Résumé des niveaux de permissions :');
        $this->command->info('   🔓 ADMIN : Toutes les permissions');
        $this->command->info('   🏢 DIRECTEUR : Toutes les permissions sauf suppression');
        $this->command->info('   👤 EMPLOYÉ : Permissions de lecture uniquement');
    }
}
