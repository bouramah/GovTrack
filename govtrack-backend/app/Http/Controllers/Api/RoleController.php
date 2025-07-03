<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Role::with(['permissions', 'users']);

            // Filtrage par nom
            if ($request->filled('nom')) {
                $query->where('nom', 'like', '%' . $request->nom . '%');
            }

            // Tri
            $sortBy = $request->get('sort_by', 'nom');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $roles = $query->paginate($perPage);

            $data = $roles->getCollection()->map(function ($role) {
                return [
                    'id' => $role->id,
                    'nom' => $role->nom,
                    'description' => $role->description,
                    'nombre_permissions' => $role->permissions->count(),
                    'permissions' => $role->permissions->pluck('nom'),
                    'nombre_utilisateurs' => $role->users->count(),
                    'utilisateurs' => $role->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'nom' => $user->nom,
                            'prenom' => $user->prenom,
                            'matricule' => $user->matricule,
                        ];
                    }),
                    'date_creation' => $role->date_creation,
                    'creer_par' => $role->creer_par,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $roles->currentPage(),
                    'last_page' => $roles->lastPage(),
                    'per_page' => $roles->perPage(),
                    'total' => $roles->total(),
                ],
                'message' => 'Rôles récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des rôles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $now = Carbon::now();
        $role = Role::create([
            'nom' => $validated['nom'],
            'description' => $validated['description'] ?? null,
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $request->user()->email,
        ]);

        // Assigner les permissions si fournies
        if (!empty($validated['permissions'])) {
            $role->permissions()->attach($validated['permissions'], [
                'date_creation' => $now
            ]);
        }

        $role->load('permissions');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $role->id,
                'nom' => $role->nom,
                'description' => $role->description,
                'permissions' => $role->permissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'nom' => $permission->nom,
                        'description' => $permission->description,
                    ];
                }),
                'date_creation' => $role->date_creation,
            ],
            'message' => 'Rôle créé avec succès'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $role = Role::with(['permissions', 'users.affectations.entite'])
                   ->findOrFail($id);

        $response = [
            'id' => $role->id,
            'nom' => $role->nom,
            'description' => $role->description,
            'permissions' => $role->permissions->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'nom' => $permission->nom,
                    'description' => $permission->description,
                    'date_assignation' => $permission->pivot->date_creation,
                ];
            }),
            'utilisateurs' => $role->users->map(function ($user) {
                $affectationActuelle = $user->affectations()
                    ->where('statut', true)
                    ->with(['poste', 'entite'])
                    ->first();

                return [
                    'id' => $user->id,
                    'matricule' => $user->matricule,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'statut' => $user->statut,
                    'affectation_actuelle' => $affectationActuelle ? [
                        'poste' => $affectationActuelle->poste->nom,
                        'entite' => $affectationActuelle->entite->nom,
                    ] : null,
                    'date_assignation_role' => $user->pivot->date_creation,
                ];
            }),
            'statistiques' => [
                'total_permissions' => $role->permissions->count(),
                'total_utilisateurs' => $role->users->count(),
                'utilisateurs_actifs' => $role->users->where('statut', true)->count(),
            ],
            'date_creation' => $role->date_creation,
            'date_modification' => $role->date_modification,
            'creer_par' => $role->creer_par,
            'modifier_par' => $role->modifier_par,
        ];

        return response()->json([
            'success' => true,
            'data' => $response,
            'message' => 'Rôle récupéré avec succès'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($id)
            ],
            'description' => 'nullable|string|max:1000',
        ]);

        $role->update([
            'nom' => $validated['nom'],
            'description' => $validated['description'] ?? $role->description,
            'date_modification' => Carbon::now(),
            'modifier_par' => $request->user()->email,
        ]);

        return response()->json([
            'success' => true,
            'data' => $role->fresh(['permissions']),
            'message' => 'Rôle mis à jour avec succès'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        // Vérifier s'il y a des utilisateurs avec ce rôle
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer ce rôle car il est assigné à des utilisateurs',
                'utilisateurs_affectes' => $role->users->map(function ($user) {
                    return [
                        'nom' => $user->nom,
                        'prenom' => $user->prenom,
                        'matricule' => $user->matricule,
                    ];
                })
            ], 422);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rôle supprimé avec succès'
        ]);
    }

    /**
     * Assign permission to role
     */
    public function assignPermission(Request $request, string $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $permission = Permission::findOrFail($validated['permission_id']);

        // Vérifier si le rôle a déjà cette permission
        if ($role->permissions()->where('permission_id', $permission->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce rôle a déjà cette permission'
            ], 422);
        }

        // Assigner la permission
        $role->permissions()->attach($permission->id, [
            'date_creation' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'role' => [
                    'id' => $role->id,
                    'nom' => $role->nom,
                ],
                'permission' => [
                    'id' => $permission->id,
                    'nom' => $permission->nom,
                    'description' => $permission->description,
                ],
                'permissions_actuelles' => $role->permissions()->pluck('nom')->toArray(),
            ],
            'message' => 'Permission assignée avec succès'
        ]);
    }

    /**
     * Remove permission from role
     */
    public function removePermission(string $roleId, string $permissionId): JsonResponse
    {
        $role = Role::findOrFail($roleId);
        $permission = Permission::findOrFail($permissionId);

        // Vérifier si le rôle a cette permission
        if (!$role->permissions()->where('permission_id', $permission->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce rôle n\'a pas cette permission'
            ], 422);
        }

        // Retirer la permission
        $role->permissions()->detach($permission->id);

        return response()->json([
            'success' => true,
            'data' => [
                'role' => [
                    'id' => $role->id,
                    'nom' => $role->nom,
                ],
                'permission_retiree' => [
                    'id' => $permission->id,
                    'nom' => $permission->nom,
                ],
                'permissions_restantes' => $role->permissions()->pluck('nom')->toArray(),
            ],
            'message' => 'Permission retirée avec succès'
        ]);
    }

    /**
     * Get all permissions available for assignment
     */
    public function availablePermissions(string $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        // Récupérer toutes les permissions qui ne sont pas encore assignées à ce rôle
        $assignedPermissionIds = $role->permissions()->pluck('permissions.id');
        $availablePermissions = Permission::whereNotIn('id', $assignedPermissionIds)
                                        ->orderBy('nom')
                                        ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'role' => [
                    'id' => $role->id,
                    'nom' => $role->nom,
                ],
                'permissions_disponibles' => $availablePermissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'nom' => $permission->nom,
                        'description' => $permission->description,
                    ];
                }),
                'permissions_deja_assignees' => $role->permissions->pluck('nom'),
            ],
            'message' => 'Permissions disponibles récupérées avec succès'
        ]);
    }
}
