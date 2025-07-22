<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\ReunionDifficulteService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReunionDifficulteController extends Controller
{
    protected $difficulteService;

    public function __construct(ReunionDifficulteService $difficulteService)
    {
        $this->difficulteService = $difficulteService;
    }

    /**
     * Récupérer toutes les difficultés d'une réunion
     */
    public function getDifficultes(Request $request, int $reunionId): JsonResponse
    {
        try {
            $filters = $request->only(['statut', 'type_difficulte', 'niveau_gravite', 'impact_estime', 'responsable_id', 'search']);

            $result = $this->difficulteService->getDifficultes($reunionId, $filters);

            return response()->json([
                'success' => true,
                'data' => $result['difficultes'],
                'total' => $result['total'],
                'filters_applied' => $result['filters_applied']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des difficultés',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer une difficulté spécifique
     */
    public function getDifficulte(int $difficulteId): JsonResponse
    {
        try {
            $difficulte = $this->difficulteService->getDifficulte($difficulteId);

            return response()->json([
                'success' => true,
                'data' => $difficulte
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la difficulté',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une nouvelle difficulté
     */
    public function createDifficulte(Request $request, int $reunionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type_difficulte' => 'nullable|in:technique,organisationnel,ressource,communication,autre',
                'niveau_gravite' => 'nullable|in:critique,elevee,moyenne,faible',
                'statut' => 'nullable|in:ouverte,en_cours,resolue,escaladee',
                'impact_estime' => 'nullable|in:eleve,modere,faible',
                'solutions_proposees' => 'nullable|array',
                'actions_mitigation' => 'nullable|array',
                'responsable_id' => 'nullable|exists:users,id',
                'date_limite_resolution' => 'nullable|date',
                'progression_resolution' => 'nullable|integer|min:0|max:100',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $difficulte = $this->difficulteService->createDifficulte($request->all(), $reunionId);

            return response()->json([
                'success' => true,
                'message' => 'Difficulté créée avec succès',
                'data' => $difficulte
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la difficulté',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une difficulté
     */
    public function updateDifficulte(Request $request, int $difficulteId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'titre' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'type_difficulte' => 'nullable|in:technique,organisationnel,ressource,communication,autre',
                'niveau_gravite' => 'nullable|in:critique,elevee,moyenne,faible',
                'statut' => 'nullable|in:ouverte,en_cours,resolue,escaladee',
                'impact_estime' => 'nullable|in:eleve,modere,faible',
                'solutions_proposees' => 'nullable|array',
                'actions_mitigation' => 'nullable|array',
                'responsable_id' => 'nullable|exists:users,id',
                'date_limite_resolution' => 'nullable|date',
                'progression_resolution' => 'nullable|integer|min:0|max:100',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $difficulte = $this->difficulteService->updateDifficulte($difficulteId, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Difficulté mise à jour avec succès',
                'data' => $difficulte
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la difficulté',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une difficulté
     */
    public function deleteDifficulte(int $difficulteId): JsonResponse
    {
        try {
            $this->difficulteService->deleteDifficulte($difficulteId);

            return response()->json([
                'success' => true,
                'message' => 'Difficulté supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la difficulté',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Changer le statut d'une difficulté
     */
    public function changeStatut(Request $request, int $difficulteId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'statut' => 'required|in:ouverte,en_cours,resolue,escaladee',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Statut invalide',
                    'errors' => $validator->errors()
                ], 422);
            }

            $difficulte = $this->difficulteService->changeStatut($difficulteId, $request->statut);

            return response()->json([
                'success' => true,
                'message' => 'Statut de la difficulté changé avec succès',
                'data' => $difficulte
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
     * Mettre à jour la progression de résolution
     */
    public function updateProgressionResolution(Request $request, int $difficulteId): JsonResponse
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

            $difficulte = $this->difficulteService->updateProgressionResolution($difficulteId, $request->progression);

            return response()->json([
                'success' => true,
                'message' => 'Progression de résolution mise à jour avec succès',
                'data' => $difficulte
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
     * Ajouter une solution proposée
     */
    public function ajouterSolution(Request $request, int $difficulteId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'description' => 'required|string',
                'efficacite_estimee' => 'nullable|in:elevee,moyenne,faible',
                'cout_estime' => 'nullable|numeric|min:0',
                'delai_implementation' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de solution invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $difficulte = $this->difficulteService->ajouterSolution($difficulteId, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Solution ajoutée avec succès',
                'data' => $difficulte
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de la solution',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des difficultés
     */
    public function getStats(int $reunionId): JsonResponse
    {
        try {
            $stats = $this->difficulteService->getStats($reunionId);

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
     * Analyser les risques et difficultés
     */
    public function analyserRisques(int $reunionId): JsonResponse
    {
        try {
            $analyse = $this->difficulteService->analyserRisques($reunionId);

            return response()->json([
                'success' => true,
                'data' => $analyse
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'analyse des risques',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
