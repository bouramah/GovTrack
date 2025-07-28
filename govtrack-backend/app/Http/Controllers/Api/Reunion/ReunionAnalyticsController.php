<?php

namespace App\Http\Controllers\Api\Reunion;

use App\Http\Controllers\Controller;
use App\Services\Reunion\ReunionAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ReunionAnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(ReunionAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Obtenir les statistiques globales
     */
    public function getGlobalStats(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $stats = $this->analyticsService->getGlobalStats(
                $request->start_date,
                $request->end_date
            );

            return response()->json([
                'success' => true,
                'data' => $stats
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
     * Obtenir les tendances temporelles
     */
    public function getTrends(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'group_by' => 'nullable|string|in:day,week,month'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $trends = $this->analyticsService->getTrends(
                $request->start_date,
                $request->end_date,
                $request->group_by ?? 'day'
            );

            return response()->json([
                'success' => true,
                'data' => $trends
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des tendances',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rapport détaillé par type de réunion
     */
    public function getEntityReport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type_reunion_id' => 'nullable|integer|exists:type_reunions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $report = $this->analyticsService->getEntityReport(
                $request->start_date,
                $request->end_date,
                $request->type_reunion_id
            );

            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du rapport par type de réunion',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rapport de performance des participants
     */
    public function getParticipantPerformanceReport(Request $request): JsonResponse
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
            $report = $this->analyticsService->getParticipantPerformanceReport(
                $request->start_date,
                $request->end_date
            );

            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du rapport de performance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rapport de qualité des PV
     */
    public function getPVQualityReport(Request $request): JsonResponse
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
            $report = $this->analyticsService->getPVQualityReport(
                $request->start_date,
                $request->end_date
            );

            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du rapport de qualité des PV',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Métriques de performance
     */
    public function getPerformanceMetrics(Request $request): JsonResponse
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
            $metrics = $this->analyticsService->getPerformanceMetrics(
                $request->start_date,
                $request->end_date
            );

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des métriques de performance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export des données
     */
    public function exportData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'nullable|string|in:json,csv,xlsx'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $this->analyticsService->exportData(
                $request->start_date,
                $request->end_date,
                $request->format ?? 'json'
            );

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export des données',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer un rapport personnalisé
     */
    public function generateCustomReport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'filters.date_debut' => 'nullable|date',
            'filters.date_fin' => 'nullable|date|after_or_equal:filters.date_debut',
            'filters.type_reunion_id' => 'nullable|integer|exists:type_reunions,id',
            'filters.statut' => 'nullable|string|in:PLANIFIEE,EN_COURS,TERMINEE,ANNULEE',
            'metrics' => 'required|array|min:1',
            'metrics.*' => 'string|in:duree_moyenne,taux_presence,repartition_par_type_reunion,evolution_temporelle'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $report = $this->analyticsService->generateCustomReport(
                $request->filters ?? [],
                $request->metrics
            );

            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du rapport personnalisé',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tableau de bord exécutif
     */
    public function getExecutiveDashboard(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
            $endDate = $request->end_date ?? now()->endOfMonth()->toDateString();

            // Récupérer toutes les métriques pour le tableau de bord
            $globalStats = $this->analyticsService->getGlobalStats($startDate, $endDate);
            $trends = $this->analyticsService->getTrends($startDate, $endDate, 'week');
            $entityReport = $this->analyticsService->getEntityReport($startDate, $endDate);
            $performanceMetrics = $this->analyticsService->getPerformanceMetrics($startDate, $endDate);
            $pvQualityReport = $this->analyticsService->getPVQualityReport($startDate, $endDate);

            $dashboard = [
                'periode' => [
                    'debut' => $startDate,
                    'fin' => $endDate
                ],
                'statistiques_globales' => $globalStats,
                'tendances' => $trends,
                'performance_entites' => $entityReport,
                'metriques_performance' => $performanceMetrics,
                'qualite_pv' => $pvQualityReport,
                'kpis_principaux' => [
                    'taux_reussite_reunions' => $globalStats['taux_reussite'],
                    'duree_moyenne_reunions' => $globalStats['duree_moyenne_minutes'],
                    'taux_presence' => $performanceMetrics['ponctualite']['taux_ponctualite'],
                    'taux_validation_pv' => $pvQualityReport['statistiques']['taux_validation']
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $dashboard
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du tableau de bord exécutif',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rapport de comparaison
     */
    public function getComparisonReport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'period1_start' => 'required|date',
            'period1_end' => 'required|date|after_or_equal:period1_start',
            'period2_start' => 'required|date',
            'period2_end' => 'required|date|after_or_equal:period2_start'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $period1Stats = $this->analyticsService->getGlobalStats(
                $request->period1_start,
                $request->period1_end
            );

            $period2Stats = $this->analyticsService->getGlobalStats(
                $request->period2_start,
                $request->period2_end
            );

            // Calculer les variations
            $comparison = [
                'periode1' => [
                    'debut' => $request->period1_start,
                    'fin' => $request->period1_end,
                    'statistiques' => $period1Stats
                ],
                'periode2' => [
                    'debut' => $request->period2_start,
                    'fin' => $request->period2_end,
                    'statistiques' => $period2Stats
                ],
                'variations' => [
                    'total_reunions' => $this->calculateVariation(
                        $period1Stats['total_reunions'],
                        $period2Stats['total_reunions']
                    ),
                    'taux_reussite' => $this->calculateVariation(
                        $period1Stats['taux_reussite'],
                        $period2Stats['taux_reussite']
                    ),
                    'duree_moyenne' => $this->calculateVariation(
                        $period1Stats['duree_moyenne_minutes'],
                        $period2Stats['duree_moyenne_minutes']
                    ),
                    'participants_uniques' => $this->calculateVariation(
                        $period1Stats['participants_uniques'],
                        $period2Stats['participants_uniques']
                    )
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $comparison
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du rapport de comparaison',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculer la variation entre deux valeurs
     */
    private function calculateVariation($oldValue, $newValue): array
    {
        if ($oldValue == 0) {
            return [
                'valeur_ancienne' => $oldValue,
                'valeur_nouvelle' => $newValue,
                'variation_absolue' => $newValue - $oldValue,
                'variation_relative' => $newValue > 0 ? 100 : 0,
                'tendance' => $newValue > 0 ? 'augmentation' : 'stable'
            ];
        }

        $variationAbsolue = $newValue - $oldValue;
        $variationRelative = ($variationAbsolue / $oldValue) * 100;

        return [
            'valeur_ancienne' => $oldValue,
            'valeur_nouvelle' => $newValue,
            'variation_absolue' => $variationAbsolue,
            'variation_relative' => round($variationRelative, 2),
            'tendance' => $variationRelative > 0 ? 'augmentation' : ($variationRelative < 0 ? 'diminution' : 'stable')
        ];
    }
}
