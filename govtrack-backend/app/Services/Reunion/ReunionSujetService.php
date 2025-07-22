<?php

namespace App\Services\Reunion;

use App\Models\ReunionSujet;
use App\Models\Reunion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ReunionSujetService
{
    /**
     * Créer un nouveau sujet de réunion
     */
    public function createSujet(array $data, int $reunionId, int $userId): ReunionSujet
    {
        try {
            DB::beginTransaction();

            // Vérifier que la réunion existe
            $reunion = Reunion::findOrFail($reunionId);

            $sujet = ReunionSujet::create([
                'reunion_ordre_jour_id' => $data['reunion_ordre_jour_id'],
                'titre' => $data['titre'],
                'description' => $data['description'],
                'difficulte_globale' => $data['difficulte_globale'] ?? null,
                'recommandation' => $data['recommandation'] ?? null,
                'statut' => $data['statut'] ?? 'EN_ATTENTE',
                'commentaire' => $data['commentaire'] ?? null,
                'pieces_jointes' => $data['pieces_jointes'] ?? [],
                'projet_id' => $data['projet_id'] ?? null,
                'entite_id' => $data['entite_id'] ?? null,
                'niveau_detail' => $data['niveau_detail'] ?? 'SIMPLE',
                'objectifs_actifs' => $data['objectifs_actifs'] ?? false,
                'difficultes_actives' => $data['difficultes_actives'] ?? false,
                'creer_par' => $userId,
                'modifier_par' => $userId,
            ]);

            Log::info('Sujet de réunion créé', [
                'sujet_id' => $sujet->id,
                'reunion_id' => $reunionId,
                'titre' => $sujet->titre,
                'user_id' => $userId
            ]);

            DB::commit();
            return $sujet;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du sujet', [
                'reunion_id' => $reunionId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Mettre à jour un sujet de réunion
     */
    public function updateSujet(int $sujetId, array $data, int $userId): ReunionSujet
    {
        try {
            DB::beginTransaction();

            $sujet = ReunionSujet::findOrFail($sujetId);

            $sujet->update([
                'titre' => $data['titre'] ?? $sujet->titre,
                'description' => $data['description'] ?? $sujet->description,
                'difficulte_globale' => $data['difficulte_globale'] ?? $sujet->difficulte_globale,
                'recommandation' => $data['recommandation'] ?? $sujet->recommandation,
                'statut' => $data['statut'] ?? $sujet->statut,
                'commentaire' => $data['commentaire'] ?? $sujet->commentaire,
                'pieces_jointes' => $data['pieces_jointes'] ?? $sujet->pieces_jointes,
                'projet_id' => $data['projet_id'] ?? $sujet->projet_id,
                'entite_id' => $data['entite_id'] ?? $sujet->entite_id,
                'niveau_detail' => $data['niveau_detail'] ?? $sujet->niveau_detail,
                'objectifs_actifs' => $data['objectifs_actifs'] ?? $sujet->objectifs_actifs,
                'difficultes_actives' => $data['difficultes_actives'] ?? $sujet->difficultes_actives,
                'modifier_par' => $userId,
            ]);

            Log::info('Sujet de réunion mis à jour', [
                'sujet_id' => $sujet->id,
                'reunion_id' => $sujet->reunion_id,
                'user_id' => $userId
            ]);

            DB::commit();
            return $sujet;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du sujet', [
                'sujet_id' => $sujetId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Supprimer un sujet de réunion
     */
    public function deleteSujet(int $sujetId): bool
    {
        try {
            DB::beginTransaction();

            $sujet = ReunionSujet::findOrFail($sujetId);
            $reunionId = $sujet->reunion_id;

            // Réorganiser l'ordre des autres sujets
            $this->reorganizeOrdre($reunionId, $sujet->ordre);

            $sujet->delete();

            Log::info('Sujet de réunion supprimé', [
                'sujet_id' => $sujetId,
                'reunion_id' => $reunionId,
                'user_id' => auth()->id()
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du sujet', [
                'sujet_id' => $sujetId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer tous les sujets d'une réunion
     */
    public function getSujets(int $reunionId, array $filters = []): array
    {
        try {
            $query = ReunionSujet::where('reunion_id', $reunionId)
                ->with(['responsable', 'reunion']);

            // Filtres
            if (isset($filters['statut'])) {
                $query->where('statut', $filters['statut']);
            }

            if (isset($filters['priorite'])) {
                $query->where('priorite', $filters['priorite']);
            }

            if (isset($filters['categorie'])) {
                $query->where('categorie', $filters['categorie']);
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

            $sujets = $query->orderBy('ordre')->get();

            return [
                'sujets' => $sujets,
                'total' => $sujets->count(),
                'filters_applied' => $filters
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des sujets', [
                'reunion_id' => $reunionId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer un sujet spécifique
     */
    public function getSujet(int $sujetId): ReunionSujet
    {
        try {
            return ReunionSujet::with(['responsable', 'reunion'])->findOrFail($sujetId);
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération du sujet', [
                'sujet_id' => $sujetId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * Changer le statut d'un sujet
     */
    public function changeStatut(int $sujetId, string $nouveauStatut): ReunionSujet
    {
        try {
            DB::beginTransaction();

            $sujet = ReunionSujet::findOrFail($sujetId);
            $ancienStatut = $sujet->statut;

            $sujet->update([
                'statut' => $nouveauStatut,
                'modifier_par' => auth()->id(),
            ]);

            Log::info('Statut du sujet changé', [
                'sujet_id' => $sujet->id,
                'ancien_statut' => $ancienStatut,
                'nouveau_statut' => $nouveauStatut,
                'user_id' => auth()->id()
            ]);

            DB::commit();
            return $sujet;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du changement de statut du sujet', [
                'sujet_id' => $sujetId,
                'nouveau_statut' => $nouveauStatut,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * Réorganiser l'ordre des sujets
     */
    public function reorderSujets(int $reunionId, array $ordreSujets): bool
    {
        try {
            DB::beginTransaction();

            foreach ($ordreSujets as $index => $sujetId) {
                ReunionSujet::where('id', $sujetId)
                    ->where('reunion_id', $reunionId)
                    ->update(['ordre' => $index + 1]);
            }

            Log::info('Ordre des sujets réorganisé', [
                'reunion_id' => $reunionId,
                'ordre' => $ordreSujets,
                'user_id' => auth()->id()
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la réorganisation des sujets', [
                'reunion_id' => $reunionId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * Obtenir les statistiques des sujets
     */
    public function getStats(int $reunionId): array
    {
        try {
            $stats = ReunionSujet::where('reunion_id', $reunionId)
                ->selectRaw('
                    COUNT(*) as total_sujets,
                    COUNT(CASE WHEN statut = "en_attente" THEN 1 END) as en_attente,
                    COUNT(CASE WHEN statut = "en_cours" THEN 1 END) as en_cours,
                    COUNT(CASE WHEN statut = "termine" THEN 1 END) as termine,
                    COUNT(CASE WHEN statut = "annule" THEN 1 END) as annule,
                    COUNT(CASE WHEN priorite = "haute" THEN 1 END) as priorite_haute,
                    COUNT(CASE WHEN priorite = "normale" THEN 1 END) as priorite_normale,
                    COUNT(CASE WHEN priorite = "basse" THEN 1 END) as priorite_basse
                ')
                ->first();

            return [
                'total_sujets' => $stats->total_sujets,
                'par_statut' => [
                    'en_attente' => $stats->en_attente,
                    'en_cours' => $stats->en_cours,
                    'termine' => $stats->termine,
                    'annule' => $stats->annule,
                ],
                'par_priorite' => [
                    'haute' => $stats->priorite_haute,
                    'normale' => $stats->priorite_normale,
                    'basse' => $stats->priorite_basse,
                ]
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors du calcul des statistiques des sujets', [
                'reunion_id' => $reunionId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * Obtenir le prochain ordre disponible
     */
    private function getNextOrdre(int $reunionId): int
    {
        $maxOrdre = ReunionSujet::where('reunion_id', $reunionId)->max('ordre');
        return ($maxOrdre ?? 0) + 1;
    }

    /**
     * Réorganiser l'ordre après suppression
     */
    private function reorganizeOrdre(int $reunionId, int $ordreSupprime): void
    {
        ReunionSujet::where('reunion_id', $reunionId)
            ->where('ordre', '>', $ordreSupprime)
            ->decrement('ordre');
    }
}
