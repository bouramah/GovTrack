<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\ReunionWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReunionWorkflowController extends Controller
{
    protected $workflowService;

    public function __construct(ReunionWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Récupérer les workflows configurés pour un type de réunion
     */
    public function getWorkflowConfigs(Request $request, int $typeReunionId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $result = $this->workflowService->getWorkflowConfigs($typeReunionId, $user);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des workflows',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un workflow de validation
     */
    public function createWorkflowConfig(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type_reunion_id' => 'required|integer|exists:type_reunions,id',
                'nom_workflow' => 'required|string|max:255',
                'etapes' => 'required|array|min:1',
                'etapes.*.nom' => 'required|string|max:255',
                'etapes.*.validateur_id' => 'required|integer|exists:users,id',
                'etapes.*.ordre' => 'required|integer|min:1',
                'etapes.*.notifier_validateur' => 'boolean',
                'actif' => 'boolean',
                'obligatoire' => 'boolean',
                'configuration' => 'array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $result = $this->workflowService->createWorkflowConfig($request->all(), $user);

            if ($result['success']) {
                return response()->json($result, 201);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du workflow',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Démarrer un workflow pour une réunion
     */
    public function startWorkflow(Request $request, int $reunionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'workflow_config_id' => 'required|integer|exists:reunion_workflow_configs,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $result = $this->workflowService->startWorkflow(
                $reunionId, 
                $request->workflow_config_id, 
                $user
            );

            if ($result['success']) {
                return response()->json($result, 201);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du démarrage du workflow',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valider une étape du workflow
     */
    public function validateEtape(Request $request, int $executionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'etape' => 'required|integer|min:1',
                'commentaire' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $result = $this->workflowService->validateEtape(
                $executionId,
                $request->etape,
                $user,
                $request->commentaire
            );

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation de l\'étape',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rejeter une étape du workflow
     */
    public function rejectEtape(Request $request, int $executionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'etape' => 'required|integer|min:1',
                'raison' => 'required|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $result = $this->workflowService->rejectEtape(
                $executionId,
                $request->etape,
                $user,
                $request->raison
            );

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rejet de l\'étape',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les workflows en cours pour l'utilisateur connecté
     */
    public function getWorkflowsEnCours(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->workflowService->getWorkflowsEnCours($user);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des workflows en cours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les détails d'une exécution de workflow
     */
    public function getWorkflowExecution(Request $request, int $executionId): JsonResponse
    {
        try {
            $user = $request->user();
            
            // TODO: Implémenter la méthode dans le service
            $result = [
                'success' => false,
                'message' => 'Méthode non implémentée'
            ];

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'exécution',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Annuler un workflow en cours
     */
    public function cancelWorkflow(Request $request, int $executionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'raison' => 'required|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            
            // TODO: Implémenter la méthode dans le service
            $result = [
                'success' => false,
                'message' => 'Méthode non implémentée'
            ];

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation du workflow',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 