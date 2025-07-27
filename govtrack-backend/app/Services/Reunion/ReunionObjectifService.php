<?php

namespace App\Services\Reunion;

use App\Models\ReunionSujetObjectif;
use App\Models\Reunion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ReunionObjectifService
{
    /**
     * Créer un nouvel objectif de réunion
     */
    public function createObjectif(array $data, int $userId): ReunionSujetObjectif
    {
        try {
            DB::beginTransaction();

            $objectif = ReunionSujetObjectif::create([
                'reunion_sujet_id' => $data['reunion_sujet_id'],
                'titre' => $data['titre'],
                'description' => $data['description'],
                'cible' => $data['cible'],
                'taux_realisation' => $data['taux_realisation'] ?? 0,
                'pourcentage_decaissement' => $data['pourcentage_decaissement'] ?? 0,
                'date_objectif' => $data['date_objectif'],
                'statut' => $data['statut'] ?? 'EN_COURS',
                'ordre' => $data['ordre'] ?? 1,
                'actif' => $data['actif'] ?? true,
                'creer_par' => $userId,
                'modifier_par' => $userId,
            ]);

            Log::info('Objectif de réunion créé', [
                'objectif_id' => $objectif->id,
                'titre' => $objectif->titre,
                'user_id' => $userId
            ]);

            DB::commit();
            return $objectif;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'objectif', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Créer plusieurs objectifs de réunion en lot
     */
    public function createMultipleObjectifs(array $objectifsList, int $userId): array
    {
        DB::beginTransaction();

        try {
            $objectifsCrees = [];
            $erreurs = [];

            foreach ($objectifsList as $index => $objectifData) {
                try {
                    $objectif = ReunionSujetObjectif::create([
                        'reunion_sujet_id' => $objectifData['reunion_sujet_id'],
                        'titre' => $objectifData['titre'],
                        'description' => $objectifData['description'],
                        'cible' => $objectifData['cible'],
                        'taux_realisation' => $objectifData['taux_realisation'] ?? 0,
                        'pourcentage_decaissement' => $objectifData['pourcentage_decaissement'] ?? 0,
                        'date_objectif' => $objectifData['date_objectif'],
                        'statut' => $objectifData['statut'] ?? 'EN_COURS',
                        'ordre' => $objectifData['ordre'] ?? 1,
                        'actif' => $objectifData['actif'] ?? true,
                        'creer_par' => $userId,
                        'modifier_par' => $userId,
                    ]);

                    $objectifsCrees[] = $objectif;

                    Log::info('Objectif de réunion créé en lot', [
                        'objectif_id' => $objectif->id,
                        'titre' => $objectif->titre,
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
                    'message' => 'Erreurs lors de la création des objectifs',
                    'errors' => $erreurs
                ];
            }

            DB::commit();

            return [
                'success' => true,
                'data' => $objectifsCrees,
                'message' => count($objectifsCrees) . ' objectifs créés avec succès'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création multiple des objectifs', [
                'objectifs_list' => $objectifsList,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la création multiple des objectifs',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour un objectif de réunion
     */
    public function updateObjectif(int $objectifId, array $data, int $userId): ReunionSujetObjectif
    {
        try {
            DB::beginTransaction();

            $objectif = ReunionSujetObjectif::findOrFail($objectifId);

            $objectif->update([
                'titre' => $data['titre'] ?? $objectif->titre,
                'description' => $data['description'] ?? $objectif->description,
                'cible' => $data['cible'] ?? $objectif->cible,
                'taux_realisation' => $data['taux_realisation'] ?? $objectif->taux_realisation,
                'pourcentage_decaissement' => $data['pourcentage_decaissement'] ?? $objectif->pourcentage_decaissement,
                'date_objectif' => $data['date_objectif'] ?? $objectif->date_objectif,
                'statut' => $data['statut'] ?? $objectif->statut,
                'ordre' => $data['ordre'] ?? $objectif->ordre,
                'actif' => $data['actif'] ?? $objectif->actif,
                'modifier_par' => $userId,
            ]);

            Log::info('Objectif de réunion mis à jour', [
                'objectif_id' => $objectif->id,
                'user_id' => $userId
            ]);

            DB::commit();
            return $objectif;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de l\'objectif', [
                'objectif_id' => $objectifId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Supprimer un objectif de réunion
     */
    public function deleteObjectif(int $objectifId, int $userId): bool
    {
        try {
            DB::beginTransaction();

            $objectif = ReunionSujetObjectif::findOrFail($objectifId);
            $sujetId = $objectif->reunion_sujet_id;

            $objectif->delete();

            Log::info('Objectif de réunion supprimé', [
                'objectif_id' => $objectifId,
                'sujet_id' => $sujetId,
                'user_id' => $userId
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de l\'objectif', [
                'objectif_id' => $objectifId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer tous les objectifs d'un sujet
     */
    public function getObjectifs(int $sujetId, array $filters = []): array
    {
        try {
            $query = ReunionSujetObjectif::where('reunion_sujet_id', $sujetId)
                ->with(['sujet']);

            // Filtres
            if (isset($filters['statut'])) {
                $query->where('statut', $filters['statut']);
            }

            if (isset($filters['actif'])) {
                $query->where('actif', $filters['actif']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('titre', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('description', 'like', '%' . $filters['search'] . '%');
                });
            }

            $objectifs = $query->orderBy('ordre')->orderBy('date_creation')->get();

            return [
                'objectifs' => $objectifs,
                'total' => $objectifs->count(),
                'filters_applied' => $filters
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des objectifs', [
                'sujet_id' => $sujetId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer un objectif spécifique
     */
    public function getObjectif(int $objectifId): ReunionSujetObjectif
    {
        try {
            return ReunionSujetObjectif::with(['sujet'])->findOrFail($objectifId);
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération de l\'objectif', [
                'objectif_id' => $objectifId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Changer le statut d'un objectif
     */
    public function changeStatut(int $objectifId, string $nouveauStatut, int $userId): ReunionSujetObjectif
    {
        try {
            DB::beginTransaction();

            $objectif = ReunionSujetObjectif::findOrFail($objectifId);
            $ancienStatut = $objectif->statut;

            $objectif->update([
                'statut' => $nouveauStatut,
                'modifier_par' => $userId,
            ]);

            Log::info('Statut de l\'objectif changé', [
                'objectif_id' => $objectif->id,
                'ancien_statut' => $ancienStatut,
                'nouveau_statut' => $nouveauStatut,
                'user_id' => $userId
            ]);

            DB::commit();
            return $objectif;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du changement de statut de l\'objectif', [
                'objectif_id' => $objectifId,
                'nouveau_statut' => $nouveauStatut,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Mettre à jour la progression d'un objectif
     */
    public function updateProgression(int $objectifId, int $progression, int $userId): ReunionSujetObjectif
    {
        try {
            DB::beginTransaction();

            $objectif = ReunionSujetObjectif::findOrFail($objectifId);
            $ancienneProgression = $objectif->taux_realisation;

            // Valider la progression (0-100)
            $progression = max(0, min(100, $progression));

            $objectif->update([
                'taux_realisation' => $progression,
                'modifier_par' => $userId,
            ]);

            Log::info('Progression de l\'objectif mise à jour', [
                'objectif_id' => $objectif->id,
                'ancienne_progression' => $ancienneProgression,
                'nouvelle_progression' => $progression,
                'user_id' => $userId
            ]);

            DB::commit();
            return $objectif;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de la progression', [
                'objectif_id' => $objectifId,
                'progression' => $progression,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Obtenir les statistiques des objectifs
     */
    public function getStats(int $sujetId): array
    {
        try {
            $stats = ReunionSujetObjectif::where('reunion_sujet_id', $sujetId)
                ->selectRaw('
                    COUNT(*) as total_objectifs,
                    COUNT(CASE WHEN statut = "EN_COURS" THEN 1 END) as en_cours,
                    COUNT(CASE WHEN statut = "ATTEINT" THEN 1 END) as atteint,
                    COUNT(CASE WHEN statut = "EN_RETARD" THEN 1 END) as en_retard,
                    COUNT(CASE WHEN actif = 1 THEN 1 END) as actifs,
                    AVG(taux_realisation) as progression_moyenne
                ')
                ->first();

            return [
                'total_objectifs' => $stats->total_objectifs,
                'par_statut' => [
                    'en_cours' => $stats->en_cours,
                    'atteint' => $stats->atteint,
                    'en_retard' => $stats->en_retard,
                ],
                'actifs' => $stats->actifs,
                'progression_moyenne' => round($stats->progression_moyenne, 2)
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors du calcul des statistiques des objectifs', [
                'sujet_id' => $sujetId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Évaluer la réalisation des objectifs
     */
    public function evaluerRealisation(int $sujetId): array
    {
        try {
            $objectifs = ReunionSujetObjectif::where('reunion_sujet_id', $sujetId)->get();

            $evaluation = [
                'total_objectifs' => $objectifs->count(),
                'objectifs_realises' => $objectifs->where('statut', 'ATTEINT')->count(),
                'objectifs_en_cours' => $objectifs->where('statut', 'EN_COURS')->count(),
                'objectifs_en_retard' => $objectifs->where('statut', 'EN_RETARD')->count(),
                'taux_realisation' => 0,
                'progression_moyenne' => 0,
                'objectifs_en_retard_date' => 0,
            ];

            if ($evaluation['total_objectifs'] > 0) {
                $evaluation['taux_realisation'] = round(
                    ($evaluation['objectifs_realises'] / $evaluation['total_objectifs']) * 100,
                    2
                );
                $evaluation['progression_moyenne'] = round($objectifs->avg('taux_realisation'), 2);

                // Objectifs en retard (taux_realisation < 50% et date_objectif dépassée)
                $evaluation['objectifs_en_retard_date'] = $objectifs
                    ->where('taux_realisation', '<', 50)
                    ->where('date_objectif', '<', now())
                    ->count();
            }

            return $evaluation;

        } catch (Exception $e) {
            Log::error('Erreur lors de l\'évaluation des objectifs', [
                'sujet_id' => $sujetId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
