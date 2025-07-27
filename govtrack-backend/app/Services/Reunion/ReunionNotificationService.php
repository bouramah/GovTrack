<?php

namespace App\Services\Reunion;

use App\Models\Reunion;
use App\Models\ReunionNotification;
use App\Models\User;
use App\Models\ReunionParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ReunionNotificationService
{
    /**
     * Récupérer les notifications d'une réunion
     */
    public function getNotifications(int $reunionId, User $user): array
    {
        try {
            $reunion = Reunion::find($reunionId);
            if (!$reunion) {
                return [
                    'success' => false,
                    'message' => 'Réunion non trouvée'
                ];
            }

            // Vérifier les permissions d'accès à la réunion
            if (!$this->canAccessReunion($reunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour accéder à cette réunion'
                ];
            }

            $notifications = ReunionNotification::with([
                'reunion.typeReunion',
                'destinataire'
            ])
            ->where('reunion_id', $reunionId)
            ->orderBy('envoye_le', 'desc')
            ->get();

            return [
                'success' => true,
                'data' => $notifications,
                'message' => 'Notifications récupérées avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des notifications', [
                'reunion_id' => $reunionId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des notifications',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer les notifications d'un utilisateur
     */
    public function getUserNotifications(Request $request, User $user): array
    {
        try {
            $query = ReunionNotification::with([
                'reunion.typeReunion'
            ])
            ->where('envoye_a', $user->id);

            // Filtres
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            if ($request->filled('date_debut')) {
                $query->where('envoye_le', '>=', $request->date_debut);
            }

            if ($request->filled('date_fin')) {
                $query->where('envoye_le', '<=', $request->date_fin);
            }

            // Tri
            $sortBy = $request->get('sort_by', 'envoye_le');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $notifications = $query->paginate($perPage);

            return [
                'success' => true,
                'data' => $notifications,
                'message' => 'Notifications récupérées avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des notifications utilisateur', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des notifications',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(int $notificationId, User $user): array
    {
        try {
            $notification = ReunionNotification::find($notificationId);

            if (!$notification) {
                return [
                    'success' => false,
                    'message' => 'Notification non trouvée'
                ];
            }

            // Vérifier que l'utilisateur est le destinataire
            if ($notification->envoye_a !== $user->id) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour cette action'
                ];
            }

            $notification->update([
                'statut' => 'LU',
            ]);

            return [
                'success' => true,
                'data' => $notification,
                'message' => 'Notification marquée comme lue'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors du marquage de la notification', [
                'notification_id' => $notificationId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors du marquage de la notification',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(User $user): array
    {
        try {
            $count = ReunionNotification::where('envoye_a', $user->id)
                ->where('statut', 'ENVOYE')
                ->update([
                    'statut' => 'LU',
                ]);

            return [
                'success' => true,
                'data' => ['count' => $count],
                'message' => $count . ' notifications marquées comme lues'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors du marquage de toutes les notifications', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors du marquage des notifications',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer une notification
     */
    public function deleteNotification(int $notificationId, User $user): array
    {
        try {
            $notification = ReunionNotification::find($notificationId);

            if (!$notification) {
                return [
                    'success' => false,
                    'message' => 'Notification non trouvée'
                ];
            }

            // Vérifier que l'utilisateur est le destinataire
            if ($notification->envoye_a !== $user->id) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour cette action'
                ];
            }

            $notification->delete();

            return [
                'success' => true,
                'message' => 'Notification supprimée avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de la notification', [
                'notification_id' => $notificationId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression de la notification',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Envoyer une notification manuelle
     */
    public function sendManualNotification(int $reunionId, array $data, User $user): array
    {
        try {
            DB::beginTransaction();

            $reunion = Reunion::find($reunionId);
            if (!$reunion) {
                return [
                    'success' => false,
                    'message' => 'Réunion non trouvée'
                ];
            }

            // Vérifier les permissions d'envoi
            if (!$this->canSendNotification($reunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour envoyer des notifications'
                ];
            }

            $notificationsEnvoyees = [];
            $erreurs = [];

            // Traiter chaque destinataire
            foreach ($data['destinataires'] as $destinataireData) {
                $destinataireId = $destinataireData['user_id'];
                $typeNotification = $destinataireData['type'] ?? $data['type'];

                try {
                    // Créer la notification
                    $notification = ReunionNotification::create([
                        'reunion_id' => $reunionId,
                        'type' => $typeNotification,
                        'envoye_a' => $destinataireId,
                        'envoye_le' => now(),
                        'statut' => 'ENVOYE',
                        'contenu_email' => $data['message'],
                        'configuration_type' => [
                            'titre' => $data['titre'],
                            'priorite' => $data['priorite'] ?? 'NORMALE',
                            'canaux' => $data['canaux'] ?? ['EMAIL']
                        ],
                        'date_creation' => now(),
                    ]);

                    // Envoyer par email si demandé
                    if (isset($data['envoyer_email']) && $data['envoyer_email']) {
                        $this->envoyerEmailNotification($notification);
                    }

                    $notificationsEnvoyees[] = $notification;

                } catch (\Exception $e) {
                    $erreurs[] = [
                        'destinataire_id' => $destinataireId,
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            return [
                'success' => true,
                'data' => [
                    'notifications_envoyees' => $notificationsEnvoyees,
                    'erreurs' => $erreurs
                ],
                'message' => count($notificationsEnvoyees) . ' notifications envoyées avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'envoi des notifications manuelles', [
                'reunion_id' => $reunionId,
                'user_id' => $user->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi des notifications',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Envoyer les notifications automatiques pour une réunion
     */
    public function sendAutomaticNotifications(Reunion $reunion, string $typeNotification): array
    {
        try {
            DB::beginTransaction();

            $notificationsEnvoyees = [];
            $erreurs = [];

            // Récupérer les participants qui ont activé ce type de notification
            $participants = $reunion->participants()
                ->whereJsonContains('notifications_actives', $typeNotification)
                ->get();

            foreach ($participants as $participant) {
                try {
                    // Vérifier si une notification similaire a déjà été envoyée
                    $notificationExistante = ReunionNotification::where('reunion_id', $reunion->id)
                        ->where('envoye_a', $participant->user_id)
                        ->where('type', $typeNotification)
                        ->exists();

                    if ($notificationExistante) {
                        continue;
                    }

                    // Préparer le contenu de la notification
                    $contenu = $this->preparerContenuNotification($reunion, $typeNotification);

                    // Créer la notification
                    $notification = ReunionNotification::create([
                        'reunion_id' => $reunion->id,
                        'type' => $typeNotification,
                        'envoye_a' => $participant->user_id,
                        'envoye_le' => now(),
                        'statut' => 'ENVOYE',
                        'contenu_email' => $contenu['message'],
                        'configuration_type' => [
                            'titre' => $contenu['titre'],
                            'type_notification' => $typeNotification
                        ],
                        'date_creation' => now(),
                    ]);

                    // Envoyer par email
                    $this->envoyerEmailNotification($notification);

                    $notificationsEnvoyees[] = $notification;

                } catch (\Exception $e) {
                    $erreurs[] = [
                        'participant_id' => $participant->user_id,
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            return [
                'success' => true,
                'data' => [
                    'notifications_envoyees' => $notificationsEnvoyees,
                    'erreurs' => $erreurs
                ],
                'message' => count($notificationsEnvoyees) . ' notifications automatiques envoyées'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'envoi des notifications automatiques', [
                'reunion_id' => $reunion->id,
                'type' => $typeNotification,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi des notifications automatiques',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer les statistiques des notifications
     */
    public function getNotificationStats(User $user): array
    {
        try {
            $query = ReunionNotification::where('envoye_a', $user->id);

            $stats = [
                'total' => $query->count(),
                'envoyees' => $query->where('statut', 'ENVOYE')->count(),
                'lues' => $query->where('statut', 'LU')->count(),
                'erreurs' => $query->where('statut', 'ERREUR')->count(),
                'confirmation_presence' => $query->where('type', 'CONFIRMATION_PRESENCE')->count(),
                'rappel_24h' => $query->where('type', 'RAPPEL_24H')->count(),
                'rappel_1h' => $query->where('type', 'RAPPEL_1H')->count(),
                'rappel_15min' => $query->where('type', 'RAPPEL_15MIN')->count(),
                'pv_disponible' => $query->where('type', 'PV_DISPONIBLE')->count(),
                'rappel_actions' => $query->where('type', 'RAPPEL_ACTIONS')->count(),
                'aujourd_hui' => $query->whereDate('envoye_le', today())->count(),
                'cette_semaine' => $query->whereBetween('envoye_le', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'ce_mois' => $query->whereMonth('envoye_le', now()->month)->count(),
            ];

            return [
                'success' => true,
                'data' => $stats,
                'message' => 'Statistiques des notifications récupérées avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques des notifications', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ];
        }
    }

    // ========================================
    // MÉTHODES PRIVÉES UTILITAIRES
    // ========================================

    /**
     * Vérifier si l'utilisateur peut accéder à la réunion
     */
    private function canAccessReunion(Reunion $reunion, User $user): bool
    {
        // L'utilisateur peut toujours accéder aux réunions qu'il a créées
        if ($reunion->creer_par === $user->id) {
            return true;
        }

        // L'utilisateur peut accéder aux réunions où il est participant
        if ($reunion->participants()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // L'utilisateur peut accéder aux réunions qu'il a modifiées
        if ($reunion->modifier_par === $user->id) {
            return true;
        }

        // Vérifier les permissions globales
        if ($user->hasPermission('view_all_reunions')) {
            return true;
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut envoyer des notifications
     */
    private function canSendNotification(Reunion $reunion, User $user): bool
    {
        // L'utilisateur peut toujours envoyer des notifications pour les réunions qu'il a créées
        if ($reunion->creer_par === $user->id) {
            return true;
        }

        // L'utilisateur peut envoyer des notifications s'il est organisateur
        if ($reunion->participants()
            ->where('user_id', $user->id)
            ->where('role', 'ORGANISATEUR')
            ->exists()) {
            return true;
        }

        // Vérifier les permissions globales
        return $user->hasPermission('send_reunion_notifications');
    }

    /**
     * Préparer le contenu d'une notification automatique
     */
    private function preparerContenuNotification(Reunion $reunion, string $type): array
    {
        $dateReunion = Carbon::parse($reunion->date_debut);
        $heureReunion = $dateReunion->format('H:i');

        switch ($type) {
            case 'CONFIRMATION_PRESENCE':
                return [
                    'titre' => 'Confirmation de présence : ' . $reunion->titre,
                    'message' => "Veuillez confirmer votre présence à la réunion '{$reunion->titre}' le {$dateReunion->format('d/m/Y')} à {$heureReunion}. Lieu : {$reunion->lieu}"
                ];

            case 'RAPPEL_24H':
                return [
                    'titre' => 'Rappel 24h : Réunion ' . $reunion->titre,
                    'message' => "Rappel : La réunion '{$reunion->titre}' aura lieu demain le {$dateReunion->format('d/m/Y')} à {$heureReunion}. Lieu : {$reunion->lieu}"
                ];

            case 'RAPPEL_1H':
                return [
                    'titre' => 'Rappel 1h : Réunion ' . $reunion->titre,
                    'message' => "Rappel : La réunion '{$reunion->titre}' aura lieu dans 1 heure, le {$dateReunion->format('d/m/Y')} à {$heureReunion}. Lieu : {$reunion->lieu}"
                ];

            case 'RAPPEL_15MIN':
                return [
                    'titre' => 'Rappel 15min : Réunion ' . $reunion->titre,
                    'message' => "Rappel : La réunion '{$reunion->titre}' aura lieu dans 15 minutes, le {$dateReunion->format('d/m/Y')} à {$heureReunion}. Lieu : {$reunion->lieu}"
                ];

            case 'PV_DISPONIBLE':
                return [
                    'titre' => 'PV disponible : Réunion ' . $reunion->titre,
                    'message' => "Le procès-verbal de la réunion '{$reunion->titre}' du {$dateReunion->format('d/m/Y')} est maintenant disponible."
                ];

            case 'RAPPEL_ACTIONS':
                return [
                    'titre' => 'Rappel actions : Réunion ' . $reunion->titre,
                    'message' => "Rappel : Vous avez des actions à effectuer suite à la réunion '{$reunion->titre}' du {$dateReunion->format('d/m/Y')}."
                ];

            default:
                return [
                    'titre' => 'Notification : ' . $reunion->titre,
                    'message' => "Nouvelle notification concernant la réunion '{$reunion->titre}'"
                ];
        }
    }

    /**
     * Envoyer une notification par email
     */
    private function envoyerEmailNotification(ReunionNotification $notification): void
    {
        try {
            $destinataire = $notification->destinataire;
            $reunion = $notification->reunion;

            // Vérifier que l'utilisateur a une adresse email
            if (!$destinataire->email) {
                Log::warning('Impossible d\'envoyer l\'email : utilisateur sans adresse email', [
                    'user_id' => $destinataire->id,
                    'notification_id' => $notification->id
                ]);
                return;
            }

            // Envoyer l'email selon le type de notification
            switch ($notification->type) {
                case 'CONFIRMATION_PRESENCE':
                    Mail::to($destinataire->email)->send(new \App\Mail\ReunionConfirmationPresence($reunion, $destinataire));
                    break;

                case 'RAPPEL_24H':
                    Mail::to($destinataire->email)->send(new \App\Mail\ReunionRappel24H($reunion, $destinataire));
                    break;

                case 'RAPPEL_1H':
                    Mail::to($destinataire->email)->send(new \App\Mail\ReunionRappel1H($reunion, $destinataire));
                    break;

                case 'RAPPEL_15MIN':
                    Mail::to($destinataire->email)->send(new \App\Mail\ReunionRappel15Min($reunion, $destinataire));
                    break;

                case 'PV_DISPONIBLE':
                    Mail::to($destinataire->email)->send(new \App\Mail\ReunionPVDisponible($reunion, $destinataire));
                    break;

                case 'RAPPEL_ACTIONS':
                    Mail::to($destinataire->email)->send(new \App\Mail\ReunionRappelActions($reunion, $destinataire));
                    break;

                default:
                    Mail::to($destinataire->email)->send(new \App\Mail\ReunionNotification($notification));
                    break;
            }

            Log::info('Email de notification envoyé avec succès', [
                'notification_id' => $notification->id,
                'destinataire_email' => $destinataire->email,
                'type' => $notification->type
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de l\'email de notification', [
                'notification_id' => $notification->id,
                'destinataire_email' => $notification->destinataire->email ?? 'N/A',
                'error' => $e->getMessage()
            ]);
        }
    }
}
