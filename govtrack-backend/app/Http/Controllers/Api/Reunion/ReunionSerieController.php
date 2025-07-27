<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\ReunionSerieService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReunionSerieController extends Controller
{
    protected $reunionSerieService;

    public function __construct(ReunionSerieService $reunionSerieService)
    {
        $this->reunionSerieService = $reunionSerieService;
    }

    /**
     * Récupérer la liste des séries de réunions
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $result = $this->reunionSerieService->getSeries($request, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Récupérer une série spécifique
     */
    public function show(Request $request, int $id)
    {
        $user = $request->user();
        $result = $this->reunionSerieService->getSerie($id, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 404);
    }

    /**
     * Créer une nouvelle série de réunions
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'description' => 'required|string',
            'type_reunion_id' => 'required|integer|exists:type_reunions,id',
            'periodicite' => 'required|in:HEBDOMADAIRE,BIHEBDOMADAIRE,MENSUELLE',
            'jour_semaine' => 'nullable|integer|between:1,7',
            'jour_mois' => 'nullable|integer|between:1,31',
            'heure_debut' => 'required|date_format:H:i:s',
            'duree_minutes' => 'required|integer|min:15|max:480',
            'lieu_defaut' => 'required|string|max:200',
            'actif' => 'nullable|boolean',
            'date_debut' => 'required|date|after_or_equal:today',
            'date_fin' => 'nullable|date|after:date_debut',
            'suspendue' => 'nullable|boolean',
            'configuration_recurrence' => 'required|array',
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

        $user = $request->user();
        $result = $this->reunionSerieService->createSerie($request->all(), $user);

        if ($result['success']) {
            return response()->json($result, 201);
        }

        return response()->json($result, 400);
    }

    /**
     * Mettre à jour une série de réunions
     */
    public function update(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'periodicite' => 'nullable|in:QUOTIDIENNE,HEBDOMADAIRE,MENSUELLE,TRIMESTRIELLE,SEMESTRIELLE,ANNUELLE',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'heure_debut' => 'nullable|date_format:H:i:s',
            'heure_fin' => 'nullable|date_format:H:i:s|after:heure_debut',
            'jour_semaine' => 'nullable|integer|between:1,7',
            'jour_mois' => 'nullable|integer|between:1,31',
            'configuration_recurrence' => 'nullable|array',
            'actif' => 'nullable|boolean',
            'regenerer_reunions' => 'nullable|boolean',
            'type_reunion_id' => 'nullable|integer|exists:type_reunions,id',
            'lieu' => 'nullable|string|max:255',
            'type_lieu' => 'nullable|in:PHYSIQUE,VIRTUEL,HYBRIDE',
            'participants' => 'nullable|array',
            'participants.*.user_id' => 'required_with:participants|integer|exists:users,id',
            'participants.*.role' => 'nullable|in:ORGANISATEUR,PARTICIPANT,OBSERVATEUR',
            'participants.*.type' => 'nullable|in:INTERNE,EXTERNE',
            'participants.*.statut_presence' => 'nullable|in:INVITE,CONFIRME,DECLINE',
            'participants.*.notifications_actives' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $result = $this->reunionSerieService->updateSerie($id, $request->all(), $user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Supprimer une série de réunions
     */
    public function destroy(Request $request, int $id)
    {
        $user = $request->user();
        $result = $this->reunionSerieService->deleteSerie($id, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Générer les réunions d'une série
     */
    public function generateReunions(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'type_reunion_id' => 'nullable|integer|exists:type_reunions,id',
            'lieu' => 'nullable|string|max:255',
            'type_lieu' => 'nullable|in:PHYSIQUE,VIRTUEL,HYBRIDE',
            'participants' => 'nullable|array',
            'participants.*.user_id' => 'required_with:participants|integer|exists:users,id',
            'participants.*.role' => 'nullable|in:ORGANISATEUR,PARTICIPANT,OBSERVATEUR',
            'participants.*.type' => 'nullable|in:INTERNE,EXTERNE',
            'participants.*.statut_presence' => 'nullable|in:INVITE,CONFIRME,DECLINE',
            'participants.*.notifications_actives' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $serie = $this->reunionSerieService->getSerie($id, $user);

        if (!$serie['success']) {
            return response()->json($serie, 404);
        }

        $result = $this->reunionSerieService->genererReunionsSerie($serie['data'], $request->all());

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Régénérer les réunions d'une série
     */
    public function regenerateReunions(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'type_reunion_id' => 'nullable|integer|exists:type_reunions,id',
            'lieu' => 'nullable|string|max:255',
            'type_lieu' => 'nullable|in:PHYSIQUE,VIRTUEL,HYBRIDE',
            'participants' => 'nullable|array',
            'participants.*.user_id' => 'required_with:participants|integer|exists:users,id',
            'participants.*.role' => 'nullable|in:ORGANISATEUR,PARTICIPANT,OBSERVATEUR',
            'participants.*.type' => 'nullable|in:INTERNE,EXTERNE',
            'participants.*.statut_presence' => 'nullable|in:INVITE,CONFIRME,DECLINE',
            'participants.*.notifications_actives' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $serie = $this->reunionSerieService->getSerie($id, $user);

        if (!$serie['success']) {
            return response()->json($serie, 404);
        }

        $result = $this->reunionSerieService->regenererReunionsSerie($serie['data'], $request->all());

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Récupérer les statistiques des séries
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $result = $this->reunionSerieService->getStats($user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Activer/Désactiver une série
     */
    public function toggleActive(Request $request, int $id)
    {
        $user = $request->user();
        $serie = $this->reunionSerieService->getSerie($id, $user);

        if (!$serie['success']) {
            return response()->json($serie, 404);
        }

        $data = [
            'actif' => !$serie['data']->actif
        ];

        $result = $this->reunionSerieService->updateSerie($id, $data, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }
}
