<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class EntityTypePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Permissions pour les types d'entités
            ['name' => 'view_entity_types_list', 'description' => 'Voir la liste des types d\'entités'],
            ['name' => 'create_entity_type', 'description' => 'Créer un type d\'entité'],
            ['name' => 'edit_entity_type', 'description' => 'Modifier un type d\'entité'],
            ['name' => 'delete_entity_type', 'description' => 'Supprimer un type d\'entité'],
            ['name' => 'view_entity_type_details', 'description' => 'Voir les détails d\'un type d\'entité'],

            // Permissions pour les postes
            ['name' => 'view_posts_list', 'description' => 'Voir la liste des postes'],
            ['name' => 'create_post', 'description' => 'Créer un poste'],
            ['name' => 'edit_post', 'description' => 'Modifier un poste'],
            ['name' => 'delete_post', 'description' => 'Supprimer un poste'],
            ['name' => 'view_post_details', 'description' => 'Voir les détails d\'un poste'],
            ['name' => 'view_posts_stats', 'description' => 'Voir les statistiques des postes'],

            // Permissions pour l'organigramme
            ['name' => 'view_organigramme', 'description' => 'Voir l\'organigramme'],
            ['name' => 'export_organigramme', 'description' => 'Exporter l\'organigramme'],
            ['name' => 'print_organigramme', 'description' => 'Imprimer l\'organigramme'],
            ['name' => 'view_organigramme_details', 'description' => 'Voir les détails dans l\'organigramme'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['nom' => $permission['name']],
                [
                    'description' => $permission['description'],
                    'date_creation' => now(),
                    'date_modification' => now(),
                    'creer_par' => 'system',
                    'modifier_par' => 'system'
                ]
            );
        }
    }
}
