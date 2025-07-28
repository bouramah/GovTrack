<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\TypeReunionService;
use App\Services\Reunion\TypeReunionGestionnaireService;
use App\Services\Reunion\TypeReunionMembrePermanentService;
use App\Services\Reunion\TypeReunionValidateurPVService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Exception;

class TypeReunionController extends Controller
{
    protected TypeReunionService $typeReunionService;
    protected TypeReunionGestionnaireService $gestionnaireService;
    protected TypeReunionMembrePermanentService $membreService;
    protected TypeReunionValidateurPVService $validateurService;

    public function __construct(
        TypeReunionService $typeReunionService,
        TypeReunionGestionnaireService $gestionnaireService,
        TypeReunionMembrePermanentService $membreService,
        TypeReunionValidateurPVService $validateurService
    ) {
        $this->typeReunionService = $typeReunionService;
        $this->gestionnaireService = $gestionnaireService;
        $this->membreService = $membreService;
        $this->validateurService = $validateurService;
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

        // Validation des données selon la migration
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100|unique:type_reunions,nom',
            'description' => 'required|string',
            'couleur' => 'required|string|max:7',
            'icone' => 'required|string|max:50',
            'actif' => 'nullable|boolean',
            'ordre' => 'required|integer',
            'niveau_complexite' => 'required|in:SIMPLE,INTERMEDIAIRE,COMPLEXE',
            'fonctionnalites_actives' => 'required|array',
            'configuration_notifications' => 'required|array',
            'creer_par' => 'nullable|exists:users,id',
            'modifier_par' => 'nullable|exists:users,id',
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
     * Récupérer les statistiques globales des types de réunion
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        // Calculer les statistiques globales
        $stats = [
            'total_types' => \App\Models\TypeReunion::count(),
            'types_actifs' => \App\Models\TypeReunion::where('actif', true)->count(),
            'types_inactifs' => \App\Models\TypeReunion::where('actif', false)->count(),
            'total_reunions' => \App\Models\Reunion::count(),
            'reunions_planifiees' => \App\Models\Reunion::where('statut', 'PLANIFIEE')->count(),
            'reunions_en_cours' => \App\Models\Reunion::where('statut', 'EN_COURS')->count(),
            'reunions_terminees' => \App\Models\Reunion::where('statut', 'TERMINEE')->count(),
            'reunions_annulees' => \App\Models\Reunion::where('statut', 'ANNULEE')->count(),
            'total_series' => \App\Models\ReunionSerie::count(),
            'series_actives' => \App\Models\ReunionSerie::where('actif', true)->count(),
            'total_participants' => \App\Models\ReunionParticipant::count(),
            'total_workflows' => \App\Models\ReunionWorkflowConfig::count(),
            'total_notifications' => \App\Models\ReunionNotification::count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Statistiques globales des types de réunion récupérées avec succès'
        ], 200);
    }

    /**
     * Récupérer les statistiques d'un type de réunion spécifique
     */
    public function statsById(Request $request, int $id): JsonResponse
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
        $filters = $request->all();

        $result = $this->gestionnaireService->getGestionnaires($id, $filters);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Récupérer les membres d'un type de réunion
     */
    public function membres(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $filters = $request->all();

        $result = $this->membreService->getMembresPermanents($id, $filters);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Récupérer les validateurs PV d'un type de réunion
     */
    public function validateursPV(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $filters = $request->all();

        try {
            $result = $this->validateurService->getValidateurs($id, $filters);

            return response()->json([
                'success' => true,
                'data' => $result
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des validateurs PV',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Ajouter des validateurs PV à un type de réunion
     */
    public function addValidateursPV(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'validateurs' => 'required|array|min:1',
            'validateurs.*.role_validateur' => 'required|string|in:SECRETAIRE,PRESIDENT,AUTRE',
            'validateurs.*.user_id' => 'nullable|exists:users,id',
            'validateurs.*.ordre_priorite' => 'required|integer|min:1',
            'validateurs.*.actif' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = [];
        foreach ($request->validateurs as $validateurData) {
            try {
                $validateurData['type_reunion_id'] = $id;
                $result = $this->validateurService->createValidateur($validateurData, $user->id);
                $results[] = [
                    'success' => true,
                    'message' => 'Validateur PV créé avec succès',
                    'data' => $result
                ];
            } catch (Exception $e) {
                $results[] = [
                    'success' => false,
                    'message' => 'Erreur lors de la création du validateur PV',
                    'error' => $e->getMessage()
                ];
            }
        }

        $successCount = collect($results)->where('success', true)->count();
        $errorCount = count($results) - $successCount;

        return response()->json([
            'success' => $errorCount === 0,
            'message' => "Ajout terminé : {$successCount} succès, {$errorCount} erreurs",
            'results' => $results
        ], $errorCount === 0 ? 200 : 207);
    }

    /**
     * Retirer des validateurs PV d'un type de réunion
     */
    public function removeValidateursPV(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'validateurs' => 'required|array|min:1',
            'validateurs.*' => 'required|integer|exists:type_reunion_validateur_pvs,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = [];
        foreach ($request->validateurs as $validateurId) {
            try {
                $result = $this->validateurService->deleteValidateur($validateurId, $user->id);
                $results[] = [
                    'success' => $result,
                    'message' => $result ? 'Validateur PV supprimé avec succès' : 'Erreur lors de la suppression'
                ];
            } catch (Exception $e) {
                $results[] = [
                    'success' => false,
                    'message' => 'Erreur lors de la suppression',
                    'error' => $e->getMessage()
                ];
            }
        }

        $successCount = collect($results)->where('success', true)->count();
        $errorCount = count($results) - $successCount;

        return response()->json([
            'success' => $errorCount === 0,
            'message' => "Suppression terminée : {$successCount} succès, {$errorCount} erreurs",
            'results' => $results
        ], $errorCount === 0 ? 200 : 207);
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
            'gestionnaires.*.user_id' => 'required|exists:users,id',
            'gestionnaires.*.permissions' => 'nullable|array',
            'gestionnaires.*.actif' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = [];
        foreach ($request->gestionnaires as $gestionnaireData) {
            $result = $this->gestionnaireService->addGestionnaire($id, $gestionnaireData, $user->id);
            $results[] = $result;
        }

        $successCount = collect($results)->where('success', true)->count();
        $errorCount = count($results) - $successCount;

        return response()->json([
            'success' => $errorCount === 0,
            'message' => "Ajout terminé : {$successCount} succès, {$errorCount} erreurs",
            'results' => $results
        ], $errorCount === 0 ? 200 : 207);
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
            'gestionnaires.*' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = [];
        foreach ($request->gestionnaires as $gestionnaireId) {
            $result = $this->gestionnaireService->removeGestionnaire($id, $gestionnaireId, $user->id);
            $results[] = $result;
        }

        $successCount = collect($results)->where('success', true)->count();
        $errorCount = count($results) - $successCount;

        return response()->json([
            'success' => $errorCount === 0,
            'message' => "Suppression terminée : {$successCount} succès, {$errorCount} erreurs",
            'results' => $results
        ], $errorCount === 0 ? 200 : 207);
    }

    /**
     * Vérifier si un utilisateur est gestionnaire d'un type de réunion
     */
    public function checkGestionnaire(Request $request, int $id, int $userId): JsonResponse
    {
        $user = $request->user();
        $isGestionnaire = $this->gestionnaireService->isGestionnaire($id, $userId);

        return response()->json([
            'success' => true,
            'data' => [
                'type_reunion_id' => $id,
                'user_id' => $userId,
                'is_gestionnaire' => $isGestionnaire
            ]
        ], 200);
    }

    /**
     * Récupérer les permissions d'un gestionnaire
     */
    public function getGestionnairePermissions(Request $request, int $id, int $userId): JsonResponse
    {
        $user = $request->user();
        $permissions = $this->gestionnaireService->getGestionnairePermissions($id, $userId);

        return response()->json([
            'success' => true,
            'data' => [
                'type_reunion_id' => $id,
                'user_id' => $userId,
                'permissions' => $permissions
            ]
        ], 200);
    }

    /**
     * Mettre à jour un gestionnaire spécifique
     */
    public function updateGestionnaire(Request $request, int $id, int $userId): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'permissions' => 'nullable|array',
            'actif' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->gestionnaireService->updateGestionnaire($id, $userId, $request->all(), $user->id);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Supprimer un gestionnaire spécifique
     */
    public function removeGestionnaire(Request $request, int $id, int $userId): JsonResponse
    {
        $user = $request->user();
        $result = $this->gestionnaireService->removeGestionnaire($id, $userId, $user->id);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Récupérer les statistiques des gestionnaires
     */
    public function getGestionnairesStats(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $stats = $this->gestionnaireService->getStats($id);

        return response()->json([
            'success' => true,
            'data' => $stats
        ], 200);
    }

    /**
     * Copier les gestionnaires vers un autre type de réunion
     */
    public function copyGestionnaires(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'type_reunion_destination_id' => 'required|exists:type_reunions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->gestionnaireService->copierGestionnaires($id, $request->type_reunion_destination_id, $user->id);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
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
            'membres.*.user_id' => 'required|exists:users,id',
            'membres.*.role_defaut' => 'nullable|string|in:PRESIDENT,SECRETAIRE,PARTICIPANT,OBSERVATEUR',
            'membres.*.notifications_par_defaut' => 'nullable|array',
            'membres.*.actif' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = [];
        foreach ($request->membres as $membreData) {
            $result = $this->membreService->addMembrePermanent($id, $membreData, $user->id);
            $results[] = $result;
        }

        $successCount = collect($results)->where('success', true)->count();
        $errorCount = count($results) - $successCount;

        return response()->json([
            'success' => $errorCount === 0,
            'message' => "Ajout terminé : {$successCount} succès, {$errorCount} erreurs",
            'results' => $results
        ], $errorCount === 0 ? 200 : 207);
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
            'membres.*' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = [];
        foreach ($request->membres as $membreId) {
            $result = $this->membreService->removeMembrePermanent($id, $membreId, $user->id);
            $results[] = $result;
        }

        $successCount = collect($results)->where('success', true)->count();
        $errorCount = count($results) - $successCount;

        return response()->json([
            'success' => $errorCount === 0,
            'message' => "Suppression terminée : {$successCount} succès, {$errorCount} erreurs",
            'results' => $results
        ], $errorCount === 0 ? 200 : 207);
    }

    /**
     * Vérifier si un utilisateur est membre permanent d'un type de réunion
     */
    public function checkMembre(Request $request, int $id, int $userId): JsonResponse
    {
        $user = $request->user();
        $isMembre = $this->membreService->isMembrePermanent($id, $userId);

        return response()->json([
            'success' => true,
            'data' => [
                'type_reunion_id' => $id,
                'user_id' => $userId,
                'is_membre_permanent' => $isMembre
            ]
        ], 200);
    }

    /**
     * Récupérer le rôle par défaut d'un membre
     */
    public function getMembreRole(Request $request, int $id, int $userId): JsonResponse
    {
        $user = $request->user();
        $role = $this->membreService->getMembreRoleDefaut($id, $userId);

        return response()->json([
            'success' => true,
            'data' => [
                'type_reunion_id' => $id,
                'user_id' => $userId,
                'role_defaut' => $role
            ]
        ], 200);
    }

    /**
     * Récupérer les notifications par défaut d'un membre
     */
    public function getMembreNotifications(Request $request, int $id, int $userId): JsonResponse
    {
        $user = $request->user();
        $notifications = $this->membreService->getMembreNotificationsDefaut($id, $userId);

        return response()->json([
            'success' => true,
            'data' => [
                'type_reunion_id' => $id,
                'user_id' => $userId,
                'notifications_par_defaut' => $notifications
            ]
        ], 200);
    }

    /**
     * Mettre à jour un membre permanent spécifique
     */
    public function updateMembre(Request $request, int $id, int $userId): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'role_defaut' => 'nullable|string|in:PRESIDENT,SECRETAIRE,PARTICIPANT,OBSERVATEUR',
            'notifications_par_defaut' => 'nullable|array',
            'actif' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->membreService->updateMembrePermanent($id, $userId, $request->all(), $user->id);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Supprimer un membre permanent spécifique
     */
    public function removeMembre(Request $request, int $id, int $userId): JsonResponse
    {
        $user = $request->user();
        $result = $this->membreService->removeMembrePermanent($id, $userId, $user->id);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Récupérer les statistiques des membres permanents
     */
    public function getMembresStats(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $stats = $this->membreService->getStats($id);

        return response()->json([
            'success' => true,
            'data' => $stats
        ], 200);
    }

    /**
     * Copier les membres permanents vers un autre type de réunion
     */
    public function copyMembres(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'type_reunion_destination_id' => 'required|exists:type_reunions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->membreService->copierMembresPermanents($id, $request->type_reunion_destination_id, $user->id);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Copier les validateurs PV vers un autre type de réunion
     */
    public function copyValidateursPV(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'type_reunion_destination_id' => 'required|exists:type_reunions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $nombreCopie = $this->validateurService->copierValidateurs($id, $request->type_reunion_destination_id, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Validateurs PV copiés avec succès',
                'data' => [
                    'nombre_copie' => $nombreCopie
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la copie des validateurs PV',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
