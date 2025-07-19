<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class MultipleAssignmentsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les nouvelles permissions pour les assignations multiples
        $permissions = [
            [
                'nom' => 'manage_project_porteurs',
                'description' => 'Gérer les porteurs multiples d\'un projet',
                'date_creation' => now(),
                'date_modification' => now(),
                'creer_par' => 'system',
                'modifier_par' => 'system'
            ],
            [
                'nom' => 'manage_task_responsables',
                'description' => 'Gérer les responsables multiples d\'une tâche',
                'date_creation' => now(),
                'date_modification' => now(),
                'creer_par' => 'system',
                'modifier_par' => 'system'
            ]
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['nom' => $permissionData['nom']],
                $permissionData
            );
        }

        // Assigner ces permissions aux rôles appropriés
        $this->assignPermissionsToRoles();
    }

    /**
     * Assigner les nouvelles permissions aux rôles existants
     */
    private function assignPermissionsToRoles(): void
    {
        // Récupérer les permissions
        $manageProjectPorteurs = Permission::where('nom', 'manage_project_porteurs')->first();
        $manageTaskResponsables = Permission::where('nom', 'manage_task_responsables')->first();

        if (!$manageProjectPorteurs || !$manageTaskResponsables) {
            $this->command->error('Permissions non trouvées');
            return;
        }

        // Rôles qui doivent avoir ces permissions
        $rolesWithProjectPorteurs = [
            'Administrateur',
            'Chef de Service',
            'Chef de Division',
            'Chef de Direction'
        ];

        $rolesWithTaskResponsables = [
            'Administrateur',
            'Chef de Service',
            'Chef de Division',
            'Chef de Direction',
            'Responsable de Projet'
        ];

        // Assigner les permissions aux rôles
        foreach ($rolesWithProjectPorteurs as $roleName) {
            $role = Role::where('nom', $roleName)->first();
                          if ($role && !$role->permissions()->where('nom', 'manage_project_porteurs')->exists()) {
                  $role->permissions()->syncWithoutDetaching([
                      $manageProjectPorteurs->id => ['date_creation' => now()]
                  ]);
                  $this->command->info("Permission 'manage_project_porteurs' assignée au rôle '{$roleName}'");
              }
        }

        foreach ($rolesWithTaskResponsables as $roleName) {
            $role = Role::where('nom', $roleName)->first();
                          if ($role && !$role->permissions()->where('nom', 'manage_task_responsables')->exists()) {
                  $role->permissions()->syncWithoutDetaching([
                      $manageTaskResponsables->id => ['date_creation' => now()]
                  ]);
                  $this->command->info("Permission 'manage_task_responsables' assignée au rôle '{$roleName}'");
              }
        }
    }
}
