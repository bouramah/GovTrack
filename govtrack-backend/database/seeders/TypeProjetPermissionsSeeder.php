<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class TypeProjetPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Permissions de base pour les types de projets
            ['nom' => 'view_type_projets_list', 'description' => 'Voir la liste des types de projets'],
            ['nom' => 'create_type_projet', 'description' => 'Créer un nouveau type de projet'],
            ['nom' => 'edit_type_projet', 'description' => 'Modifier un type de projet existant'],
            ['nom' => 'delete_type_projet', 'description' => 'Supprimer un type de projet'],
            ['nom' => 'view_type_projet_details', 'description' => 'Voir les détails d\'un type de projet'],

            // Permissions pour les statistiques
            ['nom' => 'view_type_projet_stats', 'description' => 'Voir les statistiques des types de projets'],

            // Permissions pour la gestion des projets liés
            ['nom' => 'view_type_projet_projects', 'description' => 'Voir les projets d\'un type spécifique'],
            ['nom' => 'manage_type_projet_projects', 'description' => 'Gérer les projets d\'un type spécifique'],

            // Permissions pour les paramètres avancés
            ['nom' => 'configure_type_projet_sla', 'description' => 'Configurer les SLA des types de projets'],
            ['nom' => 'manage_type_projet_workflow', 'description' => 'Gérer les workflows des types de projets'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['nom' => $permission['nom']],
                array_merge($permission, [
                    'date_creation' => now(),
                    'date_modification' => now(),
                    'creer_par' => 'system',
                    'modifier_par' => 'system'
                ])
            );
        }

        $this->command->info('Permissions des types de projets créées avec succès !');
    }
}
