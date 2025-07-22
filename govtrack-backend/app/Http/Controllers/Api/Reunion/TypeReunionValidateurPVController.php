<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\TypeReunionValidateurPVService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TypeReunionValidateurPVController extends Controller
{
    protected $validateurPVService;

    public function __construct(TypeReunionValidateurPVService $validateurPVService)
    {
        $this->validateurPVService = $validateurPVService;
    }

    /**
     * Récupérer les validateurs d'un type de réunion
     */
    public function getValidateurs(Request $request, int $typeReunionId): JsonResponse
    {
        try {
            $filters = $request->only(['role_validateur', 'actif', 'user_id']);

            $result = $this->validateurPVService->getValidateurs($typeReunionId, $filters);

            return response()->json([
                'success' => true,
                'data' => $result['validateurs'],
                'total' => $result['total'],
                'filters_applied' => $result['filters_applied']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des validateurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer un validateur spécifique
     */
    public function getValidateur(int $validateurId): JsonResponse
    {
        try {
            $validateur = $this->validateurPVService->getValidateur($validateurId);

            return response()->json([
                'success' => true,
                'data' => $validateur
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du validateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un validateur de PV
     */
    public function createValidateur(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type_reunion_id' => 'required|exists:type_reunions,id',
                'role_validateur' => 'required|in:SECRETAIRE,PRESIDENT,AUTRE',
                'user_id' => 'nullable|exists:users,id',
                'ordre_priorite' => 'required|integer|min:1',
                'actif' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validateur = $this->validateurPVService->createValidateur($request->all(), $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Validateur créé avec succès',
                'data' => $validateur
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du validateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un validateur
     */
    public function updateValidateur(Request $request, int $validateurId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'role_validateur' => 'nullable|in:SECRETAIRE,PRESIDENT,AUTRE',
                'user_id' => 'nullable|exists:users,id',
                'ordre_priorite' => 'nullable|integer|min:1',
                'actif' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validateur = $this->validateurPVService->updateValidateur($validateurId, $request->all(), $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Validateur mis à jour avec succès',
                'data' => $validateur
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du validateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un validateur
     */
    public function deleteValidateur(int $validateurId): JsonResponse
    {
        try {
            $this->validateurPVService->deleteValidateur($validateurId, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Validateur supprimé avec succès'
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
     * Activer/Désactiver un validateur
     */
    public function toggleActif(Request $request, int $validateurId): JsonResponse
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

            $validateur = $this->validateurPVService->toggleActif($validateurId, $request->actif, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Statut du validateur mis à jour avec succès',
                'data' => $validateur
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
     * Réorganiser l'ordre des validateurs
     */
    public function reorderValidateurs(Request $request, int $typeReunionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'ordre_validateurs' => 'required|array',
                'ordre_validateurs.*' => 'integer|exists:type_reunion_validateur_pvs,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $this->validateurPVService->reorderValidateurs($typeReunionId, $request->ordre_validateurs, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Ordre des validateurs réorganisé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réorganisation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des validateurs
     */
    public function getStats(int $typeReunionId = null): JsonResponse
    {
        try {
            $stats = $this->validateurPVService->getStats($typeReunionId);

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
     * Copier les validateurs d'un type de réunion vers un autre
     */
    public function copierValidateurs(Request $request): JsonResponse
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

            $nombreCopie = $this->validateurPVService->copierValidateurs(
                $request->source_type_reunion_id,
                $request->destination_type_reunion_id,
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Validateurs copiés avec succès',
                'data' => [
                    'nombre_copie' => $nombreCopie
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la copie des validateurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
