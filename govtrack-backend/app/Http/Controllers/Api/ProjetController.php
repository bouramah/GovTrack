<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Projet;
use App\Models\TypeProjet;
use App\Models\User;
use App\Models\PieceJointeProjet;
use App\Models\Entite;
use App\Events\ProjetCreated;
use App\Events\ProjetStatusChanged;
use App\Events\ProjetExecutionLevelUpdated;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ProjetController extends Controller
{
    /**
     * Récupérer récursivement toutes les entités enfants d'une entité donnée
     */
    private function getEntitesEnfantsRecursives(int $entiteId): array
    {
        $entitesIds = [$entiteId];

        $entite = Entite::find($entiteId);
        if (!$entite) {
            return $entitesIds;
        }

        // Récupérer récursivement tous les enfants
        $this->getEnfantsRecursifs($entite, $entitesIds);

        return array_unique($entitesIds);
    }

    /**
     * Méthode récursive pour récupérer tous les enfants d'une entité
     */
    private function getEnfantsRecursifs(Entite $entite, array &$entitesIds): void
    {
        $enfants = $entite->enfants;

        foreach ($enfants as $enfant) {
            $entitesIds[] = $enfant->id;
            $this->getEnfantsRecursifs($enfant, $entitesIds);
        }
    }

    /**
     * Afficher la liste des projets
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = Projet::with(['typeProjet', 'porteur', 'donneurOrdre', 'porteurs']);

            // ========================================
            // SYSTÈME DE PERMISSIONS POUR L'AFFICHAGE DES PROJETS
            // ========================================

            if ($user->hasPermission('view_all_projects')) {
                // 🔓 NIVEAU 1 : VIEW ALL PROJECTS - Accès complet à tous les projets
                // L'utilisateur peut voir tous les projets et utiliser tous les filtres
                // Aucune restriction sur la requête

            } elseif ($user->hasPermission('view_my_entity_projects')) {
                // 🏢 NIVEAU 2 : VIEW MY ENTITY PROJECTS - Projets de son entité ET entités enfants
                $affectationActuelle = $user->affectations()->where('statut', true)->first();

                if ($affectationActuelle) {
                    $entiteId = $affectationActuelle->service_id;

                    // Récupérer récursivement toutes les entités (actuelle + enfants)
                    $entitesIds = $this->getEntitesEnfantsRecursives($entiteId);

                    // Récupérer tous les utilisateurs de ces entités (actuels et passés)
                    $utilisateursEntite = \App\Models\UtilisateurEntiteHistory::whereIn('service_id', $entitesIds)
                        ->distinct()
                        ->pluck('user_id');

                    // Filtrer les projets où porteur ou donneur d'ordre fait partie de l'entité ou ses enfants
                    // OU projets ayant des tâches assignées à des membres de l'entité ou ses enfants
                    $query->where(function ($q) use ($utilisateursEntite) {
                        $q->whereIn('porteur_id', $utilisateursEntite)
                          ->orWhereIn('donneur_ordre_id', $utilisateursEntite)
                          ->orWhereHas('taches', function ($tq) use ($utilisateursEntite) {
                              $tq->whereIn('responsable_id', $utilisateursEntite);
                          });
                    });
                } else {
                    // Si pas d'affectation d'entité, fallback vers ses projets personnels
                    $query->where(function ($q) use ($user) {
                        $q->where('porteur_id', $user->id)
                          ->orWhere('donneur_ordre_id', $user->id)
                          ->orWhereHas('taches', function ($tq) use ($user) {
                              $tq->where('responsable_id', $user->id);
                          });
                    });
                }

            } elseif ($user->hasPermission('view_my_projects')) {
                // 👤 NIVEAU 3 : VIEW MY PROJECTS - Seulement ses projets
                // Projets où l'utilisateur est porteur, donneur d'ordre, ou a des tâches
                $query->where(function ($q) use ($user) {
                    $q->where('porteur_id', $user->id)
                      ->orWhere('donneur_ordre_id', $user->id)
                      ->orWhereHas('taches', function ($tq) use ($user) {
                          $tq->where('responsable_id', $user->id);
                      });
                });

            } else {
                // ❌ AUCUNE PERMISSION - Accès refusé
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions nécessaires pour consulter les projets',
                    'permissions_required' => [
                        'view_my_projects' => 'Voir mes projets personnels',
                        'view_my_entity_projects' => 'Voir les projets de mon entité',
                        'view_all_projects' => 'Voir tous les projets (administrateur)'
                    ]
                ], 403);
            }

            // ========================================
            // FILTRES AVANCÉS SELON LES PERMISSIONS
            // ========================================

            // Filtres de base (disponibles pour tous)
            if ($request->filled('statut')) {
                $query->byStatut($request->statut);
            }

            if ($request->filled('type_projet_id')) {
                $query->where('type_projet_id', $request->type_projet_id);
            }

            if ($request->filled('en_retard') && $request->boolean('en_retard')) {
                $query->enRetard();
            }

            if ($request->filled('niveau_execution_min')) {
                $query->where('niveau_execution', '>=', $request->niveau_execution_min);
            }

            if ($request->filled('niveau_execution_max')) {
                $query->where('niveau_execution', '<=', $request->niveau_execution_max);
            }

            // Filtres de date
            if ($request->filled('date_debut_previsionnelle_debut')) {
                $query->where('date_debut_previsionnelle', '>=', $request->date_debut_previsionnelle_debut);
            }

            if ($request->filled('date_debut_previsionnelle_fin')) {
                $query->where('date_debut_previsionnelle', '<=', $request->date_debut_previsionnelle_fin);
            }

            if ($request->filled('date_fin_previsionnelle_debut')) {
                $query->where('date_fin_previsionnelle', '>=', $request->date_fin_previsionnelle_debut);
            }

            if ($request->filled('date_fin_previsionnelle_fin')) {
                $query->where('date_fin_previsionnelle', '<=', $request->date_fin_previsionnelle_fin);
            }

            if ($request->filled('date_creation_debut')) {
                $query->where('created_at', '>=', $request->date_creation_debut);
            }

            if ($request->filled('date_creation_fin')) {
                $query->where('created_at', '<=', $request->date_creation_fin);
            }

            // Filtres par utilisateur (selon les permissions)
            if ($request->filled('porteur_id')) {
                // Restriction : seulement si l'utilisateur a view_all_projects ou view_my_entity_projects
                if ($user->hasPermission('view_all_projects') || $user->hasPermission('view_my_entity_projects')) {
                    $query->byPorteur($request->porteur_id);
                }
            }

            if ($request->filled('donneur_ordre_id')) {
                // Restriction : seulement si l'utilisateur a view_all_projects ou view_my_entity_projects
                if ($user->hasPermission('view_all_projects') || $user->hasPermission('view_my_entity_projects')) {
                    $query->byDonneurOrdre($request->donneur_ordre_id);
                }
            }

            // Filtre par entité (seulement pour view_all_projects)
            if ($request->filled('entite_id') && $user->hasPermission('view_all_projects')) {
                $entiteId = $request->entite_id;

                // Récupérer tous les utilisateurs de cette entité
                $utilisateursEntite = \App\Models\UtilisateurEntiteHistory::where('service_id', $entiteId)
                    ->distinct()
                    ->pluck('user_id');

                // Filtrer les projets où porteur ou donneur d'ordre fait partie de l'entité
                $query->where(function ($q) use ($utilisateursEntite) {
                    $q->whereIn('porteur_id', $utilisateursEntite)
                      ->orWhereIn('donneur_ordre_id', $utilisateursEntite)
                      ->orWhereHas('taches', function ($tq) use ($utilisateursEntite) {
                          $tq->whereIn('responsable_id', $utilisateursEntite);
                      });
                });
            }

            // Recherche textuelle
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('titre', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('porteur', function ($pq) use ($search) {
                          $pq->where('nom', 'like', "%{$search}%")
                             ->orWhere('prenom', 'like', "%{$search}%");
                      })
                      ->orWhereHas('donneurOrdre', function ($dq) use ($search) {
                          $dq->where('nom', 'like', "%{$search}%")
                             ->orWhere('prenom', 'like', "%{$search}%");
                      });
                });
            }

            // Tri
            $sortBy = $request->get('sort_by', 'date_creation');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $projets = $query->paginate($perPage);

            // Ajouter des informations calculées
            $projets->getCollection()->transform(function ($projet) {
                $projet->statut_libelle = $projet->statut_libelle;
                $projet->est_en_retard = $projet->est_en_retard;
                $projet->taches_count = $projet->taches()->count();
                return $projet;
            });

            // Ajouter les informations de permissions dans la réponse
            $permissionsInfo = [
                'level' => $user->hasPermission('view_all_projects') ? 'all_projects' :
                          ($user->hasPermission('view_my_entity_projects') ? 'entity_projects' : 'my_projects'),
                'can_filter_by_user' => $user->hasPermission('view_all_projects') || $user->hasPermission('view_my_entity_projects'),
                'can_filter_by_entity' => $user->hasPermission('view_all_projects'),
                'can_filter_by_date' => true, // Tous les utilisateurs peuvent filtrer par date
                'available_filters' => [
                    'basic' => ['statut', 'type_projet_id', 'en_retard', 'niveau_execution_min', 'niveau_execution_max', 'search'],
                    'date' => [
                        'date_debut_previsionnelle_debut', 'date_debut_previsionnelle_fin',
                        'date_fin_previsionnelle_debut', 'date_fin_previsionnelle_fin',
                        'date_creation_debut', 'date_creation_fin'
                    ],
                    'user' => $user->hasPermission('view_all_projects') || $user->hasPermission('view_my_entity_projects')
                        ? ['porteur_id', 'donneur_ordre_id'] : [],
                    'entity' => $user->hasPermission('view_all_projects') ? ['entite_id'] : []
                ],
                'description' => $user->hasPermission('view_all_projects') ? 'Accès complet à toutes les instructions' :
                               ($user->hasPermission('view_my_entity_projects') ? 'Instructions de votre entité et entités enfants' : 'Vos instructions personnelles')
            ];

            return response()->json([
                'success' => true,
                'data' => $projets->items(),
                'pagination' => [
                    'current_page' => $projets->currentPage(),
                    'last_page' => $projets->lastPage(),
                    'per_page' => $projets->perPage(),
                    'total' => $projets->total(),
                ],
                'permissions' => $permissionsInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des projets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouveau projet
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'required|string',
                'type_projet_id' => 'required|exists:type_projets,id',
                'porteur_ids' => 'required|array|min:1',
                'porteur_ids.*' => 'exists:users,id',
                'donneur_ordre_id' => 'required|exists:users,id',
                'date_debut_previsionnelle' => 'required|date|after_or_equal:today',
                'date_fin_previsionnelle' => 'nullable|date|after:date_debut_previsionnelle',
                'justification_modification_dates' => 'nullable|string',
            ]);

            // Calcul automatique de la date de fin si non fournie (SLA)
            if (empty($validated['date_fin_previsionnelle'])) {
                $typeProjet = TypeProjet::findOrFail($validated['type_projet_id']);
                $dateDebut = Carbon::parse($validated['date_debut_previsionnelle']);
                $validated['date_fin_previsionnelle'] = $dateDebut->addDays($typeProjet->duree_previsionnelle_jours)->format('Y-m-d');
            } else {
                // Si une date de fin personnalisée est fournie, vérifier la justification
                $typeProjet = TypeProjet::findOrFail($validated['type_projet_id']);
                $dateFinSLA = Carbon::parse($validated['date_debut_previsionnelle'])
                    ->addDays($typeProjet->duree_previsionnelle_jours);

                if (Carbon::parse($validated['date_fin_previsionnelle'])->ne($dateFinSLA)) {
                    if (empty($validated['justification_modification_dates'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Justification requise pour modifier les dates par rapport au SLA'
                        ], 422);
                    }
                }
            }

            // Créer le projet sans porteur principal (utilisation uniquement des tables pivot)
            $projet = Projet::create([
                'titre' => $validated['titre'],
                'description' => $validated['description'],
                'type_projet_id' => $validated['type_projet_id'],
                'donneur_ordre_id' => $validated['donneur_ordre_id'],
                'statut' => Projet::STATUT_A_FAIRE,
                'niveau_execution' => 0,
                'date_debut_previsionnelle' => $validated['date_debut_previsionnelle'],
                'date_fin_previsionnelle' => $validated['date_fin_previsionnelle'],
                'justification_modification_dates' => $validated['justification_modification_dates'] ?? null,
                'date_creation' => now(),
                'date_modification' => now(),
                'creer_par' => $request->user()->email,
                'modifier_par' => $request->user()->email,
            ]);

            // Assigner tous les porteurs
            foreach ($validated['porteur_ids'] as $porteurId) {
                $projet->porteurs()->attach($porteurId, [
                    'date_assignation' => now(),
                    'statut' => true
                ]);
            }

            // Créer l'historique initial
            $projet->historiqueStatuts()->create([
                'ancien_statut' => null,
                'nouveau_statut' => Projet::STATUT_A_FAIRE,
                'user_id' => $request->user()->id,
                'commentaire' => 'Création du projet',
                'date_changement' => now(),
            ]);

            $projet->load(['typeProjet', 'porteur', 'donneurOrdre']);

            // Déclencher l'événement de création de projet
            event(new ProjetCreated($projet, $request->user()));

            return response()->json([
                'success' => true,
                'message' => 'Projet créé avec succès',
                'data' => $projet
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
                'message' => 'Erreur lors de la création du projet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un projet spécifique
     */
    public function show(int $id): JsonResponse
    {
        try {
            $projet = Projet::with([
                'typeProjet',
                'porteurs',
                'donneurOrdre',
                'taches.responsables',
                'historiqueStatuts.user',
                'piecesJointes.user',
                'discussions.user'
            ])->findOrFail($id);

            // Ajouter des informations calculées
            $projet->statut_libelle = $projet->statut_libelle;
            $projet->est_en_retard = $projet->est_en_retard;
            $projet->taches_count = $projet->taches->count();

            return response()->json([
                'success' => true,
                'data' => $projet
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Projet non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Mettre à jour un projet
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $projet = Projet::findOrFail($id);

            $validated = $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'required|string',
                'type_projet_id' => 'required|exists:type_projets,id',
                'porteur_ids' => 'required|array|min:1',
                'porteur_ids.*' => 'exists:users,id',
                'donneur_ordre_id' => 'required|exists:users,id',
                'date_debut_previsionnelle' => 'required|date',
                'date_fin_previsionnelle' => 'required|date|after:date_debut_previsionnelle',
                'justification_modification_dates' => 'nullable|string',
                'niveau_execution' => 'sometimes|integer|min:0|max:100',
            ]);

            // RÈGLES MÉTIER POUR LE NIVEAU D'EXÉCUTION
            if (isset($validated['niveau_execution'])) {
                // Règle 1 : On ne peut modifier le niveau d'exécution que si le statut est "en_cours"
                if ($projet->statut !== Projet::STATUT_EN_COURS) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le niveau d\'exécution ne peut être modifié que lorsque le projet est en cours',
                        'current_status' => [
                            'statut' => $projet->statut,
                            'libelle' => Projet::STATUTS[$projet->statut] ?? $projet->statut
                        ]
                    ], 422);
                }

                // Règle 2 : Impossible de mettre à 100% manuellement
                if ($validated['niveau_execution'] == 100) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Impossible de définir le niveau d\'exécution à 100% manuellement. Le niveau passe automatiquement à 100% quand le projet est terminé.',
                        'niveau_actuel' => $projet->niveau_execution
                    ], 422);
                }

                // Règle 3 : Permettre la diminution du niveau d'exécution (suppression de la restriction)
                // Note: L'utilisateur peut maintenant diminuer le niveau d'exécution si nécessaire
            }

            $projet->update([
                'titre' => $validated['titre'],
                'description' => $validated['description'],
                'type_projet_id' => $validated['type_projet_id'],
                'donneur_ordre_id' => $validated['donneur_ordre_id'],
                'date_debut_previsionnelle' => $validated['date_debut_previsionnelle'],
                'date_fin_previsionnelle' => $validated['date_fin_previsionnelle'],
                'justification_modification_dates' => $validated['justification_modification_dates'] ?? null,
                'date_modification' => now(),
                'modifier_par' => $request->user()->email,
            ]);

            // Mettre à jour les porteurs multiples
            $projet->porteurs()->detach(); // Supprimer tous les porteurs actuels
            foreach ($validated['porteur_ids'] as $porteurId) {
                $projet->porteurs()->attach($porteurId, [
                    'date_assignation' => now(),
                    'statut' => true
                ]);
            }

            $projet->load(['typeProjet', 'porteurs', 'donneurOrdre']);

            return response()->json([
                'success' => true,
                'message' => 'Projet mis à jour avec succès',
                'data' => $projet
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
                'message' => 'Erreur lors de la mise à jour du projet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un projet
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $projet = Projet::findOrFail($id);

            // Vérifications de sécurité
            if ($projet->statut === Projet::STATUT_EN_COURS) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un projet en cours'
                ], 400);
            }

            $projet->delete();

            return response()->json([
                'success' => true,
                'message' => 'Projet supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du projet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Changer le statut d'un projet
     */
    public function changerStatut(Request $request, int $id): JsonResponse
    {
        try {
            $projet = Projet::findOrFail($id);

            $validated = $request->validate([
                'nouveau_statut' => 'required|in:' . implode(',', array_keys(Projet::STATUTS)),
                'commentaire' => 'nullable|string',
                'justificatif_path' => 'nullable|string',
            ]);

            $ancienStatut = $projet->statut;

            // Vérifier la logique du changement de statut
            $estMiseAJourCommentaire = false;
            if ($validated['nouveau_statut'] === $projet->statut) {
                // Même statut : vérifier si c'est pour mettre à jour le commentaire
                $commentaireActuel = $validated['commentaire'] ?? '';

                // Récupérer le dernier commentaire de l'historique
                $dernierHistorique = $projet->historiqueStatuts()
                    ->where('nouveau_statut', $projet->statut)
                    ->orderBy('date_changement', 'desc')
                    ->first();

                $dernierCommentaire = $dernierHistorique ? ($dernierHistorique->commentaire ?? '') : '';

                // Cas où aucun commentaire n'est fourni
                if (empty($commentaireActuel)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le projet a déjà le statut "' . Projet::STATUTS[$projet->statut] . '". Pour mettre à jour, veuillez fournir un commentaire différent.',
                        'current_status' => [
                            'statut' => $projet->statut,
                            'libelle' => Projet::STATUTS[$projet->statut],
                            'dernier_commentaire' => $dernierCommentaire
                        ]
                    ], 422);
                }

                // Cas où le commentaire est identique au dernier
                if ($commentaireActuel === $dernierCommentaire) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le projet a déjà le statut "' . Projet::STATUTS[$projet->statut] . '" avec ce même commentaire. Aucun changement nécessaire.',
                        'current_status' => [
                            'statut' => $projet->statut,
                            'libelle' => Projet::STATUTS[$projet->statut],
                            'dernier_commentaire' => $dernierCommentaire
                        ]
                    ], 422);
                }

                // Si on arrive ici, c'est un nouveau commentaire pour le même statut
                $estMiseAJourCommentaire = true;
            }

            // Validation spécifique selon le changement de statut
            if ($validated['nouveau_statut'] === Projet::STATUT_DEMANDE_CLOTURE) {
                // Vérifier qu'il y a au moins un justificatif (pièce jointe marquée comme justificatif)
                $aUnJustificatif = PieceJointeProjet::where('projet_id', $id)
                    ->where('est_justificatif', true)
                    ->exists();

                if (!$aUnJustificatif) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur de validation',
                        'errors' => [
                            'nouveau_statut' => ['Un justificatif (pièce jointe marquée comme justificatif) est obligatoire pour demander la clôture']
                        ]
                    ], 422);
                }
            }

            // Validation spécifique pour le statut "termine"
            if ($validated['nouveau_statut'] === Projet::STATUT_TERMINE) {
                // Vérifier qu'il y a au moins un justificatif (pièce jointe marquée comme justificatif)
                $aUnJustificatif = PieceJointeProjet::where('projet_id', $id)
                    ->where('est_justificatif', true)
                    ->exists();

                if (!$aUnJustificatif) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur de validation',
                        'errors' => [
                            'nouveau_statut' => ['Un justificatif (pièce jointe marquée comme justificatif) est obligatoire pour terminer le projet']
                        ]
                    ], 422);
                }
            }

            // Vérification de permission pour terminer un projet
            if ($validated['nouveau_statut'] === Projet::STATUT_TERMINE) {
                if (!$request->user()->hasPermission('terminate_project')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Permission insuffisante',
                        'errors' => [
                            'nouveau_statut' => ['Vous n\'avez pas la permission de terminer un projet.']
                        ]
                    ], 403);
                }
            }

            // Utiliser la méthode du modèle pour changer le statut
            $justificatifPath = isset($validated['justificatif_path']) ? $validated['justificatif_path'] : null;
            $commentaire = isset($validated['commentaire']) ? $validated['commentaire'] : null;

            $projet->changerStatut(
                $validated['nouveau_statut'],
                $request->user()->id,
                $commentaire,
                $justificatifPath
            );

            // Mettre à jour les dates réelles selon le statut
            $updates = [];
            if ($validated['nouveau_statut'] === Projet::STATUT_EN_COURS && !$projet->date_debut_reelle) {
                $updates['date_debut_reelle'] = now()->toDateString();
            }
            if ($validated['nouveau_statut'] === Projet::STATUT_TERMINE && !$projet->date_fin_reelle) {
                $updates['date_fin_reelle'] = now()->toDateString();
                $updates['niveau_execution'] = 100;
            }

            if (!empty($updates)) {
                $projet->update($updates);
            }

            $projet->load(['historiqueStatuts.user']);

            // 🔥 CORRECTION BUG 1 : Utiliser l'ancien statut sauvegardé
            if (!$estMiseAJourCommentaire) {
                event(new ProjetStatusChanged(
                    $projet,
                    $request->user(),
                    $ancienStatut,  // ✅ Utiliser l'ancien statut sauvegardé
                    $projet->statut,
                    $commentaire
                ));
            }

            // Message de succès adapté selon le type d'opération
            $message = $estMiseAJourCommentaire
                ? 'Commentaire du statut "' . Projet::STATUTS[$projet->statut] . '" mis à jour avec succès'
                : 'Statut du projet modifié avec succès';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $projet
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
                'message' => 'Erreur lors du changement de statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir le tableau de bord des projets
     */
    public function tableauBord(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Base query selon les permissions de l'utilisateur
            $query = Projet::query();

            // SYSTÈME DE PERMISSIONS POUR LE TABLEAU DE BORD

            if ($user->hasPermission('view_all_projects')) {
                // 🔓 NIVEAU 1 : VIEW ALL PROJECTS - Tous les projets
                // Aucune restriction sur la requête

            } elseif ($user->hasPermission('view_my_entity_projects')) {
                // 🏢 NIVEAU 2 : VIEW MY ENTITY PROJECTS - Projets de son entité ET entités enfants
                $affectationActuelle = $user->affectations()->where('statut', true)->first();

                if ($affectationActuelle) {
                    $entiteId = $affectationActuelle->service_id;

                    // Récupérer récursivement toutes les entités (actuelle + enfants)
                    $entitesIds = $this->getEntitesEnfantsRecursives($entiteId);

                    // Récupérer tous les utilisateurs de ces entités (actuels et passés)
                    $utilisateursEntite = \App\Models\UtilisateurEntiteHistory::whereIn('service_id', $entitesIds)
                        ->distinct()
                        ->pluck('user_id');

                    $query->where(function ($q) use ($utilisateursEntite) {
                        $q->whereIn('porteur_id', $utilisateursEntite)
                          ->orWhereIn('donneur_ordre_id', $utilisateursEntite)
                          ->orWhereHas('taches', function ($tq) use ($utilisateursEntite) {
                              $tq->whereIn('responsable_id', $utilisateursEntite);
                          });
                    });
                } else {
                    // Fallback vers ses projets personnels
                    $query->where(function ($q) use ($user) {
                        $q->where('porteur_id', $user->id)
                          ->orWhere('donneur_ordre_id', $user->id)
                          ->orWhereHas('taches', function ($tq) use ($user) {
                              $tq->where('responsable_id', $user->id);
                          });
                    });
                }

            } elseif ($user->hasPermission('view_my_projects')) {
                // 👤 NIVEAU 3 : VIEW MY PROJECTS - Seulement ses projets
                $query->where(function ($q) use ($user) {
                    $q->where('porteur_id', $user->id)
                      ->orWhere('donneur_ordre_id', $user->id)
                      ->orWhereHas('taches', function ($tq) use ($user) {
                          $tq->where('responsable_id', $user->id);
                      });
                });

            } else {
                // ❌ AUCUNE PERMISSION - Accès refusé
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions nécessaires pour consulter le tableau de bord',
                    'permissions_required' => [
                        'view_my_projects' => 'Voir mes projets personnels',
                        'view_my_entity_projects' => 'Voir les projets de mon entité',
                        'view_all_projects' => 'Voir tous les projets (administrateur)'
                    ]
                ], 403);
            }

            // ========================================
            // FILTRES AVANCÉS SELON LES PERMISSIONS
            // ========================================

            // Filtres de base (disponibles pour tous)
            if ($request->filled('statut')) {
                $query->byStatut($request->statut);
            }

            if ($request->filled('type_projet_id')) {
                $query->where('type_projet_id', $request->type_projet_id);
            }

            if ($request->filled('en_retard') && $request->boolean('en_retard')) {
                $query->enRetard();
            }

            if ($request->filled('niveau_execution_min')) {
                $query->where('niveau_execution', '>=', $request->niveau_execution_min);
            }

            if ($request->filled('niveau_execution_max')) {
                $query->where('niveau_execution', '<=', $request->niveau_execution_max);
            }

            // Filtres de date
            if ($request->filled('date_debut_previsionnelle_debut')) {
                $query->where('date_debut_previsionnelle', '>=', $request->date_debut_previsionnelle_debut);
            }

            if ($request->filled('date_debut_previsionnelle_fin')) {
                $query->where('date_debut_previsionnelle', '<=', $request->date_debut_previsionnelle_fin);
            }

            if ($request->filled('date_fin_previsionnelle_debut')) {
                $query->where('date_fin_previsionnelle', '>=', $request->date_fin_previsionnelle_debut);
            }

            if ($request->filled('date_fin_previsionnelle_fin')) {
                $query->where('date_fin_previsionnelle', '<=', $request->date_fin_previsionnelle_fin);
            }

            if ($request->filled('date_creation_debut')) {
                $query->where('created_at', '>=', $request->date_creation_debut);
            }

            if ($request->filled('date_creation_fin')) {
                $query->where('created_at', '<=', $request->date_creation_fin);
            }

            // Filtres par utilisateur (selon les permissions)
            if ($request->filled('porteur_id')) {
                // Restriction : seulement si l'utilisateur a view_all_projects ou view_my_entity_projects
                if ($user->hasPermission('view_all_projects') || $user->hasPermission('view_my_entity_projects')) {
                    $query->byPorteur($request->porteur_id);
                }
            }

            if ($request->filled('donneur_ordre_id')) {
                // Restriction : seulement si l'utilisateur a view_all_projects ou view_my_entity_projects
                if ($user->hasPermission('view_all_projects') || $user->hasPermission('view_my_entity_projects')) {
                    $query->byDonneurOrdre($request->donneur_ordre_id);
                }
            }

            // Filtre par entité (seulement pour view_all_projects)
            if ($request->filled('entite_id') && $user->hasPermission('view_all_projects')) {
                $entiteId = $request->entite_id;

                // Récupérer tous les utilisateurs de cette entité
                $utilisateursEntite = \App\Models\UtilisateurEntiteHistory::where('service_id', $entiteId)
                    ->distinct()
                    ->pluck('user_id');

                // Filtrer les projets où porteur ou donneur d'ordre fait partie de l'entité
                $query->where(function ($q) use ($utilisateursEntite) {
                    $q->whereIn('porteur_id', $utilisateursEntite)
                      ->orWhereIn('donneur_ordre_id', $utilisateursEntite)
                      ->orWhereHas('taches', function ($tq) use ($utilisateursEntite) {
                          $tq->whereIn('responsable_id', $utilisateursEntite);
                      });
                });
            }

            // Recherche textuelle
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('titre', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('porteur', function ($pq) use ($search) {
                          $pq->where('nom', 'like', "%{$search}%")
                             ->orWhere('prenom', 'like', "%{$search}%");
                      })
                      ->orWhereHas('donneurOrdre', function ($dq) use ($search) {
                          $dq->where('nom', 'like', "%{$search}%")
                             ->orWhere('prenom', 'like', "%{$search}%");
                      });
                });
            }

            // Calculer les statistiques
            $totalProjets = $query->count();

            $stats = [
                'total_projets' => $totalProjets,
                'projets_par_statut' => [],
                'projets_en_retard' => (clone $query)->enRetard()->count(),
                'niveau_execution_moyen' => round($query->avg('niveau_execution') ?? 0),
                'projets_recents' => (clone $query)->orderBy('date_creation', 'desc')
                    ->with(['typeProjet', 'porteur'])
                    ->take(5)
                    ->get(),
                'permissions_info' => [
                    'level' => $user->hasPermission('view_all_projects') ? 'all_projects' :
                              ($user->hasPermission('view_my_entity_projects') ? 'entity_projects' : 'my_projects'),
                    'description' => $user->hasPermission('view_all_projects') ? 'Tableau de bord global' :
                                   ($user->hasPermission('view_my_entity_projects') ? 'Tableau de bord de votre entité et entités enfants' : 'Votre tableau de bord personnel'),
                    'scope' => $user->hasPermission('view_all_projects') ? 'Tous les projets' :
                              ($user->hasPermission('view_my_entity_projects') ? 'Projets de votre entité et entités enfants' : 'Vos projets')
                ]
            ];

            // Répartition par statut avec requêtes séparées
            foreach (Projet::STATUTS as $statut => $libelle) {
                $queryStatut = clone $query;
                $count = $queryStatut->where('statut', $statut)->count();

                $stats['projets_par_statut'][$statut] = [
                    'libelle' => $libelle,
                    'count' => $count,
                    'pourcentage' => $totalProjets > 0
                        ? round(($count / $totalProjets) * 100, 1)
                        : 0
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du tableau de bord',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour uniquement le niveau d'exécution d'un projet
     */
    public function mettreAJourNiveauExecution(Request $request, int $id): JsonResponse
    {
        try {
            $projet = Projet::findOrFail($id);

            $validated = $request->validate([
                'niveau_execution' => 'required|integer|min:0|max:100',
                'commentaire' => 'nullable|string|max:500',
            ]);

            // RÈGLES MÉTIER POUR LE NIVEAU D'EXÉCUTION

            // Règle 1 : On ne peut modifier le niveau d'exécution que si le statut est "en_cours"
            if ($projet->statut !== Projet::STATUT_EN_COURS) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le niveau d\'exécution ne peut être modifié que lorsque le projet est en cours',
                    'current_status' => [
                        'statut' => $projet->statut,
                        'libelle' => Projet::STATUTS[$projet->statut] ?? $projet->statut,
                        'niveau_actuel' => $projet->niveau_execution
                    ]
                ], 422);
            }

            // Règle 2 : Vérifier si le projet a des tâches (mode automatique)
            if ($projet->aDesTaches()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le niveau d\'exécution est calculé automatiquement basé sur les tâches du projet. Impossible de le modifier manuellement.',
                    'niveau_actuel' => $projet->niveau_execution,
                    'mode' => 'automatique',
                    'nombre_taches' => $projet->taches()->count()
                ], 422);
            }

            // Règle 3 : Impossible de mettre à 100% manuellement
            if ($validated['niveau_execution'] == 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de définir le niveau d\'exécution à 100% manuellement. Le niveau passe automatiquement à 100% quand le projet est terminé.',
                    'niveau_actuel' => $projet->niveau_execution,
                    'niveau_max_autorise' => 99
                ], 422);
            }

            // Règle 4 : Empêcher les changements redondants (même niveau sans commentaire)
            if ($validated['niveau_execution'] == $projet->niveau_execution && empty($validated['commentaire'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le projet a déjà un niveau d\'exécution de ' . $projet->niveau_execution . '%. Pour confirmer ce niveau, veuillez ajouter un commentaire.',
                    'niveau_actuel' => $projet->niveau_execution
                ], 422);
            }

            // Mettre à jour le niveau d'exécution
            $ancienNiveau = $projet->niveau_execution;
            $projet->update([
                'niveau_execution' => $validated['niveau_execution'],
                'date_modification' => now(),
                'modifier_par' => $request->user()->email,
            ]);

            // Créer une entrée d'historique si le niveau a vraiment changé ou s'il y a un commentaire
            if ($validated['niveau_execution'] != $ancienNiveau || !empty($validated['commentaire'])) {
                $commentaireHistorique = $validated['commentaire'] ?? 'Mise à jour du niveau d\'exécution';

                $projet->historiqueStatuts()->create([
                    'ancien_statut' => $projet->statut,
                    'nouveau_statut' => $projet->statut,
                    'user_id' => $request->user()->id,
                    'commentaire' => "Niveau d'exécution: {$ancienNiveau}% → {$validated['niveau_execution']}%. {$commentaireHistorique}",
                    'date_changement' => now(),
                ]);
            }

            $projet->load(['typeProjet', 'porteur', 'donneurOrdre']);

            // Déclencher l'événement de mise à jour du niveau d'exécution
            if ($validated['niveau_execution'] != $ancienNiveau) {
                event(new ProjetExecutionLevelUpdated(
                    $projet,
                    $request->user(),
                    $ancienNiveau,
                    $validated['niveau_execution'],
                    $validated['commentaire'] ?? null
                ));
            }

            // Message adapté selon le type de mise à jour
            $progression = $validated['niveau_execution'] - $ancienNiveau;
            if ($validated['niveau_execution'] == $ancienNiveau) {
                $message = 'Commentaire ajouté avec succès pour le niveau d\'exécution';
            } elseif ($progression > 0) {
                $message = 'Niveau d\'exécution augmenté avec succès (de ' . $ancienNiveau . '% à ' . $validated['niveau_execution'] . '%)';
            } else {
                $message = 'Niveau d\'exécution diminué avec succès (de ' . $ancienNiveau . '% à ' . $validated['niveau_execution'] . '%)';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'projet' => $projet,
                    'ancien_niveau' => $ancienNiveau,
                    'nouveau_niveau' => $validated['niveau_execution'],
                    'progression' => $validated['niveau_execution'] - $ancienNiveau
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
                'message' => 'Erreur lors de la mise à jour du niveau d\'exécution',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les entités disponibles pour les filtres
     */
    public function getEntitesForFilter(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Seuls les utilisateurs avec view_all_projects peuvent filtrer par entité
            if (!$user->hasPermission('view_all_projects')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions nécessaires pour filtrer par entité'
                ], 403);
            }

            $entites = \App\Models\Entite::select('id', 'nom', 'type_entite_id')
                ->with('typeEntite:id,nom')
                ->orderBy('nom')
                ->get()
                ->map(function ($entite) {
                    return [
                        'id' => $entite->id,
                        'nom' => $entite->nom,
                        'type' => $entite->typeEntite->nom ?? 'Non défini'
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $entites
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
     * Récupérer les informations sur le niveau d'exécution d'un projet
     */
    public function getNiveauExecutionInfo(int $id): JsonResponse
    {
        try {
            $projet = Projet::with(['taches:id,projet_id,niveau_execution'])->findOrFail($id);

            $aDesTaches = $projet->aDesTaches();
            $niveauMoyen = $aDesTaches ? $projet->taches->avg('niveau_execution') : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'projet_id' => $projet->id,
                    'niveau_actuel' => $projet->niveau_execution,
                    'mode' => $aDesTaches ? 'automatique' : 'manuel',
                    'nombre_taches' => $projet->taches->count(),
                    'niveau_moyen_taches' => $aDesTaches ? round($niveauMoyen) : null,
                    'peut_modifier' => !$aDesTaches && $projet->statut === Projet::STATUT_EN_COURS
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des informations du niveau d\'exécution',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les utilisateurs disponibles pour les filtres
     */
    public function getUsersForFilter(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = User::select('id', 'nom', 'prenom', 'email', 'matricule')
                        ->orderBy('nom')
                        ->orderBy('prenom');

            // Filtrer selon les permissions
            if ($user->hasPermission('view_all_projects')) {
                // Tous les utilisateurs
            } elseif ($user->hasPermission('view_my_entity_projects')) {
                // Utilisateurs de son entité ET entités enfants
                $affectationActuelle = $user->affectations()->where('statut', true)->first();
                if ($affectationActuelle) {
                    $entiteId = $affectationActuelle->service_id;

                    // Récupérer récursivement toutes les entités (actuelle + enfants)
                    $entitesIds = $this->getEntitesEnfantsRecursives($entiteId);

                    // Récupérer tous les utilisateurs de ces entités
                    $utilisateursEntite = \App\Models\UtilisateurEntiteHistory::whereIn('service_id', $entitesIds)
                        ->distinct()
                        ->pluck('user_id');
                    $query->whereIn('id', $utilisateursEntite);
                }
            } else {
                // Seulement l'utilisateur connecté
                $query->where('id', $user->id);
            }

            $users = $query->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'matricule' => $user->matricule,
                    'display_name' => $user->prenom . ' ' . $user->nom
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $users
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
     * Récupérer l'historique des changements de statut d'un projet
     */
    public function historique(int $id): JsonResponse
    {
        try {
            $projet = Projet::with(['typeProjet', 'porteur', 'donneurOrdre'])->findOrFail($id);

            // Récupérer l'historique des changements de statut
            $historique = $projet->historiqueStatuts()
                ->with('user:id,nom,prenom,email')
                ->orderBy('date_changement', 'desc')
                ->get();

            // Récupérer l'historique des pièces jointes
            $piecesJointes = $projet->piecesJointes()
                ->with('user:id,nom,prenom,email')
                ->orderBy('date_creation', 'desc')
                ->get();

            // Récupérer l'historique des discussions
            $discussions = $projet->discussions()
                ->with('user:id,nom,prenom,email')
                ->orderBy('date_creation', 'desc')
                ->get();

            // Combiner tous les événements dans un historique chronologique
            $evenements = collect();

            // Ajouter les changements de statut
            foreach ($historique as $changement) {
                $evenements->push([
                    'type' => 'statut_change',
                    'date' => $changement->date_changement,
                    'user' => $changement->user,
                    'titre' => 'Changement de statut',
                    'description' => $changement->commentaire,
                    'ancien_statut' => $changement->ancien_statut,
                    'nouveau_statut' => $changement->nouveau_statut,
                    'data' => $changement
                ]);
            }

            // Ajouter les ajouts de pièces jointes
            foreach ($piecesJointes as $piece) {
                $evenements->push([
                    'type' => 'attachment_added',
                    'date' => $piece->date_creation,
                    'user' => $piece->user,
                    'titre' => 'Pièce jointe ajoutée',
                    'description' => "Fichier : {$piece->nom_original}",
                    'data' => $piece
                ]);
            }

            // Ajouter les commentaires
            foreach ($discussions as $commentaire) {
                $evenements->push([
                    'type' => 'comment_added',
                    'date' => $commentaire->date_creation,
                    'user' => $commentaire->user,
                    'titre' => 'Commentaire ajouté',
                    'description' => $commentaire->contenu,
                    'data' => $commentaire
                ]);
            }

            // Trier par date (plus récent en premier)
            $evenements = $evenements->sortByDesc('date')->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'projet' => [
                        'id' => $projet->id,
                        'titre' => $projet->titre,
                        'type_projet' => $projet->typeProjet,
                        'porteur' => $projet->porteur,
                        'donneur_ordre' => $projet->donneurOrdre,
                        'statut_actuel' => $projet->statut,
                        'niveau_execution' => $projet->niveau_execution,
                        'date_creation' => $projet->date_creation
                    ],
                    'historique' => $evenements,
                    'statistiques' => [
                        'total_evenements' => $evenements->count(),
                        'changements_statut' => $historique->count(),
                        'pieces_jointes' => $piecesJointes->count(),
                        'commentaires' => $discussions->count()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'historique',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // NOUVELLES MÉTHODES POUR PORTEURS MULTIPLES
    // ========================================

    /**
     * Assigner des porteurs à un projet
     */
    public function assignerPorteurs(Request $request, int $id): JsonResponse
    {
        try {
            $projet = Projet::findOrFail($id);

            $validated = $request->validate([
                'user_ids' => 'required|array|min:1',
                'user_ids.*' => 'exists:users,id',
                'commentaire' => 'nullable|string'
            ]);

            // Vérifier les permissions
            $user = $request->user();
            if (!$user->hasPermission('manage_project_porteurs') &&
                !$projet->porteurs()->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour gérer les porteurs de ce projet'
                ], 403);
            }

            $assignations = [];
            foreach ($validated['user_ids'] as $userId) {
                // Vérifier si l'utilisateur n'est pas déjà porteur
                if (!$projet->porteurs()->where('user_id', $userId)->exists()) {
                    $projet->porteurs()->attach($userId, [
                        'date_assignation' => now(),
                        'statut' => true,
                        'commentaire' => $validated['commentaire'] ?? null
                    ]);
                    $assignations[] = $userId;
                }
            }

            $projet->load('porteurs.user');

            return response()->json([
                'success' => true,
                'message' => count($assignations) > 0 ? 'Porteurs assignés avec succès' : 'Aucun nouveau porteur assigné',
                'data' => [
                    'projet_id' => $projet->id,
                    'porteurs_assignes' => $assignations,
                    'porteurs_actuels' => $projet->porteurs
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
                'message' => 'Erreur lors de l\'assignation des porteurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retirer un porteur d'un projet
     */
    public function retirerPorteur(Request $request, int $id, int $userId): JsonResponse
    {
        try {
            $projet = Projet::findOrFail($id);

            // Vérifier les permissions
            $user = $request->user();
            if (!$user->hasPermission('manage_project_porteurs') &&
                !$projet->porteurs()->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour gérer les porteurs de ce projet'
                ], 403);
            }

            // Vérifier si l'utilisateur est porteur
            if (!$projet->porteurs()->where('user_id', $userId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet utilisateur n\'est pas porteur de ce projet'
                ], 404);
            }

            // Désactiver l'assignation (ne pas supprimer pour garder l'historique)
            $projet->porteurs()->updateExistingPivot($userId, [
                'date_fin_assignation' => now(),
                'statut' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Porteur retiré avec succès',
                'data' => [
                    'projet_id' => $projet->id,
                    'user_id' => $userId
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du retrait du porteur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lister les porteurs d'un projet
     */
    public function listerPorteurs(int $id): JsonResponse
    {
        try {
            $projet = Projet::with(['porteurs.user', 'porteursHistorique.user'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'projet_id' => $projet->id,
                    'porteurs_actuels' => $projet->porteurs,
                    'porteurs_historique' => $projet->porteursHistorique
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des porteurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
