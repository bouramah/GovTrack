<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entite;
use App\Models\TypeEntite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Models\User;

class EntiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Entite::with(['typeEntite', 'parent', 'enfants', 'affectations' => function($query) {
                                 $query->where('statut', true);
                             }, 'affectations.user', 'chefs' => function($query) {
                                 $query->whereNull('date_fin');
                             }, 'chefs.user']);

            // Filtrage par nom
            if ($request->filled('nom')) {
                $query->where('nom', 'like', '%' . $request->nom . '%');
            }

            // Filtrage par type d'entité
            if ($request->filled('type_entite_id')) {
                $query->where('type_entite_id', $request->type_entite_id);
            }

            // Tri
            $sortBy = $request->get('sort_by', 'date_creation');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $entites = $query->paginate($perPage);

            $data = $entites->getCollection()->map(function ($entite) {
                // Chef actuel
                $chefActuel = $entite->chefs->first();

                return [
                    'id' => $entite->id,
                    'nom' => $entite->nom,
                    'description' => $entite->description,
                    'type_entite' => $entite->typeEntite,
                    'parent' => $entite->parent ? [
                        'id' => $entite->parent->id,
                        'nom' => $entite->parent->nom
                    ] : null,
                    'nombre_enfants' => $entite->enfants->count(),
                    'chef_actuel' => $chefActuel ? [
                        'id' => $chefActuel->user->id,
                        'nom' => $chefActuel->user->nom,
                        'prenom' => $chefActuel->user->prenom,
                        'matricule' => $chefActuel->user->matricule,
                    ] : null,
                    'employes_actuels' => $entite->affectations->map(function ($affectation) {
                        return [
                            'user' => [
                                'id' => $affectation->user->id,
                                'nom' => $affectation->user->nom,
                                'prenom' => $affectation->user->prenom,
                                'matricule' => $affectation->user->matricule,
                            ],
                        ];
                    }),
                    'date_creation' => $entite->date_creation,
                    'creer_par' => $entite->creer_par,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $entites->currentPage(),
                    'last_page' => $entites->lastPage(),
                    'per_page' => $entites->perPage(),
                    'total' => $entites->total(),
                ],
                'message' => 'Entités récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des entités',
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
            'nom' => 'required|string|max:255|unique:entites',
            'type_entite_id' => 'required|exists:type_entites,id',
            'parent_id' => 'nullable|exists:entites,id',
            'description' => 'nullable|string',
        ]);

        // Vérifier que l'entité parent n'est pas elle-même
        if (isset($validated['parent_id'])) {
            $parent = Entite::find($validated['parent_id']);
            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Entité parent introuvable'
                ], 404);
            }
        }

        $now = Carbon::now();
        $entite = Entite::create([
            'nom' => $validated['nom'],
            'type_entite_id' => $validated['type_entite_id'],
            'parent_id' => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $request->user()->email,
        ]);

        $entite->load(['typeEntite', 'parent']);

        return response()->json([
            'success' => true,
            'data' => $entite,
            'message' => 'Entité créée avec succès'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $entite = Entite::with(['typeEntite', 'parent', 'enfants.typeEntite', 'affectations.user', 'chefs.user'])
                        ->findOrFail($id);

        $response = [
            'id' => $entite->id,
            'nom' => $entite->nom,
            'description' => $entite->description,
            'type_entite' => $entite->typeEntite,
            'parent' => $entite->parent,
            'enfants' => $entite->enfants->map(function ($enfant) {
                return [
                    'id' => $enfant->id,
                    'nom' => $enfant->nom,
                    'type_entite' => $enfant->typeEntite->nom,
                ];
            }),
            'chef_actuel' => $entite->chefs()
                                   ->whereNull('date_fin')
                                   ->with('user')
                                   ->first(),
            'employes_actuels' => $entite->affectations()
                                        ->where('statut', true)
                                        ->with(['user', 'poste'])
                                        ->get()
                                        ->map(function ($affectation) {
                                            return [
                                                'user' => [
                                                    'id' => $affectation->user->id,
                                                    'nom' => $affectation->user->nom,
                                                    'prenom' => $affectation->user->prenom,
                                                    'matricule' => $affectation->user->matricule,
                                                ],
                                                'poste' => $affectation->poste->nom,
                                                'date_debut' => $affectation->date_debut,
                                            ];
                                        }),
            'date_creation' => $entite->date_creation,
            'creer_par' => $entite->creer_par,
        ];

        return response()->json([
            'success' => true,
            'data' => $response,
            'message' => 'Entité récupérée avec succès'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $entite = Entite::findOrFail($id);

        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('entites')->ignore($id)
            ],
            'type_entite_id' => 'required|exists:type_entites,id',
            'parent_id' => [
                'nullable',
                'exists:entites,id',
                function ($attribute, $value, $fail) use ($id) {
                    if ($value == $id) {
                        $fail('Une entité ne peut pas être son propre parent.');
                    }

                    // Vérifier qu'il n'y a pas de cycle dans la hiérarchie
                    if ($value && $this->wouldCreateCycle($id, $value)) {
                        $fail('Cette affectation créerait un cycle dans la hiérarchie.');
                    }
                },
            ],
            'description' => 'nullable|string',
        ]);

        $entite->update([
            'nom' => $validated['nom'],
            'type_entite_id' => $validated['type_entite_id'],
            'parent_id' => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? $entite->description,
            'date_modification' => Carbon::now(),
            'modifier_par' => $request->user()->email,
        ]);

        $entite->load(['typeEntite', 'parent']);

        return response()->json([
            'success' => true,
            'data' => $entite,
            'message' => 'Entité mise à jour avec succès'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $entite = Entite::findOrFail($id);

        // Vérifier s'il y a des entités enfants
        if ($entite->enfants()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer cette entité car elle a des entités enfants'
            ], 422);
        }

        // Vérifier s'il y a des affectations actives
        if ($entite->affectations()->where('statut', true)->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer cette entité car elle a des employés affectés'
            ], 422);
        }

        $entite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Entité supprimée avec succès'
        ]);
    }

    /**
     * Get children entities
     */
    public function enfants(string $id): JsonResponse
    {
        $entite = Entite::findOrFail($id);

        $enfants = $entite->enfants()
                         ->with(['typeEntite', 'enfants'])
                         ->get()
                         ->map(function ($enfant) {
                             return [
                                 'id' => $enfant->id,
                                 'nom' => $enfant->nom,
                                 'description' => $enfant->description,
                                 'type_entite' => $enfant->typeEntite->nom,
                                 'nombre_enfants' => $enfant->enfants->count(),
                                 'date_creation' => $enfant->date_creation,
                             ];
                         });

        return response()->json([
            'success' => true,
            'data' => $enfants,
            'parent' => [
                'id' => $entite->id,
                'nom' => $entite->nom,
            ],
            'message' => 'Entités enfants récupérées avec succès'
        ]);
    }

    /**
     * Get entity hierarchy (parents and children)
     */
    public function hierarchy(string $id): JsonResponse
    {
        $entite = Entite::with(['typeEntite', 'parent', 'enfants'])->findOrFail($id);

        $response = [
            'entite_actuelle' => [
                'id' => $entite->id,
                'nom' => $entite->nom,
                'type_entite' => $entite->typeEntite->nom,
                'description' => $entite->description,
            ],
            'parents' => $this->getParentsHierarchy($entite),
            'enfants' => $this->getChildrenHierarchy($entite),
        ];

        return response()->json([
            'success' => true,
            'data' => $response,
            'message' => 'Hiérarchie de l\'entité récupérée avec succès'
        ]);
    }

    /**
     * Affecter un chef à une entité
     */
    public function affecterChef(Request $request, string $id): JsonResponse
    {
        $entite = Entite::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date_debut' => 'required|date',
            'terminer_mandat_precedent' => 'boolean',
        ]);

        $user = User::findOrFail($validated['user_id']);

        // Vérifier si l'entité a déjà un chef actuel
        $chefActuel = $entite->chefs()->whereNull('date_fin')->first();

        if ($chefActuel && !($validated['terminer_mandat_precedent'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'Cette entité a déjà un chef actuel. Utilisez terminer_mandat_precedent=true pour terminer automatiquement le mandat précédent.',
                'chef_actuel' => [
                    'user' => [
                        'id' => $chefActuel->user->id,
                        'nom' => $chefActuel->user->nom,
                        'prenom' => $chefActuel->user->prenom,
                        'matricule' => $chefActuel->user->matricule,
                    ],
                    'date_debut' => $chefActuel->date_debut,
                ]
            ], 422);
        }

        // Terminer le mandat précédent si demandé
        if ($chefActuel && ($validated['terminer_mandat_precedent'] ?? false)) {
            $chefActuel->update([
                'date_fin' => Carbon::parse($validated['date_debut'])->subDay(),
                'date_modification' => Carbon::now(),
                'modifier_par' => 'api_user',
            ]);
        }

        // Créer le nouveau mandat de chef
        $now = Carbon::now();
        $nouveauMandat = \App\Models\EntiteChefHistory::create([
            'entite_id' => $entite->id,
            'user_id' => $user->id,
            'date_debut' => Carbon::parse($validated['date_debut']),
            'date_creation' => $now,
            'date_modification' => $now,
                            'creer_par' => $request->user()->email,
        ]);

        $nouveauMandat->load(['user', 'entite']);

        return response()->json([
            'success' => true,
            'data' => [
                'mandat' => [
                    'id' => $nouveauMandat->id,
                    'user' => [
                        'id' => $user->id,
                        'nom' => $user->nom,
                        'prenom' => $user->prenom,
                        'matricule' => $user->matricule,
                    ],
                    'entite' => [
                        'id' => $entite->id,
                        'nom' => $entite->nom,
                        'type' => $entite->typeEntite->nom,
                    ],
                    'date_debut' => $nouveauMandat->date_debut,
                ],
                'mandat_precedent_termine' => $chefActuel ? true : false,
            ],
            'message' => 'Chef affecté avec succès à l\'entité'
        ], 201);
    }

    /**
     * Terminer le mandat d'un chef d'entité
     */
    public function terminerMandatChef(Request $request, string $id): JsonResponse
    {
        $entite = Entite::findOrFail($id);

        $validated = $request->validate([
            'date_fin' => 'required|date',
            'raison' => 'nullable|string|max:500',
        ]);

        // Trouver le mandat actuel
        $mandatActuel = $entite->chefs()->whereNull('date_fin')->first();

        if (!$mandatActuel) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun chef actuel trouvé pour cette entité'
            ], 404);
        }

        // Vérifier que la date de fin n'est pas antérieure à la date de début
        $dateFin = Carbon::parse($validated['date_fin']);
        if ($dateFin->lt(Carbon::parse($mandatActuel->date_debut))) {
            return response()->json([
                'success' => false,
                'message' => 'La date de fin ne peut pas être antérieure à la date de début du mandat'
            ], 422);
        }

        // Terminer le mandat
        $mandatActuel->update([
            'date_fin' => $dateFin,
            'date_modification' => Carbon::now(),
            'modifier_par' => 'api_user',
        ]);

        $message = 'Mandat de chef terminé avec succès';
        if (!empty($validated['raison'])) {
            $message .= '. Raison: ' . $validated['raison'];
        }

        $mandatActuel->load(['user', 'entite']);

        return response()->json([
            'success' => true,
            'data' => [
                'mandat_termine' => [
                    'id' => $mandatActuel->id,
                    'user' => [
                        'id' => $mandatActuel->user->id,
                        'nom' => $mandatActuel->user->nom,
                        'prenom' => $mandatActuel->user->prenom,
                        'matricule' => $mandatActuel->user->matricule,
                    ],
                    'entite' => [
                        'id' => $entite->id,
                        'nom' => $entite->nom,
                        'type' => $entite->typeEntite->nom,
                    ],
                    'date_debut' => $mandatActuel->date_debut,
                    'date_fin' => $mandatActuel->date_fin,
                    'raison' => $validated['raison'] ?? null,
                ],
            ],
            'message' => $message
        ]);
    }

    /**
     * Historique des chefs d'une entité
     */
    public function historiqueChefs(string $id): JsonResponse
    {
        $entite = Entite::findOrFail($id);

        $historique = $entite->chefs()
                            ->with('user')
                            ->orderBy('date_debut', 'desc')
                            ->get()
                            ->map(function ($mandat) {
                                return [
                                    'id' => $mandat->id,
                                    'user' => [
                                        'id' => $mandat->user->id,
                                        'nom' => $mandat->user->nom,
                                        'prenom' => $mandat->user->prenom,
                                        'matricule' => $mandat->user->matricule,
                                        'email' => $mandat->user->email,
                                    ],
                                    'date_debut' => $mandat->date_debut,
                                    'date_fin' => $mandat->date_fin,
                                    'est_actuel' => $mandat->date_fin === null,
                                                                        'duree_mandat' => $mandat->date_fin
                                        ? (int)Carbon::parse($mandat->date_debut)->diffInDays(Carbon::parse($mandat->date_fin)) . ' jours'
                                        : (int)Carbon::parse($mandat->date_debut)->diffInDays(Carbon::now()) . ' jours (en cours)',
                                    'date_creation' => $mandat->date_creation,
                                ];
                            });

        return response()->json([
            'success' => true,
            'data' => $historique,
            'entite' => [
                'id' => $entite->id,
                'nom' => $entite->nom,
                'type' => $entite->typeEntite->nom,
            ],
            'message' => 'Historique des chefs récupéré avec succès'
        ]);
    }

    /**
     * Lister tous les chefs actuels
     */
    public function chefsActuels(): JsonResponse
    {
        $chefsActuels = \App\Models\EntiteChefHistory::whereNull('date_fin')
                                                    ->with(['user', 'entite.typeEntite'])
                                                    ->orderBy('date_debut', 'desc')
                                                    ->get()
                                                    ->map(function ($mandat) {
                                                        return [
                                                            'id' => $mandat->id,
                                                            'chef' => [
                                                                'id' => $mandat->user->id,
                                                                'nom' => $mandat->user->nom,
                                                                'prenom' => $mandat->user->prenom,
                                                                'matricule' => $mandat->user->matricule,
                                                                'email' => $mandat->user->email,
                                                            ],
                                                            'entite' => [
                                                                'id' => $mandat->entite->id,
                                                                'nom' => $mandat->entite->nom,
                                                                'type' => $mandat->entite->typeEntite->nom,
                                                            ],
                                                            'date_debut' => $mandat->date_debut,
                                                            'duree_mandat' => (int)Carbon::parse($mandat->date_debut)->diffInDays(Carbon::now()) . ' jours',
                                                            'date_creation' => $mandat->date_creation,
                                                        ];
                                                    });

        return response()->json([
            'success' => true,
            'data' => $chefsActuels,
            'statistiques' => [
                'total_chefs_actuels' => $chefsActuels->count(),
                'entites_sans_chef' => Entite::whereDoesntHave('chefs', function ($query) {
                    $query->whereNull('date_fin');
                })->count(),
            ],
            'message' => 'Chefs actuels récupérés avec succès'
        ]);
    }

    /**
     * Organigramme complet de l'organisation
     * Retourne la structure hiérarchique complète avec chefs et effectifs
     */
    public function organigramme(): JsonResponse
    {
        // Récupérer toutes les entités racines (sans parent)
        $entitesRacines = Entite::whereNull('parent_id')
                                ->with(['typeEntite', 'chefs' => function ($query) {
                                    $query->whereNull('date_fin');
                                }, 'chefs.user', 'affectations' => function ($query) {
                                    $query->where('statut', true);
                                }])
                                ->orderBy('nom')
                                ->get();

        $organigramme = $entitesRacines->map(function ($entite) {
            return $this->buildOrganigrammeNode($entite);
        });

        // Statistiques globales
        $statsGlobales = [
            'total_entites' => Entite::count(),
            'total_employes_actifs' => \App\Models\UtilisateurEntiteHistory::where('statut', true)->count(),
            'total_chefs_actuels' => \App\Models\EntiteChefHistory::whereNull('date_fin')->count(),
            'entites_racines' => $entitesRacines->count(),
            'profondeur_max' => $this->getMaxDepth(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'organigramme' => $organigramme,
                'statistiques' => $statsGlobales,
                'metadata' => [
                    'date_generation' => Carbon::now(),
                    'format' => 'hierarchique_complet',
                    'version' => '1.0'
                ]
            ],
            'message' => 'Organigramme généré avec succès'
        ]);
    }

    /**
     * Récupérer tous les utilisateurs d'une entité avec filtres avancés
     */
    public function utilisateurs(Request $request, string $id): JsonResponse
    {
        try {
            $entite = Entite::with(['typeEntite'])->findOrFail($id);

            // Paramètres de filtrage
            $includeHistorique = $request->boolean('include_historique', false);
            $statut = $request->get('statut', 'actuel'); // actuel, historique, tous
            $role = $request->get('role', 'tous'); // chef, employe, tous
            $perPage = $request->get('per_page', null); // Pour pagination optionnelle

            // Chef actuel
            $chefActuel = $entite->chefs()
                ->whereNull('date_fin')
                ->with('user')
                ->first();

            // Historique des chefs (si demandé)
            $historiqueChefs = collect();
            if ($includeHistorique || $statut === 'historique' || $statut === 'tous') {
                $historiqueChefs = $entite->chefs()
                    ->whereNotNull('date_fin')
                    ->with('user')
                    ->orderBy('date_fin', 'desc')
                    ->get()
                    ->map(function ($chef) {
                        return [
                            'user' => [
                                'id' => $chef->user->id,
                                'matricule' => $chef->user->matricule,
                                'nom' => $chef->user->nom,
                                'prenom' => $chef->user->prenom,
                                'email' => $chef->user->email,
                                'telephone' => $chef->user->telephone,
                            ],
                            'type' => 'chef',
                            'date_debut' => $chef->date_debut,
                            'date_fin' => $chef->date_fin,
                            'est_actuel' => false
                        ];
                    });
            }

            // Employés actuels
            $utilisateursActuels = collect();
            if ($statut === 'actuel' || $statut === 'tous') {
                $affectationsActuelles = $entite->affectations()
                    ->where('statut', true)
                    ->with(['user.roles', 'poste'])
                    ->get();

                $utilisateursActuels = $affectationsActuelles->map(function ($affectation) {
                    return [
                        'user' => [
                            'id' => $affectation->user->id,
                            'matricule' => $affectation->user->matricule,
                            'nom' => $affectation->user->nom,
                            'prenom' => $affectation->user->prenom,
                            'email' => $affectation->user->email,
                            'telephone' => $affectation->user->telephone,
                            'statut' => $affectation->user->statut,
                            'roles' => $affectation->user->roles->pluck('nom'),
                        ],
                        'poste' => [
                            'id' => $affectation->poste->id,
                            'nom' => $affectation->poste->nom,
                            'description' => $affectation->poste->description,
                        ],
                        'type' => 'employe',
                        'date_debut' => $affectation->date_debut,
                        'date_fin' => null,
                        'est_actuel' => true
                    ];
                });
            }

            // Historique des employés (si demandé)
            $historiqueUtilisateurs = collect();
            if ($includeHistorique || $statut === 'historique' || $statut === 'tous') {
                $affectationsPassees = $entite->affectations()
                    ->where('statut', false)
                    ->with(['user.roles', 'poste'])
                    ->orderBy('date_fin', 'desc')
                    ->get();

                $historiqueUtilisateurs = $affectationsPassees->map(function ($affectation) {
                    return [
                        'user' => [
                            'id' => $affectation->user->id,
                            'matricule' => $affectation->user->matricule,
                            'nom' => $affectation->user->nom,
                            'prenom' => $affectation->user->prenom,
                            'email' => $affectation->user->email,
                            'telephone' => $affectation->user->telephone,
                            'statut' => $affectation->user->statut,
                            'roles' => $affectation->user->roles->pluck('nom'),
                        ],
                        'poste' => [
                            'id' => $affectation->poste->id,
                            'nom' => $affectation->poste->nom,
                            'description' => $affectation->poste->description,
                        ],
                        'type' => 'employe',
                        'date_debut' => $affectation->date_debut,
                        'date_fin' => $affectation->date_fin,
                        'est_actuel' => false
                    ];
                });
            }

            // Filtrage par rôle
            $utilisateursFiltres = collect();

            if ($role === 'chef' || $role === 'tous') {
                if ($chefActuel && ($statut === 'actuel' || $statut === 'tous')) {
                    $utilisateursFiltres->push([
                        'user' => [
                            'id' => $chefActuel->user->id,
                            'matricule' => $chefActuel->user->matricule,
                            'nom' => $chefActuel->user->nom,
                            'prenom' => $chefActuel->user->prenom,
                            'email' => $chefActuel->user->email,
                            'telephone' => $chefActuel->user->telephone,
                            'statut' => $chefActuel->user->statut,
                            'roles' => $chefActuel->user->roles->pluck('nom') ?? [],
                        ],
                        'poste' => null,
                        'type' => 'chef',
                        'date_debut' => $chefActuel->date_debut,
                        'date_fin' => null,
                        'est_actuel' => true
                    ]);
                }
                if ($statut === 'historique' || $statut === 'tous') {
                    $utilisateursFiltres = $utilisateursFiltres->merge($historiqueChefs);
                }
            }

            if ($role === 'employe' || $role === 'tous') {
                if ($statut === 'actuel' || $statut === 'tous') {
                    $utilisateursFiltres = $utilisateursFiltres->merge($utilisateursActuels);
                }
                if ($statut === 'historique' || $statut === 'tous') {
                    $utilisateursFiltres = $utilisateursFiltres->merge($historiqueUtilisateurs);
                }
            }

            // Tri par nom
            $utilisateursFiltres = $utilisateursFiltres->sortBy(function ($item) {
                return $item['user']['nom'] . ' ' . $item['user']['prenom'];
            })->values();

            // Pagination optionnelle
            $response = [
                'entite' => [
                    'id' => $entite->id,
                    'nom' => $entite->nom,
                    'description' => $entite->description,
                    'type_entite' => $entite->typeEntite->nom,
                ],
                'filtres_appliques' => [
                    'statut' => $statut,
                    'role' => $role,
                    'include_historique' => $includeHistorique,
                ],
                'chef_actuel' => $chefActuel ? [
                    'user' => [
                        'id' => $chefActuel->user->id,
                        'matricule' => $chefActuel->user->matricule,
                        'nom' => $chefActuel->user->nom,
                        'prenom' => $chefActuel->user->prenom,
                        'email' => $chefActuel->user->email,
                    ],
                    'date_debut' => $chefActuel->date_debut,
                ] : null,
                'utilisateurs' => $utilisateursFiltres,
                'statistiques' => [
                    'total_utilisateurs' => $utilisateursFiltres->count(),
                    'employes_actuels' => $utilisateursActuels->count(),
                    'employes_historique' => $historiqueUtilisateurs->count(),
                    'chefs_historique' => $historiqueChefs->count(),
                    'a_chef_actuel' => $chefActuel !== null,
                ],
                'repartition' => [
                    'chefs' => $utilisateursFiltres->where('type', 'chef')->count(),
                    'employes' => $utilisateursFiltres->where('type', 'employe')->count(),
                    'actuels' => $utilisateursFiltres->where('est_actuel', true)->count(),
                    'historique' => $utilisateursFiltres->where('est_actuel', false)->count(),
                ],
            ];

            // Pagination si demandée
            if ($perPage) {
                $page = $request->get('page', 1);
                $utilisateursPage = $utilisateursFiltres->forPage($page, $perPage);
                $response['utilisateurs'] = $utilisateursPage->values();
                $response['pagination'] = [
                    'current_page' => (int) $page,
                    'per_page' => (int) $perPage,
                    'total' => $utilisateursFiltres->count(),
                    'last_page' => ceil($utilisateursFiltres->count() / $perPage),
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Utilisateurs de l\'entité récupérés avec succès',
                'data' => $response
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
     * Helper method to check if setting a parent would create a cycle
     */
    private function wouldCreateCycle(int $entityId, int $parentId): bool
    {
        $current = Entite::find($parentId);

        while ($current) {
            if ($current->id == $entityId) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }

    /**
     * Construire récursivement un nœud de l'organigramme
     */
    private function buildOrganigrammeNode(Entite $entite): array
    {
        // Charger les relations nécessaires si pas déjà chargées
        $entite->load([
            'typeEntite',
            'chefs' => function ($query) {
                $query->whereNull('date_fin');
            },
            'chefs.user',
            'affectations' => function ($query) {
                $query->where('statut', true);
            },
            'affectations.user',
            'affectations.poste',
            'enfants.typeEntite'
        ]);

        // Chef actuel
        $chefActuel = $entite->chefs->first();

        // Nombre d'employés actuels
        $nombreEmployes = $entite->affectations->count();

        // Construire les enfants récursivement
        $enfants = $entite->enfants->map(function ($enfant) {
            return $this->buildOrganigrammeNode($enfant);
        })->sortBy('nom')->values();

        return [
            'id' => $entite->id,
            'nom' => $entite->nom,
            'description' => $entite->description,
            'type_entite' => [
                'id' => $entite->typeEntite->id,
                'nom' => $entite->typeEntite->nom,
                'description' => $entite->typeEntite->description,
            ],
            'chef_actuel' => $chefActuel ? [
                'id' => $chefActuel->user->id,
                'nom' => $chefActuel->user->nom,
                'prenom' => $chefActuel->user->prenom,
                'matricule' => $chefActuel->user->matricule,
                'email' => $chefActuel->user->email,
                'date_debut_mandat' => $chefActuel->date_debut,
                'duree_mandat_jours' => (int)Carbon::parse($chefActuel->date_debut)->diffInDays(Carbon::now()),
            ] : null,
            'effectifs' => [
                'nombre_employes' => $nombreEmployes,
                'employes' => $entite->affectations->map(function ($affectation) {
                    return [
                        'id' => $affectation->user->id,
                        'nom' => $affectation->user->nom,
                        'prenom' => $affectation->user->prenom,
                        'matricule' => $affectation->user->matricule,
                        'poste' => $affectation->poste->nom ?? 'Non défini',
                        'date_debut' => $affectation->date_debut,
                    ];
                }),
            ],
            'statistiques' => [
                'nombre_enfants_directs' => $entite->enfants->count(),
                'nombre_total_descendants' => $this->countTotalDescendants($entite),
                'a_chef' => $chefActuel !== null,
                'niveau_hierarchique' => $this->getEntityLevel($entite),
            ],
            'enfants' => $enfants,
            'metadata' => [
                'date_creation' => $entite->date_creation,
                'creer_par' => $entite->creer_par,
            ]
        ];
    }

    /**
     * Calculer la profondeur maximale de la hiérarchie
     */
    private function getMaxDepth(): int
    {
        $entitesRacines = Entite::whereNull('parent_id')->get();
        $maxDepth = 0;

        foreach ($entitesRacines as $racine) {
            $depth = $this->getEntityDepth($racine);
            $maxDepth = max($maxDepth, $depth);
        }

        return $maxDepth;
    }

    /**
     * Calculer la profondeur d'une entité spécifique
     */
    private function getEntityDepth(Entite $entite): int
    {
        $enfants = $entite->enfants;

        if ($enfants->isEmpty()) {
            return 1;
        }

        $maxChildDepth = 0;
        foreach ($enfants as $enfant) {
            $childDepth = $this->getEntityDepth($enfant);
            $maxChildDepth = max($maxChildDepth, $childDepth);
        }

        return 1 + $maxChildDepth;
    }

    /**
     * Compter le nombre total de descendants d'une entité
     */
    private function countTotalDescendants(Entite $entite): int
    {
        $count = $entite->enfants->count();

        foreach ($entite->enfants as $enfant) {
            $count += $this->countTotalDescendants($enfant);
        }

        return $count;
    }

    /**
     * Obtenir le niveau hiérarchique d'une entité (distance de la racine)
     */
    private function getEntityLevel(Entite $entite): int
    {
        $level = 1;
        $current = $entite;

        while ($current->parent) {
            $level++;
            $current = $current->parent;
        }

        return $level;
    }

    /**
     * Helper method to get parents hierarchy
     */
    private function getParentsHierarchy(Entite $entite): array
    {
        $parents = [];
        $current = $entite->parent;

        while ($current) {
            $parents[] = [
                'id' => $current->id,
                'nom' => $current->nom,
                'type_entite' => $current->typeEntite->nom,
            ];
            $current = $current->parent;
        }

        return array_reverse($parents); // Pour avoir la hiérarchie du plus haut au plus bas
    }

    /**
     * Helper method to get children hierarchy
     */
    private function getChildrenHierarchy(Entite $entite): array
    {
        return $entite->enfants->map(function ($enfant) {
            return [
                'id' => $enfant->id,
                'nom' => $enfant->nom,
                'type_entite' => $enfant->typeEntite->nom,
                'enfants' => $this->getChildrenHierarchy($enfant),
            ];
        })->toArray();
    }
}
