<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginActivity;
use App\Services\LoginActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class LoginActivityController extends Controller
{
    /**
     * Obtenir les activités de connexion d'un utilisateur
     */
    public function getUserActivities(Request $request, int $userId): JsonResponse
    {
        try {
            // Vérifier les permissions
            if (!$request->user()->hasPermission('view_user_login_activities')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour voir les activités de connexion'
                ], 403);
            }

            $query = LoginActivity::with('user:id,nom,prenom,email')
                ->where('user_id', $userId);

            // Filtres
            if ($request->filled('action')) {
                $query->where('action', $request->action);
            }

            if ($request->filled('date_from')) {
                $query->where('created_at', '>=', Carbon::parse($request->date_from));
            }

            if ($request->filled('date_to')) {
                $query->where('created_at', '<=', Carbon::parse($request->date_to));
            }

            if ($request->filled('ip_address')) {
                $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
            }

            // Tri
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 20);
            $activities = $query->paginate($perPage);

            // Transformer les données
            $activities->getCollection()->transform(function ($activity) {
                return [
                    'id' => $activity->id,
                    'action' => $activity->action,
                    'ip_address' => $activity->ip_address,
                    'user_agent' => $activity->user_agent,
                    'location' => $activity->location,
                    'device_type' => $activity->device_type,
                    'browser' => $activity->browser,
                    'os' => $activity->os,
                    'session_id' => $activity->session_id,
                    'created_at' => $activity->created_at,
                    'session_duration' => $activity->getFormattedSessionDuration(),
                    'user' => [
                        'id' => $activity->user->id,
                        'nom' => $activity->user->nom,
                        'prenom' => $activity->user->prenom,
                        'email' => $activity->user->email,
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $activities->items(),
                'pagination' => [
                    'current_page' => $activities->currentPage(),
                    'last_page' => $activities->lastPage(),
                    'per_page' => $activities->perPage(),
                    'total' => $activities->total(),
                ],
                'message' => 'Activités de connexion récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des activités de connexion',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques d'activité d'un utilisateur
     */
    public function getUserStats(Request $request, int $userId): JsonResponse
    {
        try {
            // Vérifier les permissions
            if (!$request->user()->hasPermission('view_user_login_activities')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour voir les statistiques d\'activité'
                ], 403);
            }

            $days = $request->get('days', 30);
            $stats = LoginActivityService::getUserStats($userId, $days);

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Statistiques d\'activité récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques d\'activité',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les activités de connexion globales (admin)
     */
    public function getGlobalActivities(Request $request): JsonResponse
    {
        try {
            // Vérifier les permissions
            if (!$request->user()->hasPermission('view_global_login_activities')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour voir les activités globales'
                ], 403);
            }

            $query = LoginActivity::with('user:id,nom,prenom,email');

            // Filtres
            if ($request->filled('action')) {
                $query->where('action', $request->action);
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('date_from')) {
                $query->where('created_at', '>=', Carbon::parse($request->date_from));
            }

            if ($request->filled('date_to')) {
                $query->where('created_at', '<=', Carbon::parse($request->date_to));
            }

            if ($request->filled('ip_address')) {
                $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
            }

            // Tri
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 50);
            $activities = $query->paginate($perPage);

            // Transformer les données
            $activities->getCollection()->transform(function ($activity) {
                return [
                    'id' => $activity->id,
                    'action' => $activity->action,
                    'ip_address' => $activity->ip_address,
                    'user_agent' => $activity->user_agent,
                    'location' => $activity->location,
                    'device_type' => $activity->device_type,
                    'browser' => $activity->browser,
                    'os' => $activity->os,
                    'session_id' => $activity->session_id,
                    'created_at' => $activity->created_at,
                    'session_duration' => $activity->getFormattedSessionDuration(),
                    'user' => [
                        'id' => $activity->user->id,
                        'nom' => $activity->user->nom,
                        'prenom' => $activity->user->prenom,
                        'email' => $activity->user->email,
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $activities->items(),
                'pagination' => [
                    'current_page' => $activities->currentPage(),
                    'last_page' => $activities->lastPage(),
                    'per_page' => $activities->perPage(),
                    'total' => $activities->total(),
                ],
                'message' => 'Activités globales récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des activités globales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques globales (admin)
     */
    public function getGlobalStats(Request $request): JsonResponse
    {
        try {
            // Vérifier les permissions
            if (!$request->user()->hasPermission('view_global_login_activities')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour voir les statistiques globales'
                ], 403);
            }

            $days = $request->get('days', 30);
            $stats = LoginActivityService::getGlobalStats($days);

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Statistiques globales récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques globales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les activités de connexion récentes (dernières 24h)
     */
    public function getRecentActivities(Request $request): JsonResponse
    {
        try {
            // Vérifier les permissions
            if (!$request->user()->hasPermission('view_user_login_activities')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour voir les activités récentes'
                ], 403);
            }

            $hours = $request->get('hours', 24);
            $limit = $request->get('limit', 20);

            $activities = LoginActivity::with('user:id,nom,prenom,email')
                ->recent($hours)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            // Transformer les données
            $activities->transform(function ($activity) {
                return [
                    'id' => $activity->id,
                    'action' => $activity->action,
                    'ip_address' => $activity->ip_address,
                    'device_type' => $activity->device_type,
                    'browser' => $activity->browser,
                    'os' => $activity->os,
                    'created_at' => $activity->created_at,
                    'session_duration' => $activity->getFormattedSessionDuration(),
                    'user' => [
                        'id' => $activity->user->id,
                        'nom' => $activity->user->nom,
                        'prenom' => $activity->user->prenom,
                        'email' => $activity->user->email,
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $activities,
                'message' => 'Activités récentes récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des activités récentes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
