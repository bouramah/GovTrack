<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\ReunionCalendarService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ReunionCalendarController extends Controller
{
    protected $calendarService;

    public function __construct(ReunionCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Récupérer les événements calendrier
     */
    public function getEvents(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'filters.type_reunion_id' => 'nullable|integer|exists:type_reunions,id',
            'filters.entite_id' => 'nullable|integer|exists:entites,id',
            'filters.user_id' => 'nullable|integer|exists:users,id',
            'filters.status' => 'nullable|string|in:planifiee,en_cours,terminee,annulee,reportee'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $events = $this->calendarService->getCalendarEvents(
                $request->start_date,
                $request->end_date,
                $request->filters ?? []
            );

            return response()->json([
                'success' => true,
                'data' => $events,
                'total' => $events->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des événements',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vue calendrier journalière
     */
    public function getDayView(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'filters.type_reunion_id' => 'nullable|integer|exists:type_reunions,id',
            'filters.entite_id' => 'nullable|integer|exists:entites,id',
            'filters.user_id' => 'nullable|integer|exists:users,id',
            'filters.status' => 'nullable|string|in:planifiee,en_cours,terminee,annulee,reportee'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $dayView = $this->calendarService->getDayView(
                $request->date,
                $request->filters ?? []
            );

            return response()->json([
                'success' => true,
                'data' => $dayView
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la vue journalière',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vue calendrier hebdomadaire
     */
    public function getWeekView(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'filters.type_reunion_id' => 'nullable|integer|exists:type_reunions,id',
            'filters.entite_id' => 'nullable|integer|exists:entites,id',
            'filters.user_id' => 'nullable|integer|exists:users,id',
            'filters.status' => 'nullable|string|in:planifiee,en_cours,terminee,annulee,reportee'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $weekView = $this->calendarService->getWeekView(
                $request->start_date,
                $request->filters ?? []
            );

            return response()->json([
                'success' => true,
                'data' => $weekView
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la vue hebdomadaire',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vue calendrier mensuelle
     */
    public function getMonthView(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'year_month' => 'required|date_format:Y-m',
            'filters.type_reunion_id' => 'nullable|integer|exists:type_reunions,id',
            'filters.entite_id' => 'nullable|integer|exists:entites,id',
            'filters.user_id' => 'nullable|integer|exists:users,id',
            'filters.status' => 'nullable|string|in:planifiee,en_cours,terminee,annulee,reportee'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $monthView = $this->calendarService->getMonthView(
                $request->year_month,
                $request->filters ?? []
            );

            return response()->json([
                'success' => true,
                'data' => $monthView
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la vue mensuelle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier la disponibilité d'un utilisateur
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'exclude_reunion_id' => 'nullable|integer|exists:reunions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $availability = $this->calendarService->checkUserAvailability(
                $request->user_id,
                $request->start_date,
                $request->end_date,
                $request->exclude_reunion_id
            );

            return response()->json([
                'success' => true,
                'data' => $availability
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification de disponibilité',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trouver des créneaux disponibles
     */
    public function findAvailableSlots(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'integer|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'duration' => 'nullable|integer|min:15|max:480' // 15 min à 8h
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $slots = $this->calendarService->findAvailableSlots(
                $request->participant_ids,
                $request->start_date,
                $request->end_date,
                $request->duration ?? 60
            );

            return response()->json([
                'success' => true,
                'data' => $slots
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche de créneaux',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques de calendrier
     */
    public function getStats(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_id' => 'nullable|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $stats = $this->calendarService->getCalendarStats(
                $request->start_date,
                $request->end_date,
                $request->user_id
            );

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
     * Exporter les événements au format iCal
     */
    public function exportICal(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'filters.type_reunion_id' => 'nullable|integer|exists:type_reunions,id',
            'filters.entite_id' => 'nullable|integer|exists:entites,id',
            'filters.user_id' => 'nullable|integer|exists:users,id',
            'filters.status' => 'nullable|string|in:planifiee,en_cours,terminee,annulee,reportee'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $events = $this->calendarService->getCalendarEvents(
                $request->start_date,
                $request->end_date,
                $request->filters ?? []
            );

            $icalContent = $this->calendarService->exportToICal($events);

            return response()->json([
                'success' => true,
                'data' => [
                    'ical_content' => $icalContent,
                    'filename' => 'reunions_' . $request->start_date . '_' . $request->end_date . '.ics',
                    'total_events' => $events->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export iCal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les événements de l'utilisateur connecté
     */
    public function getMyEvents(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'filters.type_reunion_id' => 'nullable|integer|exists:type_reunions,id',
            'filters.entite_id' => 'nullable|integer|exists:entites,id',
            'filters.status' => 'nullable|string|in:planifiee,en_cours,terminee,annulee,reportee'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $filters = $request->filters ?? [];
            $filters['user_id'] = Auth::id();

            $events = $this->calendarService->getCalendarEvents(
                $request->start_date,
                $request->end_date,
                $filters
            );

            return response()->json([
                'success' => true,
                'data' => $events,
                'total' => $events->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de vos événements',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques personnelles
     */
    public function getMyStats(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $stats = $this->calendarService->getCalendarStats(
                $request->start_date,
                $request->end_date,
                Auth::id()
            );

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de vos statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
