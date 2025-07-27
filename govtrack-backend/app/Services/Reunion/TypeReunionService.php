<?php

namespace App\Services\Reunion;

use App\Models\TypeReunion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TypeReunionService
{
    /**
     * Récupérer la liste des types de réunions
     */
    public function getTypeReunions(Request $request, User $user): array
    {
        try {
            $query = TypeReunion::with([
                'gestionnaires',
                'membresPermanents',
                'series',
                'workflowConfigs',
                'validateursPV',
                'notificationConfigs',
                'createur',
                'modificateur'
            ]);

            // ========================================
            // SYSTÈME DE PERMISSIONS POUR L'AFFICHAGE DES TYPES
            // ========================================

            if ($user->hasPermission('view_all_reunion_types')) {
                // 🔓 NIVEAU 1 : Accès complet à tous les types
                // Aucune restriction sur la requête

            } elseif ($user->hasPermission('view_my_entity_reunion_types')) {
                // 🏢 NIVEAU 2 : Types de son entité ET entités enfants
                $affectationActuelle = $user->affectations()->where('statut', true)->first();

                if ($affectationActuelle) {
                    $entiteId = $affectationActuelle->service_id;
                    $entitesIds = $this->getEntitesEnfantsRecursives($entiteId);
                    $utilisateursEntite = \App\Models\UtilisateurEntiteHistory::whereIn('service_id', $entitesIds)
                        ->distinct()
                        ->pluck('user_id');

                    // Filtrer les types où l'utilisateur est impliqué
                    $query->where(function ($q) use ($utilisateursEntite, $user) {
                        $q->whereIn('creer_par', $utilisateursEntite)
                          ->orWhereIn('modifier_par', $utilisateursEntite)
                          ->orWhereHas('gestionnaires', function ($gq) use ($utilisateursEntite) {
                              $gq->whereIn('user_id', $utilisateursEntite);
                          })
                          ->orWhereHas('membresPermanents', function ($mq) use ($utilisateursEntite) {
                              $mq->whereIn('user_id', $utilisateursEntite);
                          });
                    });
                } else {
                    // Fallback vers ses types personnels
                    $query->where(function ($q) use ($user) {
                        $q->where('creer_par', $user->id)
                          ->orWhere('modifier_par', $user->id)
                          ->orWhereHas('gestionnaires', function ($gq) use ($user) {
                              $gq->where('user_id', $user->id);
                          })
                          ->orWhereHas('membresPermanents', function ($mq) use ($user) {
                              $mq->where('user_id', $user->id);
                          });
                    });
                }

            } else {
                // 🔒 NIVEAU 3 : Types personnels uniquement
                $query->where(function ($q) use ($user) {
                    $q->where('creer_par', $user->id)
                      ->orWhere('modifier_par', $user->id)
                      ->orWhereHas('gestionnaires', function ($gq) use ($user) {
                          $gq->where('user_id', $user->id);
                      })
                      ->orWhereHas('membresPermanents', function ($mq) use ($user) {
                          $mq->where('user_id', $user->id);
                      });
                });
            }

            // ========================================
            // FILTRES APPLIQUÉS PAR L'UTILISATEUR
            // ========================================

            // Filtre par statut
            if ($request->filled('actif')) {
                $query->where('actif', $request->boolean('actif'));
            }

            // Filtre par niveau de complexité
            if ($request->filled('niveau_complexite')) {
                $query->where('niveau_complexite', $request->niveau_complexite);
            }

            // Filtre par catégorie
            if ($request->filled('categorie')) {
                $query->where('categorie', $request->categorie);
            }

            // Filtre par créateur
            if ($request->filled('creer_par')) {
                $query->where('creer_par', $request->creer_par);
            }

            // Filtre par recherche textuelle
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            // Tri
            $sortBy = $request->get('sort_by', 'nom');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $types = $query->paginate($perPage);

            return [
                'success' => true,
                'data' => $types,
                'message' => 'Types de réunions récupérés avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des types de réunions', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des types de réunions',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer un type de réunion spécifique
     */
    public function getTypeReunion(int $id, User $user): array
    {
        try {
            $typeReunion = TypeReunion::with([
                'gestionnaires',
                'membresPermanents',
                'series',
                'workflowConfigs',
                'validateursPV',
                'notificationConfigs',
                'createur',
                'modificateur'
            ])->find($id);

            if (!$typeReunion) {
                return [
                    'success' => false,
                    'message' => 'Type de réunion non trouvé'
                ];
            }

            // Vérifier les permissions d'accès
            if (!$this->canAccessTypeReunion($typeReunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour accéder à ce type de réunion'
                ];
            }

            return [
                'success' => true,
                'data' => $typeReunion,
                'message' => 'Type de réunion récupéré avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du type de réunion', [
                'type_reunion_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération du type de réunion',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Créer un nouveau type de réunion
     */
    public function createTypeReunion(array $data, User $user): array
    {
        try {
            DB::beginTransaction();

            // Vérifier les permissions de création
            if (!$this->canCreateTypeReunion($user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour créer un type de réunion'
                ];
            }

            // Préparer les données selon la migration
            $typeReunionData = [
                'nom' => $data['nom'],
                'description' => $data['description'] ?? '',
                'couleur' => $data['couleur'] ?? '#1f2937',
                'icone' => $data['icone'] ?? 'users',
                'actif' => $data['actif'] ?? true,
                'ordre' => $data['ordre'] ?? 1,
                'niveau_complexite' => $data['niveau_complexite'] ?? 'SIMPLE',
                'fonctionnalites_actives' => $data['fonctionnalites_actives'] ?? [],
                'configuration_notifications' => $data['configuration_notifications'] ?? [],
                'creer_par' => $data['creer_par'] ?? $user->id,
                'modifier_par' => $data['modifier_par'] ?? $user->id,
            ];

            $typeReunion = TypeReunion::create($typeReunionData);

            // Ajouter les gestionnaires si fournis
            if (isset($data['gestionnaires']) && is_array($data['gestionnaires'])) {
                $pivotData = [];
                foreach ($data['gestionnaires'] as $gestionnaireId) {
                    $pivotData[$gestionnaireId] = [
                        'permissions' => json_encode(['manage_type_reunion']),
                        'actif' => true,
                        'date_creation' => now(),
                        'date_modification' => now(),
                        'creer_par' => $user->id,
                        'modifier_par' => $user->id
                    ];
                }
                $typeReunion->gestionnaires()->attach($pivotData);
            }

            // Ajouter les membres si fournis
            if (isset($data['membres']) && is_array($data['membres'])) {
                $pivotData = [];
                foreach ($data['membres'] as $membreId) {
                    $pivotData[$membreId] = [
                        'role_defaut' => 'MEMBRE',
                        'actif' => true,
                        'notifications_par_defaut' => json_encode(['rappel_24h', 'rappel_1h']),
                        'date_creation' => now(),
                        'date_modification' => now(),
                        'creer_par' => $user->id,
                        'modifier_par' => $user->id
                    ];
                }
                $typeReunion->membresPermanents()->attach($pivotData);
            }

            // Ajouter les validateurs PV si fournis
            if (isset($data['validateurs_pv']) && is_array($data['validateurs_pv'])) {
                foreach ($data['validateurs_pv'] as $validateurId) {
                    \App\Models\TypeReunionValidateurPV::create([
                        'type_reunion_id' => $typeReunion->id,
                        'user_id' => $validateurId,
                        'niveau_validation' => 'FINAL',
                        'ordre_validation' => 1,
                        'actif' => true,
                        'creer_par' => $user->id,
                        'modifier_par' => $user->id
                    ]);
                }
            }

            DB::commit();

            // Charger les relations pour la réponse
            $typeReunion->load([
                'gestionnaires',
                'membresPermanents',
                'validateursPV',
                'createur'
            ]);

            return [
                'success' => true,
                'data' => $typeReunion,
                'message' => 'Type de réunion créé avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du type de réunion', [
                'user_id' => $user->id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la création du type de réunion',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour un type de réunion
     */
    public function updateTypeReunion(int $id, array $data, User $user): array
    {
        try {
            DB::beginTransaction();

            $typeReunion = TypeReunion::find($id);
            if (!$typeReunion) {
                return [
                    'success' => false,
                    'message' => 'Type de réunion non trouvé'
                ];
            }

            // Vérifier les permissions de modification
            if (!$this->canUpdateTypeReunion($typeReunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier ce type de réunion'
                ];
            }

            // Préparer les données de mise à jour
            $updateData = array_filter([
                'nom' => $data['nom'] ?? null,
                'description' => $data['description'] ?? null,
                'categorie' => $data['categorie'] ?? null,
                'niveau_complexite' => $data['niveau_complexite'] ?? null,
                'actif' => $data['actif'] ?? null,
                'configuration' => $data['configuration'] ?? null,
                'regles_metier' => $data['regles_metier'] ?? null,
                'workflow_defaut' => $data['workflow_defaut'] ?? null,
                'notifications_defaut' => $data['notifications_defaut'] ?? null,
                'permissions_requises' => $data['permissions_requises'] ?? null,
                'modifier_par' => $user->id,
                'date_modification' => now(),
            ], function ($value) {
                return $value !== null;
            });

            $typeReunion->update($updateData);

            // Mettre à jour les gestionnaires si fournis
            if (isset($data['gestionnaires'])) {
                $typeReunion->gestionnaires()->sync($data['gestionnaires']);
            }

            // Mettre à jour les membres si fournis
            if (isset($data['membres'])) {
                $pivotData = [];
                foreach ($data['membres'] as $membreId) {
                    $pivotData[$membreId] = [
                        'role_defaut' => 'MEMBRE',
                        'actif' => true,
                        'notifications_par_defaut' => json_encode(['rappel_24h', 'rappel_1h']),
                        'date_creation' => now(),
                        'date_modification' => now(),
                        'creer_par' => $user->id,
                        'modifier_par' => $user->id
                    ];
                }
                $typeReunion->membresPermanents()->sync($pivotData);
            }

            // Mettre à jour les validateurs PV si fournis
            if (isset($data['validateurs_pv'])) {
                $typeReunion->validateursPV()->sync($data['validateurs_pv']);
            }

            DB::commit();

            // Charger les relations pour la réponse
            $typeReunion->load([
                'gestionnaires',
                'membresPermanents',
                'validateursPV',
                'modificateur'
            ]);

            return [
                'success' => true,
                'data' => $typeReunion,
                'message' => 'Type de réunion mis à jour avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du type de réunion', [
                'type_reunion_id' => $id,
                'user_id' => $user->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du type de réunion',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer un type de réunion
     */
    public function deleteTypeReunion(int $id, User $user): array
    {
        try {
            $typeReunion = TypeReunion::find($id);
            if (!$typeReunion) {
                return [
                    'success' => false,
                    'message' => 'Type de réunion non trouvé'
                ];
            }

            // Vérifier les permissions de suppression
            if (!$this->canDeleteTypeReunion($typeReunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour supprimer ce type de réunion'
                ];
            }

            // Vérifier si le type peut être supprimé
            if ($typeReunion->reunions()->exists()) {
                return [
                    'success' => false,
                    'message' => 'Impossible de supprimer un type de réunion utilisé par des réunions'
                ];
            }

            DB::beginTransaction();

            // Supprimer les relations
            $typeReunion->gestionnaires()->detach();
            $typeReunion->membresPermanents()->detach();
            $typeReunion->validateursPV()->delete();
            $typeReunion->workflowConfigs()->delete();
            $typeReunion->notificationConfigs()->delete();

            // Supprimer le type
            $typeReunion->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Type de réunion supprimé avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du type de réunion', [
                'type_reunion_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression du type de réunion',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer les types de réunions actifs pour un utilisateur
     */
    public function getActiveTypeReunions(User $user): array
    {
        try {
            $query = TypeReunion::where('actif', true);

            // Appliquer les filtres de permissions
            if (!$user->hasPermission('view_all_reunion_types')) {
                if ($user->hasPermission('view_my_entity_reunion_types')) {
                    $affectationActuelle = $user->affectations()->where('statut', true)->first();
                    if ($affectationActuelle) {
                        $entiteId = $affectationActuelle->service_id;
                        $entitesIds = $this->getEntitesEnfantsRecursives($entiteId);
                        $utilisateursEntite = \App\Models\UtilisateurEntiteHistory::whereIn('service_id', $entitesIds)
                            ->distinct()
                            ->pluck('user_id');

                        $query->where(function ($q) use ($utilisateursEntite, $user) {
                            $q->whereIn('creer_par', $utilisateursEntite)
                              ->orWhereIn('modifier_par', $utilisateursEntite)
                              ->orWhereHas('gestionnaires', function ($gq) use ($utilisateursEntite) {
                                  $gq->whereIn('user_id', $utilisateursEntite);
                              })
                              ->orWhereHas('membresPermanents', function ($mq) use ($utilisateursEntite) {
                                  $mq->whereIn('user_id', $utilisateursEntite);
                              });
                        });
                    } else {
                        $query->where(function ($q) use ($user) {
                            $q->where('creer_par', $user->id)
                              ->orWhere('modifier_par', $user->id)
                              ->orWhereHas('gestionnaires', function ($gq) use ($user) {
                                  $gq->where('user_id', $user->id);
                              })
                              ->orWhereHas('membresPermanents', function ($mq) use ($user) {
                                  $mq->where('user_id', $user->id);
                              });
                        });
                    }
                } else {
                    $query->where(function ($q) use ($user) {
                        $q->where('creer_par', $user->id)
                          ->orWhere('modifier_par', $user->id)
                          ->orWhereHas('gestionnaires', function ($gq) use ($user) {
                              $gq->where('user_id', $user->id);
                          })
                          ->orWhereHas('membresPermanents', function ($mq) use ($user) {
                              $mq->where('user_id', $user->id);
                          });
                    });
                }
            }

            $types = $query->orderBy('nom')->get();

            return [
                'success' => true,
                'data' => $types,
                'message' => 'Types de réunions actifs récupérés avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des types de réunions actifs', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des types de réunions actifs',
                'error' => $e->getMessage()
            ];
        }
    }

    // ========================================
    // MÉTHODES PRIVÉES UTILITAIRES
    // ========================================

    /**
     * Récupérer récursivement toutes les entités enfants
     */
    private function getEntitesEnfantsRecursives(int $entiteId): array
    {
        $entitesIds = [$entiteId];

        $entite = \App\Models\Entite::find($entiteId);
        if (!$entite) {
            return $entitesIds;
        }

        $this->getEnfantsRecursifs($entite, $entitesIds);

        return array_unique($entitesIds);
    }

    /**
     * Méthode récursive pour récupérer tous les enfants d'une entité
     */
    private function getEnfantsRecursifs(\App\Models\Entite $entite, array &$entitesIds): void
    {
        $enfants = $entite->enfants;

        foreach ($enfants as $enfant) {
            $entitesIds[] = $enfant->id;
            $this->getEnfantsRecursifs($enfant, $entitesIds);
        }
    }

    /**
     * Vérifier si l'utilisateur peut accéder au type de réunion
     */
    private function canAccessTypeReunion(TypeReunion $typeReunion, User $user): bool
    {
        // L'utilisateur peut toujours accéder aux types qu'il a créés
        if ($typeReunion->creer_par === $user->id) {
            return true;
        }

        // L'utilisateur peut accéder aux types où il est gestionnaire
        if ($typeReunion->gestionnaires()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // L'utilisateur peut accéder aux types où il est membre
        if ($typeReunion->membresPermanents()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Vérifier les permissions globales
        if ($user->hasPermission('view_all_reunion_types')) {
            return true;
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut créer un type de réunion
     */
    private function canCreateTypeReunion(User $user): bool
    {
        return $user->hasPermission('create_reunion_types');
    }

    /**
     * Vérifier si l'utilisateur peut modifier un type de réunion
     */
    private function canUpdateTypeReunion(TypeReunion $typeReunion, User $user): bool
    {
        // L'utilisateur peut toujours modifier les types qu'il a créés
        if ($typeReunion->creer_par === $user->id) {
            return true;
        }

        // L'utilisateur peut modifier les types où il est gestionnaire
        if ($typeReunion->gestionnaires()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Vérifier les permissions globales
        return $user->hasPermission('update_reunion_types');
    }

    /**
     * Vérifier si l'utilisateur peut supprimer un type de réunion
     */
    private function canDeleteTypeReunion(TypeReunion $typeReunion, User $user): bool
    {
        // L'utilisateur peut toujours supprimer les types qu'il a créés
        if ($typeReunion->creer_par === $user->id) {
            return true;
        }

        // L'utilisateur peut supprimer les types où il est gestionnaire
        if ($typeReunion->gestionnaires()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Vérifier les permissions globales
        return $user->hasPermission('delete_reunion_types');
    }
}
