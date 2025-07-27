<?php

namespace App\Services\Reunion;

use App\Models\ReunionSujetAvis;
use App\Models\ReunionSujet;
use App\Models\ReunionParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReunionSujetAvisService
{
    /**
     * Obtenir tous les avis d'un sujet
     */
    public function getAvis(int $sujetId): array
    {
        try {
            $avis = ReunionSujetAvis::with([
                'participant.user',
                'createur',
                'modificateur'
            ])
            ->where('reunion_sujet_id', $sujetId)
            ->orderBy('date_creation', 'desc')
            ->get();

            return [
                'success' => true,
                'data' => $avis,
                'message' => 'Avis récupérés avec succès'
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des avis', [
                'sujet_id' => $sujetId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des avis',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Créer un avis
     */
    public function createAvis(array $data, User $user): array
    {
        DB::beginTransaction();

        try {
            // Vérifier que le sujet existe
            $sujet = ReunionSujet::find($data['reunion_sujet_id']);
            if (!$sujet) {
                return [
                    'success' => false,
                    'message' => 'Sujet de réunion introuvable'
                ];
            }

            // Vérifier que le participant existe
            $participant = ReunionParticipant::find($data['participant_id']);
            if (!$participant) {
                return [
                    'success' => false,
                    'message' => 'Participant introuvable'
                ];
            }

            // Vérifier qu'il n'y a pas déjà un avis pour ce participant sur ce sujet
            $avisExistant = ReunionSujetAvis::where('reunion_sujet_id', $data['reunion_sujet_id'])
                ->where('participant_id', $data['participant_id'])
                ->first();

            if ($avisExistant) {
                return [
                    'success' => false,
                    'message' => 'Un avis existe déjà pour ce participant sur ce sujet'
                ];
            }

            $avisData = [
                'reunion_sujet_id' => $data['reunion_sujet_id'],
                'participant_id' => $data['participant_id'],
                'type_avis' => $data['type_avis'],
                'commentaire' => $data['commentaire'] ?? null,
                'statut' => 'SOUMIS',
                'creer_par' => $user->id,
                'modifier_par' => $user->id,
            ];

            $avis = ReunionSujetAvis::create($avisData);

            DB::commit();

            return [
                'success' => true,
                'data' => $avis->load(['participant.user', 'createur']),
                'message' => 'Avis créé avec succès'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'avis', [
                'data' => $data,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la création de l\'avis',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour un avis
     */
    public function updateAvis(int $avisId, array $data, User $user): array
    {
        DB::beginTransaction();

        try {
            $avis = ReunionSujetAvis::find($avisId);
            if (!$avis) {
                return [
                    'success' => false,
                    'message' => 'Avis introuvable'
                ];
            }

            $avis->update([
                'type_avis' => $data['type_avis'] ?? $avis->type_avis,
                'commentaire' => $data['commentaire'] ?? $avis->commentaire,
                'statut' => 'MODIFIE',
                'modifier_par' => $user->id,
                'date_modification' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'data' => $avis->load(['participant.user', 'createur', 'modificateur']),
                'message' => 'Avis mis à jour avec succès'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de l\'avis', [
                'avis_id' => $avisId,
                'data' => $data,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'avis',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer un avis
     */
    public function deleteAvis(int $avisId, User $user): array
    {
        DB::beginTransaction();

        try {
            $avis = ReunionSujetAvis::find($avisId);
            if (!$avis) {
                return [
                    'success' => false,
                    'message' => 'Avis introuvable'
                ];
            }

            $avis->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Avis supprimé avec succès'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de l\'avis', [
                'avis_id' => $avisId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'avis',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Créer plusieurs avis en lot
     */
    public function createMultipleAvis(array $avisList, User $user): array
    {
        DB::beginTransaction();

        try {
            $avisCrees = [];
            $erreurs = [];

            foreach ($avisList as $index => $avisData) {
                $result = $this->createAvis($avisData, $user);

                if ($result['success']) {
                    $avisCrees[] = $result['data'];
                } else {
                    $erreurs[] = [
                        'index' => $index,
                        'error' => $result['message']
                    ];
                }
            }

            if (!empty($erreurs)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Erreurs lors de la création des avis',
                    'errors' => $erreurs
                ];
            }

            DB::commit();

            return [
                'success' => true,
                'data' => $avisCrees,
                'message' => count($avisCrees) . ' avis créés avec succès'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création multiple des avis', [
                'avis_list' => $avisList,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la création multiple des avis',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les statistiques des avis pour un sujet
     */
    public function getAvisStats(int $sujetId): array
    {
        try {
            $avis = ReunionSujetAvis::where('reunion_sujet_id', $sujetId)->get();

            $stats = [
                'total_avis' => $avis->count(),
                'favorables' => $avis->where('type_avis', 'FAVORABLE')->count(),
                'defavorables' => $avis->where('type_avis', 'DEFAVORABLE')->count(),
                'reserves' => $avis->where('type_avis', 'RESERVE')->count(),
                'neutres' => $avis->where('type_avis', 'NEUTRE')->count(),
                'soumis' => $avis->where('statut', 'SOUMIS')->count(),
                'en_attente' => $avis->where('statut', 'EN_ATTENTE')->count(),
                'modifies' => $avis->where('statut', 'MODIFIE')->count(),
            ];

            // Calculer le pourcentage de chaque type
            $total = $stats['total_avis'];
            if ($total > 0) {
                $stats['pourcentage_favorables'] = round(($stats['favorables'] / $total) * 100, 2);
                $stats['pourcentage_defavorables'] = round(($stats['defavorables'] / $total) * 100, 2);
                $stats['pourcentage_reserves'] = round(($stats['reserves'] / $total) * 100, 2);
                $stats['pourcentage_neutres'] = round(($stats['neutres'] / $total) * 100, 2);
            } else {
                $stats['pourcentage_favorables'] = 0;
                $stats['pourcentage_defavorables'] = 0;
                $stats['pourcentage_reserves'] = 0;
                $stats['pourcentage_neutres'] = 0;
            }

            return [
                'success' => true,
                'data' => $stats,
                'message' => 'Statistiques des avis récupérées avec succès'
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques des avis', [
                'sujet_id' => $sujetId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques des avis',
                'error' => $e->getMessage()
            ];
        }
    }
}
