<?php

namespace App\Services\Reunion;

use App\Models\Reunion;
use App\Models\ReunionDecision;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReunionDecisionService
{
    /**
     * Récupérer les décisions d'une réunion
     */
    public function getDecisions(int $reunionId, User $user): array
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

            $decisions = ReunionDecision::with([
                'reunion',
                'responsables',
                'actions'
            ])
            ->where('reunion_id', $reunionId)
            ->orderBy('date_creation', 'desc')
            ->get();

            return [
                'success' => true,
                'data' => $decisions,
                'message' => 'Décisions récupérées avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des décisions', [
                'reunion_id' => $reunionId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des décisions',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Créer une nouvelle décision
     */
    public function createDecision(int $reunionId, array $data, User $user): array
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
            if (!$this->canCreateDecision($reunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour créer une décision'
                ];
            }

            $decisionData = [
                'reunion_id' => $reunionId,
                'reunion_sujet_id' => $data['reunion_sujet_id'] ?? null,
                'texte_decision' => $data['texte_decision'],
                'type' => $data['type'] ?? 'DEFINITIVE',
                'responsables_ids' => $data['responsables_ids'] ?? [],
                'date_limite' => $data['date_limite'],
                'statut' => 'EN_ATTENTE',
                'priorite' => $data['priorite'] ?? 'NORMALE',
                'commentaire' => $data['commentaire'] ?? null,
                'creer_par' => $user->id,
                'modifier_par' => $user->id,
            ];

            $decision = ReunionDecision::create($decisionData);

            // Créer les actions associées si fournies
            if (isset($data['actions']) && is_array($data['actions'])) {
                foreach ($data['actions'] as $actionData) {
                    $actionData['decision_id'] = $decision->id;
                    $actionData['creer_par'] = $user->id;
                    $actionData['modifier_par'] = $user->id;
                    $actionData['date_creation'] = now();
                    $actionData['date_modification'] = now();

                    // Utiliser le service d'actions pour créer l'action
                    $actionService = new ReunionActionService();
                    $actionService->createAction($actionData, $user);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'data' => $decision->load('actions'),
                'message' => 'Décision créée avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la décision', [
                'reunion_id' => $reunionId,
                'data' => $data,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la création de la décision',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour une décision
     */
    public function updateDecision(int $decisionId, array $data, User $user): array
    {
        try {
            DB::beginTransaction();

            $decision = ReunionDecision::with('reunion')->find($decisionId);
            if (!$decision) {
                return [
                    'success' => false,
                    'message' => 'Décision non trouvée'
                ];
            }

            // Vérifier les permissions de modification
            if (!$this->canUpdateDecision($decision, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier cette décision'
                ];
            }

            $updateData = array_filter([
                'reunion_sujet_id' => $data['reunion_sujet_id'] ?? null,
                'texte_decision' => $data['texte_decision'] ?? null,
                'type' => $data['type'] ?? null,
                'responsables_ids' => $data['responsables_ids'] ?? null,
                'date_limite' => $data['date_limite'] ?? null,
                'statut' => $data['statut'] ?? null,
                'priorite' => $data['priorite'] ?? null,
                'commentaire' => $data['commentaire'] ?? null,
                'modifier_par' => $user->id,
            ], function ($value) {
                return $value !== null;
            });

            $decision->update($updateData);

            DB::commit();

            return [
                'success' => true,
                'data' => $decision,
                'message' => 'Décision mise à jour avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de la décision', [
                'decision_id' => $decisionId,
                'data' => $data,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la décision',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer une décision
     */
    public function deleteDecision(int $decisionId, User $user): array
    {
        try {
            DB::beginTransaction();

            $decision = ReunionDecision::with('reunion')->find($decisionId);
            if (!$decision) {
                return [
                    'success' => false,
                    'message' => 'Décision non trouvée'
                ];
            }

            // Vérifier les permissions de suppression
            if (!$this->canDeleteDecision($decision, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour supprimer cette décision'
                ];
            }

            $decision->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Décision supprimée avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de la décision', [
                'decision_id' => $decisionId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression de la décision',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Changer le statut d'exécution d'une décision
     */
    public function changeStatutExecution(int $decisionId, string $statut, User $user): array
    {
        try {
            $decision = ReunionDecision::with('reunion')->find($decisionId);
            if (!$decision) {
                return [
                    'success' => false,
                    'message' => 'Décision non trouvée'
                ];
            }

            // Vérifier les permissions
            if (!$this->canUpdateDecision($decision, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier cette décision'
                ];
            }

            $decision->update([
                'statut_execution' => $statut,
                'modifier_par' => $user->id,
                'date_modification' => now(),
            ]);

            return [
                'success' => true,
                'data' => $decision,
                'message' => 'Statut d\'exécution mis à jour avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors du changement de statut d\'exécution', [
                'decision_id' => $decisionId,
                'statut' => $statut,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors du changement de statut d\'exécution',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les décisions en retard
     */
    public function getDecisionsEnRetard(User $user): array
    {
        try {
            $decisions = ReunionDecision::with([
                'reunion',
                'responsables'
            ])
            ->where('date_limite', '<', now())
            ->where('statut_execution', '!=', 'TERMINEE')
            ->where(function ($query) use ($user) {
                $query->where('responsables', 'like', '%' . $user->id . '%')
                      ->orWhere('creer_par', $user->id);
            })
            ->orderBy('date_limite')
            ->get();

            return [
                'success' => true,
                'data' => $decisions,
                'message' => 'Décisions en retard récupérées avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des décisions en retard', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des décisions en retard',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les statistiques des décisions
     */
    public function getDecisionStats(int $reunionId = null, User $user = null): array
    {
        try {
            $query = ReunionDecision::query();

            if ($reunionId) {
                $query->where('reunion_id', $reunionId);
            }

            if ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('responsables', 'like', '%' . $user->id . '%')
                      ->orWhere('creer_par', $user->id);
                });
            }

            $decisions = $query->get();

            $stats = [
                'total_decisions' => $decisions->count(),
                'decisions_definitives' => $decisions->where('type_decision', 'DEFINITIVE')->count(),
                'decisions_provisoires' => $decisions->where('type_decision', 'PROVISOIRE')->count(),
                'decisions_en_attente' => $decisions->where('statut_execution', 'EN_ATTENTE')->count(),
                'decisions_en_cours' => $decisions->where('statut_execution', 'EN_COURS')->count(),
                'decisions_terminees' => $decisions->where('statut_execution', 'TERMINEE')->count(),
                'decisions_en_retard' => $decisions->where('date_limite', '<', now())
                    ->where('statut_execution', '!=', 'TERMINEE')->count(),
                'decisions_critiques' => $decisions->where('priorite', 'CRITIQUE')->count(),
                'decisions_elevees' => $decisions->where('priorite', 'ELEVEE')->count(),
            ];

            return [
                'success' => true,
                'data' => $stats,
                'message' => 'Statistiques récupérées avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques des décisions', [
                'reunion_id' => $reunionId,
                'user_id' => $user?->id,
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
     * Vérifier si l'utilisateur peut accéder à la réunion
     */
    private function canAccessReunion(Reunion $reunion, User $user): bool
    {
        return $user->hasPermission('view_reunions') ||
               $reunion->participants()->where('user_id', $user->id)->exists() ||
               $reunion->creer_par === $user->id;
    }

    /**
     * Vérifier si l'utilisateur peut créer une décision
     */
    private function canCreateDecision(Reunion $reunion, User $user): bool
    {
        return $user->hasPermission('create_reunion_decisions') ||
               $reunion->participants()->where('user_id', $user->id)
                   ->whereIn('role', ['PRESIDENT', 'SECRETAIRE'])->exists() ||
               $reunion->creer_par === $user->id;
    }

    /**
     * Vérifier si l'utilisateur peut modifier une décision
     */
    private function canUpdateDecision(ReunionDecision $decision, User $user): bool
    {
        return $user->hasPermission('update_reunion_decisions') ||
               $decision->creer_par === $user->id ||
               in_array($user->id, $decision->responsables ?? []);
    }

    /**
     * Vérifier si l'utilisateur peut supprimer une décision
     */
    private function canDeleteDecision(ReunionDecision $decision, User $user): bool
    {
        return $user->hasPermission('delete_reunion_decisions') ||
               $decision->creer_par === $user->id;
    }
}
