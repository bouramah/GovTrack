<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\ReunionActionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReunionActionController extends Controller
{
    protected $actionService;

    public function __construct(ReunionActionService $actionService)
    {
        $this->actionService = $actionService;
    }

    /**
     * Récupérer les actions d'une réunion
     */
    public function getActions(Request $request, int $reunionId): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->actionService->getActions($reunionId, $user);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des actions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une nouvelle action
     */
    public function createAction(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'reunion_id' => 'nullable|integer|exists:reunions,id',
                'decision_id' => 'nullable|integer|exists:reunion_decisions,id',
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'responsable_id' => 'required|integer|exists:users,id',
                'date_limite' => 'nullable|date|after:today',
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

            // Vérifier qu'au moins une des deux relations est fournie
            if (!$request->reunion_id && !$request->decision_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une action doit être liée à une réunion ou une décision',
                    'errors' => ['reunion_id' => ['Au moins une relation est requise']]
                ], 422);
            }

            $user = $request->user();
            $result = $this->actionService->createAction($request->all(), $user);

            if ($result['success']) {
                return response()->json($result, 201);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'action',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une action
     */
    public function updateAction(Request $request, int $actionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'titre' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
                'responsable_id' => 'nullable|integer|exists:users,id',
                'date_limite' => 'nullable|date',
                'statut' => 'nullable|string|in:A_FAIRE,EN_COURS,TERMINEE',
                'priorite' => 'nullable|string|in:FAIBLE,NORMALE,ELEVEE,CRITIQUE',
                'progression' => 'nullable|integer|min:0|max:100',
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
            $result = $this->actionService->updateAction($actionId, $request->all(), $user);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'action',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une action
     */
    public function deleteAction(Request $request, int $actionId): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->actionService->deleteAction($actionId, $user);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'action',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Changer le statut d'une action
     */
    public function changeStatut(Request $request, int $actionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'statut' => 'required|string|in:A_FAIRE,EN_COURS,TERMINEE',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $result = $this->actionService->changeStatut($actionId, $request->statut, $user);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour la progression d'une action
     */
    public function updateProgression(Request $request, int $actionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'progression' => 'required|integer|min:0|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $result = $this->actionService->updateProgression($actionId, $request->progression, $user);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la progression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les actions en retard pour l'utilisateur connecté
     */
    public function getActionsEnRetard(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->actionService->getActionsEnRetard($user);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des actions en retard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des actions
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
            $result = $this->actionService->getActionStats($reunionId, $user);

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
