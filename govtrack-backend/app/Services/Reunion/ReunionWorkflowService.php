<?php

namespace App\Services\Reunion;

use App\Models\Reunion;
use App\Models\ReunionWorkflowConfig;
use App\Models\ReunionWorkflowExecution;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReunionWorkflowService
{
    /**
     * Récupérer les workflows configurés pour un type de réunion
     */
    public function getWorkflowConfigs(int $typeReunionId, User $user): array
    {
        try {
            $workflows = ReunionWorkflowConfig::with([
                'typeReunion',
                'createur',
                'modificateur'
            ])
            ->where('type_reunion_id', $typeReunionId)
            ->where('actif', true)
            ->orderBy('nom_workflow')
            ->get();

            return [
                'success' => true,
                'data' => $workflows,
                'message' => 'Workflows récupérés avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des workflows', [
                'type_reunion_id' => $typeReunionId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des workflows',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Créer un workflow de validation
     */
    public function createWorkflowConfig(array $data, User $user): array
    {
        try {
            DB::beginTransaction();

            // Vérifier les permissions
            if (!$this->canCreateWorkflow($user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour créer un workflow'
                ];
            }

            $workflowData = [
                'type_reunion_id' => $data['type_reunion_id'],
                'nom_workflow' => $data['nom_workflow'],
                'etapes' => $data['etapes'],
                'actif' => $data['actif'] ?? true,
                'obligatoire' => $data['obligatoire'] ?? true,
                'configuration' => $data['configuration'] ?? [],
                'creer_par' => $user->id,
                'modifier_par' => $user->id,
                'date_creation' => now(),
                'date_modification' => now(),
            ];

            $workflow = ReunionWorkflowConfig::create($workflowData);

            DB::commit();

            return [
                'success' => true,
                'data' => $workflow,
                'message' => 'Workflow créé avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du workflow', [
                'data' => $data,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la création du workflow',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Démarrer un workflow pour une réunion
     */
    public function startWorkflow(int $reunionId, int $workflowConfigId, User $user): array
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

            $workflowConfig = ReunionWorkflowConfig::find($workflowConfigId);
            if (!$workflowConfig) {
                return [
                    'success' => false,
                    'message' => 'Configuration de workflow non trouvée'
                ];
            }

            // Vérifier si un workflow est déjà en cours
            $workflowEnCours = ReunionWorkflowExecution::where('reunion_id', $reunionId)
                ->where('statut_global', 'EN_COURS')
                ->first();

            if ($workflowEnCours) {
                return [
                    'success' => false,
                    'message' => 'Un workflow est déjà en cours pour cette réunion'
                ];
            }

            // Créer l'exécution du workflow
            $executionData = [
                'reunion_id' => $reunionId,
                'workflow_config_id' => $workflowConfigId,
                'etape_actuelle' => 1,
                'statut_global' => 'EN_COURS',
                'date_debut' => now(),
                'historique_etapes' => [],
                'commentaire' => 'Workflow démarré par ' . $user->nom . ' ' . $user->prenom,
                'date_creation' => now(),
                'date_modification' => now(),
            ];

            $execution = ReunionWorkflowExecution::create($executionData);

            // Initialiser la première étape
            $this->initialiserEtape($execution, 1);

            DB::commit();

            return [
                'success' => true,
                'data' => $execution,
                'message' => 'Workflow démarré avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du démarrage du workflow', [
                'reunion_id' => $reunionId,
                'workflow_config_id' => $workflowConfigId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors du démarrage du workflow',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Valider une étape du workflow
     */
    public function validateEtape(int $executionId, int $etape, User $user, string $commentaire = null): array
    {
        try {
            DB::beginTransaction();

            $execution = ReunionWorkflowExecution::with('workflowConfig')->find($executionId);
            if (!$execution) {
                return [
                    'success' => false,
                    'message' => 'Exécution de workflow non trouvée'
                ];
            }

            // Vérifier que l'étape peut être validée
            if ($execution->etape_actuelle !== $etape) {
                return [
                    'success' => false,
                    'message' => 'Cette étape ne peut pas être validée actuellement'
                ];
            }

            // Mettre à jour l'historique
            $historique = $execution->historique_etapes ?? [];
            $historique[] = [
                'etape' => $etape,
                'validateur' => $user->id,
                'statut' => 'VALIDE',
                'date' => now()->toDateTimeString(),
                'commentaire' => $commentaire ?? 'Validation effectuée'
            ];

            $execution->historique_etapes = $historique;

            // Passer à l'étape suivante ou terminer
            $etapes = $execution->workflowConfig->etapes;
            $etapeSuivante = $etape + 1;

            if ($etapeSuivante <= count($etapes)) {
                $execution->etape_actuelle = $etapeSuivante;
                $this->initialiserEtape($execution, $etapeSuivante);
            } else {
                $execution->statut_global = 'TERMINE';
                $execution->date_fin = now();
            }

            $execution->date_modification = now();
            $execution->save();

            DB::commit();

            return [
                'success' => true,
                'data' => $execution,
                'message' => 'Étape validée avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la validation de l\'étape', [
                'execution_id' => $executionId,
                'etape' => $etape,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la validation de l\'étape',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Rejeter une étape du workflow
     */
    public function rejectEtape(int $executionId, int $etape, User $user, string $raison): array
    {
        try {
            DB::beginTransaction();

            $execution = ReunionWorkflowExecution::find($executionId);
            if (!$execution) {
                return [
                    'success' => false,
                    'message' => 'Exécution de workflow non trouvée'
                ];
            }

            // Mettre à jour l'historique
            $historique = $execution->historique_etapes ?? [];
            $historique[] = [
                'etape' => $etape,
                'validateur' => $user->id,
                'statut' => 'REJETE',
                'date' => now()->toDateTimeString(),
                'commentaire' => $raison
            ];

            $execution->historique_etapes = $historique;
            $execution->statut_global = 'BLOQUE';
            $execution->date_modification = now();
            $execution->save();

            DB::commit();

            return [
                'success' => true,
                'data' => $execution,
                'message' => 'Étape rejetée avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du rejet de l\'étape', [
                'execution_id' => $executionId,
                'etape' => $etape,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors du rejet de l\'étape',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les workflows en cours pour un utilisateur
     */
    public function getWorkflowsEnCours(User $user): array
    {
        try {
            $workflows = ReunionWorkflowExecution::with([
                'reunion',
                'workflowConfig.typeReunion'
            ])
            ->where('statut_global', 'EN_COURS')
            ->get()
            ->filter(function ($execution) use ($user) {
                $etapes = $execution->workflowConfig->etapes ?? [];
                foreach ($etapes as $etape) {
                    if (($etape['validateur_id'] ?? null) == $user->id) {
                        return true;
                    }
                }
                return false;
            });

            return [
                'success' => true,
                'data' => $workflows,
                'message' => 'Workflows en cours récupérés avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des workflows en cours', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des workflows en cours',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Initialiser une étape du workflow
     */
    private function initialiserEtape(ReunionWorkflowExecution $execution, int $etape): void
    {
        $etapes = $execution->workflowConfig->etapes;
        $etapeConfig = $etapes[$etape - 1] ?? null;

        if ($etapeConfig) {
            // Envoyer notification au validateur si configuré
            if ($etapeConfig['notifier_validateur'] ?? false) {
                // TODO: Implémenter notification
            }
        }
    }

        /**
     * Obtenir les détails d'une exécution de workflow
     */
    public function getWorkflowExecution(int $executionId, User $user): array
    {
        try {
            $execution = ReunionWorkflowExecution::with([
                'reunion',
                'workflowConfig.typeReunion'
            ])->findOrFail($executionId);

            return [
                'success' => true,
                'data' => $execution,
                'message' => 'Exécution de workflow récupérée avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de l\'exécution de workflow', [
                'execution_id' => $executionId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'exécution',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Annuler un workflow en cours
     */
    public function cancelWorkflow(int $executionId, string $raison, User $user): array
    {
        try {
            DB::beginTransaction();

            $execution = ReunionWorkflowExecution::findOrFail($executionId);

            // Vérifier que le workflow est en cours
            if ($execution->statut_global !== 'EN_COURS') {
                return [
                    'success' => false,
                    'message' => 'Seuls les workflows en cours peuvent être annulés'
                ];
            }

            // Mettre à jour l'historique
            $historique = $execution->historique_etapes ?? [];
            $historique[] = [
                'etape' => $execution->etape_actuelle,
                'validateur' => $user->id,
                'statut' => 'ANNULE',
                'date' => now()->toDateTimeString(),
                'commentaire' => $raison
            ];

            $execution->historique_etapes = $historique;
            $execution->statut_global = 'ANNULE';
            $execution->date_modification = now();
            $execution->save();

            DB::commit();

            return [
                'success' => true,
                'data' => $execution,
                'message' => 'Workflow annulé avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'annulation du workflow', [
                'execution_id' => $executionId,
                'raison' => $raison,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'annulation du workflow',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier si l'utilisateur peut créer un workflow
     */
    private function canCreateWorkflow(User $user): bool
    {
        return $user->hasPermission('create_reunion_workflow') ||
               $user->hasRole('admin');
    }
}
