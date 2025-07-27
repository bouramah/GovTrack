<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\ReunionDecisionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReunionDecisionController extends Controller
{
    protected $decisionService;

    public function __construct(ReunionDecisionService $decisionService)
    {
        $this->decisionService = $decisionService;
    }

    /**
     * Récupérer les décisions d'une réunion
     */
    public function getDecisions(Request $request, int $reunionId): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->decisionService->getDecisions($reunionId, $user);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des décisions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une nouvelle décision
     */
    public function createDecision(Request $request, int $reunionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'reunion_sujet_id' => 'nullable|integer|exists:reunion_sujets,id',
                'texte_decision' => 'required|string|max:2000',
                'type' => 'nullable|string|in:PROVISOIRE,DEFINITIVE',
                'responsables_ids' => 'nullable|array',
                'responsables_ids.*' => 'integer|exists:users,id',
                'date_limite' => 'nullable|date|after:today',
                'statut' => 'nullable|string|in:EN_ATTENTE,EN_COURS,TERMINEE',
                'priorite' => 'nullable|string|in:FAIBLE,NORMALE,ELEVEE,CRITIQUE',
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
            $result = $this->decisionService->createDecision($reunionId, $request->all(), $user);

            if ($result['success']) {
                return response()->json($result, 201);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la décision',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une décision
     */
    public function updateDecision(Request $request, int $decisionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'reunion_sujet_id' => 'nullable|integer|exists:reunion_sujets,id',
                'texte_decision' => 'nullable|string|max:2000',
                'type' => 'nullable|string|in:DEFINITIVE,PROVISOIRE',
                'responsables_ids' => 'nullable|array',
                'responsables_ids.*' => 'integer|exists:users,id',
                'date_limite' => 'nullable|date',
                'statut' => 'nullable|string|in:EN_ATTENTE,EN_COURS,TERMINEE',
                'priorite' => 'nullable|string|in:FAIBLE,NORMALE,ELEVEE,CRITIQUE',
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
            $result = $this->decisionService->updateDecision($decisionId, $request->all(), $user);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la décision',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une décision
     */
    public function deleteDecision(Request $request, int $decisionId): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->decisionService->deleteDecision($decisionId, $user);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la décision',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Changer le statut d'exécution d'une décision
     */
    public function changeStatutExecution(Request $request, int $decisionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'statut' => 'required|string|in:EN_ATTENTE,EN_COURS,TERMINEE',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $result = $this->decisionService->changeStatutExecution($decisionId, $request->statut, $user);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut d\'exécution',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les décisions en retard pour l'utilisateur connecté
     */
    public function getDecisionsEnRetard(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->decisionService->getDecisionsEnRetard($user);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des décisions en retard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des décisions
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'reunion_id' => 'nullable|integer|exists:reunions,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $reunionId = $request->reunion_id ?? null;
            $result = $this->decisionService->getDecisionStats($reunionId, $user);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
