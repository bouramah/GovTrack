<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Projet;
use App\Models\TypeProjet;
use App\Models\User;
use App\Models\PieceJointeProjet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ProjetController extends Controller
{
    /**
     * Afficher la liste des projets
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Projet::with(['typeProjet', 'porteur', 'donneurOrdre']);

            // Filtres
            if ($request->filled('statut')) {
                $query->byStatut($request->statut);
            }

            if ($request->filled('porteur_id')) {
                $query->byPorteur($request->porteur_id);
            }

            if ($request->filled('donneur_ordre_id')) {
                $query->byDonneurOrdre($request->donneur_ordre_id);
            }

            if ($request->filled('type_projet_id')) {
                $query->where('type_projet_id', $request->type_projet_id);
            }

            if ($request->filled('en_retard') && $request->boolean('en_retard')) {
                $query->enRetard();
            }

            if ($request->filled('niveau_execution_min')) {
                $query->where('niveau_execution', '>=', $request->niveau_execution_min);
            }

            if ($request->filled('niveau_execution_max')) {
                $query->where('niveau_execution', '<=', $request->niveau_execution_max);
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
            $projets = $query->paginate($perPage);

            // Ajouter des informations calculées
            $projets->getCollection()->transform(function ($projet) {
                $projet->statut_libelle = $projet->statut_libelle;
                $projet->est_en_retard = $projet->est_en_retard;
                return $projet;
            });

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
                'message' => 'Erreur lors de la récupération des projets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouveau projet
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'required|string',
                'type_projet_id' => 'required|exists:type_projets,id',
                'porteur_id' => 'required|exists:users,id',
                'donneur_ordre_id' => 'required|exists:users,id',
                'date_debut_previsionnelle' => 'required|date|after_or_equal:today',
                'date_fin_previsionnelle' => 'nullable|date|after:date_debut_previsionnelle',
                'justification_modification_dates' => 'nullable|string',
            ]);

            // Calcul automatique de la date de fin si non fournie (SLA)
            if (empty($validated['date_fin_previsionnelle'])) {
                $typeProjet = TypeProjet::findOrFail($validated['type_projet_id']);
                $dateDebut = Carbon::parse($validated['date_debut_previsionnelle']);
                $validated['date_fin_previsionnelle'] = $dateDebut->addDays($typeProjet->duree_previsionnelle_jours)->format('Y-m-d');
            } else {
                // Si une date de fin personnalisée est fournie, vérifier la justification
                $typeProjet = TypeProjet::findOrFail($validated['type_projet_id']);
                $dateFinSLA = Carbon::parse($validated['date_debut_previsionnelle'])
                    ->addDays($typeProjet->duree_previsionnelle_jours);

                if (Carbon::parse($validated['date_fin_previsionnelle'])->ne($dateFinSLA)) {
                    if (empty($validated['justification_modification_dates'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Justification requise pour modifier les dates par rapport au SLA'
                        ], 422);
                    }
                }
            }

            $projet = Projet::create([
                ...$validated,
                'statut' => Projet::STATUT_A_FAIRE,
                'niveau_execution' => 0,
                'date_creation' => now(),
                'date_modification' => now(),
                'creer_par' => $request->user()->email,
                'modifier_par' => $request->user()->email,
            ]);

            // Créer l'historique initial
            $projet->historiqueStatuts()->create([
                'ancien_statut' => null,
                'nouveau_statut' => Projet::STATUT_A_FAIRE,
                'user_id' => $request->user()->id,
                'commentaire' => 'Création du projet',
                'date_changement' => now(),
            ]);

            $projet->load(['typeProjet', 'porteur', 'donneurOrdre']);

            return response()->json([
                'success' => true,
                'message' => 'Projet créé avec succès',
                'data' => $projet
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
                'message' => 'Erreur lors de la création du projet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un projet spécifique
     */
    public function show(int $id): JsonResponse
    {
        try {
            $projet = Projet::with([
                'typeProjet',
                'porteur',
                'donneurOrdre',
                'taches.responsable',
                'historiqueStatuts.user',
                'piecesJointes.user',
                'discussions.user'
            ])->findOrFail($id);

            // Ajouter des informations calculées
            $projet->statut_libelle = $projet->statut_libelle;
            $projet->est_en_retard = $projet->est_en_retard;

            return response()->json([
                'success' => true,
                'data' => $projet
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Projet non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Mettre à jour un projet
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $projet = Projet::findOrFail($id);

            $validated = $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'required|string',
                'type_projet_id' => 'required|exists:type_projets,id',
                'porteur_id' => 'required|exists:users,id',
                'donneur_ordre_id' => 'required|exists:users,id',
                'date_debut_previsionnelle' => 'required|date',
                'date_fin_previsionnelle' => 'required|date|after:date_debut_previsionnelle',
                'justification_modification_dates' => 'nullable|string',
                'niveau_execution' => 'sometimes|integer|min:0|max:100',
            ]);

            $projet->update([
                ...$validated,
                'date_modification' => now(),
                'modifier_par' => $request->user()->email,
            ]);

            $projet->load(['typeProjet', 'porteur', 'donneurOrdre']);

            return response()->json([
                'success' => true,
                'message' => 'Projet mis à jour avec succès',
                'data' => $projet
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
                'message' => 'Erreur lors de la mise à jour du projet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un projet
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $projet = Projet::findOrFail($id);

            // Vérifications de sécurité
            if ($projet->statut === Projet::STATUT_EN_COURS) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un projet en cours'
                ], 400);
            }

            $projet->delete();

            return response()->json([
                'success' => true,
                'message' => 'Projet supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du projet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Changer le statut d'un projet
     */
    public function changerStatut(Request $request, int $id): JsonResponse
    {
        try {
            $projet = Projet::findOrFail($id);

            $validated = $request->validate([
                'nouveau_statut' => 'required|in:' . implode(',', array_keys(Projet::STATUTS)),
                'commentaire' => 'nullable|string',
                'justificatif_path' => 'nullable|string',
            ]);

            // Vérifier la logique du changement de statut
            $estMiseAJourCommentaire = false;
            if ($validated['nouveau_statut'] === $projet->statut) {
                // Même statut : vérifier si c'est pour mettre à jour le commentaire
                $commentaireActuel = $validated['commentaire'] ?? '';

                // Récupérer le dernier commentaire de l'historique
                $dernierHistorique = $projet->historiqueStatuts()
                    ->where('nouveau_statut', $projet->statut)
                    ->orderBy('date_changement', 'desc')
                    ->first();

                $dernierCommentaire = $dernierHistorique ? ($dernierHistorique->commentaire ?? '') : '';

                // Cas où aucun commentaire n'est fourni
                if (empty($commentaireActuel)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le projet a déjà le statut "' . Projet::STATUTS[$projet->statut] . '". Pour mettre à jour, veuillez fournir un commentaire différent.',
                        'current_status' => [
                            'statut' => $projet->statut,
                            'libelle' => Projet::STATUTS[$projet->statut],
                            'dernier_commentaire' => $dernierCommentaire
                        ]
                    ], 422);
                }

                // Cas où le commentaire est identique au dernier
                if ($commentaireActuel === $dernierCommentaire) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le projet a déjà le statut "' . Projet::STATUTS[$projet->statut] . '" avec ce même commentaire. Aucun changement nécessaire.',
                        'current_status' => [
                            'statut' => $projet->statut,
                            'libelle' => Projet::STATUTS[$projet->statut],
                            'dernier_commentaire' => $dernierCommentaire
                        ]
                    ], 422);
                }

                // Si on arrive ici, c'est un nouveau commentaire pour le même statut
                $estMiseAJourCommentaire = true;
            }

            // Validation spécifique selon le changement de statut
            if ($validated['nouveau_statut'] === Projet::STATUT_DEMANDE_CLOTURE) {
                // Vérifier qu'il y a au moins un justificatif (pièce jointe marquée comme justificatif)
                $aUnJustificatif = PieceJointeProjet::where('projet_id', $id)
                    ->where('est_justificatif', true)
                    ->exists();

                if (!$aUnJustificatif) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Un justificatif (pièce jointe marquée comme justificatif) est obligatoire pour demander la clôture'
                    ], 422);
                }
            }

            // Utiliser la méthode du modèle pour changer le statut
            $justificatifPath = isset($validated['justificatif_path']) ? $validated['justificatif_path'] : null;
            $commentaire = isset($validated['commentaire']) ? $validated['commentaire'] : null;

            $projet->changerStatut(
                $validated['nouveau_statut'],
                $request->user()->id,
                $commentaire,
                $justificatifPath
            );

            // Mettre à jour les dates réelles selon le statut
            $updates = [];
            if ($validated['nouveau_statut'] === Projet::STATUT_EN_COURS && !$projet->date_debut_reelle) {
                $updates['date_debut_reelle'] = now()->toDateString();
            }
            if ($validated['nouveau_statut'] === Projet::STATUT_TERMINE && !$projet->date_fin_reelle) {
                $updates['date_fin_reelle'] = now()->toDateString();
                $updates['niveau_execution'] = 100;
            }

            if (!empty($updates)) {
                $projet->update($updates);
            }

            $projet->load(['historiqueStatuts.user']);

            // Message de succès adapté selon le type d'opération
            $message = $estMiseAJourCommentaire
                ? 'Commentaire du statut "' . Projet::STATUTS[$projet->statut] . '" mis à jour avec succès'
                : 'Statut du projet modifié avec succès';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $projet
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
     * Obtenir le tableau de bord des projets
     */
    public function tableauBord(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Base query selon les permissions de l'utilisateur
            $query = Projet::query();

            // Si ce n'est pas un admin, limiter aux projets de l'utilisateur
            if (!$user->hasPermission('manage_projects')) {
                $query->where(function ($q) use ($user) {
                    $q->where('porteur_id', $user->id)
                      ->orWhere('donneur_ordre_id', $user->id)
                      ->orWhereHas('taches', function ($tq) use ($user) {
                          $tq->where('responsable_id', $user->id);
                      });
                });
            }

            // Calculer les statistiques
            $totalProjets = $query->count();

            $stats = [
                'total_projets' => $totalProjets,
                'projets_par_statut' => [],
                'projets_en_retard' => (clone $query)->enRetard()->count(),
                'niveau_execution_moyen' => round($query->avg('niveau_execution') ?? 0),
                'projets_recents' => (clone $query)->orderBy('date_creation', 'desc')
                    ->with(['typeProjet', 'porteur'])
                    ->take(5)
                    ->get(),
            ];

            // Répartition par statut avec requêtes séparées
            foreach (Projet::STATUTS as $statut => $libelle) {
                $queryStatut = clone $query;
                $count = $queryStatut->where('statut', $statut)->count();

                $stats['projets_par_statut'][$statut] = [
                    'libelle' => $libelle,
                    'count' => $count,
                    'pourcentage' => $totalProjets > 0
                        ? round(($count / $totalProjets) * 100, 1)
                        : 0
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du tableau de bord',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
