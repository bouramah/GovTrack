<?php

namespace App\Services\Reunion;

use App\Models\ReunionPV;
use App\Models\Reunion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReunionPVService
{
    /**
     * Récupérer les PV d'une réunion
     */
    public function getPVs(int $reunionId, User $user): array
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

            $pvs = ReunionPV::with([
                'reunion.typeReunion',
                'redacteur',
                'validateur'
            ])
            ->where('reunion_id', $reunionId)
            ->orderBy('version', 'desc')
            ->get();

            return [
                'success' => true,
                'data' => $pvs,
                'message' => 'Procès-verbaux récupérés avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des PV', [
                'reunion_id' => $reunionId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des PV',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer un PV spécifique
     */
    public function getPV(int $reunionId, int $pvId, User $user): array
    {
        try {
            $reunion = Reunion::find($reunionId);
            if (!$reunion) {
                return [
                    'success' => false,
                    'message' => 'Réunion non trouvée'
                ];
            }

            $pv = ReunionPV::with([
                'reunion.typeReunion',
                'reunion.participants.user',
                'redacteur',
                'validateur'
            ])
            ->where('reunion_id', $reunionId)
            ->where('id', $pvId)
            ->first();

            if (!$pv) {
                return [
                    'success' => false,
                    'message' => 'Procès-verbal non trouvé'
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
                'data' => $pv,
                'message' => 'Procès-verbal récupéré avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du PV', [
                'reunion_id' => $reunionId,
                'pv_id' => $pvId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération du PV',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Créer un nouveau PV
     */
    public function createPV(int $reunionId, array $data, User $user): array
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

            // Vérifier les permissions de création
            if (!$this->canCreatePV($reunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour créer un PV pour cette réunion'
                ];
            }

            // Vérifier que la réunion est terminée
            if ($reunion->statut !== 'TERMINEE') {
                return [
                    'success' => false,
                    'message' => 'Impossible de créer un PV pour une réunion non terminée'
                ];
            }

            // Déterminer la version du PV
            $derniereVersion = ReunionPV::where('reunion_id', $reunionId)
                ->max('version') ?? 0;
            $nouvelleVersion = $derniereVersion + 1;

            // Créer le PV
            $pvData = [
                'reunion_id' => $reunionId,
                'contenu' => $data['contenu'],
                'redige_par_id' => $user->id,
                'redige_le' => now(),
                'modifie_le' => now(),
                'version' => $nouvelleVersion,
                'valide_par_id' => null,
                'valide_le' => null,
                'statut' => 'BROUILLON',
                'commentaire_validation' => null,
                'notifications_envoyees' => false,
            ];

            $pv = ReunionPV::create($pvData);

            DB::commit();

            // Charger les relations pour la réponse
            $pv->load([
                'reunion.typeReunion',
                'redacteur',
                'validateur'
            ]);

            return [
                'success' => true,
                'data' => $pv,
                'message' => 'Procès-verbal créé avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du PV', [
                'reunion_id' => $reunionId,
                'user_id' => $user->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la création du PV',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour un PV
     */
    public function updatePV(int $reunionId, int $pvId, array $data, User $user): array
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

            $pv = ReunionPV::where('reunion_id', $reunionId)
                ->where('id', $pvId)
                ->first();

            if (!$pv) {
                return [
                    'success' => false,
                    'message' => 'Procès-verbal non trouvé'
                ];
            }

            // Vérifier les permissions de modification
            if (!$this->canUpdatePV($pv, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier ce PV'
                ];
            }

            // Vérifier que le PV peut être modifié
            if ($pv->statut === 'VALIDE') {
                return [
                    'success' => false,
                    'message' => 'Impossible de modifier un PV validé'
                ];
            }

            // Préparer les données de mise à jour
            $updateData = array_filter([
                'contenu' => $data['contenu'] ?? null,
                'modifie_le' => now(),
            ], function ($value) {
                return $value !== null;
            });

            $pv->update($updateData);

            DB::commit();

            // Charger les relations pour la réponse
            $pv->load([
                'reunion.typeReunion',
                'redacteur',
                'validateur'
            ]);

            return [
                'success' => true,
                'data' => $pv,
                'message' => 'Procès-verbal mis à jour avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du PV', [
                'reunion_id' => $reunionId,
                'pv_id' => $pvId,
                'user_id' => $user->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du PV',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer un PV
     */
    public function deletePV(int $reunionId, int $pvId, User $user): array
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

            $pv = ReunionPV::where('reunion_id', $reunionId)
                ->where('id', $pvId)
                ->first();

            if (!$pv) {
                return [
                    'success' => false,
                    'message' => 'Procès-verbal non trouvé'
                ];
            }

            // Vérifier les permissions de suppression
            if (!$this->canDeletePV($pv, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour supprimer ce PV'
                ];
            }

            // Vérifier que le PV peut être supprimé
            if ($pv->statut === 'VALIDE') {
                return [
                    'success' => false,
                    'message' => 'Impossible de supprimer un PV validé'
                ];
            }

            $pv->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Procès-verbal supprimé avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du PV', [
                'reunion_id' => $reunionId,
                'pv_id' => $pvId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression du PV',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Valider un PV
     */
    public function validerPV(int $reunionId, int $pvId, array $data, User $user): array
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

            $pv = ReunionPV::where('reunion_id', $reunionId)
                ->where('id', $pvId)
                ->first();

            if (!$pv) {
                return [
                    'success' => false,
                    'message' => 'Procès-verbal non trouvé'
                ];
            }

            // Vérifier les permissions de validation
            if (!$this->canValiderPV($pv, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour valider ce PV'
                ];
            }

            // Vérifier que le PV peut être validé
            if ($pv->statut === 'VALIDE') {
                return [
                    'success' => false,
                    'message' => 'Ce PV est déjà validé'
                ];
            }

            if ($pv->statut === 'REJETE') {
                return [
                    'success' => false,
                    'message' => 'Impossible de valider un PV rejeté'
                ];
            }

            // Mettre à jour le PV
            $pv->update([
                'statut' => 'VALIDE',
                'valide_par_id' => $user->id,
                'valide_le' => now(),
                'commentaire_validation' => $data['commentaire_validation'] ?? null,
                'modifie_le' => now(),
            ]);

            // Mettre à jour la réunion pour indiquer qu'elle a un PV validé
            $reunion->update([
                'pv_valide_par_id' => $user->id,
                'modifier_par' => $user->id,
                'date_modification' => now(),
            ]);

            DB::commit();

            // Charger les relations pour la réponse
            $pv->load([
                'reunion.typeReunion',
                'redacteur',
                'validateur'
            ]);

            return [
                'success' => true,
                'data' => $pv,
                'message' => 'Procès-verbal validé avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la validation du PV', [
                'reunion_id' => $reunionId,
                'pv_id' => $pvId,
                'user_id' => $user->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la validation du PV',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Rejeter un PV
     */
    public function rejeterPV(int $reunionId, int $pvId, array $data, User $user): array
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

            $pv = ReunionPV::where('reunion_id', $reunionId)
                ->where('id', $pvId)
                ->first();

            if (!$pv) {
                return [
                    'success' => false,
                    'message' => 'Procès-verbal non trouvé'
                ];
            }

            // Vérifier les permissions de rejet
            if (!$this->canValiderPV($pv, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour rejeter ce PV'
                ];
            }

            // Vérifier que le PV peut être rejeté
            if ($pv->statut === 'VALIDE') {
                return [
                    'success' => false,
                    'message' => 'Impossible de rejeter un PV validé'
                ];
            }

            // Mettre à jour le PV
            $pv->update([
                'statut' => 'REJETE',
                'valide_par_id' => $user->id,
                'valide_le' => now(),
                'commentaire_validation' => $data['commentaire_validation'] ?? null,
                'modifie_le' => now(),
            ]);

            DB::commit();

            // Charger les relations pour la réponse
            $pv->load([
                'reunion.typeReunion',
                'redacteur',
                'validateur'
            ]);

            return [
                'success' => true,
                'data' => $pv,
                'message' => 'Procès-verbal rejeté avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du rejet du PV', [
                'reunion_id' => $reunionId,
                'pv_id' => $pvId,
                'user_id' => $user->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors du rejet du PV',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer les statistiques des PV
     */
    public function getPVStats(int $reunionId, User $user): array
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

            $pvs = ReunionPV::where('reunion_id', $reunionId)->get();

            $stats = [
                'total' => $pvs->count(),
                'brouillons' => $pvs->where('statut', 'BROUILLON')->count(),
                'en_attente' => $pvs->where('statut', 'EN_ATTENTE')->count(),
                'valides' => $pvs->where('statut', 'VALIDE')->count(),
                'rejetes' => $pvs->where('statut', 'REJETE')->count(),
                'derniere_version' => $pvs->max('version') ?? 0,
                'pv_valide' => $pvs->where('statut', 'VALIDE')->first() ? true : false,
            ];

            return [
                'success' => true,
                'data' => $stats,
                'message' => 'Statistiques des PV récupérées avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques des PV', [
                'reunion_id' => $reunionId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques des PV',
                'error' => $e->getMessage()
            ];
        }
    }

    // ========================================
    // MÉTHODES PRIVÉES UTILITAIRES
    // ========================================

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

        // Vérifier les permissions globales
        if ($user->hasPermission('view_all_reunions')) {
            return true;
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut créer un PV
     */
    private function canCreatePV(Reunion $reunion, User $user): bool
    {
        // L'utilisateur peut toujours créer un PV pour les réunions qu'il a créées
        if ($reunion->creer_par === $user->id) {
            return true;
        }

        // L'utilisateur peut créer un PV s'il est participant avec le rôle d'organisateur
        if ($reunion->participants()
            ->where('user_id', $user->id)
            ->where('role', 'ORGANISATEUR')
            ->exists()) {
            return true;
        }

        // Vérifier les permissions globales
        return $user->hasPermission('create_reunion_pv');
    }

    /**
     * Vérifier si l'utilisateur peut modifier un PV
     */
    private function canUpdatePV(ReunionPV $pv, User $user): bool
    {
        // L'utilisateur peut toujours modifier les PV qu'il a rédigés
        if ($pv->redacteur_id === $user->id) {
            return true;
        }

        // L'utilisateur peut modifier les PV des réunions qu'il a créées
        if ($pv->reunion->creer_par === $user->id) {
            return true;
        }

        // Vérifier les permissions globales
        return $user->hasPermission('update_reunion_pv');
    }

    /**
     * Vérifier si l'utilisateur peut supprimer un PV
     */
    private function canDeletePV(ReunionPV $pv, User $user): bool
    {
        // L'utilisateur peut toujours supprimer les PV qu'il a rédigés
        if ($pv->redacteur_id === $user->id) {
            return true;
        }

        // L'utilisateur peut supprimer les PV des réunions qu'il a créées
        if ($pv->reunion->creer_par === $user->id) {
            return true;
        }

        // Vérifier les permissions globales
        return $user->hasPermission('delete_reunion_pv');
    }

    /**
     * Vérifier si l'utilisateur peut valider un PV
     */
    private function canValiderPV(ReunionPV $pv, User $user): bool
    {
        // L'utilisateur ne peut pas valider ses propres PV
        if ($pv->redacteur_id === $user->id) {
            return false;
        }

        // L'utilisateur peut valider les PV des réunions qu'il a créées
        if ($pv->reunion->creer_par === $user->id) {
            return true;
        }

        // Vérifier si l'utilisateur est validateur pour le type de réunion
        $typeReunion = $pv->reunion->typeReunion;
        if ($typeReunion && $typeReunion->validateursPV()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Vérifier les permissions globales
        return $user->hasPermission('validate_reunion_pv');
    }
}
