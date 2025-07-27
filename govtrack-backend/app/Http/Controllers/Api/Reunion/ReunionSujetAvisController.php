<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\ReunionSujetAvisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReunionSujetAvisController extends Controller
{
    protected ReunionSujetAvisService $reunionSujetAvisService;

    public function __construct(ReunionSujetAvisService $reunionSujetAvisService)
    {
        $this->reunionSujetAvisService = $reunionSujetAvisService;
    }

    /**
     * Obtenir tous les avis d'un sujet
     */
    public function index(Request $request, int $sujetId)
    {
        $result = $this->reunionSujetAvisService->getAvis($sujetId);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Créer un avis
     */
    public function store(Request $request, int $sujetId)
    {
        $validator = Validator::make($request->all(), [
            'participant_id' => 'required|integer|exists:reunion_participants,id',
            'type_avis' => 'required|in:FAVORABLE,DEFAVORABLE,RESERVE,NEUTRE',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['reunion_sujet_id'] = $sujetId;

        $result = $this->reunionSujetAvisService->createAvis($data, $request->user());

        if ($result['success']) {
            return response()->json($result, 201);
        }

        return response()->json($result, 400);
    }

    /**
     * Créer plusieurs avis en lot
     */
    public function storeMultiple(Request $request, int $sujetId)
    {
        $validator = Validator::make($request->all(), [
            'avis' => 'required|array|min:1',
            'avis.*.participant_id' => 'required|integer|exists:reunion_participants,id',
            'avis.*.type_avis' => 'required|in:FAVORABLE,DEFAVORABLE,RESERVE,NEUTRE',
            'avis.*.commentaire' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $avisList = $request->input('avis');

        // Ajouter le sujet_id à chaque avis
        foreach ($avisList as &$avis) {
            $avis['reunion_sujet_id'] = $sujetId;
        }

        $result = $this->reunionSujetAvisService->createMultipleAvis($avisList, $request->user());

        if ($result['success']) {
            return response()->json($result, 201);
        }

        return response()->json($result, 400);
    }

    /**
     * Obtenir un avis spécifique
     */
    public function show(Request $request, int $sujetId, int $avisId)
    {
        $result = $this->reunionSujetAvisService->getAvis($sujetId);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        $avis = $result['data']->where('id', $avisId)->first();

        if (!$avis) {
            return response()->json([
                'success' => false,
                'message' => 'Avis introuvable'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $avis,
            'message' => 'Avis récupéré avec succès'
        ], 200);
    }

    /**
     * Mettre à jour un avis
     */
    public function update(Request $request, int $sujetId, int $avisId)
    {
        $validator = Validator::make($request->all(), [
            'type_avis' => 'sometimes|in:FAVORABLE,DEFAVORABLE,RESERVE,NEUTRE',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->reunionSujetAvisService->updateAvis($avisId, $request->all(), $request->user());

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Supprimer un avis
     */
    public function destroy(Request $request, int $sujetId, int $avisId)
    {
        $result = $this->reunionSujetAvisService->deleteAvis($avisId, $request->user());

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Obtenir les statistiques des avis pour un sujet
     */
    public function stats(Request $request, int $sujetId)
    {
        $result = $this->reunionSujetAvisService->getAvisStats($sujetId);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }
}
