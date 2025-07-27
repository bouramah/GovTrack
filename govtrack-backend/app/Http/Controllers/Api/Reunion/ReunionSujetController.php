<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\ReunionSujetService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReunionSujetController extends Controller
{
    protected $sujetService;

    public function __construct(ReunionSujetService $sujetService)
    {
        $this->sujetService = $sujetService;
    }

    /**
     * Récupérer tous les sujets d'une réunion
     */
    public function getSujets(Request $request, int $reunionId): JsonResponse
    {
        try {
            $filters = $request->only(['statut', 'projet_id', 'entite_id', 'niveau_detail', 'search']);

            $result = $this->sujetService->getSujets($reunionId, $filters);

            return response()->json([
                'success' => true,
                'data' => $result['sujets'],
                'total' => $result['total'],
                'filters_applied' => $result['filters_applied']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des sujets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer un sujet spécifique
     */
    public function getSujet(int $sujetId): JsonResponse
    {
        try {
            $sujet = $this->sujetService->getSujet($sujetId);

            return response()->json([
                'success' => true,
                'data' => $sujet
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du sujet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouveau sujet
     */
    public function createSujet(Request $request, int $reunionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'reunion_ordre_jour_id' => 'required|exists:reunion_ordre_jours,id',
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string',
                'difficulte_globale' => 'nullable|string',
                'recommandation' => 'nullable|string',
                'statut' => 'nullable|in:RESOLU,EN_COURS_DE_RESOLUTION,BLOQUE,AVIS,APPROVE,REJETE,EN_ATTENTE',
                'commentaire' => 'nullable|string',
                // pieces_jointes gérées séparément via PieceJointeSujetController
                'projet_id' => 'nullable|exists:projets,id',
                'entite_id' => 'nullable|exists:entites,id',
                'niveau_detail' => 'nullable|in:SIMPLE,DETAILLE',
                'objectifs_actifs' => 'nullable|boolean',
                'difficultes_actives' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $sujet = $this->sujetService->createSujet($request->all(), $reunionId, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Sujet créé avec succès',
                'data' => $sujet
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du sujet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer plusieurs sujets en lot avec pièces jointes
     */
    public function createMultipleSujets(Request $request, int $reunionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'sujets' => 'required|array|min:1',
                'sujets.*.reunion_ordre_jour_id' => 'required|exists:reunion_ordre_jours,id',
                'sujets.*.titre' => 'required|string|max:255',
                'sujets.*.description' => 'nullable|string',
                'sujets.*.difficulte_globale' => 'nullable|string',
                'sujets.*.recommandation' => 'nullable|string',
                'sujets.*.statut' => 'nullable|in:RESOLU,EN_COURS_DE_RESOLUTION,BLOQUE,AVIS,APPROVE,REJETE,EN_ATTENTE',
                'sujets.*.commentaire' => 'nullable|string',
                'sujets.*.projet_id' => 'nullable|exists:projets,id',
                'sujets.*.entite_id' => 'nullable|exists:entites,id',
                'sujets.*.niveau_detail' => 'nullable|in:SIMPLE,DETAILLE',
                'sujets.*.objectifs_actifs' => 'nullable|boolean',
                'sujets.*.difficultes_actives' => 'nullable|boolean',
            ]);

            // Validation des fichiers
            $files = $request->allFiles();
            foreach ($files as $key => $fileArray) {
                if (is_array($fileArray)) {
                    foreach ($fileArray as $file) {
                        if ($file && !$file->isValid()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Fichier invalide détecté',
                                'error' => 'Un ou plusieurs fichiers sont corrompus'
                            ], 422);
                        }
                    }
                }
            }

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->sujetService->createMultipleSujets(
                $request->input('sujets'),
                $files,
                $reunionId,
                $request->user()->id
            );

            if ($result['success']) {
                return response()->json($result, 201);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création multiple des sujets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un sujet
     */
    public function updateSujet(Request $request, int $sujetId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'titre' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'difficulte_globale' => 'nullable|string',
                'recommandation' => 'nullable|string',
                'statut' => 'nullable|in:RESOLU,EN_COURS_DE_RESOLUTION,BLOQUE,AVIS,APPROUVE,REJETE,EN_ATTENTE',
                'commentaire' => 'nullable|string',
                'projet_id' => 'nullable|exists:projets,id',
                'entite_id' => 'nullable|exists:entites,id',
                'niveau_detail' => 'nullable|in:SIMPLE,DETAILLE',
                'objectifs_actifs' => 'nullable|boolean',
                'difficultes_actives' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $sujet = $this->sujetService->updateSujet($sujetId, $request->all(), $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Sujet mis à jour avec succès',
                'data' => $sujet
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du sujet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un sujet
     */
    public function deleteSujet(int $sujetId): JsonResponse
    {
        try {
            $this->sujetService->deleteSujet($sujetId);

            return response()->json([
                'success' => true,
                'message' => 'Sujet supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du sujet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Changer le statut d'un sujet
     */
    public function changeStatut(Request $request, int $sujetId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'statut' => 'required|in:EN_ATTENTE,EN_COURS_DE_RESOLUTION,RESOLU,BLOQUE,AVIS,APPROUVE,REJETE',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Statut invalide',
                    'errors' => $validator->errors()
                ], 422);
            }

            $sujet = $this->sujetService->changeStatut($sujetId, $request->statut, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Statut du sujet changé avec succès',
                'data' => $sujet
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
     * Réorganiser l'ordre des sujets
     */
    public function reorderSujets(Request $request, int $reunionId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'ordre_sujets' => 'required|array',
                'ordre_sujets.*.id' => 'required|integer|exists:reunion_sujets,id',
                'ordre_sujets.*.ordre' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ordre des sujets invalide',
                    'errors' => $validator->errors()
                ], 422);
            }

            $this->sujetService->reorderSujets($reunionId, $request->ordre_sujets);

            return response()->json([
                'success' => true,
                'message' => 'Ordre des sujets réorganisé avec succès'
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
     * Obtenir les statistiques des sujets
     */
    public function getStats(int $reunionId): JsonResponse
    {
        try {
            $stats = $this->sujetService->getStats($reunionId);

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
}
