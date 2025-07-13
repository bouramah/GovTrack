<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TypeTache;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class TypeTacheController extends Controller
{
    /**
     * Récupérer la liste des types de tâches
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = TypeTache::query();

            // Filtres
            if ($request->has('nom')) {
                $query->where('nom', 'like', '%' . $request->nom . '%');
            }

            if ($request->has('actif')) {
                $query->where('actif', $request->boolean('actif'));
            }

            // Tri
            $sortBy = $request->get('sort_by', 'ordre');
            $sortOrder = $request->get('sort_order', 'asc');

            if ($sortBy === 'ordre') {
                $query->byOrdre();
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $typeTaches = $query->withCount('taches')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $typeTaches->items(),
                'pagination' => [
                    'current_page' => $typeTaches->currentPage(),
                    'last_page' => $typeTaches->lastPage(),
                    'per_page' => $typeTaches->perPage(),
                    'total' => $typeTaches->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des types de tâches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer un type de tâche spécifique
     */
    public function show(int $id): JsonResponse
    {
        try {
            $typeTache = TypeTache::withCount('taches')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $typeTache
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Type de tâche non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Créer un nouveau type de tâche
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:255|unique:type_taches,nom',
                'description' => 'nullable|string|max:1000',
                'couleur' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i|max:7',
                'actif' => 'boolean',
                'ordre' => 'nullable|integer|min:0',
            ]);

            $typeTache = TypeTache::create([
                ...$validated,
                'date_creation' => now(),
                'creer_par' => $request->user()->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Type de tâche créé avec succès',
                'data' => $typeTache
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du type de tâche',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un type de tâche
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $typeTache = TypeTache::findOrFail($id);

            $validated = $request->validate([
                'nom' => 'required|string|max:255|unique:type_taches,nom,' . $id,
                'description' => 'nullable|string|max:1000',
                'couleur' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i|max:7',
                'actif' => 'boolean',
                'ordre' => 'nullable|integer|min:0',
            ]);

            $typeTache->update([
                ...$validated,
                'date_modification' => now(),
                'modifier_par' => $request->user()->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Type de tâche mis à jour avec succès',
                'data' => $typeTache
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du type de tâche',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un type de tâche
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $typeTache = TypeTache::findOrFail($id);

            // Vérifier s'il y a des tâches associées
            $nombreTaches = $typeTache->taches()->count();
            if ($nombreTaches > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Impossible de supprimer ce type de tâche car il est utilisé par {$nombreTaches} tâche(s)",
                    'nombre_taches' => $nombreTaches
                ], 422);
            }

            $typeTache->delete();

            return response()->json([
                'success' => true,
                'message' => 'Type de tâche supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du type de tâche',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les statistiques d'un type de tâche
     */
    public function statistiques(int $id): JsonResponse
    {
        try {
            $typeTache = TypeTache::with(['taches' => function ($query) {
                $query->select('id', 'type_tache_id', 'statut', 'niveau_execution', 'date_fin_previsionnelle');
            }])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'type_tache' => $typeTache,
                    'statistiques' => $typeTache->statistiques
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer tous les types de tâches actifs (pour les filtres)
     */
    public function actifs(): JsonResponse
    {
        try {
            $typeTaches = TypeTache::actif()
                ->byOrdre()
                ->select('id', 'nom', 'couleur')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $typeTaches
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des types de tâches actifs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
