<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\ReunionGenereeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReunionGenereeController extends Controller
{
    protected $reunionGenereeService;

    public function __construct(ReunionGenereeService $reunionGenereeService)
    {
        $this->reunionGenereeService = $reunionGenereeService;
    }

    /**
     * Récupérer les réunions générées d'une série
     */
    public function getReunionsGenerees(Request $request, int $serieId): JsonResponse
    {
        try {
            $filters = $request->only(['statut_generation', 'date_debut', 'date_fin']);

            $result = $this->reunionGenereeService->getReunionsGenerees($serieId, $filters);

            return response()->json([
                'success' => true,
                'data' => $result['reunions_generees'],
                'total' => $result['total'],
                'filters_applied' => $result['filters_applied']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des réunions générées',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer une réunion générée spécifique
     */
    public function getReunionGeneree(int $reunionGenereeId): JsonResponse
    {
        try {
            $reunionGeneree = $this->reunionGenereeService->getReunionGeneree($reunionGenereeId);

            return response()->json([
                'success' => true,
                'data' => $reunionGeneree
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la réunion générée',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un enregistrement de réunion générée
     */
    public function createReunionGeneree(Request $request, int $serieId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'reunion_id' => 'required|exists:reunions,id',
                'statut_generation' => 'nullable|in:SUCCES,ERREUR',
                'message_erreur' => 'nullable|string',
                'configuration_utilisee' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $reunionGeneree = $this->reunionGenereeService->createReunionGeneree(
                $serieId,
                $request->reunion_id,
                $request->statut_generation ?? 'SUCCES',
                $request->message_erreur,
                $request->configuration_utilisee ?? [],
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Enregistrement de réunion générée créé avec succès',
                'data' => $reunionGeneree
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'enregistrement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour le statut d'une réunion générée
     */
    public function updateStatut(Request $request, int $reunionGenereeId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'statut_generation' => 'required|in:SUCCES,ERREUR',
                'message_erreur' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $reunionGeneree = $this->reunionGenereeService->updateStatut(
                $reunionGenereeId,
                $request->statut_generation,
                $request->message_erreur,
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour avec succès',
                'data' => $reunionGeneree
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un enregistrement de réunion générée
     */
    public function deleteReunionGeneree(int $reunionGenereeId): JsonResponse
    {
        try {
            $this->reunionGenereeService->deleteReunionGeneree($reunionGenereeId, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Enregistrement supprimé avec succès'
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
     * Obtenir les statistiques des réunions générées
     */
    public function getStats(int $serieId = null): JsonResponse
    {
        try {
            $stats = $this->reunionGenereeService->getStats($serieId);

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
     * Nettoyer les anciens enregistrements
     */
    public function nettoyerAnciensEnregistrements(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'jours_conservation' => 'nullable|integer|min:1|max:365',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $nombreSupprime = $this->reunionGenereeService->nettoyerAnciensEnregistrements(
                $request->jours_conservation ?? 90,
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Nettoyage terminé avec succès',
                'data' => [
                    'nombre_supprime' => $nombreSupprime
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du nettoyage',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
