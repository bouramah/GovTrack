<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\ReunionService;
use App\Services\Reunion\ReunionParticipantService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReunionController extends Controller
{
    protected ReunionService $reunionService;
    protected ReunionParticipantService $participantService;

    public function __construct(
        ReunionService $reunionService,
        ReunionParticipantService $participantService
    ) {
        $this->reunionService = $reunionService;
        $this->participantService = $participantService;
    }

    /**
     * Afficher la liste des réunions
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->reunionService->getReunions($request, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Afficher une réunion spécifique
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $result = $this->reunionService->getReunion($id, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 404);
        }
    }

    /**
     * Créer une nouvelle réunion
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type_reunion_id' => 'required|exists:type_reunions,id',
            'niveau_complexite_actuel' => 'nullable|in:SIMPLE,MOYEN,COMPLEXE,TRES_COMPLEXE',
            'date_debut' => 'required|date|after_or_equal:today',
            'date_fin' => 'nullable|date|after:date_debut',
            'lieu' => 'nullable|string|max:255',
            'type_lieu' => 'nullable|in:PHYSIQUE,VIRTUEL,HYBRIDE',
            'lien_virtuel' => 'nullable|url',
            'periodicite' => 'nullable|in:PONCTUELLE,QUOTIDIENNE,HEBDOMADAIRE,MENSUELLE,TRIMESTRIELLE,SEMESTRIELLE,ANNUELLE',
            'serie_id' => 'nullable|exists:reunion_series,id',
            'suspendue' => 'nullable|boolean',
            'fonctionnalites_actives' => 'nullable|array',
            'quorum_minimum' => 'nullable|integer|min:1',
            'ordre_du_jour_type' => 'nullable|in:EXPLICITE,IMPLICITE',
            'statut' => 'nullable|in:PLANIFIEE,EN_COURS,TERMINEE,ANNULEE',
            'participants' => 'nullable|array',
            'participants.*.user_id' => 'required_with:participants|exists:users,id',
            'participants.*.role' => 'nullable|in:ORGANISATEUR,PARTICIPANT,OBSERVATEUR',
            'participants.*.type' => 'nullable|in:INTERNE,EXTERNE',
            'participants.*.statut_presence' => 'nullable|in:INVITE,CONFIRME,PRESENT,ABSENT,EXCUSE',
            'participants.*.notifications_actives' => 'nullable|array',
            'ordre_jour' => 'nullable|array',
            'ordre_jour.*.titre' => 'required_with:ordre_jour|string|max:255',
            'ordre_jour.*.description' => 'nullable|string',
            'ordre_jour.*.type' => 'nullable|in:DISCUSSION,DECISION,INFORMATION,PRESENTATION',
            'ordre_jour.*.duree_estimee_minutes' => 'nullable|integer|min:1',
            'ordre_jour.*.responsable_id' => 'nullable|exists:users,id',
            'ordre_jour.*.statut' => 'nullable|in:PLANIFIE,EN_COURS,TERMINE,REPORTE',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->reunionService->createReunion($request->all(), $user);

        if ($result['success']) {
            return response()->json($result, 201);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Mettre à jour une réunion
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'titre' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type_reunion_id' => 'nullable|exists:type_reunions,id',
            'niveau_complexite_actuel' => 'nullable|in:SIMPLE,MOYEN,COMPLEXE,TRES_COMPLEXE',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'lieu' => 'nullable|string|max:255',
            'type_lieu' => 'nullable|in:PHYSIQUE,VIRTUEL,HYBRIDE',
            'lien_virtuel' => 'nullable|url',
            'periodicite' => 'nullable|in:PONCTUELLE,QUOTIDIENNE,HEBDOMADAIRE,MENSUELLE,TRIMESTRIELLE,SEMESTRIELLE,ANNUELLE',
            'serie_id' => 'nullable|exists:reunion_series,id',
            'suspendue' => 'nullable|boolean',
            'fonctionnalites_actives' => 'nullable|array',
            'quorum_minimum' => 'nullable|integer|min:1',
            'ordre_du_jour_type' => 'nullable|in:EXPLICITE,IMPLICITE',
            'statut' => 'nullable|in:PLANIFIEE,EN_COURS,TERMINEE,ANNULEE',
            'participants' => 'nullable|array',
            'participants.*.user_id' => 'required_with:participants|exists:users,id',
            'participants.*.role' => 'nullable|in:ORGANISATEUR,PARTICIPANT,OBSERVATEUR',
            'participants.*.type' => 'nullable|in:INTERNE,EXTERNE',
            'participants.*.statut_presence' => 'nullable|in:INVITE,CONFIRME,PRESENT,ABSENT,EXCUSE',
            'participants.*.notifications_actives' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->reunionService->updateReunion($id, $request->all(), $user);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Supprimer une réunion
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $result = $this->reunionService->deleteReunion($id, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Changer le statut d'une réunion
     */
    public function changeStatut(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'nouveau_statut' => 'required|in:PLANIFIEE,EN_COURS,TERMINEE,ANNULEE',
            'commentaire' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->reunionService->changeStatut(
            $id,
            $request->nouveau_statut,
            $user,
            $request->commentaire
        );

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Récupérer les statistiques des réunions
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->reunionService->getStats($user);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    // ========================================
    // MÉTHODES POUR LES PARTICIPANTS
    // ========================================

    /**
     * Récupérer les participants d'une réunion
     */
    public function participants(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $result = $this->participantService->getParticipants($id, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Ajouter un participant à une réunion
     */
    public function addParticipant(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role' => 'nullable|in:ORGANISATEUR,PARTICIPANT,OBSERVATEUR',
            'type' => 'nullable|in:INTERNE,EXTERNE',
            'statut_presence' => 'nullable|in:INVITE,CONFIRME,PRESENT,ABSENT,EXCUSE',
            'nom_affichage' => 'nullable|string|max:255',
            'email_contact' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'notifications_actives' => 'nullable|array',
            'commentaires' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->participantService->addParticipant($id, $request->all(), $user);

        if ($result['success']) {
            return response()->json($result, 201);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Mettre à jour un participant
     */
    public function updateParticipant(Request $request, int $reunionId, int $participantId): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'role' => 'nullable|in:ORGANISATEUR,PARTICIPANT,OBSERVATEUR',
            'type' => 'nullable|in:INTERNE,EXTERNE',
            'statut_presence' => 'nullable|in:INVITE,CONFIRME,PRESENT,ABSENT,EXCUSE',
            'nom_affichage' => 'nullable|string|max:255',
            'email_contact' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'notifications_actives' => 'nullable|array',
            'commentaires' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->participantService->updateParticipant($reunionId, $participantId, $request->all(), $user);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Supprimer un participant d'une réunion
     */
    public function removeParticipant(Request $request, int $reunionId, int $participantId): JsonResponse
    {
        $user = $request->user();
        $result = $this->participantService->removeParticipant($reunionId, $participantId, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Mettre à jour le statut de présence d'un participant
     */
    public function updatePresenceStatus(Request $request, int $reunionId, int $participantId): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'statut' => 'required|in:INVITE,CONFIRME,PRESENT,ABSENT,EXCUSE',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->participantService->updatePresenceStatus($reunionId, $participantId, $request->statut, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Ajouter plusieurs participants en lot
     */
    public function addMultipleParticipants(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Validation des données
        $validator = Validator::make($request->all(), [
            'participants' => 'required|array|min:1',
            'participants.*.user_id' => 'required|exists:users,id',
            'participants.*.role' => 'nullable|in:PRESIDENT,SECRETAIRE,PARTICIPANT,OBSERVATEUR,VALIDATEUR_PV',
            'participants.*.type' => 'nullable|in:PERMANENT,INVITE',
            'participants.*.statut_presence' => 'nullable|in:CONFIRME,ABSENT,EN_ATTENTE',
            'participants.*.nom_affichage' => 'nullable|string|max:255',
            'participants.*.email_contact' => 'nullable|email|max:255',
            'participants.*.telephone' => 'nullable|string|max:20',
            'participants.*.notifications_actives' => 'nullable|array',
            'participants.*.commentaires' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->participantService->addMultipleParticipants($id, $request->participants, $user);

        if ($result['success']) {
            return response()->json($result, 201);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Récupérer les statistiques des participants
     */
    public function participantStats(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $result = $this->participantService->getParticipantStats($id, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }
}
