<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiscussionProjet;
use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class DiscussionProjetController extends Controller
{
    /**
     * Afficher les discussions d'un projet
     */
    public function index(Request $request, int $projetId): JsonResponse
    {
        try {
            // Vérifier que le projet existe
            $projet = Projet::findOrFail($projetId);

            $query = DiscussionProjet::with(['user', 'reponses.user'])
                ->where('projet_id', $projetId)
                ->messagesRacine(); // Seulement les messages principaux (pas les réponses)

            // Tri par date
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy('date_creation', $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 20);
            $discussions = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $discussions->items(),
                'pagination' => [
                    'current_page' => $discussions->currentPage(),
                    'last_page' => $discussions->lastPage(),
                    'per_page' => $discussions->perPage(),
                    'total' => $discussions->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des discussions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Poster un nouveau message dans la discussion
     */
    public function store(Request $request, int $projetId): JsonResponse
    {
        try {
            // Vérifier que le projet existe
            $projet = Projet::findOrFail($projetId);

            $validated = $request->validate([
                'message' => 'required|string|min:1',
                'parent_id' => 'nullable|exists:discussion_projets,id',
            ]);

            // Si c'est une réponse, vérifier que le message parent appartient au même projet
            if (!empty($validated['parent_id'])) {
                $messageParent = DiscussionProjet::findOrFail($validated['parent_id']);
                if ($messageParent->projet_id !== $projetId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le message parent n\'appartient pas à ce projet'
                    ], 400);
                }
            }

            $discussion = DiscussionProjet::create([
                'projet_id' => $projetId,
                'user_id' => $request->user()->id,
                'parent_id' => $validated['parent_id'] ?? null,
                'message' => $validated['message'],
                'est_modifie' => false,
                'date_creation' => now(),
                'creer_par' => $request->user()->email,
            ]);

            $discussion->load(['user', 'reponses.user']);

            return response()->json([
                'success' => true,
                'message' => 'Message posté avec succès',
                'data' => $discussion
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
                'message' => 'Erreur lors de la création du message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un message spécifique avec ses réponses
     */
    public function show(int $projetId, int $id): JsonResponse
    {
        try {
            $discussion = DiscussionProjet::with(['user', 'reponses.user', 'parent.user'])
                ->where('projet_id', $projetId)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $discussion
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Message non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Modifier un message (seulement par l'auteur)
     */
    public function update(Request $request, int $projetId, int $id): JsonResponse
    {
        try {
            $discussion = DiscussionProjet::where('projet_id', $projetId)->findOrFail($id);

            // Vérifier que l'utilisateur est l'auteur du message
            if ($discussion->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez modifier que vos propres messages'
                ], 403);
            }

            $validated = $request->validate([
                'message' => 'required|string|min:1',
            ]);

            $discussion->update([
                'message' => $validated['message'],
                'est_modifie' => true,
                'date_modification' => now(),
                'modifier_par' => $request->user()->email,
            ]);

            $discussion->load(['user', 'reponses.user']);

            return response()->json([
                'success' => true,
                'message' => 'Message modifié avec succès',
                'data' => $discussion
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
                'message' => 'Erreur lors de la modification du message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un message (seulement par l'auteur)
     */
    public function destroy(Request $request, int $projetId, int $id): JsonResponse
    {
        try {
            $discussion = DiscussionProjet::where('projet_id', $projetId)->findOrFail($id);

            // Vérifier que l'utilisateur est l'auteur du message
            if ($discussion->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez supprimer que vos propres messages'
                ], 403);
            }

            // Vérifier s'il y a des réponses
            if ($discussion->reponses()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un message qui a des réponses'
                ], 400);
            }

            $discussion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Message supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des discussions d'un projet
     */
    public function statistiques(int $projetId): JsonResponse
    {
        try {
            $projet = Projet::findOrFail($projetId);

            $stats = [
                'total_messages' => DiscussionProjet::where('projet_id', $projetId)->count(),
                'messages_racine' => DiscussionProjet::where('projet_id', $projetId)
                    ->whereNull('parent_id')->count(),
                'reponses' => DiscussionProjet::where('projet_id', $projetId)
                    ->whereNotNull('parent_id')->count(),
                'participants' => DiscussionProjet::where('projet_id', $projetId)
                    ->distinct('user_id')->count('user_id'),
                'dernier_message' => DiscussionProjet::where('projet_id', $projetId)
                    ->with('user')
                    ->latest('date_creation')
                    ->first(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
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
