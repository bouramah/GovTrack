<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProjetFavoriController extends Controller
{
    /**
     * Ajouter une instruction aux favoris
     */
    public function store(Request $request, int $projetId): JsonResponse
    {
        try {
            $user = $request->user();
            $projet = Projet::findOrFail($projetId);

            // Vérifier si l'instruction est déjà en favori
            if ($projet->estFavoriPour($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette instruction est déjà dans vos favoris'
                ], 400);
            }

            // Ajouter aux favoris
            $projet->favoris()->attach($user->id, [
                'date_ajout' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Instruction ajoutée aux favoris avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout aux favoris',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retirer une instruction des favoris
     */
    public function destroy(Request $request, int $projetId): JsonResponse
    {
        try {
            $user = $request->user();
            $projet = Projet::findOrFail($projetId);

            // Vérifier si l'instruction est en favori
            if (!$projet->estFavoriPour($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette instruction n\'est pas dans vos favoris'
                ], 400);
            }

            // Retirer des favoris
            $projet->favoris()->detach($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Instruction retirée des favoris avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du retrait des favoris',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Basculer l'état favori d'une instruction
     */
    public function toggle(Request $request, int $projetId): JsonResponse
    {
        try {
            $user = $request->user();
            $projet = Projet::findOrFail($projetId);

            $estFavori = $projet->estFavoriPour($user->id);

            // Log pour débogage
            Log::info("Toggle favori - Projet: {$projetId}, User: {$user->id}, État initial: " . ($estFavori ? 'true' : 'false'));

            if ($estFavori) {
                // Retirer des favoris
                $projet->favoris()->detach($user->id);
                $message = 'Instruction retirée des favoris';
                $action = 'removed';
            } else {
                // Ajouter aux favoris
                $projet->favoris()->attach($user->id, [
                    'date_ajout' => now()
                ]);
                $message = 'Instruction ajoutée aux favoris';
                $action = 'added';
            }

            $nouvelEtat = !$estFavori;

            // Log pour débogage
            Log::info("Toggle favori - Nouvel état: " . ($nouvelEtat ? 'true' : 'false') . ", Action: {$action}");

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'est_favori' => $nouvelEtat,
                    'action' => $action
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur toggle favori: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification des favoris',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir la liste des instructions favoris de l'utilisateur
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $projets = $user->projetsFavoris()
                ->with(['typeProjet', 'porteurs', 'donneurOrdre', 'taches'])
                ->orderBy('pivot_date_ajout', 'desc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $projets->items(),
                'pagination' => [
                    'current_page' => $projets->currentPage(),
                    'last_page' => $projets->lastPage(),
                    'per_page' => $projets->perPage(),
                    'total' => $projets->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des favoris',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
