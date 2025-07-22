<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\TypeReunionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TypeReunionController extends Controller
{
    protected TypeReunionService $typeReunionService;

    public function __construct(TypeReunionService $typeReunionService)
    {
        $this->typeReunionService = $typeReunionService;
    }

    /**
     * Afficher la liste des types de réunions
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->typeReunionService->getTypeReunions($request, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Afficher un type de réunion spécifique
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $result = $this->typeReunionService->getTypeReunion($id, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 404);
        }
    }

    /**
     * Créer un nouveau type de réunion
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255|unique:type_reunions,nom',
            'description' => 'nullable|string',
            'categorie' => 'nullable|in:GENERAL,ADMINISTRATIF,TECHNIQUE,STRATEGIQUE,OPERATIONNEL',
            'niveau_complexite' => 'nullable|in:SIMPLE,MOYEN,COMPLEXE,TRES_COMPLEXE',
            'actif' => 'nullable|boolean',
            'configuration' => 'nullable|array',
            'regles_metier' => 'nullable|array',
            'workflow_defaut' => 'nullable|array',
            'notifications_defaut' => 'nullable|array',
            'permissions_requises' => 'nullable|array',
            'gestionnaires' => 'nullable|array',
            'gestionnaires.*' => 'exists:users,id',
            'membres' => 'nullable|array',
            'membres.*' => 'exists:users,id',
            'validateurs_pv' => 'nullable|array',
            'validateurs_pv.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->typeReunionService->createTypeReunion($request->all(), $user);

        if ($result['success']) {
            return response()->json($result, 201);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Mettre à jour un type de réunion
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'nom' => 'nullable|string|max:255|unique:type_reunions,nom,' . $id,
            'description' => 'nullable|string',
            'categorie' => 'nullable|in:GENERAL,ADMINISTRATIF,TECHNIQUE,STRATEGIQUE,OPERATIONNEL',
            'niveau_complexite' => 'nullable|in:SIMPLE,MOYEN,COMPLEXE,TRES_COMPLEXE',
            'actif' => 'nullable|boolean',
            'configuration' => 'nullable|array',
            'regles_metier' => 'nullable|array',
            'workflow_defaut' => 'nullable|array',
            'notifications_defaut' => 'nullable|array',
            'permissions_requises' => 'nullable|array',
            'gestionnaires' => 'nullable|array',
            'gestionnaires.*' => 'exists:users,id',
            'membres' => 'nullable|array',
            'membres.*' => 'exists:users,id',
            'validateurs_pv' => 'nullable|array',
            'validateurs_pv.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->typeReunionService->updateTypeReunion($id, $request->all(), $user);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Supprimer un type de réunion
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $result = $this->typeReunionService->deleteTypeReunion($id, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Récupérer les types de réunions actifs
     */
    public function active(Request $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->typeReunionService->getActiveTypeReunions($user);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Activer/Désactiver un type de réunion
     */
    public function toggleActive(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'actif' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->typeReunionService->updateTypeReunion($id, ['actif' => $request->actif], $user);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Récupérer les statistiques d'un type de réunion
     */
    public function stats(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Récupérer le type de réunion
        $typeReunion = $this->typeReunionService->getTypeReunion($id, $user);

        if (!$typeReunion['success']) {
            return response()->json($typeReunion, 404);
        }

        $type = $typeReunion['data'];

        // Calculer les statistiques
        $stats = [
            'total_reunions' => $type->reunions()->count(),
            'reunions_planifiees' => $type->reunions()->where('statut', 'PLANIFIEE')->count(),
            'reunions_en_cours' => $type->reunions()->where('statut', 'EN_COURS')->count(),
            'reunions_terminees' => $type->reunions()->where('statut', 'TERMINEE')->count(),
            'reunions_annulees' => $type->reunions()->where('statut', 'ANNULEE')->count(),
            'total_participants' => $type->reunions()->withCount('participants')->get()->sum('participants_count'),
            'total_series' => $type->series()->count(),
            'total_workflows' => $type->workflows()->count(),
            'total_notifications' => $type->notifications()->count(),
            'gestionnaires_count' => $type->gestionnaires()->count(),
            'membres_count' => $type->membres()->count(),
            'validateurs_pv_count' => $type->validateursPV()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Statistiques du type de réunion récupérées avec succès'
        ], 200);
    }

    /**
     * Récupérer les réunions d'un type spécifique
     */
    public function reunions(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Récupérer le type de réunion
        $typeReunion = $this->typeReunionService->getTypeReunion($id, $user);

        if (!$typeReunion['success']) {
            return response()->json($typeReunion, 404);
        }

        $type = $typeReunion['data'];

        // Récupérer les réunions avec pagination
        $perPage = $request->get('per_page', 15);
        $reunions = $type->reunions()
            ->with(['participants.user', 'createur', 'modificateur'])
            ->orderBy('date_debut', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $reunions,
            'message' => 'Réunions du type récupérées avec succès'
        ], 200);
    }

    /**
     * Récupérer les gestionnaires d'un type de réunion
     */
    public function gestionnaires(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Récupérer le type de réunion
        $typeReunion = $this->typeReunionService->getTypeReunion($id, $user);

        if (!$typeReunion['success']) {
            return response()->json($typeReunion, 404);
        }

        $type = $typeReunion['data'];
        $gestionnaires = $type->gestionnaires()->get();

        return response()->json([
            'success' => true,
            'data' => $gestionnaires,
            'message' => 'Gestionnaires récupérés avec succès'
        ], 200);
    }

    /**
     * Récupérer les membres d'un type de réunion
     */
    public function membres(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Récupérer le type de réunion
        $typeReunion = $this->typeReunionService->getTypeReunion($id, $user);

        if (!$typeReunion['success']) {
            return response()->json($typeReunion, 404);
        }

        $type = $typeReunion['data'];
        $membres = $type->membres()->get();

        return response()->json([
            'success' => true,
            'data' => $membres,
            'message' => 'Membres récupérés avec succès'
        ], 200);
    }

    /**
     * Récupérer les validateurs PV d'un type de réunion
     */
    public function validateursPV(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Récupérer le type de réunion
        $typeReunion = $this->typeReunionService->getTypeReunion($id, $user);

        if (!$typeReunion['success']) {
            return response()->json($typeReunion, 404);
        }

        $type = $typeReunion['data'];
        $validateurs = $type->validateursPV()->get();

        return response()->json([
            'success' => true,
            'data' => $validateurs,
            'message' => 'Validateurs PV récupérés avec succès'
        ], 200);
    }

    /**
     * Ajouter des gestionnaires à un type de réunion
     */
    public function addGestionnaires(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'gestionnaires' => 'required|array|min:1',
            'gestionnaires.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        // Récupérer le type de réunion
        $typeReunion = $this->typeReunionService->getTypeReunion($id, $user);

        if (!$typeReunion['success']) {
            return response()->json($typeReunion, 404);
        }

        $type = $typeReunion['data'];

        // Ajouter les gestionnaires
        $type->gestionnaires()->attach($request->gestionnaires);

        return response()->json([
            'success' => true,
            'message' => 'Gestionnaires ajoutés avec succès'
        ], 200);
    }

    /**
     * Retirer des gestionnaires d'un type de réunion
     */
    public function removeGestionnaires(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'gestionnaires' => 'required|array|min:1',
            'gestionnaires.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        // Récupérer le type de réunion
        $typeReunion = $this->typeReunionService->getTypeReunion($id, $user);

        if (!$typeReunion['success']) {
            return response()->json($typeReunion, 404);
        }

        $type = $typeReunion['data'];

        // Retirer les gestionnaires
        $type->gestionnaires()->detach($request->gestionnaires);

        return response()->json([
            'success' => true,
            'message' => 'Gestionnaires retirés avec succès'
        ], 200);
    }

    /**
     * Ajouter des membres à un type de réunion
     */
    public function addMembres(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'membres' => 'required|array|min:1',
            'membres.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        // Récupérer le type de réunion
        $typeReunion = $this->typeReunionService->getTypeReunion($id, $user);

        if (!$typeReunion['success']) {
            return response()->json($typeReunion, 404);
        }

        $type = $typeReunion['data'];

        // Ajouter les membres
        $type->membres()->attach($request->membres);

        return response()->json([
            'success' => true,
            'message' => 'Membres ajoutés avec succès'
        ], 200);
    }

    /**
     * Retirer des membres d'un type de réunion
     */
    public function removeMembres(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'membres' => 'required|array|min:1',
            'membres.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        // Récupérer le type de réunion
        $typeReunion = $this->typeReunionService->getTypeReunion($id, $user);

        if (!$typeReunion['success']) {
            return response()->json($typeReunion, 404);
        }

        $type = $typeReunion['data'];

        // Retirer les membres
        $type->membres()->detach($request->membres);

        return response()->json([
            'success' => true,
            'message' => 'Membres retirés avec succès'
        ], 200);
    }
}
