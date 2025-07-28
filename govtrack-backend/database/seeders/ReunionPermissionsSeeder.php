<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;

class ReunionPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $creator = 'system';

        // =================================================================
        // PERMISSIONS NOTIFICATIONS
        // =================================================================
        $notificationPermissions = [
            ['nom' => 'view_reunion_notifications', 'description' => 'Voir les notifications d\'une réunion'],
            ['nom' => 'create_reunion_notifications', 'description' => 'Créer des configurations de notifications'],
            ['nom' => 'update_reunion_notifications', 'description' => 'Modifier des configurations de notifications'],
            ['nom' => 'delete_reunion_notifications', 'description' => 'Supprimer des configurations de notifications'],
            ['nom' => 'send_reunion_notifications', 'description' => 'Envoyer des notifications de réunion'],
            ['nom' => 'view_notifications', 'description' => 'Voir les notifications globales'],
        ];

        // =================================================================
        // PERMISSIONS RÉUNIONS
        // =================================================================
        $reunionPermissions = [
            ['nom' => 'view_reunions', 'description' => 'Voir les réunions'],
            ['nom' => 'create_reunion', 'description' => 'Créer une réunion'],
            ['nom' => 'edit_reunion', 'description' => 'Modifier une réunion'],
            ['nom' => 'delete_reunion', 'description' => 'Supprimer une réunion'],
            ['nom' => 'view_reunion_details', 'description' => 'Voir les détails d\'une réunion'],
            ['nom' => 'change_reunion_status', 'description' => 'Changer le statut d\'une réunion'],
        ];

        // =================================================================
        // PERMISSIONS SÉRIES DE RÉUNIONS
        // =================================================================
        $seriesPermissions = [
            ['nom' => 'view_reunion_series', 'description' => 'Voir les séries de réunions'],
            ['nom' => 'create_reunion_series', 'description' => 'Créer une série de réunions'],
            ['nom' => 'edit_reunion_series', 'description' => 'Modifier une série de réunions'],
            ['nom' => 'delete_reunion_series', 'description' => 'Supprimer une série de réunions'],
        ];

        // =================================================================
        // PERMISSIONS RÉUNIONS GÉNÉRÉES
        // =================================================================
        $generatedReunionPermissions = [
            ['nom' => 'view_reunion_series', 'description' => 'Voir les réunions générées'],
            ['nom' => 'create_reunion_series', 'description' => 'Créer des réunions générées'],
            ['nom' => 'update_reunion_series', 'description' => 'Modifier des réunions générées'],
            ['nom' => 'delete_reunion_series', 'description' => 'Supprimer des réunions générées'],
        ];

        // =================================================================
        // PERMISSIONS TYPES DE RÉUNIONS
        // =================================================================
        $typeReunionPermissions = [
            ['nom' => 'view_type_reunions', 'description' => 'Voir les types de réunions'],
            ['nom' => 'create_type_reunion', 'description' => 'Créer un type de réunion'],
            ['nom' => 'edit_type_reunion', 'description' => 'Modifier un type de réunion'],
            ['nom' => 'delete_type_reunion', 'description' => 'Supprimer un type de réunion'],
        ];

        // =================================================================
        // PERMISSIONS PARTICIPANTS
        // =================================================================
        $participantPermissions = [
            ['nom' => 'manage_reunion_participants', 'description' => 'Gérer les participants de réunion'],
            ['nom' => 'view_reunion_participants', 'description' => 'Voir les participants de réunion'],
        ];

        // =================================================================
        // PERMISSIONS ORDRE DU JOUR
        // =================================================================
        $ordreJourPermissions = [
            ['nom' => 'view_reunion_ordre_jour', 'description' => 'Voir l\'ordre du jour'],
            ['nom' => 'create_reunion_ordre_jour', 'description' => 'Créer l\'ordre du jour'],
            ['nom' => 'edit_reunion_ordre_jour', 'description' => 'Modifier l\'ordre du jour'],
            ['nom' => 'delete_reunion_ordre_jour', 'description' => 'Supprimer l\'ordre du jour'],
        ];

        // =================================================================
        // PERMISSIONS SUJETS
        // =================================================================
        $sujetPermissions = [
            ['nom' => 'view_reunion_sujets', 'description' => 'Voir les sujets de réunion'],
            ['nom' => 'create_reunion_sujet', 'description' => 'Créer un sujet de réunion'],
            ['nom' => 'edit_reunion_sujet', 'description' => 'Modifier un sujet de réunion'],
            ['nom' => 'delete_reunion_sujet', 'description' => 'Supprimer un sujet de réunion'],
        ];

        // =================================================================
        // PERMISSIONS OBJECTIFS
        // =================================================================
        $objectifPermissions = [
            ['nom' => 'view_reunion_objectifs', 'description' => 'Voir les objectifs de réunion'],
            ['nom' => 'create_reunion_objectif', 'description' => 'Créer un objectif de réunion'],
            ['nom' => 'edit_reunion_objectif', 'description' => 'Modifier un objectif de réunion'],
            ['nom' => 'delete_reunion_objectif', 'description' => 'Supprimer un objectif de réunion'],
        ];

        // =================================================================
        // PERMISSIONS DIFFICULTÉS
        // =================================================================
        $difficultePermissions = [
            ['nom' => 'view_reunion_difficultes', 'description' => 'Voir les difficultés de réunion'],
            ['nom' => 'create_reunion_difficulte', 'description' => 'Créer une difficulté de réunion'],
            ['nom' => 'edit_reunion_difficulte', 'description' => 'Modifier une difficulté de réunion'],
            ['nom' => 'delete_reunion_difficulte', 'description' => 'Supprimer une difficulté de réunion'],
        ];

        // =================================================================
        // PERMISSIONS DÉCISIONS
        // =================================================================
        $decisionPermissions = [
            ['nom' => 'view_reunion_decisions', 'description' => 'Voir les décisions de réunion'],
            ['nom' => 'create_reunion_decision', 'description' => 'Créer une décision de réunion'],
            ['nom' => 'edit_reunion_decision', 'description' => 'Modifier une décision de réunion'],
            ['nom' => 'delete_reunion_decision', 'description' => 'Supprimer une décision de réunion'],
        ];

        // =================================================================
        // PERMISSIONS ACTIONS
        // =================================================================
        $actionPermissions = [
            ['nom' => 'view_reunion_actions', 'description' => 'Voir les actions de réunion'],
            ['nom' => 'create_reunion_action', 'description' => 'Créer une action de réunion'],
            ['nom' => 'edit_reunion_action', 'description' => 'Modifier une action de réunion'],
            ['nom' => 'delete_reunion_action', 'description' => 'Supprimer une action de réunion'],
        ];

        // =================================================================
        // PERMISSIONS PV (PROCÈS-VERBAUX)
        // =================================================================
        $pvPermissions = [
            ['nom' => 'view_reunion_pvs', 'description' => 'Voir les procès-verbaux'],
            ['nom' => 'create_reunion_pv', 'description' => 'Créer un procès-verbal'],
            ['nom' => 'edit_reunion_pv', 'description' => 'Modifier un procès-verbal'],
            ['nom' => 'delete_reunion_pv', 'description' => 'Supprimer un procès-verbal'],
            ['nom' => 'validate_reunion_pv', 'description' => 'Valider un procès-verbal'],
        ];

        // =================================================================
        // PERMISSIONS WORKFLOWS
        // =================================================================
        $workflowPermissions = [
            ['nom' => 'view_reunion_workflows', 'description' => 'Voir les workflows de réunion'],
            ['nom' => 'create_reunion_workflow', 'description' => 'Créer un workflow de réunion'],
            ['nom' => 'start_reunion_workflow', 'description' => 'Démarrer un workflow de réunion'],
            ['nom' => 'validate_reunion_workflow', 'description' => 'Valider une étape de workflow'],
            ['nom' => 'cancel_reunion_workflow', 'description' => 'Annuler un workflow de réunion'],
            ['nom' => 'edit_reunion_workflow', 'description' => 'Modifier un workflow de réunion'],
            ['nom' => 'delete_reunion_workflow', 'description' => 'Supprimer un workflow de réunion'],
        ];

        // =================================================================
        // PERMISSIONS CALENDRIER
        // =================================================================
        $calendarPermissions = [
            ['nom' => 'view_reunion_calendar', 'description' => 'Voir le calendrier de réunions'],
            ['nom' => 'manage_reunion_calendar', 'description' => 'Gérer le calendrier de réunions'],
        ];

        // =================================================================
        // PERMISSIONS ANALYTICS
        // =================================================================
        $analyticsPermissions = [
            ['nom' => 'view_reunion_analytics', 'description' => 'Voir les analytics de réunions'],
            ['nom' => 'export_reunion_analytics', 'description' => 'Exporter les analytics de réunions'],
        ];

        // =================================================================
        // FUSION DE TOUTES LES PERMISSIONS
        // =================================================================
        $allPermissions = array_merge(
            $notificationPermissions,
            $reunionPermissions,
            $seriesPermissions,
            $generatedReunionPermissions,
            $typeReunionPermissions,
            $participantPermissions,
            $ordreJourPermissions,
            $sujetPermissions,
            $objectifPermissions,
            $difficultePermissions,
            $decisionPermissions,
            $actionPermissions,
            $pvPermissions,
            $workflowPermissions,
            $calendarPermissions,
            $analyticsPermissions
        );

        // =================================================================
        // CRÉATION DES PERMISSIONS
        // =================================================================
        $createdPermissions = [];
        foreach ($allPermissions as $permissionData) {
            $permission = Permission::firstOrCreate(
                ['nom' => $permissionData['nom']],
                [
                    'description' => $permissionData['description'],
                    'date_creation' => $now,
                    'date_modification' => $now,
                    'creer_par' => $creator,
                ]
            );
            $createdPermissions[] = $permission;
        }

        // =================================================================
        // AFFECTATION DES PERMISSIONS À L'ADMIN
        // =================================================================
        $adminRole = Role::where('nom', 'Administrateur')->first();
        if ($adminRole) {
            foreach ($createdPermissions as $permission) {
                if (!$adminRole->permissions()->where('permission_id', $permission->id)->exists()) {
                    $adminRole->permissions()->attach($permission->id, [
                        'date_creation' => $now,
                    ]);
                }
            }
            $this->command->info('✅ Permissions affectées au rôle Administrateur avec succès !');
        } else {
            $this->command->warn('⚠️ Rôle Administrateur non trouvé. Permissions non affectées.');
        }

        // =================================================================
        // AFFECTATION DES PERMISSIONS AUX UTILISATEURS ADMIN
        // =================================================================
        $adminUsers = User::whereHas('roles', function ($query) {
            $query->where('nom', 'Administrateur');
        })->get();

        if ($adminUsers->count() > 0) {
            $this->command->info("✅ {$adminUsers->count()} utilisateur(s) admin trouvé(s) - permissions héritées via le rôle !");
        }

        // =================================================================
        // RAPPORT FINAL
        // =================================================================
        $this->command->info('🎉 Seeder ReunionPermissionsSeeder terminé avec succès !');
        $this->command->info("📋 Total des permissions créées : " . count($createdPermissions));

        $this->command->info('📋 Permissions créées par catégorie :');
        $this->command->info("   🔔 Notifications : " . count($notificationPermissions));
        $this->command->info("   📅 Réunions : " . count($reunionPermissions));
        $this->command->info("   🔄 Séries : " . count($seriesPermissions));
        $this->command->info("   🎯 Réunions Générées : " . count($generatedReunionPermissions));
        $this->command->info("   🏷️ Types : " . count($typeReunionPermissions));
        $this->command->info("   👥 Participants : " . count($participantPermissions));
        $this->command->info("   📋 Ordre du jour : " . count($ordreJourPermissions));
        $this->command->info("   📝 Sujets : " . count($sujetPermissions));
        $this->command->info("   🎯 Objectifs : " . count($objectifPermissions));
        $this->command->info("   ⚠️ Difficultés : " . count($difficultePermissions));
        $this->command->info("   ✅ Décisions : " . count($decisionPermissions));
        $this->command->info("   🔧 Actions : " . count($actionPermissions));
        $this->command->info("   📄 PV : " . count($pvPermissions));
        $this->command->info("   🔄 Workflows : " . count($workflowPermissions));
        $this->command->info("   📅 Calendrier : " . count($calendarPermissions));
        $this->command->info("   📊 Analytics : " . count($analyticsPermissions));
    }
}
