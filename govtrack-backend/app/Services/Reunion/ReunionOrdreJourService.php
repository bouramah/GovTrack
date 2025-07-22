<?php

namespace App\Services\Reunion;

use App\Models\Reunion;
use App\Models\ReunionOrdreJour;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReunionOrdreJourService
{
    /**
     * Récupérer l'ordre du jour d'une réunion
     */
    public function getOrdreJour(int $reunionId, User $user): array
    {
        try {
            $reunion = Reunion::find($reunionId);
            if (!$reunion) {
                return [
                    'success' => false,
                    'message' => 'Réunion non trouvée'
                ];
            }

            // Vérifier les permissions d'accès à la réunion
            if (!$this->canAccessReunion($reunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour accéder à cette réunion'
                ];
            }

            $ordreJour = ReunionOrdreJour::with([
                'reunion',
                'responsable',
                'sujets'
            ])
            ->where('reunion_id', $reunionId)
            ->orderBy('ordre')
            ->get();

            return [
                'success' => true,
                'data' => $ordreJour,
                'message' => 'Ordre du jour récupéré avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de l\'ordre du jour', [
                'reunion_id' => $reunionId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'ordre du jour',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Ajouter un point à l'ordre du jour
     */
    public function addPointOrdreJour(int $reunionId, array $data, User $user): array
    {
        try {
            DB::beginTransaction();

            $reunion = Reunion::find($reunionId);
            if (!$reunion) {
                return [
                    'success' => false,
                    'message' => 'Réunion non trouvée'
                ];
            }

            // Vérifier les permissions de modification
            if (!$this->canModifyOrdreJour($reunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier l\'ordre du jour'
                ];
            }

            // Déterminer l'ordre automatiquement si non fourni
            if (!isset($data['ordre'])) {
                $dernierOrdre = ReunionOrdreJour::where('reunion_id', $reunionId)
                    ->max('ordre');
                $data['ordre'] = ($dernierOrdre ?? 0) + 1;
            }

            $pointData = [
                'reunion_id' => $reunionId,
                'ordre' => $data['ordre'],
                'titre' => $data['titre'],
                'description' => $data['description'] ?? '',
                'type' => $data['type'] ?? 'DISCUSSION',
                'duree_estimee_minutes' => $data['duree_estimee_minutes'] ?? 15,
                'responsable_id' => $data['responsable_id'] ?? null,
                'statut' => 'PLANIFIE',
                'niveau_detail' => $data['niveau_detail'] ?? 'SIMPLE',
                'commentaires' => $data['commentaires'] ?? [],
                'creer_par' => $user->id,
                'modifier_par' => $user->id,
                'date_creation' => now(),
                'date_modification' => now(),
            ];

            $point = ReunionOrdreJour::create($pointData);

            DB::commit();

            return [
                'success' => true,
                'data' => $point,
                'message' => 'Point ajouté à l\'ordre du jour avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'ajout du point à l\'ordre du jour', [
                'reunion_id' => $reunionId,
                'data' => $data,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du point à l\'ordre du jour',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour un point de l'ordre du jour
     */
    public function updatePointOrdreJour(int $pointId, array $data, User $user): array
    {
        try {
            DB::beginTransaction();

            $point = ReunionOrdreJour::with('reunion')->find($pointId);
            if (!$point) {
                return [
                    'success' => false,
                    'message' => 'Point de l\'ordre du jour non trouvé'
                ];
            }

            // Vérifier les permissions de modification
            if (!$this->canModifyOrdreJour($point->reunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier l\'ordre du jour'
                ];
            }

            $updateData = array_filter([
                'ordre' => $data['ordre'] ?? null,
                'titre' => $data['titre'] ?? null,
                'description' => $data['description'] ?? null,
                'type' => $data['type'] ?? null,
                'duree_estimee_minutes' => $data['duree_estimee_minutes'] ?? null,
                'responsable_id' => $data['responsable_id'] ?? null,
                'statut' => $data['statut'] ?? null,
                'niveau_detail' => $data['niveau_detail'] ?? null,
                'commentaires' => $data['commentaires'] ?? null,
                'modifier_par' => $user->id,
                'date_modification' => now(),
            ], function ($value) {
                return $value !== null;
            });

            $point->update($updateData);

            DB::commit();

            return [
                'success' => true,
                'data' => $point,
                'message' => 'Point de l\'ordre du jour mis à jour avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du point de l\'ordre du jour', [
                'point_id' => $pointId,
                'data' => $data,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du point de l\'ordre du jour',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer un point de l'ordre du jour
     */
    public function deletePointOrdreJour(int $pointId, User $user): array
    {
        try {
            DB::beginTransaction();

            $point = ReunionOrdreJour::with('reunion')->find($pointId);
            if (!$point) {
                return [
                    'success' => false,
                    'message' => 'Point de l\'ordre du jour non trouvé'
                ];
            }

            // Vérifier les permissions de suppression
            if (!$this->canDeleteOrdreJour($point->reunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour supprimer ce point'
                ];
            }

            $point->delete();

            // Réorganiser l'ordre des points restants
            $this->reorganiserOrdre($point->reunion_id);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Point supprimé de l\'ordre du jour avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du point de l\'ordre du jour', [
                'point_id' => $pointId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression du point de l\'ordre du jour',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Réorganiser l'ordre des points
     */
    public function reorderPoints(int $reunionId, array $newOrder, User $user): array
    {
        try {
            DB::beginTransaction();

            $reunion = Reunion::find($reunionId);
            if (!$reunion) {
                return [
                    'success' => false,
                    'message' => 'Réunion non trouvée'
                ];
            }

            // Vérifier les permissions de modification
            if (!$this->canModifyOrdreJour($reunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier l\'ordre du jour'
                ];
            }

            foreach ($newOrder as $index => $pointId) {
                ReunionOrdreJour::where('id', $pointId)
                    ->where('reunion_id', $reunionId)
                    ->update(['ordre' => $index + 1]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Ordre des points mis à jour avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la réorganisation de l\'ordre du jour', [
                'reunion_id' => $reunionId,
                'new_order' => $newOrder,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la réorganisation de l\'ordre du jour',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Changer le statut d'un point
     */
    public function changeStatutPoint(int $pointId, string $statut, User $user): array
    {
        try {
            $point = ReunionOrdreJour::with('reunion')->find($pointId);
            if (!$point) {
                return [
                    'success' => false,
                    'message' => 'Point de l\'ordre du jour non trouvé'
                ];
            }

            // Vérifier les permissions
            if (!$this->canModifyOrdreJour($point->reunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier ce point'
                ];
            }

            $point->update([
                'statut' => $statut,
                'modifier_par' => $user->id,
                'date_modification' => now(),
            ]);

            return [
                'success' => true,
                'data' => $point,
                'message' => 'Statut du point mis à jour avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors du changement de statut du point', [
                'point_id' => $pointId,
                'statut' => $statut,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors du changement de statut du point',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les statistiques de l'ordre du jour
     */
    public function getOrdreJourStats(int $reunionId, User $user): array
    {
        try {
            $reunion = Reunion::find($reunionId);
            if (!$reunion) {
                return [
                    'success' => false,
                    'message' => 'Réunion non trouvée'
                ];
            }

            $points = ReunionOrdreJour::where('reunion_id', $reunionId)->get();

            $stats = [
                'total_points' => $points->count(),
                'points_planifies' => $points->where('statut', 'PLANIFIE')->count(),
                'points_en_cours' => $points->where('statut', 'EN_COURS')->count(),
                'points_termines' => $points->where('statut', 'TERMINE')->count(),
                'points_reportes' => $points->where('statut', 'REPORTE')->count(),
                'duree_totale_estimee' => $points->sum('duree_estimee_minutes'),
                'duree_totale_reelle' => $points->where('statut', 'TERMINE')->sum('duree_reelle_minutes'),
            ];

            return [
                'success' => true,
                'data' => $stats,
                'message' => 'Statistiques récupérées avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques', [
                'reunion_id' => $reunionId,
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

    /**
     * Réorganiser l'ordre des points après suppression
     */
    private function reorganiserOrdre(int $reunionId): void
    {
        $points = ReunionOrdreJour::where('reunion_id', $reunionId)
            ->orderBy('ordre')
            ->get();

        foreach ($points as $index => $point) {
            $point->update(['ordre' => $index + 1]);
        }
    }

    /**
     * Vérifier si l'utilisateur peut accéder à la réunion
     */
    private function canAccessReunion(Reunion $reunion, User $user): bool
    {
        return $user->hasPermission('view_reunions') || 
               $reunion->participants()->where('user_id', $user->id)->exists() ||
               $reunion->creer_par === $user->id;
    }

    /**
     * Vérifier si l'utilisateur peut modifier l'ordre du jour
     */
    private function canModifyOrdreJour(Reunion $reunion, User $user): bool
    {
        return $user->hasPermission('update_reunion_ordre_jour') ||
               $reunion->participants()->where('user_id', $user->id)
                   ->whereIn('role', ['PRESIDENT', 'SECRETAIRE'])->exists() ||
               $reunion->creer_par === $user->id;
    }

    /**
     * Vérifier si l'utilisateur peut supprimer l'ordre du jour
     */
    private function canDeleteOrdreJour(Reunion $reunion, User $user): bool
    {
        return $user->hasPermission('delete_reunion_ordre_jour') ||
               $reunion->creer_par === $user->id;
    }
} 