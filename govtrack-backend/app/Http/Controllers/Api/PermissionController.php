<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Permission::with(['roles.users']);

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
            $permissions = $query->paginate($perPage);

            $data = $permissions->getCollection()->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'nom' => $permission->nom,
                    'description' => $permission->description,
                    'nombre_roles' => $permission->roles->count(),
                    'roles' => $permission->roles->pluck('nom'),
                    'nombre_utilisateurs_total' => $permission->roles
                        ->pluck('users')
                        ->flatten()
                        ->unique('id')
                        ->count(),
                    'date_creation' => $permission->date_creation,
                    'creer_par' => $permission->creer_par,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $permissions->currentPage(),
                    'last_page' => $permissions->lastPage(),
                    'per_page' => $permissions->perPage(),
                    'total' => $permissions->total(),
                ],
                'message' => 'Permissions récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des permissions',
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
            'nom' => 'required|string|max:255|unique:permissions',
            'description' => 'nullable|string|max:1000',
        ]);

        $now = Carbon::now();
        $permission = Permission::create([
            'nom' => $validated['nom'],
            'description' => $validated['description'] ?? null,
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $request->user()->email,
        ]);

        return response()->json([
            'success' => true,
            'data' => $permission,
            'message' => 'Permission créée avec succès'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $permission = Permission::with(['roles.users.affectations.entite'])
                                ->findOrFail($id);

        // Récupérer tous les utilisateurs uniques ayant cette permission via leurs rôles
        $utilisateursAvecPermission = $permission->roles
            ->pluck('users')
            ->flatten()
            ->unique('id')
            ->map(function ($user) use ($permission) {
                $affectationActuelle = $user->affectations()
                    ->where('statut', true)
                    ->with(['poste', 'entite'])
                    ->first();

                // Récupérer les rôles de cet utilisateur qui ont cette permission
                $rolesAvecCettePermission = $user->roles()
                    ->whereHas('permissions', function ($query) use ($permission) {
                        $query->where('permissions.id', $permission->id);
                    })
                    ->pluck('nom')
                    ->toArray();

                return [
                    'id' => $user->id,
                    'matricule' => $user->matricule,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'statut' => $user->statut,
                    'roles_avec_cette_permission' => $rolesAvecCettePermission,
                    'affectation_actuelle' => $affectationActuelle ? [
                        'poste' => $affectationActuelle->poste->nom,
                        'entite' => $affectationActuelle->entite->nom,
                    ] : null,
                ];
            });

        $response = [
            'id' => $permission->id,
            'nom' => $permission->nom,
            'description' => $permission->description,
            'roles' => $permission->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'nom' => $role->nom,
                    'description' => $role->description,
                    'nombre_utilisateurs' => $role->users->count(),
                    'date_assignation' => $role->pivot->date_creation,
                ];
            }),
            'utilisateurs_avec_permission' => $utilisateursAvecPermission->values(),
            'statistiques' => [
                'total_roles' => $permission->roles->count(),
                'total_utilisateurs' => $utilisateursAvecPermission->count(),
                'utilisateurs_actifs' => $utilisateursAvecPermission->where('statut', true)->count(),
            ],
            'date_creation' => $permission->date_creation,
            'date_modification' => $permission->date_modification,
            'creer_par' => $permission->creer_par,
            'modifier_par' => $permission->modifier_par,
        ];

        return response()->json([
            'success' => true,
            'data' => $response,
            'message' => 'Permission récupérée avec succès'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);

        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions')->ignore($id)
            ],
            'description' => 'nullable|string|max:1000',
        ]);

        $permission->update([
            'nom' => $validated['nom'],
            'description' => $validated['description'] ?? $permission->description,
            'date_modification' => Carbon::now(),
            'modifier_par' => $request->user()->email,
        ]);

        return response()->json([
            'success' => true,
            'data' => $permission->fresh(['roles']),
            'message' => 'Permission mise à jour avec succès'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);

        // Vérifier s'il y a des rôles avec cette permission
        if ($permission->roles()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer cette permission car elle est assignée à des rôles',
                'roles_affectes' => $permission->roles->map(function ($role) {
                    return [
                        'nom' => $role->nom,
                        'nombre_utilisateurs' => $role->users->count(),
                    ];
                })
            ], 422);
        }

        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permission supprimée avec succès'
        ]);
    }

    /**
     * Get users who have this permission (via their roles)
     */
    public function users(string $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);

        // Récupérer tous les utilisateurs qui ont cette permission via leurs rôles
        $utilisateurs = collect();

        foreach ($permission->roles as $role) {
            foreach ($role->users as $user) {
                if (!$utilisateurs->contains('id', $user->id)) {
                    $affectationActuelle = $user->affectations()
                        ->where('statut', true)
                        ->with(['poste', 'entite'])
                        ->first();

                    $rolesAvecCettePermission = $user->roles()
                        ->whereHas('permissions', function ($query) use ($permission) {
                            $query->where('permissions.id', $permission->id);
                        })
                        ->get();

                    $utilisateurs->push([
                        'id' => $user->id,
                        'matricule' => $user->matricule,
                        'nom' => $user->nom,
                        'prenom' => $user->prenom,
                        'email' => $user->email,
                        'statut' => $user->statut,
                        'roles_avec_cette_permission' => $rolesAvecCettePermission->map(function ($role) {
                            return [
                                'id' => $role->id,
                                'nom' => $role->nom,
                                'date_assignation_role' => $role->pivot->date_creation,
                            ];
                        }),
                        'affectation_actuelle' => $affectationActuelle ? [
                            'poste' => $affectationActuelle->poste->nom,
                            'entite' => $affectationActuelle->entite->nom,
                            'date_debut' => $affectationActuelle->date_debut,
                        ] : null,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'permission' => [
                    'id' => $permission->id,
                    'nom' => $permission->nom,
                    'description' => $permission->description,
                ],
                'utilisateurs' => $utilisateurs->sortBy('nom')->values(),
                'statistiques' => [
                    'total_utilisateurs' => $utilisateurs->count(),
                    'utilisateurs_actifs' => $utilisateurs->where('statut', true)->count(),
                    'roles_avec_permission' => $permission->roles->count(),
                ],
            ],
            'message' => 'Utilisateurs avec cette permission récupérés avec succès'
        ]);
    }

    /**
     * Get available roles that don't have this permission yet
     */
    public function availableRoles(string $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);

        // Récupérer tous les rôles qui n'ont pas encore cette permission
        $assignedRoleIds = $permission->roles()->pluck('roles.id');
        $availableRoles = \App\Models\Role::whereNotIn('id', $assignedRoleIds)
                                         ->orderBy('nom')
                                         ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'permission' => [
                    'id' => $permission->id,
                    'nom' => $permission->nom,
                ],
                'roles_disponibles' => $availableRoles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'nom' => $role->nom,
                        'description' => $role->description,
                        'nombre_utilisateurs' => $role->users->count(),
                    ];
                }),
                'roles_deja_assignes' => $permission->roles->pluck('nom'),
            ],
            'message' => 'Rôles disponibles récupérés avec succès'
        ]);
    }
}
