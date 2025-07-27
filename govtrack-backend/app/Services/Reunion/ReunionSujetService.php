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
     * Créer plusieurs sujets de réunion en lot avec gestion des pièces jointes
     */
    public function createMultipleSujets(array $sujetsList, array $files = [], int $reunionId, int $userId): array
    {
        DB::beginTransaction();

        try {
            // Vérifier que la réunion existe
            $reunion = Reunion::findOrFail($reunionId);

            $sujetsCrees = [];
            $erreurs = [];

            foreach ($sujetsList as $index => $sujetData) {
                try {
                    // Créer le sujet sans pièces jointes d'abord
                    $sujet = ReunionSujet::create([
                        'reunion_ordre_jour_id' => $sujetData['reunion_ordre_jour_id'],
                        'titre' => $sujetData['titre'],
                        'description' => $sujetData['description'],
                        'difficulte_globale' => $sujetData['difficulte_globale'] ?? null,
                        'recommandation' => $sujetData['recommandation'] ?? null,
                        'statut' => $sujetData['statut'] ?? 'EN_ATTENTE',
                        'commentaire' => $sujetData['commentaire'] ?? null,
                        'pieces_jointes' => [], // Initialiser vide
                        'projet_id' => $sujetData['projet_id'] ?? null,
                        'entite_id' => $sujetData['entite_id'] ?? null,
                        'niveau_detail' => $sujetData['niveau_detail'] ?? 'SIMPLE',
                        'objectifs_actifs' => $sujetData['objectifs_actifs'] ?? false,
                        'difficultes_actives' => $sujetData['difficultes_actives'] ?? false,
                        'creer_par' => $userId,
                        'modifier_par' => $userId,
                    ]);

                    // Gérer les pièces jointes pour ce sujet
                    if (isset($files["sujet_{$index}_files"]) && is_array($files["sujet_{$index}_files"])) {
                        $piecesJointes = [];

                        foreach ($files["sujet_{$index}_files"] as $file) {
                            if ($file && $file->isValid()) {
                                $fileName = time() . '_' . $file->getClientOriginalName();
                                $filePath = $file->storeAs(
                                    "reunions/{$reunionId}/sujets/{$sujet->id}",
                                    $fileName,
                                    'public'
                                );

                                $piecesJointes[] = [
                                    'nom' => $file->getClientOriginalName(),
                                    'chemin' => $filePath,
                                    'taille' => $file->getSize(),
                                    'type' => $file->getMimeType(),
                                    'uploaded_at' => now()->toISOString()
                                ];
                            }
                        }

                        // Mettre à jour le sujet avec les pièces jointes
                        $sujet->update(['pieces_jointes' => $piecesJointes]);
                    }

                    $sujetsCrees[] = $sujet;

                    Log::info('Sujet de réunion créé en lot avec pièces jointes', [
                        'sujet_id' => $sujet->id,
                        'reunion_id' => $reunionId,
                        'titre' => $sujet->titre,
                        'pieces_jointes_count' => count($sujet->pieces_jointes ?? []),
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
                    'message' => 'Erreurs lors de la création des sujets',
                    'errors' => $erreurs
                ];
            }

            DB::commit();

            return [
                'success' => true,
                'data' => $sujetsCrees,
                'message' => count($sujetsCrees) . ' sujets créés avec succès'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création multiple des sujets', [
                'reunion_id' => $reunionId,
                'sujets_list' => $sujetsList,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la création multiple des sujets',
                'error' => $e->getMessage()
            ];
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

            $sujet->delete();

            Log::info('Sujet de réunion supprimé', [
                'sujet_id' => $sujetId
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du sujet', [
                'sujet_id' => $sujetId,
                'error' => $e->getMessage()
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
            // Récupérer les sujets via la relation reunion_ordre_jour
            $query = ReunionSujet::whereHas('ordreJour', function ($q) use ($reunionId) {
                $q->where('reunion_id', $reunionId);
            })->with(['ordreJour', 'projet', 'entite', 'createur', 'modificateur']);

            // Filtres
            if (isset($filters['statut'])) {
                $query->where('statut', $filters['statut']);
            }

            if (isset($filters['projet_id'])) {
                $query->where('projet_id', $filters['projet_id']);
            }

            if (isset($filters['entite_id'])) {
                $query->where('entite_id', $filters['entite_id']);
            }

            if (isset($filters['niveau_detail'])) {
                $query->where('niveau_detail', $filters['niveau_detail']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('titre', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('description', 'like', '%' . $filters['search'] . '%');
                });
            }

            $sujets = $query->orderBy('date_creation')->get();

            return [
                'sujets' => $sujets,
                'total' => $sujets->count(),
                'filters_applied' => $filters
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des sujets', [
                'reunion_id' => $reunionId,
                'error' => $e->getMessage()
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
            return ReunionSujet::with(['ordreJour', 'projet', 'entite', 'createur', 'modificateur'])->findOrFail($sujetId);
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération du sujet', [
                'sujet_id' => $sujetId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Changer le statut d'un sujet
     */
    public function changeStatut(int $sujetId, string $nouveauStatut, int $userId): ReunionSujet
    {
        try {
            DB::beginTransaction();

            $sujet = ReunionSujet::findOrFail($sujetId);
            $ancienStatut = $sujet->statut;

            $sujet->update([
                'statut' => $nouveauStatut,
                'modifier_par' => $userId,
            ]);

            Log::info('Statut du sujet changé', [
                'sujet_id' => $sujet->id,
                'ancien_statut' => $ancienStatut,
                'nouveau_statut' => $nouveauStatut,
                'user_id' => $userId
            ]);

            DB::commit();
            return $sujet;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du changement de statut du sujet', [
                'sujet_id' => $sujetId,
                'nouveau_statut' => $nouveauStatut,
                'error' => $e->getMessage(),
                'user_id' => $userId
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

            // Pour les sujets, l'ordre est géré par la table reunion_ordre_jours
            // Cette méthode peut être utilisée pour mettre à jour l'ordre des points d'ordre du jour
            // qui contiennent les sujets

            Log::info('Réorganisation des sujets demandée', [
                'reunion_id' => $reunionId,
                'ordre' => $ordreSujets
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la réorganisation des sujets', [
                'reunion_id' => $reunionId,
                'error' => $e->getMessage()
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
            $stats = ReunionSujet::whereHas('ordreJour', function ($q) use ($reunionId) {
                $q->where('reunion_id', $reunionId);
            })
            ->selectRaw('
                COUNT(*) as total_sujets,
                COUNT(CASE WHEN statut = "EN_ATTENTE" THEN 1 END) as en_attente,
                COUNT(CASE WHEN statut = "EN_COURS_DE_RESOLUTION" THEN 1 END) as en_cours,
                COUNT(CASE WHEN statut = "RESOLU" THEN 1 END) as resolu,
                COUNT(CASE WHEN statut = "BLOQUE" THEN 1 END) as bloque,
                COUNT(CASE WHEN statut = "AVIS" THEN 1 END) as avis,
                COUNT(CASE WHEN statut = "APPROUVE" THEN 1 END) as approuve,
                COUNT(CASE WHEN statut = "REJETE" THEN 1 END) as rejete
            ')
            ->first();

            return [
                'total_sujets' => $stats->total_sujets,
                'par_statut' => [
                    'EN_ATTENTE' => $stats->en_attente,
                    'EN_COURS_DE_RESOLUTION' => $stats->en_cours,
                    'RESOLU' => $stats->resolu,
                    'BLOQUE' => $stats->bloque,
                    'AVIS' => $stats->avis,
                    'APPROUVE' => $stats->approuve,
                    'REJETE' => $stats->rejete,
                ]
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors du calcul des statistiques des sujets', [
                'reunion_id' => $reunionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtenir le prochain ordre disponible
     */
    private function getNextOrdre(int $reunionId): int
    {
        // L'ordre est géré par la table reunion_ordre_jours
        // Cette méthode n'est plus nécessaire pour les sujets
        return 1;
    }

    /**
     * Réorganiser l'ordre après suppression
     */
    private function reorganizeOrdre(int $reunionId, int $ordreSupprime): void
    {
        // L'ordre est géré par la table reunion_ordre_jours
        // Cette méthode n'est plus nécessaire pour les sujets
    }
}
