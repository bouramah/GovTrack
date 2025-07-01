<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tache;
use App\Models\Projet;
use App\Models\User;
use App\Models\PieceJointeTache;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class TacheController extends Controller
{
    /**
     * Afficher la liste des tâches
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Tache::with(['projet', 'responsable']);

            // Filtres
            if ($request->filled('projet_id')) {
                $query->byProjet($request->projet_id);
            }

            if ($request->filled('statut')) {
                $query->byStatut($request->statut);
            }

            if ($request->filled('responsable_id')) {
                $query->byResponsable($request->responsable_id);
            }

            if ($request->filled('en_retard') && $request->boolean('en_retard')) {
                $query->enRetard();
            }

            // Recherche textuelle
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('titre', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Tri
            $sortBy = $request->get('sort_by', 'date_creation');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $taches = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $taches->items(),
                'pagination' => [
                    'current_page' => $taches->currentPage(),
                    'last_page' => $taches->lastPage(),
                    'per_page' => $taches->perPage(),
                    'total' => $taches->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des tâches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une nouvelle tâche
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string',
                'projet_id' => 'required|exists:projets,id',
                'responsable_id' => 'nullable|exists:users,id',
                'date_debut_previsionnelle' => 'nullable|date',
                'date_fin_previsionnelle' => 'nullable|date|after_or_equal:date_debut_previsionnelle',
            ]);

            $tache = Tache::create([
                ...$validated,
                'statut' => Tache::STATUT_A_FAIRE,
                'niveau_execution' => 0,
                'date_creation' => now(),
                'date_modification' => now(),
                'creer_par' => $request->user()->email,
                'modifier_par' => $request->user()->email,
            ]);

            $tache->load(['projet', 'responsable']);

            return response()->json([
                'success' => true,
                'message' => 'Tâche créée avec succès',
                'data' => $tache
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
                'message' => 'Erreur lors de la création de la tâche',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher une tâche spécifique
     */
    public function show(int $id): JsonResponse
    {
        try {
            $tache = Tache::with([
                'projet.typeProjet',
                'responsable',
                'piecesJointes.user',
                'discussions.user'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $tache
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tâche non trouvée',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Mettre à jour une tâche
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $tache = Tache::findOrFail($id);

            $validated = $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string',
                'responsable_id' => 'nullable|exists:users,id',
                'date_debut_previsionnelle' => 'nullable|date',
                'date_fin_previsionnelle' => 'nullable|date|after_or_equal:date_debut_previsionnelle',
                'niveau_execution' => 'sometimes|integer|min:0|max:100',
            ]);

            $tache->update([
                ...$validated,
                'date_modification' => now(),
                'modifier_par' => $request->user()->email,
            ]);

            // Mettre à jour le niveau du projet parent
            $tache->mettreAJourNiveauProjet();

            $tache->load(['projet', 'responsable']);

            return response()->json([
                'success' => true,
                'message' => 'Tâche mise à jour avec succès',
                'data' => $tache
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
                'message' => 'Erreur lors de la mise à jour de la tâche',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une tâche
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $tache = Tache::findOrFail($id);

            // Sauvegarder le projet pour mise à jour
            $projet = $tache->projet;

            $tache->delete();

            // Mettre à jour le niveau du projet parent
            if ($projet && $projet->taches()->count() > 0) {
                $niveauMoyen = $projet->taches()->avg('niveau_execution') ?? 0;
                $projet->update([
                    'niveau_execution' => round($niveauMoyen),
                    'date_modification' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tâche supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la tâche',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Changer le statut d'une tâche
     */
    public function changerStatut(Request $request, int $id): JsonResponse
    {
        try {
            $tache = Tache::findOrFail($id);

            $validated = $request->validate([
                'nouveau_statut' => 'required|in:' . implode(',', array_keys(Tache::STATUTS)),
                'niveau_execution' => 'sometimes|integer|min:0|max:100',
                'commentaire' => 'nullable|string',
                'justificatif_path' => 'nullable|string',
            ]);

            // Validation spécifique selon le changement de statut
            if ($validated['nouveau_statut'] === Tache::STATUT_DEMANDE_CLOTURE) {
                if (empty($validated['justificatif_path'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Un justificatif est obligatoire pour demander la clôture de la tâche'
                    ], 422);
                }

                // Vérifier que le justificatif existe en tant que pièce jointe
                $justificatifExiste = PieceJointeTache::where('tache_id', $id)
                    ->where('fichier_path', $validated['justificatif_path'])
                    ->where('est_justificatif', true)
                    ->exists();

                if (!$justificatifExiste) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le justificatif spécifié doit être un fichier valide marqué comme justificatif'
                    ], 422);
                }
            }

            // Validation pour le passage au statut "terminé"
            if ($validated['nouveau_statut'] === Tache::STATUT_TERMINE) {
                $projet = $tache->projet;

                // Seul le porteur du projet peut terminer une tâche
                if ($projet->porteur_id !== $request->user()->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Seul le porteur du projet peut terminer une tâche'
                    ], 403);
                }
            }

            $updates = [
                'statut' => $validated['nouveau_statut'],
                'date_modification' => now(),
                'modifier_par' => $request->user()->email,
            ];

            // Mettre à jour les dates réelles selon le statut
            if ($validated['nouveau_statut'] === Tache::STATUT_EN_COURS && !$tache->date_debut_reelle) {
                $updates['date_debut_reelle'] = now()->toDateString();
            }

            if ($validated['nouveau_statut'] === Tache::STATUT_TERMINE) {
                if (!$tache->date_fin_reelle) {
                    $updates['date_fin_reelle'] = now()->toDateString();
                }
                $updates['niveau_execution'] = 100;
            }

            if (isset($validated['niveau_execution'])) {
                $updates['niveau_execution'] = $validated['niveau_execution'];
            }

            $tache->update($updates);

            // Mettre à jour le niveau du projet parent
            $tache->mettreAJourNiveauProjet();

            return response()->json([
                'success' => true,
                'message' => 'Statut de la tâche modifié avec succès',
                'data' => $tache->fresh()
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
                'message' => 'Erreur lors du changement de statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les tâches d'un utilisateur
     */
    public function mesTaches(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $query = Tache::with(['projet.typeProjet'])
                ->where('responsable_id', $user->id);

            // Filtres
            if ($request->filled('statut')) {
                $query->byStatut($request->statut);
            }

            if ($request->filled('en_retard') && $request->boolean('en_retard')) {
                $query->enRetard();
            }

            // Tri
            $sortBy = $request->get('sort_by', 'date_fin_previsionnelle');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $taches = $query->get();

            return response()->json([
                'success' => true,
                'data' => $taches
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des tâches',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
