<?php

namespace App\Services\Reunion;

use App\Models\Reunion;
use App\Models\ReunionSerie;
use App\Models\ReunionParticipant;
use App\Models\ReunionPV;
use App\Models\User;
use App\Models\Entite;
use App\Models\TypeReunion;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReunionAnalyticsService
{
    /**
     * Obtenir les statistiques globales
     */
    public function getGlobalStats(string $startDate = null, string $endDate = null): array
    {
        $startDate = $startDate ? Carbon::parse($startDate) : Carbon::now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : Carbon::now()->endOfMonth();

        $query = Reunion::whereBetween('date_debut', [$startDate, $endDate]);

        $totalReunions = $query->count();
        $reunionsTerminees = $query->where('status', 'terminee')->count();
        $reunionsAnnulees = $query->where('status', 'annulee')->count();
        $reunionsEnCours = $query->where('status', 'en_cours')->count();

        $dureeTotale = $query->get()->sum(function ($reunion) {
            return Carbon::parse($reunion->date_debut)->diffInMinutes($reunion->date_fin);
        });

        $participantsUniques = ReunionParticipant::whereHas('reunion', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('date_debut', [$startDate, $endDate]);
        })->distinct('user_id')->count();

        return [
            'periode' => [
                'debut' => $startDate->toDateString(),
                'fin' => $endDate->toDateString()
            ],
            'total_reunions' => $totalReunions,
            'reunions_terminees' => $reunionsTerminees,
            'reunions_annulees' => $reunionsAnnulees,
            'reunions_en_cours' => $reunionsEnCours,
            'taux_reussite' => $totalReunions > 0 ? round(($reunionsTerminees / $totalReunions) * 100, 2) : 0,
            'duree_totale_heures' => round($dureeTotale / 60, 2),
            'duree_moyenne_minutes' => $totalReunions > 0 ? round($dureeTotale / $totalReunions, 2) : 0,
            'participants_uniques' => $participantsUniques,
            'reunions_avec_pv' => ReunionPV::whereHas('reunion', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date_debut', [$startDate, $endDate]);
            })->count()
        ];
    }

    /**
     * Obtenir les tendances temporelles
     */
    public function getTrends(string $startDate, string $endDate, string $groupBy = 'day'): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $trends = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $periodStart = $current->copy();
            $periodEnd = $current->copy();

            switch ($groupBy) {
                case 'day':
                    $periodEnd->endOfDay();
                    $format = 'Y-m-d';
                    break;
                case 'week':
                    $periodEnd->endOfWeek();
                    $format = 'Y-W';
                    break;
                case 'month':
                    $periodEnd->endOfMonth();
                    $format = 'Y-m';
                    break;
                default:
                    $periodEnd->endOfDay();
                    $format = 'Y-m-d';
            }

            $reunions = Reunion::whereBetween('date_debut', [$periodStart, $periodEnd])->get();

            $trends[] = [
                'periode' => $current->format($format),
                'date_debut' => $periodStart->toDateString(),
                'date_fin' => $periodEnd->toDateString(),
                'total_reunions' => $reunions->count(),
                'reunions_terminees' => $reunions->where('status', 'terminee')->count(),
                'reunions_annulees' => $reunions->where('status', 'annulee')->count(),
                'duree_totale' => $reunions->sum(function ($reunion) {
                    return Carbon::parse($reunion->date_debut)->diffInMinutes($reunion->date_fin);
                }),
                'participants' => ReunionParticipant::whereHas('reunion', function ($q) use ($periodStart, $periodEnd) {
                    $q->whereBetween('date_debut', [$periodStart, $periodEnd]);
                })->count()
            ];

            switch ($groupBy) {
                case 'day':
                    $current->addDay();
                    break;
                case 'week':
                    $current->addWeek();
                    break;
                case 'month':
                    $current->addMonth();
                    break;
            }
        }

        return $trends;
    }

    /**
     * Rapport détaillé par entité
     */
    public function getEntityReport(string $startDate, string $endDate, ?int $entiteId = null): array
    {
        $query = Reunion::with(['entite', 'typeReunion', 'participants'])
            ->whereBetween('date_debut', [$startDate, $endDate]);

        if ($entiteId) {
            $query->where('entite_id', $entiteId);
        }

        $reunions = $query->get();

        $entites = $reunions->groupBy('entite_id')->map(function ($entiteReunions, $entiteId) {
            $entite = $entiteReunions->first()->entite;
            $totalReunions = $entiteReunions->count();
            $dureeTotale = $entiteReunions->sum(function ($reunion) {
                return Carbon::parse($reunion->date_debut)->diffInMinutes($reunion->date_fin);
            });

            return [
                'entite_id' => $entiteId,
                'entite_nom' => $entite->nom,
                'total_reunions' => $totalReunions,
                'reunions_terminees' => $entiteReunions->where('status', 'terminee')->count(),
                'reunions_annulees' => $entiteReunions->where('status', 'annulee')->count(),
                'duree_totale_heures' => round($dureeTotale / 60, 2),
                'duree_moyenne_minutes' => $totalReunions > 0 ? round($dureeTotale / $totalReunions, 2) : 0,
                'participants_uniques' => $entiteReunions->flatMap->participants->unique('user_id')->count(),
                'types_reunion' => $entiteReunions->groupBy('type_reunion_id')->map(function ($typeReunions, $typeId) {
                    $type = $typeReunions->first()->typeReunion;
                    return [
                        'type_id' => $typeId,
                        'type_nom' => $type->nom,
                        'count' => $typeReunions->count()
                    ];
                })->values()
            ];
        })->values();

        return [
            'periode' => [
                'debut' => $startDate,
                'fin' => $endDate
            ],
            'entites' => $entites,
            'total_entites' => $entites->count(),
            'entite_la_plus_active' => $entites->sortByDesc('total_reunions')->first()
        ];
    }

    /**
     * Rapport de performance des participants
     */
    public function getParticipantPerformanceReport(string $startDate, string $endDate): array
    {
        $participants = ReunionParticipant::with(['user', 'reunion'])
            ->whereHas('reunion', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date_debut', [$startDate, $endDate]);
            })
            ->get()
            ->groupBy('user_id')
            ->map(function ($userParticipations, $userId) {
                $user = $userParticipations->first()->user;
                $reunions = $userParticipations->map->reunion;
                $totalReunions = $reunions->count();
                $reunionsPresent = $userParticipations->where('status', 'present')->count();
                $reunionsAbsent = $userParticipations->where('status', 'absent')->count();

                return [
                    'user_id' => $userId,
                    'user_nom' => $user->nom . ' ' . $user->prenom,
                    'user_email' => $user->email,
                    'total_reunions' => $totalReunions,
                    'reunions_present' => $reunionsPresent,
                    'reunions_absent' => $reunionsAbsent,
                    'taux_presence' => $totalReunions > 0 ? round(($reunionsPresent / $totalReunions) * 100, 2) : 0,
                    'roles' => $userParticipations->groupBy('role')->map->count(),
                    'duree_totale_heures' => round($reunions->sum(function ($reunion) {
                        return Carbon::parse($reunion->date_debut)->diffInMinutes($reunion->date_fin);
                    }) / 60, 2)
                ];
            })
            ->values()
            ->sortByDesc('total_reunions');

        return [
            'periode' => [
                'debut' => $startDate,
                'fin' => $endDate
            ],
            'participants' => $participants,
            'total_participants' => $participants->count(),
            'moyenne_presence' => $participants->avg('taux_presence'),
            'participant_plus_actif' => $participants->first(),
            'participant_moins_actif' => $participants->last()
        ];
    }

    /**
     * Rapport de qualité des PV
     */
    public function getPVQualityReport(string $startDate, string $endDate): array
    {
        $pvs = ReunionPV::with(['reunion', 'redacteur'])
            ->whereHas('reunion', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date_debut', [$startDate, $endDate]);
            })
            ->get();

        $stats = [
            'total_pv' => $pvs->count(),
            'pv_valides' => $pvs->where('status', 'valide')->count(),
            'pv_en_attente' => $pvs->where('status', 'en_attente')->count(),
            'pv_rejetes' => $pvs->where('status', 'rejete')->count(),
            'pv_brouillon' => $pvs->where('status', 'brouillon')->count(),
            'taux_validation' => $pvs->count() > 0 ? round(($pvs->where('status', 'valide')->count() / $pvs->count()) * 100, 2) : 0,
            'delai_moyen_validation' => $pvs->where('status', 'valide')->avg(function ($pv) {
                return Carbon::parse($pv->date_creation)->diffInDays($pv->date_validation);
            }),
            'redacteurs' => $pvs->groupBy('redacteur_id')->map(function ($redacteurPVs, $redacteurId) {
                $redacteur = $redacteurPVs->first()->redacteur;
                return [
                    'redacteur_id' => $redacteurId,
                    'redacteur_nom' => $redacteur->nom . ' ' . $redacteur->prenom,
                    'total_pv' => $redacteurPVs->count(),
                    'pv_valides' => $redacteurPVs->where('status', 'valide')->count(),
                    'taux_validation' => $redacteurPVs->count() > 0 ? round(($redacteurPVs->where('status', 'valide')->count() / $redacteurPVs->count()) * 100, 2) : 0
                ];
            })->values()
        ];

        return [
            'periode' => [
                'debut' => $startDate,
                'fin' => $endDate
            ],
            'statistiques' => $stats
        ];
    }

    /**
     * Métriques de performance
     */
    public function getPerformanceMetrics(string $startDate, string $endDate): array
    {
        $reunions = Reunion::whereBetween('date_debut', [$startDate, $endDate])->get();

        // Métriques de ponctualité
        $reunionsAvecRetard = $reunions->filter(function ($reunion) {
            $participants = $reunion->participants;
            if ($participants->isEmpty()) return false;

            $dateDebut = Carbon::parse($reunion->date_debut);
            $premierPresent = $participants->where('status', 'present')
                ->min('heure_arrivee');

            return $premierPresent && Carbon::parse($premierPresent)->gt($dateDebut);
        });

        // Métriques d'efficacité
        $dureePlanifiee = $reunions->sum(function ($reunion) {
            return Carbon::parse($reunion->date_debut)->diffInMinutes($reunion->date_fin);
        });

        $dureeReelle = $reunions->where('status', 'terminee')->sum(function ($reunion) {
            return Carbon::parse($reunion->date_debut)->diffInMinutes($reunion->date_fin);
        });

        return [
            'periode' => [
                'debut' => $startDate,
                'fin' => $endDate
            ],
            'ponctualite' => [
                'total_reunions' => $reunions->count(),
                'reunions_avec_retard' => $reunionsAvecRetard->count(),
                'taux_ponctualite' => $reunions->count() > 0 ? round((($reunions->count() - $reunionsAvecRetard->count()) / $reunions->count()) * 100, 2) : 0
            ],
            'efficacite' => [
                'duree_planifiee_heures' => round($dureePlanifiee / 60, 2),
                'duree_reelle_heures' => round($dureeReelle / 60, 2),
                'ratio_efficacite' => $dureePlanifiee > 0 ? round(($dureeReelle / $dureePlanifiee) * 100, 2) : 0
            ],
            'productivite' => [
                'reunions_par_jour' => $reunions->count() > 0 ? round($reunions->count() / Carbon::parse($startDate)->diffInDays($endDate), 2) : 0,
                'participants_par_reunion' => $reunions->avg(function ($reunion) {
                    return $reunion->participants->count();
                }),
                'pv_par_reunion' => $reunions->avg(function ($reunion) {
                    return $reunion->pv()->count();
                })
            ]
        ];
    }

    /**
     * Export des données pour analyse externe
     */
    public function exportData(string $startDate, string $endDate, string $format = 'json'): array
    {
        $reunions = Reunion::with(['entite', 'typeReunion', 'participants.user', 'pv'])
            ->whereBetween('date_debut', [$startDate, $endDate])
            ->get();

        $data = [
            'metadata' => [
                'export_date' => now()->toISOString(),
                'periode_debut' => $startDate,
                'periode_fin' => $endDate,
                'total_records' => $reunions->count()
            ],
            'reunions' => $reunions->map(function ($reunion) {
                return [
                    'id' => $reunion->id,
                    'titre' => $reunion->titre,
                    'description' => $reunion->description,
                    'date_debut' => $reunion->date_debut,
                    'date_fin' => $reunion->date_fin,
                    'lieu' => $reunion->lieu,
                    'status' => $reunion->status,
                    'entite' => [
                        'id' => $reunion->entite->id,
                        'nom' => $reunion->entite->nom
                    ],
                    'type_reunion' => [
                        'id' => $reunion->typeReunion->id,
                        'nom' => $reunion->typeReunion->nom
                    ],
                    'participants' => $reunion->participants->map(function ($participant) {
                        return [
                            'user_id' => $participant->user->id,
                            'nom' => $participant->user->nom,
                            'prenom' => $participant->user->prenom,
                            'email' => $participant->user->email,
                            'role' => $participant->role,
                            'status' => $participant->status,
                            'heure_arrivee' => $participant->heure_arrivee
                        ];
                    }),
                    'pv' => $reunion->pv->map(function ($pv) {
                        return [
                            'id' => $pv->id,
                            'contenu' => $pv->contenu,
                            'status' => $pv->status,
                            'date_creation' => $pv->date_creation,
                            'date_validation' => $pv->date_validation
                        ];
                    })
                ];
            })
        ];

        return $data;
    }

    /**
     * Générer un rapport personnalisé
     */
    public function generateCustomReport(array $filters, array $metrics): array
    {
        $query = Reunion::with(['entite', 'typeReunion', 'participants', 'pv']);

        // Appliquer les filtres
        if (!empty($filters['date_debut'])) {
            $query->where('date_debut', '>=', $filters['date_debut']);
        }
        if (!empty($filters['date_fin'])) {
            $query->where('date_debut', '<=', $filters['date_fin']);
        }
        if (!empty($filters['entite_id'])) {
            $query->where('entite_id', $filters['entite_id']);
        }
        if (!empty($filters['type_reunion_id'])) {
            $query->where('type_reunion_id', $filters['type_reunion_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $reunions = $query->get();

        $report = [
            'filtres_appliques' => $filters,
            'metriques_demandees' => $metrics,
            'total_reunions' => $reunions->count()
        ];

        // Calculer les métriques demandées
        foreach ($metrics as $metric) {
            switch ($metric) {
                case 'duree_moyenne':
                    $report['duree_moyenne_minutes'] = $reunions->avg(function ($reunion) {
                        return Carbon::parse($reunion->date_debut)->diffInMinutes($reunion->date_fin);
                    });
                    break;
                case 'taux_presence':
                    $totalParticipants = $reunions->sum(function ($reunion) {
                        return $reunion->participants->count();
                    });
                    $participantsPresent = $reunions->sum(function ($reunion) {
                        return $reunion->participants->where('status', 'present')->count();
                    });
                    $report['taux_presence'] = $totalParticipants > 0 ? round(($participantsPresent / $totalParticipants) * 100, 2) : 0;
                    break;
                case 'repartition_par_entite':
                    $report['repartition_par_entite'] = $reunions->groupBy('entite_id')->map(function ($entiteReunions, $entiteId) {
                        $entite = $entiteReunions->first()->entite;
                        return [
                            'entite_id' => $entiteId,
                            'entite_nom' => $entite->nom,
                            'count' => $entiteReunions->count()
                        ];
                    })->values();
                    break;
                case 'evolution_temporelle':
                    $report['evolution_temporelle'] = $reunions->groupBy(function ($reunion) {
                        return Carbon::parse($reunion->date_debut)->format('Y-m');
                    })->map->count();
                    break;
            }
        }

        return $report;
    }
}
