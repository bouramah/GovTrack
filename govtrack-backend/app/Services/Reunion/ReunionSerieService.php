<?php

namespace App\Services\Reunion;

use App\Models\ReunionSerie;
use App\Models\Reunion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReunionSerieService
{
    /**
     * Récupérer la liste des séries de réunions
     */
    public function getSeries(Request $request, User $user): array
    {
        try {
            $query = ReunionSerie::with([
                'reunions',
                'createur',
                'modificateur'
            ]);

            // ========================================
            // SYSTÈME DE PERMISSIONS POUR L'AFFICHAGE DES SÉRIES
            // ========================================

            if ($user->hasPermission('view_all_reunion_series')) {
                // 🔓 NIVEAU 1 : Accès complet à toutes les séries
                // Aucune restriction sur la requête

            } elseif ($user->hasPermission('view_my_entity_reunion_series')) {
                // 🏢 NIVEAU 2 : Séries de son entité ET entités enfants
                $affectationActuelle = $user->affectations()->where('statut', true)->first();

                if ($affectationActuelle) {
                    $entiteId = $affectationActuelle->service_id;
                    $entitesIds = $this->getEntitesEnfantsRecursives($entiteId);
                    $utilisateursEntite = \App\Models\UtilisateurEntiteHistory::whereIn('service_id', $entitesIds)
                        ->distinct()
                        ->pluck('user_id');

                    // Filtrer les séries où l'utilisateur est impliqué
                    $query->where(function ($q) use ($utilisateursEntite, $user) {
                        $q->whereIn('creer_par', $utilisateursEntite)
                          ->orWhereIn('modifier_par', $utilisateursEntite)
                          ->orWhereHas('reunions', function ($rq) use ($utilisateursEntite) {
                              $rq->whereIn('creer_par', $utilisateursEntite)
                                 ->orWhereIn('modifier_par', $utilisateursEntite)
                                 ->orWhereHas('participants', function ($pq) use ($utilisateursEntite) {
                                     $pq->whereIn('user_id', $utilisateursEntite);
                                 });
                          });
                    });
                } else {
                    // Fallback vers ses séries personnelles
                    $query->where(function ($q) use ($user) {
                        $q->where('creer_par', $user->id)
                          ->orWhere('modifier_par', $user->id)
                          ->orWhereHas('reunions', function ($rq) use ($user) {
                              $rq->where('creer_par', $user->id)
                                 ->orWhere('modifier_par', $user->id)
                                 ->orWhereHas('participants', function ($pq) use ($user) {
                                     $pq->where('user_id', $user->id);
                                 });
                          });
                    });
                }

            } else {
                // 🔒 NIVEAU 3 : Séries personnelles uniquement
                $query->where(function ($q) use ($user) {
                    $q->where('creer_par', $user->id)
                      ->orWhere('modifier_par', $user->id)
                      ->orWhereHas('reunions', function ($rq) use ($user) {
                          $rq->where('creer_par', $user->id)
                             ->orWhere('modifier_par', $user->id)
                             ->orWhereHas('participants', function ($pq) use ($user) {
                                 $pq->where('user_id', $user->id);
                             });
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

            // Filtre par périodicité
            if ($request->filled('periodicite')) {
                $query->where('periodicite', $request->periodicite);
            }

            // Filtre par créateur
            if ($request->filled('creer_par')) {
                $query->where('creer_par', $request->creer_par);
            }

            // Filtre par date de début
            if ($request->filled('date_debut')) {
                $query->where('date_debut', '>=', $request->date_debut);
            }

            // Filtre par date de fin
            if ($request->filled('date_fin')) {
                $query->where('date_fin', '<=', $request->date_fin);
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
            $sortBy = $request->get('sort_by', 'date_debut');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $series = $query->paginate($perPage);

            return [
                'success' => true,
                'data' => $series,
                'message' => 'Séries de réunions récupérées avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des séries de réunions', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des séries de réunions',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer une série spécifique
     */
    public function getSerie(int $id, User $user): array
    {
        try {
            $serie = ReunionSerie::with([
                'reunions.participants.user',
                'reunions.typeReunion',
                'createur',
                'modificateur'
            ])->find($id);

            if (!$serie) {
                return [
                    'success' => false,
                    'message' => 'Série de réunions non trouvée'
                ];
            }

            // Vérifier les permissions d'accès
            if (!$this->canAccessSerie($serie, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour accéder à cette série'
                ];
            }

            return [
                'success' => true,
                'data' => $serie,
                'message' => 'Série de réunions récupérée avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la série', [
                'serie_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération de la série',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Créer une nouvelle série de réunions
     */
    public function createSerie(array $data, User $user): array
    {
        try {
            DB::beginTransaction();

            // Vérifier les permissions de création
            if (!$this->canCreateSerie($user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour créer une série de réunions'
                ];
            }

            // Préparer les données selon la migration
            $serieData = [
                'nom' => $data['nom'],
                'description' => $data['description'] ?? '',
                'type_reunion_id' => $data['type_reunion_id'] ?? 1,
                'periodicite' => $data['periodicite'],
                'jour_semaine' => $data['jour_semaine'] ?? null,
                'jour_mois' => $data['jour_mois'] ?? null,
                'heure_debut' => $data['heure_debut'] ?? '09:00:00',
                'duree_minutes' => $data['duree_minutes'] ?? 120,
                'lieu_defaut' => $data['lieu_defaut'] ?? 'Salle de réunion',
                'actif' => $data['actif'] ?? true,
                'date_debut_serie' => $data['date_debut'],
                'date_fin_serie' => $data['date_fin'] ?? null,
                'suspendue' => $data['suspendue'] ?? false,
                'configuration_recurrence' => $data['configuration_recurrence'] ?? [],
                'creer_par' => $data['creer_par'] ?? $user->id,
                'modifier_par' => $data['modifier_par'] ?? $user->id,
            ];

            $serie = ReunionSerie::create($serieData);

            // Générer les réunions de la série si demandé
            if (isset($data['generer_reunions']) && $data['generer_reunions']) {
                $this->genererReunionsSerie($serie, $data);
            }

            DB::commit();

            // Charger les relations pour la réponse
            $serie->load([
                'reunions.participants.user',
                'reunions.typeReunion',
                'createur'
            ]);

            return [
                'success' => true,
                'data' => $serie,
                'message' => 'Série de réunions créée avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la série', [
                'user_id' => $user->id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la création de la série',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour une série de réunions
     */
    public function updateSerie(int $id, array $data, User $user): array
    {
        try {
            DB::beginTransaction();

            $serie = ReunionSerie::find($id);
            if (!$serie) {
                return [
                    'success' => false,
                    'message' => 'Série de réunions non trouvée'
                ];
            }

            // Vérifier les permissions de modification
            if (!$this->canUpdateSerie($serie, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier cette série'
                ];
            }

            // Préparer les données de mise à jour
            $updateData = array_filter([
                'nom' => $data['nom'] ?? null,
                'description' => $data['description'] ?? null,
                'periodicite' => $data['periodicite'] ?? null,
                'date_debut' => $data['date_debut'] ?? null,
                'date_fin' => $data['date_fin'] ?? null,
                'heure_debut' => $data['heure_debut'] ?? null,
                'heure_fin' => $data['heure_fin'] ?? null,
                'jour_semaine' => $data['jour_semaine'] ?? null,
                'jour_mois' => $data['jour_mois'] ?? null,
                'configuration_recurrence' => $data['configuration_recurrence'] ?? null,
                'actif' => $data['actif'] ?? null,
                'modifier_par' => $user->id,
                'date_modification' => now(),
            ], function ($value) {
                return $value !== null;
            });

            $serie->update($updateData);

            // Régénérer les réunions si demandé
            if (isset($data['regenerer_reunions']) && $data['regenerer_reunions']) {
                $this->regenererReunionsSerie($serie, $data);
            }

            DB::commit();

            // Charger les relations pour la réponse
            $serie->load([
                'reunions.participants.user',
                'reunions.typeReunion',
                'modificateur'
            ]);

            return [
                'success' => true,
                'data' => $serie,
                'message' => 'Série de réunions mise à jour avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de la série', [
                'serie_id' => $id,
                'user_id' => $user->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la série',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer une série de réunions
     */
    public function deleteSerie(int $id, User $user): array
    {
        try {
            $serie = ReunionSerie::find($id);
            if (!$serie) {
                return [
                    'success' => false,
                    'message' => 'Série de réunions non trouvée'
                ];
            }

            // Vérifier les permissions de suppression
            if (!$this->canDeleteSerie($serie, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour supprimer cette série'
                ];
            }

            // Vérifier si la série peut être supprimée
            if ($serie->reunions()->where('statut', '!=', 'ANNULEE')->exists()) {
                return [
                    'success' => false,
                    'message' => 'Impossible de supprimer une série avec des réunions actives'
                ];
            }

            DB::beginTransaction();

            // Supprimer les réunions de la série
            $serie->reunions()->delete();

            // Supprimer la série
            $serie->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Série de réunions supprimée avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de la série', [
                'serie_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression de la série',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Générer les réunions d'une série
     */
    public function genererReunionsSerie(ReunionSerie $serie, array $data = []): array
    {
        try {
            DB::beginTransaction();

            $reunionsGenerees = [];
            $dateDebut = Carbon::parse($serie->date_debut_serie);
            $dateFin = $serie->date_fin_serie ? Carbon::parse($serie->date_fin_serie) : $dateDebut->copy()->addMonths(6);
            $heureDebut = $serie->heure_debut;
            $heureFin = $serie->heure_fin; // Utilise l'accesseur getHeureFinAttribute()

            // Calculer les dates des réunions selon la périodicité
            $datesReunions = $this->calculerDatesReunions($serie, $dateDebut, $dateFin);

            foreach ($datesReunions as $dateReunion) {
                // Vérifier si une réunion existe déjà pour cette date
                $reunionExistante = $serie->reunions()
                    ->whereDate('date_debut', $dateReunion)
                    ->first();

                if (!$reunionExistante) {
                    // Créer la réunion
                    $reunionData = [
                        'titre' => $serie->nom,
                        'description' => $serie->description,
                        'type_reunion_id' => $data['type_reunion_id'] ?? $serie->type_reunion_id,
                        'niveau_complexite_actuel' => $serie->typeReunion->niveau_complexite ?? 'INTERMEDIAIRE',
                        'date_debut' => $dateReunion->format('Y-m-d') . ' ' . $heureDebut->format('H:i:s'),
                        'date_fin' => $dateReunion->format('Y-m-d') . ' ' . $heureFin,
                        'lieu' => $data['lieu'] ?? $serie->lieu_defaut,
                        'type_lieu' => $data['type_lieu'] ?? 'PHYSIQUE',
                        'periodicite' => 'PONCTUELLE',
                        'serie_id' => $serie->id,
                        'fonctionnalites_actives' => $serie->typeReunion->fonctionnalites_actives ?? [],
                        'ordre_du_jour_type' => 'EXPLICITE',
                        'statut' => 'PLANIFIEE',
                        'creer_par' => $serie->creer_par,
                        'modifier_par' => $serie->modifier_par,
                        'date_creation' => now(),
                        'date_modification' => now(),
                    ];

                    $reunion = Reunion::create($reunionData);

                    // Ajouter les participants par défaut si fournis
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

                    $reunionsGenerees[] = $reunion;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'data' => $reunionsGenerees,
                'message' => count($reunionsGenerees) . ' réunions générées avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la génération des réunions de la série', [
                'serie_id' => $serie->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la génération des réunions',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Régénérer les réunions d'une série
     */
    public function regenererReunionsSerie(ReunionSerie $serie, array $data = []): array
    {
        try {
            DB::beginTransaction();

            // Supprimer les réunions futures de la série
            $serie->reunions()
                ->where('date_debut', '>', now())
                ->where('statut', '!=', 'TERMINEE')
                ->delete();

            // Régénérer les réunions
            $result = $this->genererReunionsSerie($serie, $data);

            DB::commit();

            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la régénération des réunions de la série', [
                'serie_id' => $serie->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la régénération des réunions',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculer les dates des réunions selon la périodicité
     */
    private function calculerDatesReunions(ReunionSerie $serie, Carbon $dateDebut, Carbon $dateFin): array
    {
        $dates = [];
        $dateCourante = $dateDebut->copy();

        switch ($serie->periodicite) {
            case 'QUOTIDIENNE':
                while ($dateCourante <= $dateFin) {
                    $dates[] = $dateCourante->copy();
                    $dateCourante->addDay();
                }
                break;

            case 'HEBDOMADAIRE':
                while ($dateCourante <= $dateFin) {
                    $dates[] = $dateCourante->copy();
                    $dateCourante->addWeek();
                }
                break;

            case 'MENSUELLE':
                while ($dateCourante <= $dateFin) {
                    $dates[] = $dateCourante->copy();
                    $dateCourante->addMonth();
                }
                break;

            case 'TRIMESTRIELLE':
                while ($dateCourante <= $dateFin) {
                    $dates[] = $dateCourante->copy();
                    $dateCourante->addMonths(3);
                }
                break;

            case 'SEMESTRIELLE':
                while ($dateCourante <= $dateFin) {
                    $dates[] = $dateCourante->copy();
                    $dateCourante->addMonths(6);
                }
                break;

            case 'ANNUELLE':
                while ($dateCourante <= $dateFin) {
                    $dates[] = $dateCourante->copy();
                    $dateCourante->addYear();
                }
                break;

            default:
                $dates[] = $dateCourante->copy();
                break;
        }

        return $dates;
    }

    /**
     * Récupérer les statistiques des séries
     */
    public function getStats(User $user): array
    {
        try {
            $query = ReunionSerie::query();

            // Appliquer les mêmes filtres de permissions que pour la liste
            if (!$user->hasPermission('view_all_reunion_series')) {
                if ($user->hasPermission('view_my_entity_reunion_series')) {
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
                              ->orWhereHas('reunions', function ($rq) use ($utilisateursEntite) {
                                  $rq->whereIn('creer_par', $utilisateursEntite)
                                     ->orWhereIn('modifier_par', $utilisateursEntite)
                                     ->orWhereHas('participants', function ($pq) use ($utilisateursEntite) {
                                         $pq->whereIn('user_id', $utilisateursEntite);
                                     });
                              });
                        });
                    } else {
                        $query->where(function ($q) use ($user) {
                            $q->where('creer_par', $user->id)
                              ->orWhere('modifier_par', $user->id)
                              ->orWhereHas('reunions', function ($rq) use ($user) {
                                  $rq->where('creer_par', $user->id)
                                     ->orWhere('modifier_par', $user->id)
                                     ->orWhereHas('participants', function ($pq) use ($user) {
                                         $pq->where('user_id', $user->id);
                                     });
                              });
                        });
                    }
                } else {
                    $query->where(function ($q) use ($user) {
                        $q->where('creer_par', $user->id)
                          ->orWhere('modifier_par', $user->id)
                          ->orWhereHas('reunions', function ($rq) use ($user) {
                              $rq->where('creer_par', $user->id)
                                 ->orWhere('modifier_par', $user->id)
                                 ->orWhereHas('participants', function ($pq) use ($user) {
                                     $pq->where('user_id', $user->id);
                                 });
                          });
                    });
                }
            }

            $stats = [
                'total' => $query->count(),
                'actives' => $query->where('actif', true)->count(),
                'inactives' => $query->where('actif', false)->count(),
                'quotidiennes' => $query->where('periodicite', 'QUOTIDIENNE')->count(),
                'hebdomadaires' => $query->where('periodicite', 'HEBDOMADAIRE')->count(),
                'mensuelles' => $query->where('periodicite', 'MENSUELLE')->count(),
                'trimestrielles' => $query->where('periodicite', 'TRIMESTRIELLE')->count(),
                'semestrielles' => $query->where('periodicite', 'SEMESTRIELLE')->count(),
                'annuelles' => $query->where('periodicite', 'ANNUELLE')->count(),
            ];

            return [
                'success' => true,
                'data' => $stats,
                'message' => 'Statistiques des séries récupérées avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques des séries', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques des séries',
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
     * Vérifier si l'utilisateur peut accéder à la série
     */
    private function canAccessSerie(ReunionSerie $serie, User $user): bool
    {
        // L'utilisateur peut toujours accéder aux séries qu'il a créées
        if ($serie->creer_par === $user->id) {
            return true;
        }

        // L'utilisateur peut accéder aux séries qu'il a modifiées
        if ($serie->modifier_par === $user->id) {
            return true;
        }

        // L'utilisateur peut accéder aux séries où il est impliqué via les réunions
        if ($serie->reunions()->whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->exists()) {
            return true;
        }

        // Vérifier les permissions globales
        if ($user->hasPermission('view_all_reunion_series')) {
            return true;
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut créer une série
     */
    private function canCreateSerie(User $user): bool
    {
        return $user->hasPermission('create_reunion_series');
    }

    /**
     * Vérifier si l'utilisateur peut modifier une série
     */
    private function canUpdateSerie(ReunionSerie $serie, User $user): bool
    {
        // L'utilisateur peut toujours modifier les séries qu'il a créées
        if ($serie->creer_par === $user->id) {
            return true;
        }

        // Vérifier les permissions globales
        return $user->hasPermission('update_reunion_series');
    }

    /**
     * Vérifier si l'utilisateur peut supprimer une série
     */
    private function canDeleteSerie(ReunionSerie $serie, User $user): bool
    {
        // L'utilisateur peut toujours supprimer les séries qu'il a créées
        if ($serie->creer_par === $user->id) {
            return true;
        }

        // Vérifier les permissions globales
        return $user->hasPermission('delete_reunion_series');
    }
}
