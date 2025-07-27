<?php

namespace App\Services\Reunion;

use App\Models\ReunionObjectifDifficulte;
use App\Models\Reunion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ReunionDifficulteService
{
    /**
     * Créer une nouvelle difficulté de réunion
     */
    public function createDifficulte(array $data, int $reunionId, int $userId): ReunionObjectifDifficulte
    {
        try {
            DB::beginTransaction();

            // Vérifier que la réunion existe
            $reunion = Reunion::findOrFail($reunionId);

            $difficulte = ReunionObjectifDifficulte::create([
                'objectif_id' => $data['objectif_id'],
                'entite_id' => $data['entite_id'],
                'description_difficulte' => $data['description_difficulte'],
                'niveau_difficulte' => $data['niveau_difficulte'] ?? 'MOYEN',
                'impact' => $data['impact'],
                'solution_proposee' => $data['solution_proposee'] ?? null,
                'statut' => $data['statut'] ?? 'IDENTIFIEE',
                'creer_par' => $userId,
                'modifier_par' => $userId,
            ]);

            Log::info('Difficulté de réunion créée', [
                'difficulte_id' => $difficulte->id,
                'objectif_id' => $difficulte->objectif_id,
                'entite_id' => $difficulte->entite_id,
                'user_id' => $userId
            ]);

            DB::commit();
            return $difficulte;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la difficulté', [
                'objectif_id' => $data['objectif_id'] ?? null,
                'entite_id' => $data['entite_id'] ?? null,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Créer plusieurs difficultés de réunion en lot
     */
    public function createMultipleDifficultes(array $difficultesList, int $reunionId, int $userId): array
    {
        DB::beginTransaction();

        try {
            // Vérifier que la réunion existe
            $reunion = Reunion::findOrFail($reunionId);

            $difficultesCrees = [];
            $erreurs = [];

            foreach ($difficultesList as $index => $difficulteData) {
                try {
                    $difficulte = ReunionObjectifDifficulte::create([
                        'objectif_id' => $difficulteData['objectif_id'],
                        'entite_id' => $difficulteData['entite_id'],
                        'description_difficulte' => $difficulteData['description_difficulte'],
                        'niveau_difficulte' => $difficulteData['niveau_difficulte'] ?? 'MOYEN',
                        'impact' => $difficulteData['impact'],
                        'solution_proposee' => $difficulteData['solution_proposee'] ?? null,
                        'statut' => $difficulteData['statut'] ?? 'IDENTIFIEE',
                        'creer_par' => $userId,
                        'modifier_par' => $userId,
                    ]);

                    $difficultesCrees[] = $difficulte;

                    Log::info('Difficulté de réunion créée en lot', [
                        'difficulte_id' => $difficulte->id,
                        'objectif_id' => $difficulte->objectif_id,
                        'entite_id' => $difficulte->entite_id,
                        'user_id' => $userId
                    ]);

                } catch (Exception $e) {
                    $erreurs[] = [
                        'index' => $index,
                        'error' => $e->getMessage()
                    ];
                }
            }

            if (!empty($erreurs)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Erreurs lors de la création des difficultés',
                    'errors' => $erreurs
                ];
            }

            DB::commit();

            return [
                'success' => true,
                'data' => $difficultesCrees,
                'message' => count($difficultesCrees) . ' difficultés créées avec succès'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création multiple des difficultés', [
                'reunion_id' => $reunionId,
                'difficultes_list' => $difficultesList,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la création multiple des difficultés',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour une difficulté de réunion
     */
    public function updateDifficulte(int $difficulteId, array $data): ReunionObjectifDifficulte
    {
        try {
            DB::beginTransaction();

            $difficulte = ReunionObjectifDifficulte::findOrFail($difficulteId);

            $difficulte->update([
                'description_difficulte' => $data['description_difficulte'] ?? $difficulte->description_difficulte,
                'niveau_difficulte' => $data['niveau_difficulte'] ?? $difficulte->niveau_difficulte,
                'impact' => $data['impact'] ?? $difficulte->impact,
                'solution_proposee' => $data['solution_proposee'] ?? $difficulte->solution_proposee,
                'statut' => $data['statut'] ?? $difficulte->statut,
                'modifier_par' => auth()->id(),
            ]);

            Log::info('Difficulté de réunion mise à jour', [
                'difficulte_id' => $difficulte->id,
                'objectif_id' => $difficulte->objectif_id,
                'user_id' => auth()->id()
            ]);

            DB::commit();
            return $difficulte;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de la difficulté', [
                'difficulte_id' => $difficulteId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * Supprimer une difficulté de réunion
     */
    public function deleteDifficulte(int $difficulteId): bool
    {
        try {
            DB::beginTransaction();

            $difficulte = ReunionObjectifDifficulte::findOrFail($difficulteId);
            $objectifId = $difficulte->objectif_id;

            $difficulte->delete();

            Log::info('Difficulté de réunion supprimée', [
                'difficulte_id' => $difficulteId,
                'objectif_id' => $objectifId
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de la difficulté', [
                'difficulte_id' => $difficulteId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer toutes les difficultés d'une réunion
     */
    public function getDifficultes(int $reunionId, array $filters = []): array
    {
        try {
            $query = ReunionObjectifDifficulte::whereHas('objectif.sujet.ordreJour', function ($q) use ($reunionId) {
                $q->where('reunion_id', $reunionId);
            })->with(['objectif', 'entite', 'createur', 'modificateur']);

            // Filtres
            if (isset($filters['statut'])) {
                $query->where('statut', $filters['statut']);
            }

            if (isset($filters['niveau_difficulte'])) {
                $query->where('niveau_difficulte', $filters['niveau_difficulte']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('description_difficulte', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('impact', 'like', '%' . $filters['search'] . '%');
                });
            }

            $difficultes = $query->orderBy('niveau_difficulte', 'desc')->orderBy('date_creation')->get();

            return [
                'difficultes' => $difficultes,
                'total' => $difficultes->count(),
                'filters_applied' => $filters
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des difficultés', [
                'reunion_id' => $reunionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer une difficulté spécifique
     */
    public function getDifficulte(int $difficulteId): ReunionObjectifDifficulte
    {
        try {
            return ReunionObjectifDifficulte::with(['objectif', 'entite', 'createur', 'modificateur'])->findOrFail($difficulteId);
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération de la difficulté', [
                'difficulte_id' => $difficulteId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Changer le statut d'une difficulté
     */
    public function changeStatut(int $difficulteId, string $nouveauStatut): ReunionObjectifDifficulte
    {
        try {
            DB::beginTransaction();

            $difficulte = ReunionObjectifDifficulte::findOrFail($difficulteId);
            $ancienStatut = $difficulte->statut;

            $difficulte->update([
                'statut' => $nouveauStatut,
                'modifier_par' => auth()->id(),
            ]);

            Log::info('Statut de la difficulté changé', [
                'difficulte_id' => $difficulte->id,
                'ancien_statut' => $ancienStatut,
                'nouveau_statut' => $nouveauStatut
            ]);

            DB::commit();
            return $difficulte;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du changement de statut de la difficulté', [
                'difficulte_id' => $difficulteId,
                'nouveau_statut' => $nouveauStatut,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Mettre à jour la progression de résolution
     */
    public function updateProgressionResolution(int $difficulteId, int $progression): ReunionObjectifDifficulte
    {
        try {
            DB::beginTransaction();

            $difficulte = ReunionObjectifDifficulte::findOrFail($difficulteId);

            // Valider la progression (0-100)
            $progression = max(0, min(100, $progression));

            $difficulte->update([
                'statut' => $progression >= 100 ? 'RESOLUE' : 'EN_COURS_RESOLUTION',
                'modifier_par' => auth()->id(),
            ]);

            Log::info('Progression de résolution mise à jour', [
                'difficulte_id' => $difficulte->id,
                'nouvelle_progression' => $progression
            ]);

            DB::commit();
            return $difficulte;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de la progression de résolution', [
                'difficulte_id' => $difficulteId,
                'progression' => $progression,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Ajouter une solution proposée
     */
    public function ajouterSolution(int $difficulteId, array $solution): ReunionObjectifDifficulte
    {
        try {
            DB::beginTransaction();

            $difficulte = ReunionObjectifDifficulte::findOrFail($difficulteId);

            $difficulte->update([
                'solution_proposee' => $solution['description'],
                'modifier_par' => auth()->id(),
            ]);

            Log::info('Solution ajoutée à la difficulté', [
                'difficulte_id' => $difficulte->id,
                'solution' => $solution['description']
            ]);

            DB::commit();
            return $difficulte;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'ajout de la solution', [
                'difficulte_id' => $difficulteId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtenir les statistiques des difficultés
     */
    public function getStats(int $reunionId): array
    {
        try {
            $stats = ReunionObjectifDifficulte::whereHas('objectif.sujet.ordreJour', function ($q) use ($reunionId) {
                $q->where('reunion_id', $reunionId);
            })
                ->selectRaw('
                    COUNT(*) as total_difficultes,
                    COUNT(CASE WHEN statut = "IDENTIFIEE" THEN 1 END) as identifiees,
                    COUNT(CASE WHEN statut = "EN_COURS_RESOLUTION" THEN 1 END) as en_cours,
                    COUNT(CASE WHEN statut = "RESOLUE" THEN 1 END) as resolues,
                    COUNT(CASE WHEN niveau_difficulte = "CRITIQUE" THEN 1 END) as gravite_critique,
                    COUNT(CASE WHEN niveau_difficulte = "ELEVE" THEN 1 END) as gravite_elevee,
                    COUNT(CASE WHEN niveau_difficulte = "MOYEN" THEN 1 END) as gravite_moyenne,
                    COUNT(CASE WHEN niveau_difficulte = "FAIBLE" THEN 1 END) as gravite_faible
                ')
                ->first();

            return [
                'total_difficultes' => $stats->total_difficultes,
                'par_statut' => [
                    'identifiees' => $stats->identifiees,
                    'en_cours' => $stats->en_cours,
                    'resolues' => $stats->resolues,
                ],
                'par_gravite' => [
                    'critique' => $stats->gravite_critique,
                    'elevee' => $stats->gravite_elevee,
                    'moyenne' => $stats->gravite_moyenne,
                    'faible' => $stats->gravite_faible,
                ]
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors du calcul des statistiques des difficultés', [
                'reunion_id' => $reunionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Analyser les risques et difficultés
     */
    public function analyserRisques(int $reunionId): array
    {
        try {
            $difficultes = ReunionObjectifDifficulte::whereHas('objectif.sujet.ordreJour', function ($q) use ($reunionId) {
                $q->where('reunion_id', $reunionId);
            })->get();

            $analyse = [
                'total_difficultes' => $difficultes->count(),
                'difficultes_critiques' => $difficultes->where('niveau_difficulte', 'CRITIQUE')->count(),
                'difficultes_elevees' => $difficultes->where('niveau_difficulte', 'ELEVE')->count(),
                'difficultes_non_resolues' => $difficultes->whereIn('statut', ['IDENTIFIEE', 'EN_COURS_RESOLUTION'])->count(),
                'difficultes_en_retard' => 0,
                'niveau_risque_global' => 'faible',
                'recommandations' => [],
            ];

            // Déterminer le niveau de risque global
            $scoreRisque = ($analyse['difficultes_critiques'] * 3) +
                          ($analyse['difficultes_elevees'] * 2) +
                          ($analyse['difficultes_non_resolues'] * 1);

            if ($scoreRisque >= 10) {
                $analyse['niveau_risque_global'] = 'critique';
            } elseif ($scoreRisque >= 6) {
                $analyse['niveau_risque_global'] = 'eleve';
            } elseif ($scoreRisque >= 3) {
                $analyse['niveau_risque_global'] = 'modere';
            }

            // Générer des recommandations
            if ($analyse['difficultes_critiques'] > 0) {
                $analyse['recommandations'][] = 'Prioriser la résolution des difficultés critiques';
            }
            if ($analyse['difficultes_non_resolues'] > 5) {
                $analyse['recommandations'][] = 'Considérer l\'escalade des difficultés non résolues';
            }

            return $analyse;

        } catch (Exception $e) {
            Log::error('Erreur lors de l\'analyse des risques', [
                'reunion_id' => $reunionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
