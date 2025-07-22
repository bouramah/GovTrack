<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\TypeReunionGestionnaireService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TypeReunionGestionnaireController extends Controller
{
    protected $gestionnaireService;

    public function __construct(TypeReunionGestionnaireService $gestionnaireService)
    {
        $this->gestionnaireService = $gestionnaireService;
    }

    /**
     * Récupérer les gestionnaires d'un type de réunion
     */
    public function getGestionnaires(Request $request, int $typeReunionId): JsonResponse
    {
        try {
            $filters = $request->only(['actif', 'search']);

            $result = $this->gestionnaireService->getGestionnaires($typeReunionId, $filters);

            return response()->json([
                'success' => $result['success'],
                'data' => $result['data'] ?? [],
                'total' => $result['total'] ?? 0,
                'filters_applied' => $result['filters_applied'] ?? [],
                'message' => $result['message'] ?? 'Gestionnaires récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des gestionnaires',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajouter un gestionnaire
     */
    public function addGestionnaire(Request $request, int $typeReunionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'permissions' => 'nullable|array',
                'actif' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->gestionnaireService->addGestionnaire($typeReunionId, $request->all(), $request->user()->id);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ], $result['success'] ? 201 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du gestionnaire',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un gestionnaire
     */
    public function updateGestionnaire(Request $request, int $typeReunionId, int $gestionnaireId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'permissions' => 'nullable|array',
                'actif' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->gestionnaireService->updateGestionnaire($typeReunionId, $gestionnaireId, $request->all(), $request->user()->id);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ], $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du gestionnaire',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un gestionnaire
     */
    public function removeGestionnaire(int $typeReunionId, int $gestionnaireId): JsonResponse
    {
        try {
            $result = $this->gestionnaireService->removeGestionnaire($typeReunionId, $gestionnaireId, auth()->id());

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message']
            ], $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du gestionnaire',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier si un utilisateur est gestionnaire
     */
    public function isGestionnaire(int $typeReunionId, int $userId): JsonResponse
    {
        try {
            $isGestionnaire = $this->gestionnaireService->isGestionnaire($typeReunionId, $userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'is_gestionnaire' => $isGestionnaire,
                    'type_reunion_id' => $typeReunionId,
                    'user_id' => $userId
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les permissions d'un gestionnaire
     */
    public function getGestionnairePermissions(int $typeReunionId, int $userId): JsonResponse
    {
        try {
            $permissions = $this->gestionnaireService->getGestionnairePermissions($typeReunionId, $userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'permissions' => $permissions,
                    'type_reunion_id' => $typeReunionId,
                    'user_id' => $userId
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des gestionnaires
     */
    public function getStats(int $typeReunionId = null): JsonResponse
    {
        try {
            $result = $this->gestionnaireService->getStats($typeReunionId);

            return response()->json([
                'success' => $result['success'],
                'data' => $result['data'] ?? [],
                'message' => $result['message'] ?? 'Statistiques récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Copier les gestionnaires d'un type de réunion vers un autre
     */
    public function copierGestionnaires(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'source_type_reunion_id' => 'required|exists:type_reunions,id',
                'destination_type_reunion_id' => 'required|exists:type_reunions,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->gestionnaireService->copierGestionnaires(
                $request->source_type_reunion_id,
                $request->destination_type_reunion_id,
                $request->user()->id
            );

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ], $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la copie des gestionnaires',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
