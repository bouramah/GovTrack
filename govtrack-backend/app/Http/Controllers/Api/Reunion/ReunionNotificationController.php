<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\ReunionNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReunionNotificationController extends Controller
{
    protected $reunionNotificationService;

    public function __construct(ReunionNotificationService $reunionNotificationService)
    {
        $this->reunionNotificationService = $reunionNotificationService;
    }

    /**
     * Récupérer les notifications d'une réunion
     */
    public function getReunionNotifications(int $reunionId)
    {
        $user = auth()->user();
        $result = $this->reunionNotificationService->getNotifications($reunionId, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Récupérer les notifications de l'utilisateur connecté
     */
    public function getUserNotifications(Request $request)
    {
        $user = auth()->user();
        $result = $this->reunionNotificationService->getUserNotifications($request, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(int $notificationId)
    {
        $user = auth()->user();
        $result = $this->reunionNotificationService->markAsRead($notificationId, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead()
    {
        $user = auth()->user();
        $result = $this->reunionNotificationService->markAllAsRead($user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Supprimer une notification
     */
    public function deleteNotification(int $notificationId)
    {
        $user = auth()->user();
        $result = $this->reunionNotificationService->deleteNotification($notificationId, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Envoyer une notification manuelle
     */
    public function sendManualNotification(Request $request, int $reunionId)
    {
        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:CONFIRMATION_PRESENCE,RAPPEL_24H,RAPPEL_1H,RAPPEL_15MIN,PV_DISPONIBLE,RAPPEL_ACTIONS',
            'destinataires' => 'required|array|min:1',
            'destinataires.*.user_id' => 'required|integer|exists:users,id',
            'destinataires.*.type' => 'nullable|in:CONFIRMATION_PRESENCE,RAPPEL_24H,RAPPEL_1H,RAPPEL_15MIN,PV_DISPONIBLE,RAPPEL_ACTIONS',
            'envoyer_email' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $result = $this->reunionNotificationService->sendManualNotification($reunionId, $request->all(), $user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Récupérer les statistiques des notifications
     */
    public function getNotificationStats()
    {
        $user = auth()->user();
        $result = $this->reunionNotificationService->getNotificationStats($user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Envoyer des notifications automatiques (pour les webhooks/commandes)
     */
    public function sendAutomaticNotifications(Request $request, int $reunionId)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:CONFIRMATION_PRESENCE,RAPPEL_24H,RAPPEL_1H,RAPPEL_15MIN,PV_DISPONIBLE,RAPPEL_ACTIONS',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        // Récupérer la réunion
        $reunion = \App\Models\Reunion::find($reunionId);
        if (!$reunion) {
            return response()->json([
                'success' => false,
                'message' => 'Réunion non trouvée'
            ], 404);
        }

        $result = $this->reunionNotificationService->sendAutomaticNotifications($reunion, $request->type);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Récupérer les notifications non lues
     */
    public function getUnreadNotifications(Request $request)
    {
        $user = auth()->user();

        // Modifier la requête pour ne récupérer que les notifications non lues
        $request->merge(['lu' => false]);

        $result = $this->reunionNotificationService->getUserNotifications($request, $user);

        if ($result['success']) {
            return response()->json($result, 200);
        }

        return response()->json($result, 400);
    }

    /**
     * Récupérer le nombre de notifications non lues
     */
    public function getUnreadCount()
    {
        $user = auth()->user();

        $count = \App\Models\ReunionNotification::where('envoye_a', $user->id)
            ->where('statut', 'ENVOYE')
            ->count();

        return response()->json([
            'success' => true,
            'data' => ['count' => $count],
            'message' => 'Nombre de notifications non lues récupéré'
        ], 200);
    }

    /**
     * Supprimer toutes les notifications lues
     */
    public function deleteReadNotifications()
    {
        $user = auth()->user();

        $count = \App\Models\ReunionNotification::where('envoye_a', $user->id)
            ->where('statut', 'LU')
            ->delete();

        return response()->json([
            'success' => true,
            'data' => ['count' => $count],
            'message' => $count . ' notifications lues supprimées'
        ], 200);
    }

    /**
     * Supprimer toutes les notifications
     */
    public function deleteAllNotifications()
    {
        $user = auth()->user();

        $count = \App\Models\ReunionNotification::where('envoye_a', $user->id)
            ->delete();

        return response()->json([
            'success' => true,
            'data' => ['count' => $count],
            'message' => $count . ' notifications supprimées'
        ], 200);
    }
}
