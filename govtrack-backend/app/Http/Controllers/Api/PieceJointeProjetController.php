<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PieceJointeProjet;
use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
    public function store(Request $request, int $projetId): JsonResponse
    {
        try {
            $projet = Projet::findOrFail($projetId);

            $validated = $request->validate([
                'fichier' => 'required|file|max:10240', // 10MB max
                'description' => 'nullable|string|max:500',
                'est_justificatif' => 'nullable|in:1,true,on',
                'type_document' => 'nullable|string|in:rapport,justificatif,piece_jointe,documentation,autre',
            ]);

            $fichier = $request->file('fichier');

            // Générer un nom unique pour le fichier (nettoyer les caractères spéciaux)
            $nomOriginal = $fichier->getClientOriginalName();
            $extension = pathinfo($nomOriginal, PATHINFO_EXTENSION);
            $nomSansExtension = pathinfo($nomOriginal, PATHINFO_FILENAME);

            // Nettoyer le nom du fichier (enlever accents et caractères spéciaux)
            $nomNettoye = $this->nettoyerNomFichier($nomSansExtension);
            $nomFichier = time() . '_' . $nomNettoye . '.' . $extension;
            $cheminFichier = 'projets/' . $projetId . '/' . $nomFichier;

            // Stocker le fichier avec putFileAs (plus fiable)
            $path = Storage::disk('public')->putFileAs(
                dirname($cheminFichier),
                $fichier,
                basename($cheminFichier)
            );

            // Debug: Afficher les informations de stockage
            Log::info('File upload attempt', [
                'nom_original' => $nomOriginal,
                'nom_nettoye' => $nomNettoye,
                'nom_fichier_final' => $nomFichier,
                'chemin_fichier' => $cheminFichier,
                'path_returned' => $path,
                'dirname' => dirname($cheminFichier),
                'basename' => basename($cheminFichier),
                'file_exists' => Storage::disk('public')->exists($path)
            ]);

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
                'fichier_path' => asset('storage/' . $path), // Stocker l'URL complète comme dans AuthController
                'nom_original' => $fichier->getClientOriginalName(),
                'mime_type' => $fichier->getMimeType(),
                'taille' => $fichier->getSize(),
                'description' => $validated['description'],
                'est_justificatif' => !empty($validated['est_justificatif']),
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

            // Debug: Afficher les informations du fichier
            Log::info('Download attempt', [
                'piece_jointe_id' => $pieceJointe->id,
                'fichier_path' => $pieceJointe->fichier_path,
                'nom_original' => $pieceJointe->nom_original
            ]);

            // Extraire le chemin relatif depuis l'URL stockée
            $urlParts = parse_url($pieceJointe->fichier_path);
            $cheminRelatif = str_replace('/storage/', '', $urlParts['path']);
            $cheminComplet = storage_path('app/public/' . $cheminRelatif);

            if (!file_exists($cheminComplet)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier non trouvé sur le serveur',
                    'debug' => [
                        'fichier_path' => $pieceJointe->fichier_path,
                        'chemin_relatif' => $cheminRelatif,
                        'chemin_complet' => $cheminComplet,
                        'exists' => file_exists($cheminComplet)
                    ]
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
                $utiliseEnHistorique = DB::table('projet_historique_statuts')
                    ->where('justificatif_path', $pieceJointe->fichier_path)
                    ->exists();

                if ($utiliseEnHistorique) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Impossible de supprimer un justificatif utilisé dans l\'historique'
                    ], 400);
                }
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

    /**
     * Nettoyer le nom d'un fichier (enlever accents et caractères spéciaux)
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
