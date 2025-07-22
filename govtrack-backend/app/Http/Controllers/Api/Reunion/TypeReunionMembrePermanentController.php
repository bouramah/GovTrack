<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\TypeReunionMembrePermanentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TypeReunionMembrePermanentController extends Controller
{
    protected $membrePermanentService;

    public function __construct(TypeReunionMembrePermanentService $membrePermanentService)
    {
        $this->membrePermanentService = $membrePermanentService;
    }

    /**
     * Récupérer les membres permanents d'un type de réunion
     */
    public function getMembresPermanents(Request $request, int $typeReunionId): JsonResponse
    {
        try {
            $filters = $request->only(['role_defaut', 'actif', 'search']);

            $result = $this->membrePermanentService->getMembresPermanents($typeReunionId, $filters);

            return response()->json([
                'success' => $result['success'],
                'data' => $result['data'] ?? [],
                'total' => $result['total'] ?? 0,
                'filters_applied' => $result['filters_applied'] ?? [],
                'message' => $result['message'] ?? 'Membres permanents récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des membres permanents',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajouter un membre permanent
     */
    public function addMembrePermanent(Request $request, int $typeReunionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'role_defaut' => 'nullable|in:PRESIDENT,SECRETAIRE,PARTICIPANT,OBSERVATEUR',
                'actif' => 'nullable|boolean',
                'notifications_par_defaut' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->membrePermanentService->addMembrePermanent($typeReunionId, $request->all(), $request->user()->id);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ], $result['success'] ? 201 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du membre permanent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un membre permanent
     */
    public function updateMembrePermanent(Request $request, int $typeReunionId, int $membreId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'role_defaut' => 'nullable|in:PRESIDENT,SECRETAIRE,PARTICIPANT,OBSERVATEUR',
                'actif' => 'nullable|boolean',
                'notifications_par_defaut' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->membrePermanentService->updateMembrePermanent($typeReunionId, $membreId, $request->all(), $request->user()->id);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ], $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du membre permanent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un membre permanent
     */
    public function removeMembrePermanent(int $typeReunionId, int $membreId): JsonResponse
    {
        try {
            $result = $this->membrePermanentService->removeMembrePermanent($typeReunionId, $membreId, auth()->id());

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message']
            ], $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du membre permanent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier si un utilisateur est membre permanent
     */
    public function isMembrePermanent(int $typeReunionId, int $userId): JsonResponse
    {
        try {
            $isMembrePermanent = $this->membrePermanentService->isMembrePermanent($typeReunionId, $userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'is_membre_permanent' => $isMembrePermanent,
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
     * Obtenir le rôle par défaut d'un membre permanent
     */
    public function getMembreRoleDefaut(int $typeReunionId, int $userId): JsonResponse
    {
        try {
            $roleDefaut = $this->membrePermanentService->getMembreRoleDefaut($typeReunionId, $userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'role_defaut' => $roleDefaut,
                    'type_reunion_id' => $typeReunionId,
                    'user_id' => $userId
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du rôle par défaut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les notifications par défaut d'un membre permanent
     */
    public function getMembreNotificationsDefaut(int $typeReunionId, int $userId): JsonResponse
    {
        try {
            $notifications = $this->membrePermanentService->getMembreNotificationsDefaut($typeReunionId, $userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'notifications_par_defaut' => $notifications,
                    'type_reunion_id' => $typeReunionId,
                    'user_id' => $userId
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des notifications par défaut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des membres permanents
     */
    public function getStats(int $typeReunionId = null): JsonResponse
    {
        try {
            $result = $this->membrePermanentService->getStats($typeReunionId);

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
     * Copier les membres permanents d'un type de réunion vers un autre
     */
    public function copierMembresPermanents(Request $request): JsonResponse
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

            $result = $this->membrePermanentService->copierMembresPermanents(
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
                'message' => 'Erreur lors de la copie des membres permanents',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
