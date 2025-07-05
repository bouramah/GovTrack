<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AuditController extends Controller
{
    /**
     * Afficher la liste des logs d'audit
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Vérifier les permissions
            if (!$user->hasPermission('view_audit_logs')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions nécessaires pour consulter les logs d\'audit'
                ], 403);
            }

            $query = AuditLog::with('user')->orderBy('created_at', 'desc');

            // Filtres
            if ($request->filled('action')) {
                $query->byAction($request->action);
            }

            if ($request->filled('table_name')) {
                $query->byTable($request->table_name);
            }

            if ($request->filled('user_id')) {
                $query->byUser($request->user_id);
            }

            if ($request->filled('record_type')) {
                $query->byRecordType($request->record_type);
            }

            if ($request->filled('date_debut') && $request->filled('date_fin')) {
                $query->byDateRange($request->date_debut, $request->date_fin);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('deleted_data_summary', 'like', "%{$search}%")
                      ->orWhere('user_name', 'like', "%{$search}%")
                      ->orWhere('user_email', 'like', "%{$search}%")
                      ->orWhere('reason', 'like', "%{$search}%");
                });
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $logs = $query->paginate($perPage);

            // Statistiques
            $stats = [
                'total_logs' => AuditLog::count(),
                'deletions' => AuditLog::byAction('delete')->count(),
                'force_deletions' => AuditLog::byAction('force_delete')->count(),
                'restorations' => AuditLog::byAction('restore')->count(),
                'today_logs' => AuditLog::whereDate('created_at', today())->count(),
                'this_week_logs' => AuditLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'this_month_logs' => AuditLog::whereMonth('created_at', now()->month)->count(),
            ];

            // Tables les plus affectées
            $topTables = AuditLog::selectRaw('table_name, COUNT(*) as count')
                ->groupBy('table_name')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();

            // Utilisateurs les plus actifs
            $topUsers = AuditLog::selectRaw('user_id, user_name, COUNT(*) as count')
                ->whereNotNull('user_id')
                ->groupBy('user_id', 'user_name')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $logs->items(),
                'pagination' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                ],
                'statistiques' => $stats,
                'top_tables' => $topTables,
                'top_users' => $topUsers,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des logs d\'audit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher les détails d'un log d'audit
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = request()->user();

            // Vérifier les permissions
            if (!$user->hasPermission('view_audit_logs')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions nécessaires pour consulter les logs d\'audit'
                ], 403);
            }

            $log = AuditLog::with('user')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $log
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du log d\'audit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques d'audit
     */
    public function stats(): JsonResponse
    {
        try {
            $user = request()->user();

            // Vérifier les permissions
            if (!$user->hasPermission('view_audit_logs')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions nécessaires pour consulter les statistiques d\'audit'
                ], 403);
            }

            // Statistiques générales
            $stats = [
                'total_logs' => AuditLog::count(),
                'deletions' => AuditLog::byAction('delete')->count(),
                'force_deletions' => AuditLog::byAction('force_delete')->count(),
                'restorations' => AuditLog::byAction('restore')->count(),
            ];

            // Statistiques par table
            $tableStats = AuditLog::selectRaw('table_name, action, COUNT(*) as count')
                ->groupBy('table_name', 'action')
                ->get()
                ->groupBy('table_name');

            // Statistiques par jour (7 derniers jours)
            $dailyStats = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $dailyStats[$date] = [
                    'date' => $date,
                    'total' => AuditLog::whereDate('created_at', $date)->count(),
                    'deletions' => AuditLog::whereDate('created_at', $date)->byAction('delete')->count(),
                    'force_deletions' => AuditLog::whereDate('created_at', $date)->byAction('force_delete')->count(),
                    'restorations' => AuditLog::whereDate('created_at', $date)->byAction('restore')->count(),
                ];
            }

            // Top 10 des utilisateurs les plus actifs
            $topUsers = AuditLog::selectRaw('user_id, user_name, COUNT(*) as count')
                ->whereNotNull('user_id')
                ->groupBy('user_id', 'user_name')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'general_stats' => $stats,
                    'table_stats' => $tableStats,
                    'daily_stats' => array_values($dailyStats),
                    'top_users' => $topUsers,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques d\'audit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exporter les logs d'audit
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Vérifier les permissions
            if (!$user->hasPermission('export_audit_logs')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions nécessaires pour exporter les logs d\'audit'
                ], 403);
            }

            $query = AuditLog::with('user')->orderBy('created_at', 'desc');

            // Appliquer les mêmes filtres que pour l'index
            if ($request->filled('action')) {
                $query->byAction($request->action);
            }

            if ($request->filled('table_name')) {
                $query->byTable($request->table_name);
            }

            if ($request->filled('user_id')) {
                $query->byUser($request->user_id);
            }

            if ($request->filled('date_debut') && $request->filled('date_fin')) {
                $query->byDateRange($request->date_debut, $request->date_fin);
            }

            $logs = $query->get();

            // Préparer les données pour l'export
            $exportData = $logs->map(function ($log) {
                return [
                    'ID' => $log->id,
                    'Action' => $log->action_label,
                    'Table' => $log->table_label,
                    'ID Enregistrement' => $log->record_id,
                    'Résumé' => $log->deleted_data_summary,
                    'Utilisateur' => $log->user_full_name,
                    'Email Utilisateur' => $log->user_email,
                    'Adresse IP' => $log->ip_address,
                    'URL' => $log->request_url,
                    'Méthode' => $log->request_method,
                    'Raison' => $log->reason,
                    'Date' => $log->formatted_date,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $exportData,
                'total_records' => $logs->count(),
                'export_date' => now()->format('Y-m-d H:i:s'),
                'exported_by' => $user->prenom . ' ' . $user->nom,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export des logs d\'audit',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
