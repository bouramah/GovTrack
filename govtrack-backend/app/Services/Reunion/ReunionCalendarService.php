<?php

namespace App\Services\Reunion;

use App\Models\Reunion;
use App\Models\ReunionSerie;
use App\Models\ReunionParticipant;
use App\Models\User;
use App\Models\Entite;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReunionCalendarService
{
    /**
     * Récupérer les événements calendrier pour une période donnée
     */
    public function getCalendarEvents(string $startDate, string $endDate, array $filters = []): Collection
    {
        $query = Reunion::with(['typeReunion', 'participants.user', 'serie'])
            ->whereBetween('date_debut', [$startDate, $endDate]);

        // Filtres
        if (!empty($filters['type_reunion_id'])) {
            $query->where('type_reunion_id', $filters['type_reunion_id']);
        }

        if (!empty($filters['entite_id'])) {
            $query->where('entite_id', $filters['entite_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->whereHas('participants', function ($q) use ($filters) {
                $q->where('user_id', $filters['user_id']);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $reunions = $query->get();

        return $reunions->map(function ($reunion) {
            return $this->formatCalendarEvent($reunion);
        });
    }

    /**
     * Formater un événement pour le calendrier
     */
    public function formatCalendarEvent(Reunion $reunion): array
    {
        $participants = $reunion->participants->map(function ($participant) {
            return [
                'id' => $participant->user->id,
                'name' => $participant->user->nom . ' ' . $participant->user->prenom,
                'role' => $participant->role,
                'status' => $participant->status
            ];
        });

        return [
            'id' => $reunion->id,
            'title' => $reunion->titre,
            'start' => $reunion->date_debut,
            'end' => $reunion->date_fin,
            'allDay' => false,
            'type' => 'reunion',
            'type_reunion' => $reunion->typeReunion->nom,
            'entite' => $reunion->entite->nom,
            'lieu' => $reunion->lieu,
            'status' => $reunion->status,
            'participants' => $participants,
            'serie_id' => $reunion->serie_id,
            'color' => $this->getEventColor($reunion),
            'url' => '/reunions/' . $reunion->id
        ];
    }

    /**
     * Obtenir la couleur de l'événement selon le type et le statut
     */
    private function getEventColor(Reunion $reunion): string
    {
        $colors = [
            'planifiee' => '#3B82F6', // bleu
            'en_cours' => '#F59E0B',  // orange
            'terminee' => '#10B981',  // vert
            'annulee' => '#EF4444',   // rouge
            'reportee' => '#8B5CF6'   // violet
        ];

        return $colors[$reunion->status] ?? '#6B7280';
    }

    /**
     * Vue calendrier journalière
     */
    public function getDayView(string $date, array $filters = []): array
    {
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();

        $events = $this->getCalendarEvents($startOfDay->toDateString(), $endOfDay->toDateString(), $filters);

        // Créer des créneaux horaires
        $timeSlots = [];
        for ($hour = 8; $hour <= 20; $hour++) {
            $timeSlots[] = [
                'time' => sprintf('%02d:00', $hour),
                'events' => $events->filter(function ($event) use ($hour) {
                    $eventHour = Carbon::parse($event['start'])->hour;
                    return $eventHour === $hour;
                })->values()
            ];
        }

        return [
            'date' => $date,
            'time_slots' => $timeSlots,
            'total_events' => $events->count()
        ];
    }

    /**
     * Vue calendrier hebdomadaire
     */
    public function getWeekView(string $startDate, array $filters = []): array
    {
        $startOfWeek = Carbon::parse($startDate)->startOfWeek();
        $endOfWeek = Carbon::parse($startDate)->endOfWeek();

        $events = $this->getCalendarEvents($startOfWeek->toDateString(), $endOfWeek->toDateString(), $filters);

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $dayEvents = $events->filter(function ($event) use ($day) {
                return Carbon::parse($event['start'])->isSameDay($day);
            });

            $days[] = [
                'date' => $day->toDateString(),
                'day_name' => $day->format('l'),
                'day_number' => $day->day,
                'events' => $dayEvents->values(),
                'event_count' => $dayEvents->count()
            ];
        }

        return [
            'week_start' => $startOfWeek->toDateString(),
            'week_end' => $endOfWeek->toDateString(),
            'days' => $days,
            'total_events' => $events->count()
        ];
    }

    /**
     * Vue calendrier mensuelle
     */
    public function getMonthView(string $yearMonth, array $filters = []): array
    {
        $date = Carbon::parse($yearMonth . '-01');
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $events = $this->getCalendarEvents($startOfMonth->toDateString(), $endOfMonth->toDateString(), $filters);

        $weeks = [];
        $currentWeek = $startOfMonth->copy()->startOfWeek();
        $endOfView = $endOfMonth->copy()->endOfWeek();

        while ($currentWeek->lte($endOfView)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $day = $currentWeek->copy()->addDays($i);
                $dayEvents = $events->filter(function ($event) use ($day) {
                    return Carbon::parse($event['start'])->isSameDay($day);
                });

                $week[] = [
                    'date' => $day->toDateString(),
                    'day_number' => $day->day,
                    'is_current_month' => $day->month === $date->month,
                    'is_today' => $day->isToday(),
                    'events' => $dayEvents->values(),
                    'event_count' => $dayEvents->count()
                ];
            }
            $weeks[] = $week;
            $currentWeek->addWeek();
        }

        return [
            'year' => $date->year,
            'month' => $date->month,
            'month_name' => $date->format('F'),
            'weeks' => $weeks,
            'total_events' => $events->count()
        ];
    }

    /**
     * Vérifier la disponibilité d'un utilisateur
     */
    public function checkUserAvailability(int $userId, string $startDate, string $endDate, ?int $excludeReunionId = null): array
    {
        $query = ReunionParticipant::where('user_id', $userId)
            ->whereHas('reunion', function ($q) use ($startDate, $endDate) {
                $q->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date_debut', [$startDate, $endDate])
                      ->orWhereBetween('date_fin', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('date_debut', '<=', $startDate)
                            ->where('date_fin', '>=', $endDate);
                      });
                });
            });

        if ($excludeReunionId) {
            $query->where('reunion_id', '!=', $excludeReunionId);
        }

        $conflicts = $query->with(['reunion.typeReunion'])->get();

        return [
            'available' => $conflicts->isEmpty(),
            'conflicts' => $conflicts->map(function ($conflict) {
                return [
                    'reunion_id' => $conflict->reunion_id,
                    'titre' => $conflict->reunion->titre,
                    'date_debut' => $conflict->reunion->date_debut,
                    'date_fin' => $conflict->reunion->date_fin,
                    'type' => $conflict->reunion->typeReunion->nom
                ];
            })
        ];
    }

    /**
     * Trouver des créneaux disponibles pour une réunion
     */
    public function findAvailableSlots(array $participantIds, string $startDate, string $endDate, int $duration = 60): array
    {
        $slots = [];
        $currentDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        while ($currentDate->lte($endDate)) {
            // Heures de travail : 8h-18h
            for ($hour = 8; $hour <= 17; $hour++) {
                $slotStart = $currentDate->copy()->setHour($hour)->setMinute(0);
                $slotEnd = $slotStart->copy()->addMinutes($duration);

                // Vérifier si le créneau est disponible pour tous les participants
                $available = true;
                $conflicts = [];

                foreach ($participantIds as $participantId) {
                    $availability = $this->checkUserAvailability(
                        $participantId,
                        $slotStart->toDateTimeString(),
                        $slotEnd->toDateTimeString()
                    );

                    if (!$availability['available']) {
                        $available = false;
                        $conflicts = array_merge($conflicts, $availability['conflicts']);
                    }
                }

                if ($available) {
                    $slots[] = [
                        'start' => $slotStart->toDateTimeString(),
                        'end' => $slotEnd->toDateTimeString(),
                        'duration' => $duration,
                        'available' => true
                    ];
                }
            }

            $currentDate->addDay();
        }

        return [
            'slots' => $slots,
            'total_available' => count($slots)
        ];
    }

    /**
     * Obtenir les statistiques de calendrier
     */
    public function getCalendarStats(string $startDate, string $endDate, ?int $userId = null): array
    {
        $query = Reunion::whereBetween('date_debut', [$startDate, $endDate]);

        if ($userId) {
            $query->whereHas('participants', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        $reunions = $query->get();

        $stats = [
            'total_reunions' => $reunions->count(),
            'reunions_par_status' => $reunions->groupBy('status')->map->count(),
            'reunions_par_type' => $reunions->groupBy('type_reunion_id')->map->count(),
            'duree_moyenne' => $reunions->avg(function ($reunion) {
                return Carbon::parse($reunion->date_debut)->diffInMinutes($reunion->date_fin);
            }),
            'reunions_par_jour' => $reunions->groupBy(function ($reunion) {
                return Carbon::parse($reunion->date_debut)->format('Y-m-d');
            })->map->count()
        ];

        return $stats;
    }

    /**
     * Exporter les événements au format iCal
     */
    public function exportToICal(Collection $events): string
    {
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//GovTrack//Reunions//FR\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";

        foreach ($events as $event) {
            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "UID:" . $event['id'] . "@govtrack.com\r\n";
            $ical .= "DTSTART:" . Carbon::parse($event['start'])->format('Ymd\THis\Z') . "\r\n";
            $ical .= "DTEND:" . Carbon::parse($event['end'])->format('Ymd\THis\Z') . "\r\n";
            $ical .= "SUMMARY:" . $event['title'] . "\r\n";
            $ical .= "DESCRIPTION:" . ($event['description'] ?? '') . "\r\n";
            $ical .= "LOCATION:" . ($event['lieu'] ?? '') . "\r\n";
            $ical .= "STATUS:" . strtoupper($event['status']) . "\r\n";
            $ical .= "END:VEVENT\r\n";
        }

        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }
}
