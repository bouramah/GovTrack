<?php

namespace App\Services\Reunion;

use App\Models\Reunion;
use App\Models\TypeReunion;
use App\Models\User;
use App\Models\ReunionParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReunionService
{
    /**
     * Récupérer la liste des réunions avec filtres
     */
    public function getReunions(Request $request, User $user): array
    {
        try {
            $query = Reunion::with([
                'typeReunion',
                'serie',
                'participants.user',
                'createur',
                'modificateur',
                'validateurPV'
            ]);

            // ========================================
            // SYSTÈME DE PERMISSIONS POUR L'AFFICHAGE DES RÉUNIONS
            // ========================================

            if ($user->hasPermission('view_all_reunions')) {
                // 🔓 NIVEAU 1 : Accès complet à toutes les réunions
                // Aucune restriction sur la requête

            } elseif ($user->hasPermission('view_my_entity_reunions')) {
                // 🏢 NIVEAU 2 : Réunions de son entité ET entités enfants
                $affectationActuelle = $user->affectations()->where('statut', true)->first();

                if ($affectationActuelle) {
                    $entiteId = $affectationActuelle->service_id;

                    // Récupérer récursivement toutes les entités (actuelle + enfants)
                    $entitesIds = $this->getEntitesEnfantsRecursives($entiteId);

                    // Récupérer tous les utilisateurs de ces entités
                    $utilisateursEntite = \App\Models\UtilisateurEntiteHistory::whereIn('service_id', $entitesIds)
                        ->distinct()
                        ->pluck('user_id');

                    // Filtrer les réunions où l'utilisateur est impliqué
                    $query->where(function ($q) use ($utilisateursEntite, $user) {
                        $q->whereIn('creer_par', $utilisateursEntite)
                          ->orWhereIn('modifier_par', $utilisateursEntite)
                          ->orWhereIn('pv_valide_par_id', $utilisateursEntite)
                          ->orWhereHas('participants', function ($pq) use ($utilisateursEntite) {
                              $pq->whereIn('user_id', $utilisateursEntite);
                          });
                    });
                } else {
                    // Si pas d'affectation d'entité, fallback vers ses réunions personnelles
                    $query->where(function ($q) use ($user) {
                        $q->where('creer_par', $user->id)
                          ->orWhere('modifier_par', $user->id)
                          ->orWhere('pv_valide_par_id', $user->id)
                          ->orWhereHas('participants', function ($pq) use ($user) {
                              $pq->where('user_id', $user->id);
                          });
                    });
                }

            } else {
                // 🔒 NIVEAU 3 : Réunions personnelles uniquement
                $query->where(function ($q) use ($user) {
                    $q->where('creer_par', $user->id)
                      ->orWhere('modifier_par', $user->id)
                      ->orWhere('pv_valide_par_id', $user->id)
                      ->orWhereHas('participants', function ($pq) use ($user) {
                          $pq->where('user_id', $user->id);
                      });
                });
            }

            // ========================================
            // FILTRES APPLIQUÉS PAR L'UTILISATEUR
            // ========================================

            // Filtre par type de réunion
            if ($request->filled('type_reunion_id')) {
                $query->where('type_reunion_id', $request->type_reunion_id);
            }

            // Filtre par statut
            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            // Filtre par niveau de complexité
            if ($request->filled('niveau_complexite')) {
                $query->where('niveau_complexite_actuel', $request->niveau_complexite);
            }

            // Filtre par période
            if ($request->filled('date_debut')) {
                $query->where('date_debut', '>=', $request->date_debut);
            }

            if ($request->filled('date_fin')) {
                $query->where('date_fin', '<=', $request->date_fin);
            }

            // Filtre par lieu
            if ($request->filled('type_lieu')) {
                $query->where('type_lieu', $request->type_lieu);
            }

            // Filtre par créateur
            if ($request->filled('creer_par')) {
                $query->where('creer_par', $request->creer_par);
            }

            // Filtre par participant
            if ($request->filled('participant_id')) {
                $query->whereHas('participants', function ($q) use ($request) {
                    $q->where('user_id', $request->participant_id);
                });
            }

            // Filtre par recherche textuelle
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('titre', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhere('lieu', 'LIKE', "%{$search}%");
                });
            }

            // Tri
            $sortBy = $request->get('sort_by', 'date_debut');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $reunions = $query->paginate($perPage);

            return [
                'success' => true,
                'data' => $reunions,
                'message' => 'Réunions récupérées avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des réunions', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des réunions',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer une réunion spécifique
     */
    public function getReunion(int $id, User $user): array
    {
        try {
            $reunion = Reunion::with([
                'typeReunion',
                'serie',
                'participants.user',
                'ordreJours.responsable',
                'decisions.responsable',
                'actions.responsable',
                'pvs.redacteur',
                'pvs.validateur',
                'notifications.destinataire',
                'workflowExecutions.workflowConfig',
                'createur',
                'modificateur',
                'validateurPV'
            ])->find($id);

            if (!$reunion) {
                return [
                    'success' => false,
                    'message' => 'Réunion non trouvée'
                ];
            }

            // Vérifier les permissions d'accès
            if (!$this->canAccessReunion($reunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour accéder à cette réunion'
                ];
            }

            return [
                'success' => true,
                'data' => $reunion,
                'message' => 'Réunion récupérée avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la réunion', [
                'reunion_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération de la réunion',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Créer une nouvelle réunion
     */
    public function createReunion(array $data, User $user): array
    {
        try {
            DB::beginTransaction();

            // Vérifier les permissions de création
            if (!$this->canCreateReunion($data, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour créer cette réunion'
                ];
            }

            // Préparer les données
            $reunionData = [
                'titre' => $data['titre'],
                'description' => $data['description'] ?? '',
                'type_reunion_id' => $data['type_reunion_id'],
                'niveau_complexite_actuel' => $data['niveau_complexite_actuel'] ?? 'SIMPLE',
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'] ?? null,
                'lieu' => $data['lieu'] ?? null,
                'type_lieu' => $data['type_lieu'] ?? 'PHYSIQUE',
                'lien_virtuel' => $data['lien_virtuel'] ?? null,
                'periodicite' => $data['periodicite'] ?? 'PONCTUELLE',
                'serie_id' => $data['serie_id'] ?? null,
                'suspendue' => $data['suspendue'] ?? false,
                'fonctionnalites_actives' => $data['fonctionnalites_actives'] ?? [],
                'quorum_minimum' => $data['quorum_minimum'] ?? null,
                'ordre_du_jour_type' => $data['ordre_du_jour_type'] ?? 'EXPLICITE',
                'statut' => $data['statut'] ?? 'PLANIFIEE',
                'creer_par' => $user->id,
                'modifier_par' => $user->id,
                'date_creation' => now(),
                'date_modification' => now(),
            ];

            $reunion = Reunion::create($reunionData);

            // Ajouter les participants si fournis
            if (isset($data['participants']) && is_array($data['participants'])) {
                foreach ($data['participants'] as $participantData) {
                    $reunion->participants()->create([
                        'user_id' => $participantData['user_id'],
                        'role' => $participantData['role'] ?? 'PARTICIPANT',
                        'type' => $participantData['type'] ?? 'INTERNE',
                        'statut_presence' => $participantData['statut_presence'] ?? 'INVITE',
                        'notifications_actives' => $participantData['notifications_actives'] ?? [],
                        'date_creation' => now(),
                        'date_modification' => now(),
                    ]);
                }
            }

            // Ajouter l'ordre du jour si fourni
            if (isset($data['ordre_jour']) && is_array($data['ordre_jour'])) {
                foreach ($data['ordre_jour'] as $index => $ordreJourData) {
                    $reunion->ordreJours()->create([
                        'ordre' => $index + 1,
                        'titre' => $ordreJourData['titre'],
                        'description' => $ordreJourData['description'] ?? '',
                        'type' => $ordreJourData['type'] ?? 'DISCUSSION',
                        'duree_estimee_minutes' => $ordreJourData['duree_estimee_minutes'] ?? null,
                        'responsable_id' => $ordreJourData['responsable_id'] ?? null,
                        'statut' => $ordreJourData['statut'] ?? 'PLANIFIE',
                        'niveau_detail' => $ordreJourData['niveau_detail'] ?? 1,
                        'commentaires' => $ordreJourData['commentaires'] ?? [],
                        'date_creation' => now(),
                        'date_modification' => now(),
                    ]);
                }
            }

            DB::commit();

            // Charger les relations pour la réponse
            $reunion->load([
                'typeReunion',
                'serie',
                'participants.user',
                'ordreJours.responsable',
                'createur'
            ]);

            return [
                'success' => true,
                'data' => $reunion,
                'message' => 'Réunion créée avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la réunion', [
                'user_id' => $user->id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la création de la réunion',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour une réunion
     */
    public function updateReunion(int $id, array $data, User $user): array
    {
        try {
            DB::beginTransaction();

            $reunion = Reunion::find($id);
            if (!$reunion) {
                return [
                    'success' => false,
                    'message' => 'Réunion non trouvée'
                ];
            }

            // Vérifier les permissions de modification
            if (!$this->canUpdateReunion($reunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier cette réunion'
                ];
            }

            // Préparer les données de mise à jour
            $updateData = array_filter([
                'titre' => $data['titre'] ?? null,
                'description' => $data['description'] ?? null,
                'type_reunion_id' => $data['type_reunion_id'] ?? null,
                'niveau_complexite_actuel' => $data['niveau_complexite_actuel'] ?? null,
                'date_debut' => $data['date_debut'] ?? null,
                'date_fin' => $data['date_fin'] ?? null,
                'lieu' => $data['lieu'] ?? null,
                'type_lieu' => $data['type_lieu'] ?? null,
                'lien_virtuel' => $data['lien_virtuel'] ?? null,
                'periodicite' => $data['periodicite'] ?? null,
                'serie_id' => $data['serie_id'] ?? null,
                'suspendue' => $data['suspendue'] ?? null,
                'fonctionnalites_actives' => $data['fonctionnalites_actives'] ?? null,
                'quorum_minimum' => $data['quorum_minimum'] ?? null,
                'ordre_du_jour_type' => $data['ordre_du_jour_type'] ?? null,
                'statut' => $data['statut'] ?? null,
                'modifier_par' => $user->id,
                'date_modification' => now(),
            ], function ($value) {
                return $value !== null;
            });

            $reunion->update($updateData);

            // Mettre à jour les participants si fournis
            if (isset($data['participants']) && is_array($data['participants'])) {
                // Supprimer les participants existants
                $reunion->participants()->delete();

                // Ajouter les nouveaux participants
                foreach ($data['participants'] as $participantData) {
                    $reunion->participants()->create([
                        'user_id' => $participantData['user_id'],
                        'role' => $participantData['role'] ?? 'PARTICIPANT',
                        'type' => $participantData['type'] ?? 'INTERNE',
                        'statut_presence' => $participantData['statut_presence'] ?? 'INVITE',
                        'notifications_actives' => $participantData['notifications_actives'] ?? [],
                        'date_creation' => now(),
                        'date_modification' => now(),
                    ]);
                }
            }

            DB::commit();

            // Charger les relations pour la réponse
            $reunion->load([
                'typeReunion',
                'serie',
                'participants.user',
                'ordreJours.responsable',
                'modificateur'
            ]);

            return [
                'success' => true,
                'data' => $reunion,
                'message' => 'Réunion mise à jour avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de la réunion', [
                'reunion_id' => $id,
                'user_id' => $user->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la réunion',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer une réunion
     */
    public function deleteReunion(int $id, User $user): array
    {
        try {
            $reunion = Reunion::find($id);
            if (!$reunion) {
                return [
                    'success' => false,
                    'message' => 'Réunion non trouvée'
                ];
            }

            // Vérifier les permissions de suppression
            if (!$this->canDeleteReunion($reunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour supprimer cette réunion'
                ];
            }

            // Vérifier si la réunion peut être supprimée
            if ($reunion->statut === 'TERMINEE' && $reunion->pvs()->exists()) {
                return [
                    'success' => false,
                    'message' => 'Impossible de supprimer une réunion terminée avec des PV'
                ];
            }

            DB::beginTransaction();

            // Supprimer les données associées
            $reunion->participants()->delete();
            $reunion->ordreJours()->delete();
            $reunion->decisions()->delete();
            $reunion->actions()->delete();
            $reunion->notifications()->delete();
            $reunion->workflowExecutions()->delete();
            $reunion->pvs()->delete();

            // Supprimer la réunion
            $reunion->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Réunion supprimée avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de la réunion', [
                'reunion_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression de la réunion',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Changer le statut d'une réunion
     */
    public function changeStatut(int $id, string $nouveauStatut, User $user, ?string $commentaire = null): array
    {
        try {
            $reunion = Reunion::find($id);
            if (!$reunion) {
                return [
                    'success' => false,
                    'message' => 'Réunion non trouvée'
                ];
            }

            // Vérifier les permissions
            if (!$this->canUpdateReunion($reunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier cette réunion'
                ];
            }

            // Vérifier la validité du changement de statut
            if (!$this->isValidStatutTransition($reunion->statut, $nouveauStatut)) {
                return [
                    'success' => false,
                    'message' => 'Transition de statut non autorisée'
                ];
            }

            $ancienStatut = $reunion->statut;
            $reunion->update([
                'statut' => $nouveauStatut,
                'modifier_par' => $user->id,
                'date_modification' => now(),
            ]);

            // Log du changement de statut
            Log::info('Changement de statut de réunion', [
                'reunion_id' => $id,
                'ancien_statut' => $ancienStatut,
                'nouveau_statut' => $nouveauStatut,
                'user_id' => $user->id,
                'commentaire' => $commentaire
            ]);

            return [
                'success' => true,
                'data' => $reunion,
                'message' => 'Statut de la réunion mis à jour avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors du changement de statut de la réunion', [
                'reunion_id' => $id,
                'user_id' => $user->id,
                'nouveau_statut' => $nouveauStatut,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors du changement de statut',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer les statistiques des réunions
     */
    public function getStats(User $user): array
    {
        try {
            $query = Reunion::query();

            // Appliquer les mêmes filtres de permissions que pour la liste
            if (!$user->hasPermission('view_all_reunions')) {
                if ($user->hasPermission('view_my_entity_reunions')) {
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
                              ->orWhereIn('pv_valide_par_id', $utilisateursEntite)
                              ->orWhereHas('participants', function ($pq) use ($utilisateursEntite) {
                                  $pq->whereIn('user_id', $utilisateursEntite);
                              });
                        });
                    } else {
                        $query->where(function ($q) use ($user) {
                            $q->where('creer_par', $user->id)
                              ->orWhere('modifier_par', $user->id)
                              ->orWhere('pv_valide_par_id', $user->id)
                              ->orWhereHas('participants', function ($pq) use ($user) {
                                  $pq->where('user_id', $user->id);
                              });
                        });
                    }
                } else {
                    $query->where(function ($q) use ($user) {
                        $q->where('creer_par', $user->id)
                          ->orWhere('modifier_par', $user->id)
                          ->orWhere('pv_valide_par_id', $user->id)
                          ->orWhereHas('participants', function ($pq) use ($user) {
                              $pq->where('user_id', $user->id);
                          });
                    });
                }
            }

            $stats = [
                'total' => $query->count(),
                'planifiees' => $query->where('statut', 'PLANIFIEE')->count(),
                'en_cours' => $query->where('statut', 'EN_COURS')->count(),
                'terminees' => $query->where('statut', 'TERMINEE')->count(),
                'annulees' => $query->where('statut', 'ANNULEE')->count(),
                'aujourdhui' => $query->whereDate('date_debut', today())->count(),
                'cette_semaine' => $query->whereBetween('date_debut', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'ce_mois' => $query->whereMonth('date_debut', now()->month)->count(),
            ];

            return [
                'success' => true,
                'data' => $stats,
                'message' => 'Statistiques récupérées avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
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
     * Vérifier si l'utilisateur peut accéder à la réunion
     */
    private function canAccessReunion(Reunion $reunion, User $user): bool
    {
        // L'utilisateur peut toujours accéder aux réunions qu'il a créées
        if ($reunion->creer_par === $user->id) {
            return true;
        }

        // L'utilisateur peut accéder aux réunions où il est participant
        if ($reunion->participants()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // L'utilisateur peut accéder aux réunions qu'il a modifiées
        if ($reunion->modifier_par === $user->id) {
            return true;
        }

        // L'utilisateur peut accéder aux réunions où il a validé le PV
        if ($reunion->pv_valide_par_id === $user->id) {
            return true;
        }

        // Vérifier les permissions globales
        if ($user->hasPermission('view_all_reunions')) {
            return true;
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut créer une réunion
     */
    private function canCreateReunion(array $data, User $user): bool
    {
        // Vérifier les permissions de création
        if ($user->hasPermission('create_reunions')) {
            return true;
        }

        // Vérifier si l'utilisateur peut gérer le type de réunion
        if (isset($data['type_reunion_id'])) {
            return $user->peutGererTypeReunion($data['type_reunion_id']);
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut modifier une réunion
     */
    private function canUpdateReunion(Reunion $reunion, User $user): bool
    {
        // L'utilisateur peut toujours modifier les réunions qu'il a créées
        if ($reunion->creer_par === $user->id) {
            return true;
        }

        // Vérifier les permissions de modification
        if ($user->hasPermission('update_reunions')) {
            return true;
        }

        // Vérifier si l'utilisateur peut gérer le type de réunion
        return $user->peutGererTypeReunion($reunion->type_reunion_id);
    }

    /**
     * Vérifier si l'utilisateur peut supprimer une réunion
     */
    private function canDeleteReunion(Reunion $reunion, User $user): bool
    {
        // L'utilisateur peut toujours supprimer les réunions qu'il a créées (si pas encore terminée)
        if ($reunion->creer_par === $user->id && $reunion->statut !== 'TERMINEE') {
            return true;
        }

        // Vérifier les permissions de suppression
        if ($user->hasPermission('delete_reunions')) {
            return true;
        }

        // Vérifier si l'utilisateur peut gérer le type de réunion
        return $user->peutGererTypeReunion($reunion->type_reunion_id);
    }

    /**
     * Vérifier si la transition de statut est valide
     */
    private function isValidStatutTransition(string $ancienStatut, string $nouveauStatut): bool
    {
        $transitionsValides = [
            'PLANIFIEE' => ['EN_COURS', 'ANNULEE'],
            'EN_COURS' => ['TERMINEE', 'ANNULEE'],
            'TERMINEE' => [], // Pas de transition possible
            'ANNULEE' => ['PLANIFIEE'], // Réactivation possible
        ];

        return in_array($nouveauStatut, $transitionsValides[$ancienStatut] ?? []);
    }
}
