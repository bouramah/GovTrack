<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\ReunionPVService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReunionPVController extends Controller
{
    protected $reunionPVService;

    public function __construct(ReunionPVService $reunionPVService)
    {
        $this->reunionPVService = $reunionPVService;
    }

    /**
     * Récupérer les PV d'une réunion
     */
    public function index(Request $request, int $reunionId)
    {
        $user = $request->user();
        $result = $this->reunionPVService->getPVs($reunionId, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Récupérer un PV spécifique
     */
    public function show(Request $request, int $reunionId, int $pvId)
    {
        $user = $request->user();
        $result = $this->reunionPVService->getPV($reunionId, $pvId, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 404);
    }

    /**
     * Créer un nouveau PV
     */
    public function store(Request $request, int $reunionId)
    {
        $validator = Validator::make($request->all(), [
            'titre' => 'nullable|string|max:255',
            'contenu' => 'required|string',
            'resume' => 'nullable|string',
            'decisions_prises' => 'nullable|array',
            'decisions_prises.*.titre' => 'required_with:decisions_prises|string|max:255',
            'decisions_prises.*.description' => 'nullable|string',
            'decisions_prises.*.responsable' => 'nullable|string|max:255',
            'decisions_prises.*.echeance' => 'nullable|date',
            'actions_a_suivre' => 'nullable|array',
            'actions_a_suivre.*.titre' => 'required_with:actions_a_suivre|string|max:255',
            'actions_a_suivre.*.description' => 'nullable|string',
            'actions_a_suivre.*.responsable' => 'nullable|string|max:255',
            'actions_a_suivre.*.echeance' => 'nullable|date',
            'actions_a_suivre.*.statut' => 'nullable|in:EN_COURS,TERMINEE,ANNULEE',
            'participants_presents' => 'nullable|array',
            'participants_presents.*.user_id' => 'required_with:participants_presents|integer|exists:users,id',
            'participants_presents.*.role' => 'nullable|string|max:100',
            'participants_absents' => 'nullable|array',
            'participants_absents.*.user_id' => 'required_with:participants_absents|integer|exists:users,id',
            'participants_absents.*.motif' => 'nullable|string|max:255',
            'statut_validation' => 'nullable|in:BROUILLON,EN_ATTENTE,VALIDE,REJETE',
            'validateur_id' => 'nullable|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $result = $this->reunionPVService->createPV($reunionId, $request->all(), $user);

        if ($result['success']) {
            return response()->json($result, 201);
        }

        return response()->json($result, 400);
    }

    /**
     * Mettre à jour un PV
     */
    public function update(Request $request, int $reunionId, int $pvId)
    {
        $validator = Validator::make($request->all(), [
            'titre' => 'nullable|string|max:255',
            'contenu' => 'nullable|string',
            'resume' => 'nullable|string',
            'decisions_prises' => 'nullable|array',
            'decisions_prises.*.titre' => 'required_with:decisions_prises|string|max:255',
            'decisions_prises.*.description' => 'nullable|string',
            'decisions_prises.*.responsable' => 'nullable|string|max:255',
            'decisions_prises.*.echeance' => 'nullable|date',
            'actions_a_suivre' => 'nullable|array',
            'actions_a_suivre.*.titre' => 'required_with:actions_a_suivre|string|max:255',
            'actions_a_suivre.*.description' => 'nullable|string',
            'actions_a_suivre.*.responsable' => 'nullable|string|max:255',
            'actions_a_suivre.*.echeance' => 'nullable|date',
            'actions_a_suivre.*.statut' => 'nullable|in:EN_COURS,TERMINEE,ANNULEE',
            'participants_presents' => 'nullable|array',
            'participants_presents.*.user_id' => 'required_with:participants_presents|integer|exists:users,id',
            'participants_presents.*.role' => 'nullable|string|max:100',
            'participants_absents' => 'nullable|array',
            'participants_absents.*.user_id' => 'required_with:participants_absents|integer|exists:users,id',
            'participants_absents.*.motif' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $result = $this->reunionPVService->updatePV($reunionId, $pvId, $request->all(), $user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Supprimer un PV
     */
    public function destroy(Request $request, int $reunionId, int $pvId)
    {
        $user = $request->user();
        $result = $this->reunionPVService->deletePV($reunionId, $pvId, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Valider un PV
     */
    public function validate(Request $request, int $reunionId, int $pvId)
    {
        $validator = Validator::make($request->all(), [
            'commentaire_validation' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $result = $this->reunionPVService->validerPV($reunionId, $pvId, $request->all(), $user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Rejeter un PV
     */
    public function reject(Request $request, int $reunionId, int $pvId)
    {
        $validator = Validator::make($request->all(), [
            'commentaire_validation' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $result = $this->reunionPVService->rejeterPV($reunionId, $pvId, $request->all(), $user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Récupérer les statistiques des PV
     */
    public function stats(Request $request, int $reunionId)
    {
        $user = $request->user();
        $result = $this->reunionPVService->getPVStats($reunionId, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Soumettre un PV pour validation
     */
    public function submitForValidation(Request $request, int $reunionId, int $pvId)
    {
        $user = $request->user();

        // Mettre à jour le statut du PV
        $data = [
            'statut' => 'EN_ATTENTE'
        ];

        $result = $this->reunionPVService->updatePV($reunionId, $pvId, $data, $user);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'PV soumis pour validation avec succès',
                'data' => $result['data']
            ], 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Récupérer le dernier PV validé d'une réunion
     */
    public function getLastValidated(Request $request, int $reunionId)
    {
        $user = $request->user();

        // Récupérer tous les PV de la réunion
        $pvs = $this->reunionPVService->getPVs($reunionId, $user);

        if (!$pvs['success']) {
            return response()->json($pvs, 400);
        }

        // Trouver le dernier PV validé
        $pvValide = collect($pvs['data'])->firstWhere('statut_validation', 'VALIDE');

        if (!$pvValide) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun PV validé trouvé pour cette réunion'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $pvValide,
            'message' => 'Dernier PV validé récupéré avec succès'
        ], 200);
    }
}
