<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\ReunionNotificationConfigService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReunionNotificationConfigController extends Controller
{
    protected $notificationConfigService;

    public function __construct(ReunionNotificationConfigService $notificationConfigService)
    {
        $this->notificationConfigService = $notificationConfigService;
    }

    /**
     * Récupérer les configurations d'un type de réunion
     */
    public function getConfigs(Request $request, int $typeReunionId): JsonResponse
    {
        try {
            $filters = $request->only(['type_notification', 'actif']);

            $result = $this->notificationConfigService->getConfigs($typeReunionId, $filters);

            return response()->json([
                'success' => true,
                'data' => $result['configs'],
                'total' => $result['total'],
                'filters_applied' => $result['filters_applied']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des configurations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer une configuration spécifique
     */
    public function getConfig(int $configId): JsonResponse
    {
        try {
            $config = $this->notificationConfigService->getConfig($configId);

            return response()->json([
                'success' => true,
                'data' => $config
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une configuration de notification
     */
    public function createConfig(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type_reunion_id' => 'required|exists:type_reunions,id',
                'type_notification' => 'required|in:CONFIRMATION_PRESENCE,RAPPEL,PV_DISPONIBLE,RAPPEL_ACTIONS',
                'actif' => 'nullable|boolean',
                'delai_jours' => 'nullable|integer|min:0',
                'template_email' => 'required|string|max:100',
                'destinataires_par_defaut' => 'nullable|array',
                'configuration_avancee' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $config = $this->notificationConfigService->createConfig($request->all(), $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Configuration créée avec succès',
                'data' => $config
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une configuration
     */
    public function updateConfig(Request $request, int $configId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type_notification' => 'nullable|in:CONFIRMATION_PRESENCE,RAPPEL,PV_DISPONIBLE,RAPPEL_ACTIONS',
                'actif' => 'nullable|boolean',
                'delai_jours' => 'nullable|integer|min:0',
                'template_email' => 'nullable|string|max:100',
                'destinataires_par_defaut' => 'nullable|array',
                'configuration_avancee' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $config = $this->notificationConfigService->updateConfig($configId, $request->all(), $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Configuration mise à jour avec succès',
                'data' => $config
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une configuration
     */
    public function deleteConfig(int $configId): JsonResponse
    {
        try {
            $this->notificationConfigService->deleteConfig($configId, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Configuration supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/Désactiver une configuration
     */
    public function toggleActif(Request $request, int $configId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'actif' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $config = $this->notificationConfigService->toggleActif($configId, $request->actif, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Statut de la configuration mis à jour avec succès',
                'data' => $config
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des configurations
     */
    public function getStats(int $typeReunionId = null): JsonResponse
    {
        try {
            $stats = $this->notificationConfigService->getStats($typeReunionId);

            return response()->json([
                'success' => true,
                'data' => $stats
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
     * Copier les configurations d'un type de réunion vers un autre
     */
    public function copierConfigs(Request $request): JsonResponse
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

            $nombreCopie = $this->notificationConfigService->copierConfigs(
                $request->source_type_reunion_id,
                $request->destination_type_reunion_id,
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Configurations copiées avec succès',
                'data' => [
                    'nombre_copie' => $nombreCopie
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la copie des configurations',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
