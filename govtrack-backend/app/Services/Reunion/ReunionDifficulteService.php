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
     * Mettre à jour une difficulté de réunion
     */
    public function updateDifficulte(int $difficulteId, array $data): ReunionObjectifDifficulte
    {
        try {
            DB::beginTransaction();

            $difficulte = ReunionObjectifDifficulte::findOrFail($difficulteId);

            $difficulte->update([
                'titre' => $data['titre'] ?? $difficulte->titre,
                'description' => $data['description'] ?? $difficulte->description,
                'type_difficulte' => $data['type_difficulte'] ?? $difficulte->type_difficulte,
                'niveau_gravite' => $data['niveau_gravite'] ?? $difficulte->niveau_gravite,
                'statut' => $data['statut'] ?? $difficulte->statut,
                'impact_estime' => $data['impact_estime'] ?? $difficulte->impact_estime,
                'solutions_proposees' => $data['solutions_proposees'] ?? $difficulte->solutions_proposees,
                'actions_mitigation' => $data['actions_mitigation'] ?? $difficulte->actions_mitigation,
                'responsable_id' => $data['responsable_id'] ?? $difficulte->responsable_id,
                'date_limite_resolution' => $data['date_limite_resolution'] ?? $difficulte->date_limite_resolution,
                'progression_resolution' => $data['progression_resolution'] ?? $difficulte->progression_resolution,
                'notes' => $data['notes'] ?? $difficulte->notes,
                'modifier_par' => auth()->id(),
            ]);

            Log::info('Difficulté de réunion mise à jour', [
                'difficulte_id' => $difficulte->id,
                'reunion_id' => $difficulte->reunion_id,
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
            $reunionId = $difficulte->reunion_id;

            $difficulte->delete();

            Log::info('Difficulté de réunion supprimée', [
                'difficulte_id' => $difficulteId,
                'reunion_id' => $reunionId,
                'user_id' => auth()->id()
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de la difficulté', [
                'difficulte_id' => $difficulteId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
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
            $query = ReunionObjectifDifficulte::where('reunion_id', $reunionId)
                ->with(['responsable', 'reunion']);

            // Filtres
            if (isset($filters['statut'])) {
                $query->where('statut', $filters['statut']);
            }

            if (isset($filters['type_difficulte'])) {
                $query->where('type_difficulte', $filters['type_difficulte']);
            }

            if (isset($filters['niveau_gravite'])) {
                $query->where('niveau_gravite', $filters['niveau_gravite']);
            }

            if (isset($filters['impact_estime'])) {
                $query->where('impact_estime', $filters['impact_estime']);
            }

            if (isset($filters['responsable_id'])) {
                $query->where('responsable_id', $filters['responsable_id']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('titre', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('description', 'like', '%' . $filters['search'] . '%');
                });
            }

            $difficultes = $query->orderBy('niveau_gravite', 'desc')->orderBy('created_at')->get();

            return [
                'difficultes' => $difficultes,
                'total' => $difficultes->count(),
                'filters_applied' => $filters
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des difficultés', [
                'reunion_id' => $reunionId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
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
            return ReunionObjectifDifficulte::with(['responsable', 'reunion'])->findOrFail($difficulteId);
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération de la difficulté', [
                'difficulte_id' => $difficulteId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
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
                'updated_by' => auth()->id(),
            ]);

            Log::info('Statut de la difficulté changé', [
                'difficulte_id' => $difficulte->id,
                'ancien_statut' => $ancienStatut,
                'nouveau_statut' => $nouveauStatut,
                'user_id' => auth()->id()
            ]);

            DB::commit();
            return $difficulte;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du changement de statut de la difficulté', [
                'difficulte_id' => $difficulteId,
                'nouveau_statut' => $nouveauStatut,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
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
            $ancienneProgression = $difficulte->progression_resolution;

            // Valider la progression (0-100)
            $progression = max(0, min(100, $progression));

            $difficulte->update([
                'progression_resolution' => $progression,
                'updated_by' => auth()->id(),
            ]);

            Log::info('Progression de résolution mise à jour', [
                'difficulte_id' => $difficulte->id,
                'ancienne_progression' => $ancienneProgression,
                'nouvelle_progression' => $progression,
                'user_id' => auth()->id()
            ]);

            DB::commit();
            return $difficulte;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de la progression de résolution', [
                'difficulte_id' => $difficulteId,
                'progression' => $progression,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
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
            $solutions = $difficulte->solutions_proposees ?? [];

            $solutions[] = [
                'id' => uniqid(),
                'description' => $solution['description'],
                'proposee_par' => auth()->id(),
                'date_proposition' => now()->toISOString(),
                'efficacite_estimee' => $solution['efficacite_estimee'] ?? 'moyenne',
                'cout_estime' => $solution['cout_estime'] ?? null,
                'delai_implementation' => $solution['delai_implementation'] ?? null,
            ];

            $difficulte->update([
                'solutions_proposees' => $solutions,
                'updated_by' => auth()->id(),
            ]);

            Log::info('Solution ajoutée à la difficulté', [
                'difficulte_id' => $difficulte->id,
                'solution_id' => end($solutions)['id'],
                'user_id' => auth()->id()
            ]);

            DB::commit();
            return $difficulte;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'ajout de la solution', [
                'difficulte_id' => $difficulteId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
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
            $stats = ReunionObjectifDifficulte::where('reunion_id', $reunionId)
                ->selectRaw('
                    COUNT(*) as total_difficultes,
                    COUNT(CASE WHEN statut = "ouverte" THEN 1 END) as ouvertes,
                    COUNT(CASE WHEN statut = "en_cours" THEN 1 END) as en_cours,
                    COUNT(CASE WHEN statut = "resolue" THEN 1 END) as resolues,
                    COUNT(CASE WHEN statut = "escaladee" THEN 1 END) as escaladees,
                    COUNT(CASE WHEN niveau_gravite = "critique" THEN 1 END) as gravite_critique,
                    COUNT(CASE WHEN niveau_gravite = "elevee" THEN 1 END) as gravite_elevee,
                    COUNT(CASE WHEN niveau_gravite = "moyenne" THEN 1 END) as gravite_moyenne,
                    COUNT(CASE WHEN niveau_gravite = "faible" THEN 1 END) as gravite_faible,
                    AVG(progression_resolution) as progression_moyenne
                ')
                ->first();

            return [
                'total_difficultes' => $stats->total_difficultes,
                'par_statut' => [
                    'ouvertes' => $stats->ouvertes,
                    'en_cours' => $stats->en_cours,
                    'resolues' => $stats->resolues,
                    'escaladees' => $stats->escaladees,
                ],
                'par_gravite' => [
                    'critique' => $stats->gravite_critique,
                    'elevee' => $stats->gravite_elevee,
                    'moyenne' => $stats->gravite_moyenne,
                    'faible' => $stats->gravite_faible,
                ],
                'progression_moyenne' => round($stats->progression_moyenne, 2)
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors du calcul des statistiques des difficultés', [
                'reunion_id' => $reunionId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
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
            $difficultes = ReunionObjectifDifficulte::where('reunion_id', $reunionId)->get();

            $analyse = [
                'total_difficultes' => $difficultes->count(),
                'difficultes_critiques' => $difficultes->where('niveau_gravite', 'critique')->count(),
                'difficultes_elevees' => $difficultes->where('niveau_gravite', 'elevee')->count(),
                'difficultes_non_resolues' => $difficultes->whereIn('statut', ['ouverte', 'en_cours'])->count(),
                'difficultes_en_retard' => 0,
                'niveau_risque_global' => 'faible',
                'recommandations' => [],
            ];

            // Calculer les difficultés en retard
            $analyse['difficultes_en_retard'] = $difficultes
                ->where('date_limite_resolution', '<', now())
                ->whereIn('statut', ['ouverte', 'en_cours'])
                ->count();

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
            if ($analyse['difficultes_en_retard'] > 0) {
                $analyse['recommandations'][] = 'Accélérer la résolution des difficultés en retard';
            }
            if ($analyse['difficultes_non_resolues'] > 5) {
                $analyse['recommandations'][] = 'Considérer l\'escalade des difficultés non résolues';
            }

            return $analyse;

        } catch (Exception $e) {
            Log::error('Erreur lors de l\'analyse des risques', [
                'reunion_id' => $reunionId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }
}
