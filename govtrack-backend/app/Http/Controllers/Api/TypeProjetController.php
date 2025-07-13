<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TypeProjet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class TypeProjetController extends Controller
{
    /**
     * Afficher la liste des types de projets
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = TypeProjet::withCount('projets');

            // Filtrage par nom
            if ($request->filled('nom')) {
                $query->byNom($request->nom);
            }

            // Tri
            $sortBy = $request->get('sort_by', 'date_creation');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $typeProjets = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $typeProjets->items(),
                'pagination' => [
                    'current_page' => $typeProjets->currentPage(),
                    'last_page' => $typeProjets->lastPage(),
                    'per_page' => $typeProjets->perPage(),
                    'total' => $typeProjets->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des types de projets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouveau type de projet
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:255|unique:type_projets,nom',
                'description' => 'nullable|string',
                'duree_previsionnelle_jours' => 'required|integer|min:1|max:365',
                'description_sla' => 'nullable|string',
            ]);

            $typeProjet = TypeProjet::create([
                ...$validated,
                'date_creation' => now(),
                'date_modification' => now(),
                'creer_par' => $request->user()->email,
                'modifier_par' => $request->user()->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Type de projet créé avec succès',
                'data' => $typeProjet
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
                'message' => 'Erreur lors de la création du type de projet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un type de projet spécifique
     */
    public function show(int $id): JsonResponse
    {
        try {
            $typeProjet = TypeProjet::with('projets')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    ...$typeProjet->toArray(),
                    'duree_formattee' => $typeProjet->duree_formattee,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Type de projet non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Mettre à jour un type de projet
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $typeProjet = TypeProjet::findOrFail($id);

            $validated = $request->validate([
                'nom' => 'required|string|max:255|unique:type_projets,nom,' . $id,
                'description' => 'nullable|string',
                'duree_previsionnelle_jours' => 'required|integer|min:1|max:365',
                'description_sla' => 'nullable|string',
            ]);

            $typeProjet->update([
                ...$validated,
                'date_modification' => now(),
                'modifier_par' => $request->user()->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Type de projet mis à jour avec succès',
                'data' => $typeProjet->fresh()
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
                'message' => 'Erreur lors de la mise à jour du type de projet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un type de projet
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $typeProjet = TypeProjet::findOrFail($id);

            // Vérifier s'il y a des projets associés
            if ($typeProjet->projets()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce type de projet car il est associé à des projets existants'
                ], 400);
            }

            $typeProjet->delete();

            return response()->json([
                'success' => true,
                'message' => 'Type de projet supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du type de projet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques d'un type de projet
     */
    public function statistiques(int $id): JsonResponse
    {
        try {
            $typeProjet = TypeProjet::with('projets')->findOrFail($id);
            $projets = $typeProjet->projets;

            $stats = [
                'total_projets' => $projets->count(),
                'projets_par_statut' => [],
                'niveau_execution_moyen' => round($projets->avg('niveau_execution') ?? 0),
                'projets_en_retard' => $projets->filter(function ($projet) {
                    return $projet->est_en_retard;
                })->count(),
                'duree_moyenne_reelle' => null,
            ];

            // Répartition par statut
            foreach (\App\Models\Projet::STATUTS as $statut => $libelle) {
                $stats['projets_par_statut'][$statut] = [
                    'libelle' => $libelle,
                    'count' => $projets->where('statut', $statut)->count()
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'type_projet' => $typeProjet,
                    'statistiques' => $stats
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
}
