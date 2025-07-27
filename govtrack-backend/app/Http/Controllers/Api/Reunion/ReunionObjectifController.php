<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\ReunionObjectifService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReunionObjectifController extends Controller
{
    protected $objectifService;

    public function __construct(ReunionObjectifService $objectifService)
    {
        $this->objectifService = $objectifService;
    }

    /**
     * Récupérer tous les objectifs d'une réunion
     */
    public function getObjectifs(Request $request, int $reunionId): JsonResponse
    {
        try {
            $filters = $request->only(['statut', 'type_objectif', 'priorite', 'responsable_id', 'search']);

            $result = $this->objectifService->getObjectifs($reunionId, $filters);

            return response()->json([
                'success' => true,
                'data' => $result['objectifs'],
                'total' => $result['total'],
                'filters_applied' => $result['filters_applied']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des objectifs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer un objectif spécifique
     */
    public function getObjectif(int $objectifId): JsonResponse
    {
        try {
            $objectif = $this->objectifService->getObjectif($objectifId);

            return response()->json([
                'success' => true,
                'data' => $objectif
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'objectif',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouvel objectif
     */
    public function createObjectif(Request $request, int $reunionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'reunion_sujet_id' => 'required|exists:reunion_sujets,id',
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string',
                'cible' => 'nullable|string',
                'taux_realisation' => 'nullable|integer|min:0|max:100',
                'pourcentage_decaissement' => 'nullable|numeric|min:0|max:100',
                'date_objectif' => 'nullable|date',
                'statut' => 'nullable|in:EN_COURS,ATTEINT,EN_RETARD',
                'ordre' => 'nullable|integer|min:1',
                'actif' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $objectif = $this->objectifService->createObjectif($request->all(), $reunionId, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Objectif créé avec succès',
                'data' => $objectif
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'objectif',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer plusieurs objectifs en lot
     */
    public function createMultipleObjectifs(Request $request, int $reunionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'objectifs' => 'required|array|min:1',
                'objectifs.*.reunion_sujet_id' => 'required|exists:reunion_sujets,id',
                'objectifs.*.titre' => 'required|string|max:255',
                'objectifs.*.description' => 'nullable|string',
                'objectifs.*.cible' => 'nullable|string',
                'objectifs.*.taux_realisation' => 'nullable|integer|min:0|max:100',
                'objectifs.*.pourcentage_decaissement' => 'nullable|numeric|min:0|max:100',
                'objectifs.*.date_objectif' => 'nullable|date',
                'objectifs.*.statut' => 'nullable|in:EN_COURS,ATTEINT,EN_RETARD',
                'objectifs.*.ordre' => 'nullable|integer|min:1',
                'objectifs.*.actif' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->objectifService->createMultipleObjectifs($request->input('objectifs'), $reunionId, $request->user()->id);

            if ($result['success']) {
                return response()->json($result, 201);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création multiple des objectifs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un objectif
     */
    public function updateObjectif(Request $request, int $objectifId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'titre' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'type_objectif' => 'nullable|in:general,strategique,operationnel,technique',
                'priorite' => 'nullable|in:haute,normale,basse',
                'statut' => 'nullable|in:en_cours,termine,annule',
                'date_objectif' => 'nullable|date',
                'indicateurs_mesure' => 'nullable|array',
                'criteres_succes' => 'nullable|array',
                'risques_identifies' => 'nullable|array',
                'actions_requises' => 'nullable|array',
                'responsable_id' => 'nullable|exists:users,id',
                'progression' => 'nullable|integer|min:0|max:100',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $objectif = $this->objectifService->updateObjectif($objectifId, $request->all(), $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Objectif mis à jour avec succès',
                'data' => $objectif
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'objectif',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un objectif
     */
    public function deleteObjectif(Request $request, int $objectifId): JsonResponse
    {
        try {
            $this->objectifService->deleteObjectif($objectifId, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Objectif supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'objectif',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Changer le statut d'un objectif
     */
    public function changeStatut(Request $request, int $objectifId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'statut' => 'required|in:en_cours,termine,annule',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Statut invalide',
                    'errors' => $validator->errors()
                ], 422);
            }

            $objectif = $this->objectifService->changeStatut($objectifId, $request->statut, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Statut de l\'objectif changé avec succès',
                'data' => $objectif
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
     * Mettre à jour la progression d'un objectif
     */
    public function updateProgression(Request $request, int $objectifId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'progression' => 'required|integer|min:0|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Progression invalide',
                    'errors' => $validator->errors()
                ], 422);
            }

            $objectif = $this->objectifService->updateProgression($objectifId, $request->progression, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Progression mise à jour avec succès',
                'data' => $objectif
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la progression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des objectifs
     */
    public function getStats(int $reunionId): JsonResponse
    {
        try {
            $stats = $this->objectifService->getStats($reunionId);

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
     * Évaluer la réalisation des objectifs
     */
    public function evaluerRealisation(int $reunionId): JsonResponse
    {
        try {
            $evaluation = $this->objectifService->evaluerRealisation($reunionId);

            return response()->json([
                'success' => true,
                'data' => $evaluation
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'évaluation des objectifs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
