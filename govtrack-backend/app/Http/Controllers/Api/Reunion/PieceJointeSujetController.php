<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Models\ReunionSujet;
use App\Models\PieceJointeSujet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PieceJointeSujetController extends Controller
{
    /**
     * Lister les pièces jointes d'un sujet
     */
    public function index(Request $request, int $sujetId): JsonResponse
    {
        try {
            $sujet = ReunionSujet::findOrFail($sujetId);

            $query = PieceJointeSujet::with('user')
                ->where('reunion_sujet_id', $sujetId);

            // Filtres
            if ($request->has('type_document')) {
                $query->where('type_document', $request->get('type_document'));
            }

            // Tri
            $sortBy = $request->get('sort_by', 'date_creation');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

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
    public function store(Request $request, int $sujetId): JsonResponse
    {
        try {
            $sujet = ReunionSujet::findOrFail($sujetId);

            $validated = $request->validate([
                'fichier' => 'required|file|max:10240', // 10MB max
                'description' => 'nullable|string|max:500',
                'type_document' => 'nullable|string|in:rapport,justificatif,piece_jointe,documentation,autre',
            ]);

            $fichier = $request->file('fichier');

            // Générer un nom unique pour le fichier
            $nomOriginal = $fichier->getClientOriginalName();
            $extension = pathinfo($nomOriginal, PATHINFO_EXTENSION);
            $nomSansExtension = pathinfo($nomOriginal, PATHINFO_FILENAME);

            // Nettoyer le nom du fichier
            $nomNettoye = $this->nettoyerNomFichier($nomSansExtension);
            $nomFichier = time() . '_' . $nomNettoye . '.' . $extension;
            $cheminFichier = 'reunions/sujets/' . $sujetId . '/' . $nomFichier;

            // Stocker le fichier
            $path = Storage::disk('public')->putFileAs(
                dirname($cheminFichier),
                $fichier,
                basename($cheminFichier)
            );

            if (!$path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du stockage du fichier'
                ], 500);
            }

            // Créer l'enregistrement en base
            $pieceJointe = PieceJointeSujet::create([
                'reunion_sujet_id' => $sujetId,
                'user_id' => $request->user()->id,
                'fichier_path' => asset('storage/' . $path),
                'nom_original' => $fichier->getClientOriginalName(),
                'mime_type' => $fichier->getMimeType(),
                'taille' => $fichier->getSize(),
                'description' => $validated['description'],
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
    public function download(int $sujetId, int $id): BinaryFileResponse|JsonResponse
    {
        try {
            $pieceJointe = PieceJointeSujet::where('reunion_sujet_id', $sujetId)->findOrFail($id);

            // Extraire le chemin relatif depuis l'URL stockée
            $urlParts = parse_url($pieceJointe->fichier_path);
            $cheminRelatif = str_replace('/storage/', '', $urlParts['path']);
            $cheminComplet = storage_path('app/public/' . $cheminRelatif);

            if (!file_exists($cheminComplet)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier non trouvé sur le serveur'
                ], 404);
            }

            // Nettoyer le nom original pour le téléchargement
            $nomTelechargement = $this->nettoyerNomFichier(pathinfo($pieceJointe->nom_original, PATHINFO_FILENAME)) . '.' . pathinfo($pieceJointe->nom_original, PATHINFO_EXTENSION);

            return response()->download($cheminComplet, $nomTelechargement);

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
    public function show(int $sujetId, int $id): JsonResponse
    {
        try {
            $pieceJointe = PieceJointeSujet::with('user')
                ->where('reunion_sujet_id', $sujetId)
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
    public function update(Request $request, int $sujetId, int $id): JsonResponse
    {
        try {
            $pieceJointe = PieceJointeSujet::where('reunion_sujet_id', $sujetId)->findOrFail($id);

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
    public function destroy(Request $request, int $sujetId, int $id): JsonResponse
    {
        try {
            $pieceJointe = PieceJointeSujet::where('reunion_sujet_id', $sujetId)->findOrFail($id);

            // Seul l'auteur peut supprimer
            if ($pieceJointe->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez supprimer que vos propres fichiers'
                ], 403);
            }

            // Extraire le chemin relatif depuis l'URL stockée pour la suppression
            $urlParts = parse_url($pieceJointe->fichier_path);
            $cheminRelatif = str_replace('/storage/', '', $urlParts['path']);
            $cheminComplet = storage_path('app/public/' . $cheminRelatif);

            // Supprimer le fichier physique
            if (file_exists($cheminComplet)) {
                unlink($cheminComplet);
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
     * Nettoyer le nom d'un fichier
     */
    private function nettoyerNomFichier(string $nom): string
    {
        // Convertir les accents en caractères ASCII
        $nom = $this->transliterate($nom);

        // Remplacer les caractères spéciaux par des underscores
        $nom = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $nom);

        // Supprimer les underscores multiples
        $nom = preg_replace('/_+/', '_', $nom);

        // Supprimer les underscores au début et à la fin
        $nom = trim($nom, '_');

        // Limiter la longueur
        if (strlen($nom) > 50) {
            $nom = substr($nom, 0, 50);
        }

        // Si le nom est vide après nettoyage, utiliser un nom par défaut
        if (empty($nom)) {
            $nom = 'fichier';
        }

        return $nom;
    }

    /**
     * Translittérer les caractères accentués
     */
    private function transliterate(string $string): string
    {
        $search = [
            'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ',
            'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý',
            'é', 'è', 'ê', 'ë', 'à', 'â', 'ä', 'î', 'ï', 'ô', 'ö', 'ù', 'û', 'ü', 'ÿ', 'ñ', 'ç',
            'É', 'È', 'Ê', 'Ë', 'À', 'Â', 'Ä', 'Î', 'Ï', 'Ô', 'Ö', 'Ù', 'Û', 'Ü', 'Ñ', 'Ç'
        ];

        $replace = [
            'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y',
            'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y',
            'e', 'e', 'e', 'e', 'a', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'u', 'y', 'n', 'c',
            'E', 'E', 'E', 'E', 'A', 'A', 'A', 'I', 'I', 'O', 'O', 'U', 'U', 'U', 'N', 'C'
        ];

        return str_replace($search, $replace, $string);
    }
}
