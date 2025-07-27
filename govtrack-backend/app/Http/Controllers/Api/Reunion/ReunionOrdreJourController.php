<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\ReunionOrdreJourService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReunionOrdreJourController extends Controller
{
    protected $ordreJourService;

    public function __construct(ReunionOrdreJourService $ordreJourService)
    {
        $this->ordreJourService = $ordreJourService;
    }

    /**
     * Récupérer l'ordre du jour d'une réunion
     */
    public function getOrdreJour(Request $request, int $reunionId): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->ordreJourService->getOrdreJour($reunionId, $user);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'ordre du jour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajouter un point à l'ordre du jour
     */
    public function addPoint(Request $request, int $reunionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'type' => 'nullable|string|in:SUJET_SPECIFIQUE,POINT_DIVERS,SUIVI_PROJETS',
                'duree_estimee_minutes' => 'nullable|integer|min:1|max:480',
                'responsable_id' => 'nullable|integer|exists:users,id',
                'ordre' => 'nullable|integer|min:1',
                'niveau_detail' => 'nullable|string|in:SIMPLE,DETAILLE',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $result = $this->ordreJourService->addPointOrdreJour($reunionId, $request->all(), $user);

            if ($result['success']) {
                return response()->json($result, 201);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du point à l\'ordre du jour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajouter plusieurs points à l'ordre du jour
     */
    public function addMultiplePoints(Request $request, int $reunionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'points' => 'required|array|min:1',
                'points.*.titre' => 'required|string|max:255',
                'points.*.description' => 'nullable|string|max:1000',
                'points.*.type' => 'nullable|string|in:SUJET_SPECIFIQUE,POINT_DIVERS,SUIVI_PROJETS',
                'points.*.duree_estimee_minutes' => 'nullable|integer|min:1|max:480',
                'points.*.responsable_id' => 'nullable|integer|exists:users,id',
                'points.*.ordre' => 'nullable|integer|min:1',
                'points.*.niveau_detail' => 'nullable|string|in:SIMPLE,DETAILLE',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $result = $this->ordreJourService->addMultiplePointsOrdreJour($reunionId, $request->input('points'), $user);

            if ($result['success']) {
                return response()->json($result, 201);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout multiple des points à l\'ordre du jour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un point de l'ordre du jour
     */
    public function updatePoint(Request $request, int $pointId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'titre' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
                'type' => 'nullable|string|in:SUJET_SPECIFIQUE,POINT_DIVERS,SUIVI_PROJETS',
                'duree_estimee_minutes' => 'nullable|integer|min:1|max:480',
                'responsable_id' => 'nullable|integer|exists:users,id',
                'ordre' => 'nullable|integer|min:1',
                'statut' => 'nullable|string|in:PLANIFIE,EN_COURS,TERMINE,REPORTE',
                'niveau_detail' => 'nullable|string|in:SIMPLE,DETAILLE',
                'commentaires' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $result = $this->ordreJourService->updatePointOrdreJour($pointId, $request->all(), $user);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du point',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un point de l'ordre du jour
     */
    public function deletePoint(Request $request, int $pointId): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->ordreJourService->deletePointOrdreJour($pointId, $user);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du point',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Réorganiser l'ordre des points
     */
    public function reorderPoints(Request $request, int $reunionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'new_order' => 'required|array|min:1',
                'new_order.*.id' => 'required|integer|exists:reunion_ordre_jours,id',
                'new_order.*.ordre' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $result = $this->ordreJourService->reorderPoints($reunionId, $request->new_order, $user);

            if ($result['success']) {
                return response()->json($result, 200);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réorganisation de l\'ordre du jour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Changer le statut d'un point
     */
    public function changeStatut(Request $request, int $pointId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'statut' => 'required|string|in:PLANIFIE,EN_COURS,TERMINE,REPORTE',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $result = $this->ordreJourService->changeStatutPoint($pointId, $request->statut, $user);

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
     * Obtenir les statistiques de l'ordre du jour
     */
    public function getStats(Request $request, int $reunionId): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->ordreJourService->getOrdreJourStats($reunionId, $user);

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
