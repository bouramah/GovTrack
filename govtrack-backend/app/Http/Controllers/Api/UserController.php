<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Poste;
use App\Models\Entite;
use App\Models\UtilisateurEntiteHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::with(['roles', 'affectations.poste', 'affectations.entite'])
                ->orderBy('nom');

            // Filtres optionnels
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('matricule', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->filled('statut')) {
                $query->where('statut', $request->boolean('statut'));
            }

            if ($request->filled('role')) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('nom', $request->role);
                });
            }

            // Tri
            $sortBy = $request->get('sort_by', 'nom');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $users = $query->paginate($perPage);

            // Transformation des données
            $users->getCollection()->transform(function ($user) {
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
                    'telephone' => $user->telephone,
                    'adresse' => $user->adresse,
                    'statut' => $user->statut,
                    'roles' => $user->roles->pluck('nom'),
                    'affectation_actuelle' => $affectationActuelle ? [
                        'poste' => $affectationActuelle->poste->nom,
                        'entite' => $affectationActuelle->entite->nom,
                        'date_debut' => $affectationActuelle->date_debut,
                    ] : null,
                    'date_creation' => $user->date_creation,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
                'message' => 'Utilisateurs récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des utilisateurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'matricule' => 'required|string|max:20|unique:users',
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'telephone' => 'nullable|string|max:20',
                'adresse' => 'nullable|string|max:500',
                'password' => 'required|string|min:8',
                'statut' => 'boolean',
            ]);

            $now = Carbon::now();
            $user = User::create([
                'matricule' => $validated['matricule'],
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'name' => $validated['prenom'] . ' ' . $validated['nom'], // Pour compatibilité Laravel
                'email' => $validated['email'],
                'telephone' => $validated['telephone'] ?? null,
                'adresse' => $validated['adresse'] ?? null,
                'password' => Hash::make($validated['password']),
                'statut' => $validated['statut'] ?? true,
                'date_creation' => $now,
                'date_modification' => $now,
                'creer_par' => $request->user()->email,
            ]);

            // Ne pas retourner le mot de passe
            $user->makeHidden(['password']);

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Utilisateur créé avec succès'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $user = User::with([
            'roles.permissions',
            'affectations.poste',
            'affectations.entite.typeEntite',
            'entitesDigees.entite'
        ])->findOrFail($id);

        // Récupérer l'affectation actuelle
        $affectationActuelle = $user->affectations()
            ->where('statut', true)
            ->with(['poste', 'entite'])
            ->first();

        $response = [
            'id' => $user->id,
            'matricule' => $user->matricule,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'name' => $user->name,
            'email' => $user->email,
            'telephone' => $user->telephone,
            'adresse' => $user->adresse,
            'photo' => $user->photo_url,
            'statut' => $user->statut,
            'roles' => $user->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'nom' => $role->nom,
                    'description' => $role->description,
                    'permissions' => $role->permissions->pluck('nom'),
                ];
            }),
            'permissions' => $user->getAllPermissions()->pluck('nom')->unique()->values(),
            'affectation_actuelle' => $affectationActuelle ? [
                'poste' => $affectationActuelle->poste->nom,
                'entite' => $affectationActuelle->entite->nom,
                'date_debut' => $affectationActuelle->date_debut,
            ] : null,
            'historique_affectations' => $user->affectations()
                ->where('statut', false)
                ->with(['poste', 'entite'])
                ->orderBy('date_fin', 'desc')
                ->get()
                ->map(function ($affectation) {
                    return [
                        'poste' => $affectation->poste->nom,
                        'entite' => $affectation->entite->nom,
                        'date_debut' => $affectation->date_debut,
                        'date_fin' => $affectation->date_fin,
                    ];
                }),
            'entites_dirigees' => $user->entitesDigees()
                ->whereNull('date_fin')
                ->with('entite')
                ->get()
                ->map(function ($direction) {
                    return [
                        'entite_id' => $direction->entite->id,
                        'entite_nom' => $direction->entite->nom,
                        'date_debut' => $direction->date_debut,
                    ];
                }),
            'statistiques' => [
                'total_affectations' => $user->affectations->count(),
                'entites_dirigees_actuellement' => $user->entitesDigees()->whereNull('date_fin')->count(),
                'roles_actifs' => $user->roles->count(),
            ],
            'date_creation' => $user->date_creation,
            'date_modification' => $user->date_modification,
            'creer_par' => $user->creer_par,
            'modifier_par' => $user->modifier_par,
        ];

        return response()->json([
            'success' => true,
            'data' => $response,
            'message' => 'Utilisateur récupéré avec succès'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'matricule' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('users')->ignore($id)
                ],
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users')->ignore($id)
                ],
                'telephone' => 'nullable|string|max:20',
                'adresse' => 'nullable|string|max:500',
                'password' => 'nullable|string|min:8',
                'statut' => 'boolean',
            ]);

            $updateData = [
                'matricule' => $validated['matricule'],
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'name' => $validated['prenom'] . ' ' . $validated['nom'],
                'email' => $validated['email'],
                'telephone' => $validated['telephone'] ?? $user->telephone,
                'adresse' => $validated['adresse'] ?? $user->adresse,
                'statut' => $validated['statut'] ?? $user->statut,
                'date_modification' => Carbon::now(),
                'modifier_par' => $request->user()->email,
            ];

            // Mettre à jour le mot de passe seulement s'il est fourni
            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);
            $user->makeHidden(['password']);

            return response()->json([
                'success' => true,
                'data' => $user->fresh(),
                'message' => 'Utilisateur mis à jour avec succès'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Vérifier s'il y a des affectations actives
        if ($user->affectations()->where('statut', true)->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer cet utilisateur car il a des affectations actives'
            ], 422);
        }

        // Vérifier s'il dirige actuellement des entités
        if ($user->entitesDigees()->whereNull('date_fin')->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer cet utilisateur car il dirige actuellement des entités'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }

    /**
     * Get user affectations history
     */
    public function affectations(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $affectations = $user->affectations()
            ->with(['poste', 'entite.typeEntite'])
            ->orderBy('date_debut', 'desc')
            ->get()
            ->map(function ($affectation) {
                return [
                    'id' => $affectation->id,
                    'poste' => [
                        'id' => $affectation->poste->id,
                        'nom' => $affectation->poste->nom,
                        'description' => $affectation->poste->description,
                    ],
                    'entite' => [
                        'id' => $affectation->entite->id,
                        'nom' => $affectation->entite->nom,
                        'type' => $affectation->entite->typeEntite->nom,
                    ],
                    'statut' => $affectation->statut,
                    'date_debut' => $affectation->date_debut,
                    'date_fin' => $affectation->date_fin,
                    'date_creation' => $affectation->date_creation,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $affectations,
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'matricule' => $user->matricule,
            ],
            'message' => 'Historique des affectations récupéré avec succès'
        ]);
    }

    /**
     * Assign user to a poste in an entite
     */
    public function affecter(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'poste_id' => 'required|exists:postes,id',
            'entite_id' => 'required|exists:entites,id',
            'date_debut' => 'required|date',
            'terminer_affectation_precedente' => 'boolean',
        ]);

        // Vérifier si l'utilisateur a déjà une affectation active
        $affectationActive = $user->affectations()->where('statut', true)->first();

        if ($affectationActive && !($validated['terminer_affectation_precedente'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'L\'utilisateur a déjà une affectation active. Utilisez terminer_affectation_precedente=true pour la terminer automatiquement.',
                'affectation_active' => [
                    'poste' => $affectationActive->poste->nom,
                    'entite' => $affectationActive->entite->nom,
                    'date_debut' => $affectationActive->date_debut,
                ]
            ], 422);
        }

        // Terminer l'affectation précédente si demandé
        if ($affectationActive && ($validated['terminer_affectation_precedente'] ?? false)) {
            $affectationActive->update([
                'statut' => false,
                'date_fin' => Carbon::parse($validated['date_debut'])->subDay(),
                'date_modification' => Carbon::now(),
                'modifier_par' => 'api_user',
            ]);
        }

        // Créer la nouvelle affectation
        $now = Carbon::now();
        $nouvelleAffectation = UtilisateurEntiteHistory::create([
            'user_id' => $user->id,
            'poste_id' => $validated['poste_id'],
            'service_id' => $validated['entite_id'],
            'statut' => true,
            'date_debut' => Carbon::parse($validated['date_debut']),
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $request->user()->email,
        ]);

        $nouvelleAffectation->load(['poste', 'entite']);

        return response()->json([
            'success' => true,
            'data' => [
                'affectation' => [
                    'id' => $nouvelleAffectation->id,
                    'poste' => $nouvelleAffectation->poste->nom,
                    'entite' => $nouvelleAffectation->entite->nom,
                    'date_debut' => $nouvelleAffectation->date_debut,
                    'statut' => $nouvelleAffectation->statut,
                ],
                'affectation_precedente_terminee' => $affectationActive ? true : false,
            ],
            'message' => 'Utilisateur affecté avec succès'
        ], 201);
    }

    /**
     * Terminer l'affectation active d'un utilisateur
     */
    public function terminerAffectation(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'date_fin' => 'required|date',
            'raison' => 'nullable|string|max:500',
        ]);

        // Trouver l'affectation active
        $affectationActive = $user->affectations()->where('statut', true)->first();

        if (!$affectationActive) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune affectation active trouvée pour cet utilisateur'
            ], 404);
        }

        // Vérifier que la date de fin n'est pas antérieure à la date de début
        $dateFin = Carbon::parse($validated['date_fin']);
        if ($dateFin->lt(Carbon::parse($affectationActive->date_debut))) {
            return response()->json([
                'success' => false,
                'message' => 'La date de fin ne peut pas être antérieure à la date de début de l\'affectation'
            ], 422);
        }

        // Terminer l'affectation
        $affectationActive->update([
            'statut' => false,
            'date_fin' => $dateFin,
            'date_modification' => Carbon::now(),
            'modifier_par' => 'api_user',
        ]);

        // Log de la raison si fournie
        $message = 'Affectation terminée avec succès';
        if (!empty($validated['raison'])) {
            $message .= '. Raison: ' . $validated['raison'];
        }

        $affectationActive->load(['poste', 'entite']);

        return response()->json([
            'success' => true,
            'data' => [
                'affectation_terminee' => [
                    'id' => $affectationActive->id,
                    'poste' => $affectationActive->poste->nom,
                    'entite' => $affectationActive->entite->nom,
                    'date_debut' => $affectationActive->date_debut,
                    'date_fin' => $affectationActive->date_fin,
                    'raison' => $validated['raison'] ?? null,
                ],
                'user' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'matricule' => $user->matricule,
                ],
            ],
            'message' => $message
        ]);
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($validated['role_id']);

        // Vérifier si l'utilisateur a déjà ce rôle
        if ($user->roles()->where('role_id', $role->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'L\'utilisateur a déjà ce rôle'
            ], 422);
        }

        // Assigner le rôle
        $user->roles()->attach($role->id, [
            'date_creation' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'matricule' => $user->matricule,
                ],
                'role' => [
                    'id' => $role->id,
                    'nom' => $role->nom,
                    'description' => $role->description,
                ],
                'roles_actuels' => $user->roles()->pluck('nom')->toArray(),
            ],
            'message' => 'Rôle assigné avec succès'
        ]);
    }

    /**
     * Remove role from user
     */
    public function removeRole(string $userId, string $roleId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $role = Role::findOrFail($roleId);

        // Vérifier si l'utilisateur a ce rôle
        if (!$user->roles()->where('role_id', $role->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'L\'utilisateur n\'a pas ce rôle'
            ], 422);
        }

        // Retirer le rôle
        $user->roles()->detach($role->id);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'matricule' => $user->matricule,
                ],
                'role_retire' => [
                    'id' => $role->id,
                    'nom' => $role->nom,
                ],
                'roles_restants' => $user->roles()->pluck('nom')->toArray(),
            ],
            'message' => 'Rôle retiré avec succès'
        ]);
    }

    /**
     * Réinitialiser le mot de passe d'un utilisateur (admin)
     */
    public function resetPassword(Request $request, string $id): JsonResponse
    {
        try {
            $request->validate([
                'password' => 'nullable|string|min:8',
            ]);

            $defaultPassword = $request->get('password', config('auth.default_admin_reset_password', 'Default@123'));

            $user = User::findOrFail($id);
            $user->password = Hash::make($defaultPassword);
            $user->date_modification = Carbon::now();
            $user->modifier_par = $request->user()->email;
            $user->save();

            // Optionnel : notifier l'utilisateur de son nouveau mot de passe

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe réinitialisé avec succès',
                'data' => [
                    'default_password' => app()->environment('production') ? null : $defaultPassword
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation du mot de passe',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
