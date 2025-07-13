<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class TypeTachePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permissions pour les types de tâches
        $permissions = [
            'view_type_taches_list' => 'Voir la liste des types de tâches',
            'view_type_tache_details' => 'Voir les détails d\'un type de tâche',
            'view_type_tache_stats' => 'Voir les statistiques d\'un type de tâche',
            'create_type_tache' => 'Créer un type de tâche',
            'edit_type_tache' => 'Modifier un type de tâche',
            'delete_type_tache' => 'Supprimer un type de tâche',
        ];

        foreach ($permissions as $nom => $description) {
            Permission::firstOrCreate([
                'nom' => $nom,
            ], [
                'description' => $description,
                'date_creation' => now(),
                'date_modification' => now(),
                'creer_par' => 'seeder',
                'modifie_par' => 'seeder',
            ]);
        }

        // Attribuer les permissions aux rôles existants
        $roles = Role::all();

        foreach ($roles as $role) {
            $now = now();
            // Permissions à attribuer selon le rôle
            if (in_array($role->nom, ['Super Administrateur', 'Administrateur'])) {
                $perms = Permission::whereIn('nom', array_keys($permissions))->get();
            } elseif (in_array($role->nom, ['Manager', 'Chef de Service'])) {
                $perms = Permission::whereIn('nom', [
                    'view_type_taches_list',
                    'view_type_tache_details',
                    'view_type_tache_stats'
                ])->get();
            } else {
                $perms = Permission::where('nom', 'view_type_taches_list')->get();
            }
            foreach ($perms as $perm) {
                // On vérifie si la permission n'est pas déjà attachée
                if (!$role->permissions->contains($perm->id)) {
                    $role->permissions()->attach($perm->id, [
                        'date_creation' => $now,
                    ]);
                }
            }
        }
    }
}
