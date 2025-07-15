<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tache;
use App\Models\TacheHistoriqueStatut;
use App\Models\Projet;
use App\Models\User;
use App\Models\PieceJointeTache;
use App\Events\TacheCreated;
use App\Events\TacheStatusChanged;
use App\Events\TacheExecutionLevelUpdated;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class TacheController extends Controller
{
    /**
     * Récupérer récursivement toutes les entités enfants d'une entité donnée
     */
    private function getEntitesEnfantsRecursives(int $entiteId): array
    {
        $entitesIds = [$entiteId];

        $entite = \App\Models\Entite::find($entiteId);
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
    private function getEnfantsRecursifs(\App\Models\Entite $entite, array &$entitesIds): void
    {
        $enfants = $entite->enfants;

        foreach ($enfants as $enfant) {
            $entitesIds[] = $enfant->id;
            $this->getEnfantsRecursifs($enfant, $entitesIds);
        }
    }

    /**
     * Afficher la liste des tâches
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = Tache::with(['projet', 'typeTache', 'responsable.affectations.entite', 'piecesJointes.user']);

            // ========================================
            // SYSTÈME DE PERMISSIONS POUR L'AFFICHAGE DES TÂCHES
            // ========================================

            if ($user->hasPermission('view_all_tasks')) {
                // 🔓 NIVEAU 1 : VIEW ALL TASKS - Accès complet à toutes les tâches
                // L'utilisateur peut voir toutes les tâches et utiliser tous les filtres
                // Aucune restriction sur la requête

            } elseif ($user->hasPermission('view_my_entity_tasks')) {
                // 🏢 NIVEAU 2 : VIEW MY ENTITY TASKS - Tâches de son entité ET entités enfants
                $affectationActuelle = $user->affectations()->where('statut', true)->first();

                if ($affectationActuelle) {
                    $entiteId = $affectationActuelle->service_id;

                    // Récupérer récursivement toutes les entités (actuelle + enfants)
                    $entitesIds = $this->getEntitesEnfantsRecursives($entiteId);

                    // Récupérer tous les utilisateurs de ces entités (actuels et passés)
                    $utilisateursEntite = \App\Models\UtilisateurEntiteHistory::whereIn('service_id', $entitesIds)
                        ->distinct()
                        ->pluck('user_id');

                    // Filtrer les tâches assignées à des membres de l'entité ou ses enfants
                    $query->whereIn('responsable_id', $utilisateursEntite);
                } else {
                    // Si pas d'affectation d'entité, fallback vers ses tâches personnelles
                    $query->where('responsable_id', $user->id);
                }

            } elseif ($user->hasPermission('view_my_tasks')) {
                // 👤 NIVEAU 3 : VIEW MY TASKS - Seulement ses tâches
                $query->where('responsable_id', $user->id);

            } else {
                // ❌ AUCUNE PERMISSION - Accès refusé
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions nécessaires pour consulter les tâches',
                    'permissions_required' => [
                        'view_my_tasks' => 'Voir mes tâches personnelles',
                        'view_my_entity_tasks' => 'Voir les tâches de mon entité',
                        'view_all_tasks' => 'Voir toutes les tâches (administrateur)'
                    ]
                ], 403);
            }

            // ========================================
            // FILTRES AVANCÉS SELON LES PERMISSIONS
            // ========================================

            // Filtres de base (disponibles pour tous)
            if ($request->filled('projet_id')) {
                $query->byProjet($request->projet_id);
            }

            if ($request->filled('statut')) {
                $query->byStatut($request->statut);
            }

            if ($request->filled('type_tache_id')) {
                $query->byTypeTache($request->type_tache_id);
            }

            if ($request->filled('en_retard') && $request->boolean('en_retard')) {
                $query->enRetard();
            }

            // Recherche textuelle
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('titre', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filtres par utilisateur (selon les permissions)
            if ($request->filled('responsable_id')) {
                // Restriction : seulement si l'utilisateur a view_all_tasks ou view_my_entity_tasks
                if ($user->hasPermission('view_all_tasks') || $user->hasPermission('view_my_entity_tasks')) {
                    $query->byResponsable($request->responsable_id);
                }
            }

            // Filtre par entité (seulement pour view_all_tasks)
            if ($request->filled('entite_id') && $user->hasPermission('view_all_tasks')) {
                $query->byEntite($request->entite_id);
            }

            // Tri
            $sortBy = $request->get('sort_by', 'date_creation');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $taches = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $taches->items(),
                'pagination' => [
                    'current_page' => $taches->currentPage(),
                    'last_page' => $taches->lastPage(),
                    'per_page' => $taches->perPage(),
                    'total' => $taches->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des tâches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une nouvelle tâche
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string',
                'projet_id' => 'required|exists:projets,id',
                'type_tache_id' => 'nullable|exists:type_taches,id',
                'responsable_id' => 'nullable|exists:users,id',
                'date_debut_previsionnelle' => 'nullable|date',
                'date_fin_previsionnelle' => 'nullable|date|after_or_equal:date_debut_previsionnelle',
            ]);

            $tache = Tache::create([
                ...$validated,
                'statut' => Tache::STATUT_A_FAIRE,
                'niveau_execution' => 0,
                'date_creation' => now(),
                'date_modification' => now(),
                'creer_par' => $request->user()->email,
                'modifier_par' => $request->user()->email,
            ]);

            $tache->load(['projet', 'typeTache', 'responsable']);

            // Déclencher l'événement de création de tâche
            event(new TacheCreated($tache, $request->user()));

            return response()->json([
                'success' => true,
                'message' => 'Tâche créée avec succès',
                'data' => $tache
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
                'message' => 'Erreur lors de la création de la tâche',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher une tâche spécifique
     */
    public function show(int $id): JsonResponse
    {
        try {
            $tache = Tache::with([
                'projet.typeProjet',
                'typeTache',
                'responsable',
                'piecesJointes.user',
                'discussions.user',
                'historiqueStatuts.user',
                'projet.porteur'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $tache
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tâche non trouvée',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Mettre à jour une tâche
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $tache = Tache::findOrFail($id);

            $validated = $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type_tache_id' => 'nullable|exists:type_taches,id',
                'responsable_id' => 'nullable|exists:users,id',
                'date_debut_previsionnelle' => 'nullable|date',
                'date_fin_previsionnelle' => 'nullable|date|after_or_equal:date_debut_previsionnelle',
                'niveau_execution' => 'sometimes|integer|min:0|max:100',
            ]);

            $tache->update([
                ...$validated,
                'date_modification' => now(),
                'modifier_par' => $request->user()->email,
            ]);

            // Mettre à jour le niveau du projet parent
            $tache->mettreAJourNiveauProjet();

            $tache->load(['projet', 'responsable']);

            return response()->json([
                'success' => true,
                'message' => 'Tâche mise à jour avec succès',
                'data' => $tache
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
                'message' => 'Erreur lors de la mise à jour de la tâche',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une tâche
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $tache = Tache::findOrFail($id);

            // Sauvegarder le projet pour mise à jour
            $projet = $tache->projet;

            $tache->delete();

            // Mettre à jour le niveau du projet parent
            if ($projet && $projet->taches()->count() > 0) {
                $niveauMoyen = $projet->taches()->avg('niveau_execution') ?? 0;
                $projet->update([
                    'niveau_execution' => round($niveauMoyen),
                    'date_modification' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tâche supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la tâche',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Changer le statut d'une tâche
     */
    public function changerStatut(Request $request, int $id): JsonResponse
    {
        try {
            $tache = Tache::with(['projet.porteur', 'responsable'])->findOrFail($id);

            $validated = $request->validate([
                'nouveau_statut' => 'required|in:' . implode(',', array_keys(Tache::STATUTS)),
                'niveau_execution' => 'sometimes|integer|min:0|max:100',
                'commentaire' => 'nullable|string',
            ]);

            $nouveauStatut = $validated['nouveau_statut'];
            $currentUserId = $request->user()->id;

            // VALIDATION DES PERMISSIONS POUR CHANGER LE STATUT
            // Récupérer les IDs des personnes autorisées
            $responsableTacheId = $tache->responsable_id;
            $porteurProjetId = $tache->projet->porteur_id;

            // Pour tous les statuts sauf "terminé" : responsable de la tâche OU porteur du projet
            if ($nouveauStatut !== Tache::STATUT_TERMINE) {
                if ($currentUserId !== $responsableTacheId && $currentUserId !== $porteurProjetId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Seuls le responsable de la tâche ou le porteur du projet peuvent modifier ce statut',
                        'permissions' => [
                            'responsable_tache' => [
                                'id' => $tache->responsable->id ?? null,
                                'nom' => $tache->responsable ? $tache->responsable->nom . ' ' . $tache->responsable->prenom : 'Non défini'
                            ],
                            'porteur_projet' => [
                                'id' => $tache->projet->porteur->id ?? null,
                                'nom' => $tache->projet->porteur ? $tache->projet->porteur->nom . ' ' . $tache->projet->porteur->prenom : 'Non défini'
                            ]
                        ]
                    ], 403);
                }
            }

            // Logique de validation similaire aux projets
            $commentaire = $validated['commentaire'] ?? null;

            // Récupérer le dernier historique pour comparer les commentaires
            $dernierHistorique = $tache->historiqueStatuts()->first();

            if ($nouveauStatut === $tache->statut) {
                if (empty($commentaire)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La tâche a déjà le statut "' . Tache::STATUTS[$tache->statut] . '". Aucun changement nécessaire.',
                        'current_status' => [
                            'statut' => $tache->statut,
                            'libelle' => Tache::STATUTS[$tache->statut]
                        ]
                    ], 422);
                }

                // Vérifier si le commentaire est différent du dernier historique
                if ($dernierHistorique && $dernierHistorique->commentaire === $commentaire) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La tâche a déjà le statut "' . Tache::STATUTS[$tache->statut] . '" avec le même commentaire. Aucun changement nécessaire.',
                        'current_status' => [
                            'statut' => $tache->statut,
                            'libelle' => Tache::STATUTS[$tache->statut],
                            'commentaire' => $commentaire
                        ]
                    ], 422);
                }
            }

            // Validation spécifique selon le changement de statut
            if ($nouveauStatut === Tache::STATUT_DEMANDE_CLOTURE) {
                // Vérifier qu'il y a au moins un justificatif (pièce jointe marquée comme justificatif)
                $aUnJustificatif = \App\Models\PieceJointeTache::where('tache_id', $id)
                    ->where('est_justificatif', true)
                    ->exists();

                if (!$aUnJustificatif) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Un justificatif (pièce jointe marquée comme justificatif) est obligatoire pour demander la clôture de la tâche'
                    ], 422);
                }
            }

            // Validation spécifique pour le statut "termine"
            if ($nouveauStatut === Tache::STATUT_TERMINE) {
                // Vérifier qu'il y a au moins un justificatif (pièce jointe marquée comme justificatif)
                $aUnJustificatif = \App\Models\PieceJointeTache::where('tache_id', $id)
                    ->where('est_justificatif', true)
                    ->exists();

                if (!$aUnJustificatif) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Un justificatif (pièce jointe marquée comme justificatif) est obligatoire pour terminer la tâche'
                    ], 422);
                }
            }

            // Validation pour le passage au statut "terminé"
            if ($nouveauStatut === Tache::STATUT_TERMINE) {
                $projet = $tache->projet;

                // Seul le porteur du projet peut terminer une tâche
                if ($projet->porteur_id !== $request->user()->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Seul le porteur du projet peut terminer une tâche'
                    ], 403);
                }
            }

            // Enregistrer dans l'historique
            $ancienStatut = $tache->statut; // Sauvegarder avant mise à jour
            $historiqueStatut = new \App\Models\TacheHistoriqueStatut();
            $historiqueStatut->tache_id = $tache->id;
            $historiqueStatut->user_id = $request->user()->id;
            $historiqueStatut->ancien_statut = $ancienStatut;
            $historiqueStatut->nouveau_statut = $nouveauStatut;
            $historiqueStatut->commentaire = $commentaire;
            $historiqueStatut->justificatif_path = null; // Plus utilisé, on garde null
            $historiqueStatut->date_changement = now();
            $historiqueStatut->save();

            $updates = [
                'statut' => $nouveauStatut,
                'date_modification' => now(),
                'modifier_par' => $request->user()->email,
            ];

            // Mettre à jour les dates réelles selon le statut
            if ($nouveauStatut === Tache::STATUT_EN_COURS && !$tache->date_debut_reelle) {
                $updates['date_debut_reelle'] = now()->toDateString();
            }

            if ($nouveauStatut === Tache::STATUT_TERMINE) {
                if (!$tache->date_fin_reelle) {
                    $updates['date_fin_reelle'] = now()->toDateString();
                }
                $updates['niveau_execution'] = 100;
            }

            if (isset($validated['niveau_execution'])) {
                $updates['niveau_execution'] = $validated['niveau_execution'];
            }

            $tache->update($updates);

            // Mettre à jour le niveau du projet parent
            $tache->mettreAJourNiveauProjet();

            // Déclencher l'événement de changement de statut (seulement si le statut a vraiment changé)
            if ($nouveauStatut !== $ancienStatut) {
                event(new TacheStatusChanged(
                    $tache->fresh(),
                    $request->user(),
                    $ancienStatut,
                    $nouveauStatut,
                    $commentaire
                ));
            }

            // Message adaptatif
            $message = ($nouveauStatut === $ancienStatut && !empty($commentaire))
                ? 'Commentaire ajouté avec succès pour la tâche'
                : 'Statut de la tâche modifié avec succès';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $tache->fresh(['projet.typeProjet', 'typeTache', 'responsable', 'piecesJointes.user', 'historiqueStatuts.user'])
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
     * Obtenir les tâches d'un utilisateur
     */
    public function mesTaches(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $query = Tache::with(['projet.typeProjet', 'typeTache', 'responsable.affectations.entite', 'piecesJointes.user'])
                ->where('responsable_id', $user->id);

            // Filtres
            if ($request->filled('statut')) {
                $query->byStatut($request->statut);
            }

            if ($request->filled('type_tache_id')) {
                $query->byTypeTache($request->type_tache_id);
            }

            if ($request->filled('en_retard') && $request->boolean('en_retard')) {
                $query->enRetard();
            }

            if ($request->filled('entite_id')) {
                $query->byEntite($request->entite_id);
            }

            // Tri
            $sortBy = $request->get('sort_by', 'date_creation');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $taches = $query->get();

            return response()->json([
                'success' => true,
                'data' => $taches
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des tâches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir l'historique des statuts d'une tâche
     */
    public function historiqueStatuts(int $id): JsonResponse
    {
        try {
            $tache = Tache::findOrFail($id);

            $historique = $tache->historiqueStatuts()
                ->with('user:id,nom,prenom,email')
                ->orderBy('date_changement', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $historique,
                'tache' => [
                    'id' => $tache->id,
                    'titre' => $tache->titre,
                    'statut_actuel' => $tache->statut,
                    'statut_libelle' => $tache->statut_libelle,
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

    /**
     * Mettre à jour uniquement le niveau d'exécution d'une tâche
     */
    public function mettreAJourNiveauExecution(Request $request, int $id): JsonResponse
    {
        try {
            $tache = Tache::findOrFail($id);

            $validated = $request->validate([
                'niveau_execution' => 'required|integer|min:0|max:100',
                'commentaire' => 'nullable|string|max:500',
            ]);

            // RÈGLES MÉTIER POUR LE NIVEAU D'EXÉCUTION

            // Règle 1 : On ne peut modifier le niveau d'exécution que si le statut est "en_cours"
            if ($tache->statut !== Tache::STATUT_EN_COURS) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le niveau d\'exécution ne peut être modifié que lorsque la tâche est en cours',
                    'current_status' => [
                        'statut' => $tache->statut,
                        'libelle' => Tache::STATUTS[$tache->statut] ?? $tache->statut,
                        'niveau_actuel' => $tache->niveau_execution
                    ]
                ], 422);
            }

            // Règle 2 : Impossible de mettre à 100% manuellement
            if ($validated['niveau_execution'] == 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de définir le niveau d\'exécution à 100% manuellement. Le niveau passe automatiquement à 100% quand la tâche est terminée.',
                    'niveau_actuel' => $tache->niveau_execution,
                    'niveau_max_autorise' => 99
                ], 422);
            }

            // Règle 3 : Permettre la diminution du niveau d'exécution
            // Note: L'utilisateur peut maintenant diminuer le niveau d'exécution si nécessaire

            // Règle 4 : Empêcher les changements redondants (même niveau sans commentaire)
            if ($validated['niveau_execution'] == $tache->niveau_execution && empty($validated['commentaire'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'La tâche a déjà un niveau d\'exécution de ' . $tache->niveau_execution . '%. Pour confirmer ce niveau, veuillez ajouter un commentaire.',
                    'niveau_actuel' => $tache->niveau_execution
                ], 422);
            }

            // Mettre à jour le niveau d'exécution
            $ancienNiveau = $tache->niveau_execution;
            $tache->update([
                'niveau_execution' => $validated['niveau_execution'],
                'date_modification' => now(),
                'modifier_par' => $request->user()->email,
            ]);

            // Créer une entrée d'historique si le niveau a vraiment changé ou s'il y a un commentaire
            if ($validated['niveau_execution'] != $ancienNiveau || !empty($validated['commentaire'])) {
                $commentaireHistorique = $validated['commentaire'] ?? 'Mise à jour du niveau d\'exécution';

                $tache->historiqueStatuts()->create([
                    'ancien_statut' => $tache->statut,
                    'nouveau_statut' => $tache->statut,
                    'user_id' => $request->user()->id,
                    'commentaire' => "Niveau d'exécution: {$ancienNiveau}% → {$validated['niveau_execution']}%. {$commentaireHistorique}",
                    'date_changement' => now(),
                ]);
            }

            // Mettre à jour le niveau du projet parent
            $tache->mettreAJourNiveauProjet();

            $tache->load(['projet', 'responsable']);

            // Déclencher l'événement de mise à jour du niveau d'exécution
            if ($validated['niveau_execution'] != $ancienNiveau) {
                event(new TacheExecutionLevelUpdated(
                    $tache,
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
                    'tache' => $tache,
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
}
