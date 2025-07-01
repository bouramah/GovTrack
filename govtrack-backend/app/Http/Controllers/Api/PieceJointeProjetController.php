<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PieceJointeProjet;
use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PieceJointeProjetController extends Controller
{
    /**
     * Lister les pièces jointes d'un projet
     */
    public function index(Request $request, int $projetId): JsonResponse
    {
        try {
            $projet = Projet::findOrFail($projetId);

            $query = PieceJointeProjet::with('user')
                ->where('projet_id', $projetId);

            // Filtres
            if ($request->has('est_justificatif')) {
                $query->where('est_justificatif', $request->boolean('est_justificatif'));
            }

            if ($request->has('type_document')) {
                $query->where('type_document', $request->get('type_document'));
            }

            // Tri par date de création (plus récent en premier)
            $query->orderBy('date_creation', 'desc');

            $piecesJointes = $query->paginate($request->get('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $piecesJointes->items(),
                'pagination' => [
                    'current_page' => $piecesJointes->currentPage(),
                    'last_page' => $piecesJointes->lastPage(),
                    'per_page' => $piecesJointes->perPage(),
                    'total' => $piecesJointes->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des pièces jointes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Uploader une nouvelle pièce jointe
     */
    public function store(Request $request, int $projetId): JsonResponse
    {
        try {
            $projet = Projet::findOrFail($projetId);

            $validated = $request->validate([
                'fichier' => 'required|file|max:10240', // 10MB max
                'description' => 'nullable|string|max:500',
                'est_justificatif' => 'boolean',
                'type_document' => 'nullable|string|in:rapport,justificatif,piece_jointe,documentation,autre',
            ]);

            $fichier = $request->file('fichier');

            // Générer un nom unique pour le fichier
            $nomFichier = time() . '_' . $fichier->getClientOriginalName();
            $cheminFichier = 'projets/' . $projetId . '/' . $nomFichier;

            // Stocker le fichier
            $path = $fichier->storeAs('public/' . dirname($cheminFichier), basename($cheminFichier));

            if (!$path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du stockage du fichier'
                ], 500);
            }

            // Créer l'enregistrement en base
            $pieceJointe = PieceJointeProjet::create([
                'projet_id' => $projetId,
                'user_id' => $request->user()->id,
                'fichier_path' => $cheminFichier,
                'nom_original' => $fichier->getClientOriginalName(),
                'mime_type' => $fichier->getMimeType(),
                'taille' => $fichier->getSize(),
                'description' => $validated['description'],
                'est_justificatif' => $validated['est_justificatif'] ?? false,
                'type_document' => $validated['type_document'] ?? 'piece_jointe',
                'date_creation' => now(),
            ]);

            $pieceJointe->load('user');

            return response()->json([
                'success' => true,
                'message' => 'Fichier téléchargé avec succès',
                'data' => $pieceJointe
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
                'message' => 'Erreur lors du téléchargement du fichier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Télécharger une pièce jointe
     */
    public function download(int $projetId, int $id): BinaryFileResponse|JsonResponse
    {
        try {
            $pieceJointe = PieceJointeProjet::where('projet_id', $projetId)->findOrFail($id);

            $cheminComplet = storage_path('app/public/' . $pieceJointe->fichier_path);

            if (!file_exists($cheminComplet)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier non trouvé sur le serveur'
                ], 404);
            }

            return response()->download($cheminComplet, $pieceJointe->nom_original);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Afficher les détails d'une pièce jointe
     */
    public function show(int $projetId, int $id): JsonResponse
    {
        try {
            $pieceJointe = PieceJointeProjet::with('user')
                ->where('projet_id', $projetId)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $pieceJointe
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pièce jointe non trouvée',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Mettre à jour les informations d'une pièce jointe
     */
    public function update(Request $request, int $projetId, int $id): JsonResponse
    {
        try {
            $pieceJointe = PieceJointeProjet::where('projet_id', $projetId)->findOrFail($id);

            // Seul l'auteur peut modifier
            if ($pieceJointe->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez modifier que vos propres fichiers'
                ], 403);
            }

            $validated = $request->validate([
                'description' => 'nullable|string|max:500',
                'type_document' => 'nullable|string|in:rapport,justificatif,piece_jointe,documentation,autre',
            ]);

            $pieceJointe->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Pièce jointe mise à jour avec succès',
                'data' => $pieceJointe->fresh(['user'])
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
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une pièce jointe
     */
    public function destroy(Request $request, int $projetId, int $id): JsonResponse
    {
        try {
            $pieceJointe = PieceJointeProjet::where('projet_id', $projetId)->findOrFail($id);

            // Seul l'auteur peut supprimer
            if ($pieceJointe->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez supprimer que vos propres fichiers'
                ], 403);
            }

            // Empêcher la suppression des justificatifs utilisés dans l'historique
            if ($pieceJointe->est_justificatif) {
                $utiliseEnHistorique = \DB::table('projet_historique_statuts')
                    ->where('justificatif_path', $pieceJointe->fichier_path)
                    ->exists();

                if ($utiliseEnHistorique) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Impossible de supprimer un justificatif utilisé dans l\'historique'
                    ], 400);
                }
            }

            // Supprimer le fichier physique
            $cheminComplet = 'public/' . $pieceJointe->fichier_path;
            if (Storage::exists($cheminComplet)) {
                Storage::delete($cheminComplet);
            }

            // Supprimer l'enregistrement
            $pieceJointe->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pièce jointe supprimée avec succès'
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
     * Obtenir les statistiques des pièces jointes d'un projet
     */
    public function statistiques(int $projetId): JsonResponse
    {
        try {
            $projet = Projet::findOrFail($projetId);

            $stats = [
                'total_fichiers' => PieceJointeProjet::where('projet_id', $projetId)->count(),
                'total_justificatifs' => PieceJointeProjet::where('projet_id', $projetId)
                    ->where('est_justificatif', true)->count(),
                'taille_totale' => PieceJointeProjet::where('projet_id', $projetId)
                    ->sum('taille'),
                'types_documents' => PieceJointeProjet::where('projet_id', $projetId)
                    ->selectRaw('type_document, COUNT(*) as count')
                    ->groupBy('type_document')
                    ->get(),
                'dernier_upload' => PieceJointeProjet::where('projet_id', $projetId)
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
