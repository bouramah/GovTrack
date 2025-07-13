<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiscussionTache;
use App\Models\Tache;
use App\Events\DiscussionTacheCreated;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class DiscussionTacheController extends Controller
{
    /**
     * Afficher les discussions d'une tâche
     */
    public function index(Request $request, int $tacheId): JsonResponse
    {
        try {
            // Vérifier que la tâche existe
            $tache = Tache::findOrFail($tacheId);

            $query = DiscussionTache::with(['user', 'reponses.user'])
                ->where('tache_id', $tacheId)
                ->messagesRacine(); // Seulement les messages principaux (pas les réponses)

            // Tri par date
            $sortOrder = $request->get('sort_order', 'desc');
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
    public function store(Request $request, int $tacheId): JsonResponse
    {
        try {
            // Vérifier que la tâche existe
            $tache = Tache::findOrFail($tacheId);

            $validated = $request->validate([
                'message' => 'required|string|min:1',
                'parent_id' => 'nullable|exists:discussion_taches,id',
            ]);

            // Si c'est une réponse, vérifier que le message parent appartient à la même tâche
            if (!empty($validated['parent_id'])) {
                $messageParent = DiscussionTache::findOrFail($validated['parent_id']);
                if ($messageParent->tache_id !== $tacheId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le message parent n\'appartient pas à cette tâche'
                    ], 400);
                }
            }

            $discussion = DiscussionTache::create([
                'tache_id' => $tacheId,
                'user_id' => $request->user()->id,
                'parent_id' => $validated['parent_id'] ?? null,
                'message' => $validated['message'],
                'est_modifie' => false,
                'date_creation' => now(),
                'creer_par' => $request->user()->email,
            ]);

            $discussion->load(['user', 'reponses.user']);

            // Déclencher l'événement de notification
            $isReply = !empty($validated['parent_id']);
            event(new DiscussionTacheCreated($discussion, $request->user(), $isReply));

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
    public function show(int $tacheId, int $id): JsonResponse
    {
        try {
            $discussion = DiscussionTache::with(['user', 'reponses.user', 'parent.user'])
                ->where('tache_id', $tacheId)
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
    public function update(Request $request, int $tacheId, int $id): JsonResponse
    {
        try {
            $discussion = DiscussionTache::where('tache_id', $tacheId)->findOrFail($id);

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
    public function destroy(Request $request, int $tacheId, int $id): JsonResponse
    {
        try {
            $discussion = DiscussionTache::where('tache_id', $tacheId)->findOrFail($id);

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
     * Obtenir les statistiques des discussions d'une tâche
     */
    public function statistiques(int $tacheId): JsonResponse
    {
        try {
            $tache = Tache::findOrFail($tacheId);

            $stats = [
                'total_messages' => DiscussionTache::where('tache_id', $tacheId)->count(),
                'messages_racine' => DiscussionTache::where('tache_id', $tacheId)
                    ->whereNull('parent_id')->count(),
                'reponses' => DiscussionTache::where('tache_id', $tacheId)
                    ->whereNotNull('parent_id')->count(),
                'participants' => DiscussionTache::where('tache_id', $tacheId)
                    ->distinct('user_id')->count('user_id'),
                'dernier_message' => DiscussionTache::where('tache_id', $tacheId)
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
