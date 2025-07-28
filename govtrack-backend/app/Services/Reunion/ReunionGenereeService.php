<?php

namespace App\Services\Reunion;

use App\Models\ReunionGeneree;
use App\Models\ReunionSerie;
use App\Models\Reunion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ReunionGenereeService
{
    /**
     * Créer un enregistrement de réunion générée
     */
    public function createReunionGeneree(int $serieId, int $reunionId, string $statut = 'SUCCES', string $messageErreur = null, array $configurationUtilisee = [], int $userId): ReunionGeneree
    {
        try {
            DB::beginTransaction();

            $reunionGeneree = ReunionGeneree::create([
                'serie_id' => $serieId,
                'reunion_id' => $reunionId,
                'genere_le' => now(),
                'statut_generation' => $statut,
                'message_erreur' => $messageErreur,
                'configuration_utilisee' => $configurationUtilisee,
            ]);

            Log::info('Enregistrement de réunion générée créé', [
                'reunion_generee_id' => $reunionGeneree->id,
                'serie_id' => $serieId,
                'reunion_id' => $reunionId,
                'statut' => $statut,
                'user_id' => $userId
            ]);

            DB::commit();
            return $reunionGeneree;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'enregistrement de réunion générée', [
                'serie_id' => $serieId,
                'reunion_id' => $reunionId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer les réunions générées d'une série
     */
    public function getReunionsGenerees(int $serieId, array $filters = []): array
    {
        try {
            $query = ReunionGeneree::with(['serie', 'reunion'])
                ->where('serie_id', $serieId);

            // Filtres
            if (isset($filters['statut_generation'])) {
                $query->where('statut_generation', $filters['statut_generation']);
            }

            if (isset($filters['date_debut'])) {
                $query->where('genere_le', '>=', $filters['date_debut']);
            }

            if (isset($filters['date_fin'])) {
                $query->where('genere_le', '<=', $filters['date_fin']);
            }

            $reunionsGenerees = $query->orderBy('genere_le', 'desc')->get();

            return [
                'reunions_generees' => $reunionsGenerees,
                'total' => $reunionsGenerees->count(),
                'filters_applied' => $filters
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des réunions générées', [
                'serie_id' => $serieId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer une réunion générée spécifique
     */
    public function getReunionGeneree(int $reunionGenereeId): ReunionGeneree
    {
        try {
            return ReunionGeneree::with(['serie', 'reunion'])->findOrFail($reunionGenereeId);
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération de la réunion générée', [
                'reunion_generee_id' => $reunionGenereeId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * Mettre à jour le statut d'une réunion générée
     */
    public function updateStatut(int $reunionGenereeId, string $statut, string $messageErreur = null, int $userId): ReunionGeneree
    {
        try {
            DB::beginTransaction();

            $reunionGeneree = ReunionGeneree::findOrFail($reunionGenereeId);
            $ancienStatut = $reunionGeneree->statut_generation;

            $reunionGeneree->update([
                'statut_generation' => $statut,
                'message_erreur' => $messageErreur,
            ]);

            Log::info('Statut de réunion générée mis à jour', [
                'reunion_generee_id' => $reunionGeneree->id,
                'ancien_statut' => $ancienStatut,
                'nouveau_statut' => $statut,
                'user_id' => $userId
            ]);

            DB::commit();
            return $reunionGeneree;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du statut de réunion générée', [
                'reunion_generee_id' => $reunionGenereeId,
                'statut' => $statut,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Supprimer un enregistrement de réunion générée
     */
    public function deleteReunionGeneree(int $reunionGenereeId, int $userId): bool
    {
        try {
            DB::beginTransaction();

            $reunionGeneree = ReunionGeneree::findOrFail($reunionGenereeId);
            $serieId = $reunionGeneree->serie_id;

            $reunionGeneree->delete();

            Log::info('Enregistrement de réunion générée supprimé', [
                'reunion_generee_id' => $reunionGenereeId,
                'serie_id' => $serieId,
                'user_id' => $userId
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de l\'enregistrement de réunion générée', [
                'reunion_generee_id' => $reunionGenereeId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Obtenir les statistiques des réunions générées
     */
    public function getStats(int $serieId = null): array
    {
        try {
            $query = ReunionGeneree::query();

            if ($serieId) {
                $query->where('serie_id', $serieId);
            }

            $stats = $query->selectRaw('
                COUNT(*) as total_generations,
                COUNT(CASE WHEN statut_generation = "SUCCES" THEN 1 END) as succes,
                COUNT(CASE WHEN statut_generation = "ERREUR" THEN 1 END) as erreurs,
                AVG(CASE WHEN statut_generation = "SUCCES" THEN 1 ELSE 0 END) * 100 as taux_succes
            ')
            ->first();

            return [
                'total_generations' => $stats->total_generations,
                'succes' => $stats->succes,
                'erreurs' => $stats->erreurs,
                'taux_succes' => round($stats->taux_succes, 2)
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors du calcul des statistiques des réunions générées', [
                'serie_id' => $serieId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * Nettoyer les anciens enregistrements de réunions générées
     */
    public function nettoyerAnciensEnregistrements(int $joursConservation = 90, int $userId): int
    {
        try {
            DB::beginTransaction();

            $dateLimite = now()->subDays($joursConservation);

            $nombreSupprime = ReunionGeneree::where('genere_le', '<', $dateLimite)
                ->where('statut_generation', 'SUCCES')
                ->delete();

            Log::info('Nettoyage des anciens enregistrements de réunions générées', [
                'nombre_supprime' => $nombreSupprime,
                'jours_conservation' => $joursConservation,
                'date_limite' => $dateLimite,
                'user_id' => $userId
            ]);

            DB::commit();
            return $nombreSupprime;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du nettoyage des anciens enregistrements', [
                'jours_conservation' => $joursConservation,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }
}
