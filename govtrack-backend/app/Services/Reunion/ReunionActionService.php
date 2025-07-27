<?php

namespace App\Services\Reunion;

use App\Models\Reunion;
use App\Models\ReunionAction;
use App\Models\ReunionDecision;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReunionActionService
{
    /**
     * Récupérer les actions d'une réunion
     */
    public function getActions(int $reunionId, User $user): array
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

            $actions = ReunionAction::with([
                'reunion',
                'decision',
                'responsable',
                'createur',
                'modificateur'
            ])
            ->where('reunion_id', $reunionId)
            ->orderBy('date_limite')
            ->get();

            return [
                'success' => true,
                'data' => $actions,
                'message' => 'Actions récupérées avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des actions', [
                'reunion_id' => $reunionId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des actions',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Créer une nouvelle action
     */
    public function createAction(array $data, User $user): array
    {
        try {
            DB::beginTransaction();

            // Vérifier si c'est une action liée à une réunion ou une décision
            if (isset($data['reunion_id'])) {
                $reunion = Reunion::find($data['reunion_id']);
                if (!$reunion) {
                    return [
                        'success' => false,
                        'message' => 'Réunion non trouvée'
                    ];
                }

                // Vérifier les permissions de création
                if (!$this->canCreateAction($reunion, $user)) {
                    return [
                        'success' => false,
                        'message' => 'Vous n\'avez pas les permissions pour créer une action'
                    ];
                }
            } elseif (isset($data['decision_id'])) {
                $decision = ReunionDecision::with('reunion')->find($data['decision_id']);
                if (!$decision) {
                    return [
                        'success' => false,
                        'message' => 'Décision non trouvée'
                    ];
                }

                // Vérifier les permissions de création
                if (!$this->canCreateAction($decision->reunion, $user)) {
                    return [
                        'success' => false,
                        'message' => 'Vous n\'avez pas les permissions pour créer une action'
                    ];
                }

                $data['reunion_id'] = $decision->reunion_id;
            } else {
                return [
                    'success' => false,
                    'message' => 'Une action doit être liée à une réunion ou une décision'
                ];
            }

            $actionData = [
                'reunion_id' => $data['reunion_id'] ?? null,
                'decision_id' => $data['decision_id'] ?? null,
                'titre' => $data['titre'],
                'description' => $data['description'],
                'responsable_id' => $data['responsable_id'],
                'date_limite' => $data['date_limite'],
                'statut' => 'A_FAIRE',
                'commentaire' => $data['commentaire'] ?? null,
                'pieces_jointes' => $data['pieces_jointes'] ?? [],
                'priorite' => $data['priorite'] ?? 'NORMALE',
                'progression' => $data['progression'] ?? 0,
                'creer_par' => $user->id,
                'modifier_par' => $user->id,
            ];

            $action = ReunionAction::create($actionData);

            DB::commit();

            return [
                'success' => true,
                'data' => $action,
                'message' => 'Action créée avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'action', [
                'data' => $data,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la création de l\'action',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour une action
     */
    public function updateAction(int $actionId, array $data, User $user): array
    {
        try {
            DB::beginTransaction();

            $action = ReunionAction::with('reunion')->find($actionId);
            if (!$action) {
                return [
                    'success' => false,
                    'message' => 'Action non trouvée'
                ];
            }

            // Vérifier les permissions de modification
            if (!$this->canUpdateAction($action, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier cette action'
                ];
            }

            $updateData = array_filter([
                'titre' => $data['titre'] ?? null,
                'description' => $data['description'] ?? null,
                'responsable_id' => $data['responsable_id'] ?? null,
                'date_limite' => $data['date_limite'] ?? null,
                'statut' => $data['statut'] ?? null,
                'commentaire' => $data['commentaire'] ?? null,
                'pieces_jointes' => $data['pieces_jointes'] ?? null,
                'priorite' => $data['priorite'] ?? null,
                'progression' => $data['progression'] ?? null,
                'modifier_par' => $user->id,
            ], function ($value) {
                return $value !== null;
            });

            $action->update($updateData);

            DB::commit();

            return [
                'success' => true,
                'data' => $action,
                'message' => 'Action mise à jour avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de l\'action', [
                'action_id' => $actionId,
                'data' => $data,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'action',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer une action
     */
    public function deleteAction(int $actionId, User $user): array
    {
        try {
            DB::beginTransaction();

            $action = ReunionAction::with('reunion')->find($actionId);
            if (!$action) {
                return [
                    'success' => false,
                    'message' => 'Action non trouvée'
                ];
            }

            // Vérifier les permissions de suppression
            if (!$this->canDeleteAction($action, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour supprimer cette action'
                ];
            }

            $action->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Action supprimée avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de l\'action', [
                'action_id' => $actionId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'action',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Changer le statut d'une action
     */
    public function changeStatut(int $actionId, string $statut, User $user): array
    {
        try {
            $action = ReunionAction::with('reunion')->find($actionId);
            if (!$action) {
                return [
                    'success' => false,
                    'message' => 'Action non trouvée'
                ];
            }

            // Vérifier les permissions
            if (!$this->canUpdateAction($action, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier cette action'
                ];
            }

            $action->update([
                'statut' => $statut,
                'modifier_par' => $user->id,
                'date_modification' => now(),
            ]);

            return [
                'success' => true,
                'data' => $action,
                'message' => 'Statut de l\'action mis à jour avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors du changement de statut de l\'action', [
                'action_id' => $actionId,
                'statut' => $statut,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors du changement de statut de l\'action',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour la progression d'une action
     */
    public function updateProgression(int $actionId, int $progression, User $user): array
    {
        try {
            $action = ReunionAction::with('reunion')->find($actionId);
            if (!$action) {
                return [
                    'success' => false,
                    'message' => 'Action non trouvée'
                ];
            }

            // Vérifier les permissions
            if (!$this->canUpdateAction($action, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier cette action'
                ];
            }

            // Valider la progression (0-100)
            if ($progression < 0 || $progression > 100) {
                return [
                    'success' => false,
                    'message' => 'La progression doit être comprise entre 0 et 100'
                ];
            }

            $action->update([
                'progression' => $progression,
                'modifier_par' => $user->id,
                'date_modification' => now(),
            ]);

            // Mettre à jour automatiquement le statut si la progression est à 100%
            if ($progression >= 100) {
                $action->update(['statut' => 'TERMINEE']);
            } elseif ($progression > 0) {
                $action->update(['statut' => 'EN_COURS']);
            }

            return [
                'success' => true,
                'data' => $action,
                'message' => 'Progression de l\'action mise à jour avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de la progression', [
                'action_id' => $actionId,
                'progression' => $progression,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la progression',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les actions en retard
     */
    public function getActionsEnRetard(User $user): array
    {
        try {
            $actions = ReunionAction::with([
                'reunion',
                'decision',
                'responsable'
            ])
            ->where('date_limite', '<', now())
            ->where('statut', '!=', 'TERMINEE')
            ->where(function ($query) use ($user) {
                $query->where('responsable_id', $user->id)
                      ->orWhere('creer_par', $user->id);
            })
            ->orderBy('date_limite')
            ->get();

            return [
                'success' => true,
                'data' => $actions,
                'message' => 'Actions en retard récupérées avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des actions en retard', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des actions en retard',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les statistiques des actions
     */
    public function getActionStats(int $reunionId = null, User $user = null): array
    {
        try {
            $query = ReunionAction::query();

            if ($reunionId) {
                $query->where('reunion_id', $reunionId);
            }

            if ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('responsable_id', $user->id)
                      ->orWhere('creer_par', $user->id);
                });
            }

            $actions = $query->get();

            $stats = [
                'total_actions' => $actions->count(),
                'actions_en_attente' => $actions->where('statut', 'EN_ATTENTE')->count(),
                'actions_en_cours' => $actions->where('statut', 'EN_COURS')->count(),
                'actions_terminees' => $actions->where('statut', 'TERMINEE')->count(),
                'actions_en_retard' => $actions->where('date_limite', '<', now())
                    ->where('statut', '!=', 'TERMINEE')->count(),
                'actions_critiques' => $actions->where('priorite', 'CRITIQUE')->count(),
                'actions_elevees' => $actions->where('priorite', 'ELEVEE')->count(),
                'progression_moyenne' => $actions->avg('progression'),
            ];

            return [
                'success' => true,
                'data' => $stats,
                'message' => 'Statistiques récupérées avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques des actions', [
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
     * Vérifier si l'utilisateur peut créer une action
     */
    private function canCreateAction(Reunion $reunion, User $user): bool
    {
        return $user->hasPermission('create_reunion_actions') ||
               $reunion->participants()->where('user_id', $user->id)
                   ->whereIn('role', ['PRESIDENT', 'SECRETAIRE'])->exists() ||
               $reunion->creer_par === $user->id;
    }

    /**
     * Vérifier si l'utilisateur peut modifier une action
     */
    private function canUpdateAction(ReunionAction $action, User $user): bool
    {
        return $user->hasPermission('update_reunion_actions') ||
               $action->creer_par === $user->id ||
               $action->responsable_id === $user->id;
    }

    /**
     * Vérifier si l'utilisateur peut supprimer une action
     */
    private function canDeleteAction(ReunionAction $action, User $user): bool
    {
        return $user->hasPermission('delete_reunion_actions') ||
               $action->creer_par === $user->id;
    }
}
