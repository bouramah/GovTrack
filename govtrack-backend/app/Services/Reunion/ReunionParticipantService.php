<?php

namespace App\Services\Reunion;

use App\Models\Reunion;
use App\Models\ReunionParticipant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReunionParticipantService
{
    /**
     * Récupérer les participants d'une réunion
     */
    public function getParticipants(int $reunionId, User $user): array
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

            $participants = ReunionParticipant::with([
                'user',
                'reunion.typeReunion'
            ])
            ->where('reunion_id', $reunionId)
            ->orderBy('role')
            ->orderBy('user_id')
            ->get();

            return [
                'success' => true,
                'data' => $participants,
                'message' => 'Participants récupérés avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des participants', [
                'reunion_id' => $reunionId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des participants',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Ajouter un participant à une réunion
     */
    public function addParticipant(int $reunionId, array $data, User $user): array
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

            // Vérifier les permissions de modification
            if (!$this->canModifyReunion($reunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier cette réunion'
                ];
            }

            // Vérifier si le participant existe déjà
            $existingParticipant = ReunionParticipant::where('reunion_id', $reunionId)
                ->where('user_id', $data['user_id'])
                ->first();

            if ($existingParticipant) {
                return [
                    'success' => false,
                    'message' => 'Ce participant est déjà inscrit à cette réunion'
                ];
            }

            // Vérifier que l'utilisateur existe
            $participantUser = User::find($data['user_id']);
            if (!$participantUser) {
                return [
                    'success' => false,
                    'message' => 'Utilisateur participant non trouvé'
                ];
            }

            // Créer le participant
            $participant = ReunionParticipant::create([
                'reunion_id' => $reunionId,
                'user_id' => $data['user_id'],
                'role' => $data['role'] ?? 'PARTICIPANT',
                'type' => $data['type'] ?? 'PERMANENT',
                'statut_presence' => $data['statut_presence'] ?? 'EN_ATTENTE',
                'notifications_actives' => $data['notifications_actives'] ?? [],
                'date_creation' => now(),
                'date_modification' => now(),
                'creer_par' => $user->id,
                'modifier_par' => $user->id,
            ]);

            DB::commit();

            // Charger les relations pour la réponse
            $participant->load(['user', 'reunion.typeReunion']);

            return [
                'success' => true,
                'data' => $participant,
                'message' => 'Participant ajouté avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'ajout du participant', [
                'reunion_id' => $reunionId,
                'user_id' => $user->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du participant',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour un participant
     */
    public function updateParticipant(int $reunionId, int $participantId, array $data, User $user): array
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

            $participant = ReunionParticipant::where('reunion_id', $reunionId)
                ->where('id', $participantId)
                ->first();

            if (!$participant) {
                return [
                    'success' => false,
                    'message' => 'Participant non trouvé'
                ];
            }

            // Vérifier les permissions de modification
            if (!$this->canModifyReunion($reunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier cette réunion'
                ];
            }

            // Préparer les données de mise à jour
            $updateData = array_filter([
                'role' => $data['role'] ?? null,
                'type' => $data['type'] ?? null,
                'statut_presence' => $data['statut_presence'] ?? null,
                'notifications_actives' => $data['notifications_actives'] ?? null,
                'date_modification' => now(),
            ], function ($value) {
                return $value !== null;
            });

            $participant->update($updateData);

            DB::commit();

            // Charger les relations pour la réponse
            $participant->load(['user', 'reunion.typeReunion']);

            return [
                'success' => true,
                'data' => $participant,
                'message' => 'Participant mis à jour avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du participant', [
                'reunion_id' => $reunionId,
                'participant_id' => $participantId,
                'user_id' => $user->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du participant',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer un participant d'une réunion
     */
    public function removeParticipant(int $reunionId, int $participantId, User $user): array
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

            $participant = ReunionParticipant::where('reunion_id', $reunionId)
                ->where('id', $participantId)
                ->first();

            if (!$participant) {
                return [
                    'success' => false,
                    'message' => 'Participant non trouvé'
                ];
            }

            // Vérifier les permissions de modification
            if (!$this->canModifyReunion($reunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier cette réunion'
                ];
            }

            // Empêcher la suppression du créateur de la réunion
            if ($participant->user_id === $reunion->creer_par) {
                return [
                    'success' => false,
                    'message' => 'Impossible de supprimer le créateur de la réunion'
                ];
            }

            $participant->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Participant supprimé avec succès'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du participant', [
                'reunion_id' => $reunionId,
                'participant_id' => $participantId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression du participant',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour le statut de présence d'un participant
     */
    public function updatePresenceStatus(int $reunionId, int $participantId, string $statut, User $user): array
    {
        try {
            $reunion = Reunion::find($reunionId);
            if (!$reunion) {
                return [
                    'success' => false,
                    'message' => 'Réunion non trouvée'
                ];
            }

            $participant = ReunionParticipant::where('reunion_id', $reunionId)
                ->where('id', $participantId)
                ->first();

            if (!$participant) {
                return [
                    'success' => false,
                    'message' => 'Participant non trouvé'
                ];
            }

            // Vérifier les permissions
            if (!$this->canModifyReunion($reunion, $user) && $participant->user_id !== $user->id) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier ce participant'
                ];
            }

            // Vérifier la validité du statut
            $statutsValides = ['CONFIRME', 'ABSENT', 'EN_ATTENTE'];
            if (!in_array($statut, $statutsValides)) {
                return [
                    'success' => false,
                    'message' => 'Statut de présence invalide'
                ];
            }

            $participant->update([
                'statut_presence' => $statut,
                'date_modification' => now(),
            ]);

            return [
                'success' => true,
                'data' => $participant,
                'message' => 'Statut de présence mis à jour avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du statut de présence', [
                'reunion_id' => $reunionId,
                'participant_id' => $participantId,
                'user_id' => $user->id,
                'statut' => $statut,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut de présence',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Ajouter plusieurs participants en lot
     */
    public function addMultipleParticipants(int $reunionId, array $participantsData, User $user): array
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

            // Vérifier les permissions de modification
            if (!$this->canModifyReunion($reunion, $user)) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'avez pas les permissions pour modifier cette réunion'
                ];
            }

            $addedParticipants = [];
            $errors = [];

            foreach ($participantsData as $index => $participantData) {
                try {
                    // Vérifier si le participant existe déjà
                    $existingParticipant = ReunionParticipant::where('reunion_id', $reunionId)
                        ->where('user_id', $participantData['user_id'])
                        ->first();

                    if ($existingParticipant) {
                        $errors[] = "Participant {$participantData['user_id']} déjà inscrit (ligne " . ($index + 1) . ")";
                        continue;
                    }

                    // Vérifier que l'utilisateur existe
                    $participantUser = User::find($participantData['user_id']);
                    if (!$participantUser) {
                        $errors[] = "Utilisateur {$participantData['user_id']} non trouvé (ligne " . ($index + 1) . ")";
                        continue;
                    }

                    // Créer le participant
                    $participant = ReunionParticipant::create([
                        'reunion_id' => $reunionId,
                        'user_id' => $participantData['user_id'],
                        'role' => $participantData['role'] ?? 'PARTICIPANT',
                        'type' => $participantData['type'] ?? 'PERMANENT',
                        'statut_presence' => $participantData['statut_presence'] ?? 'EN_ATTENTE',
                        'notifications_actives' => $participantData['notifications_actives'] ?? [],
                        'date_creation' => now(),
                        'date_modification' => now(),
                        'creer_par' => $user->id,
                        'modifier_par' => $user->id,
                    ]);

                    $addedParticipants[] = $participant;

                } catch (\Exception $e) {
                    $errors[] = "Erreur pour le participant {$participantData['user_id']} (ligne " . ($index + 1) . "): " . $e->getMessage();
                }
            }

            DB::commit();

            return [
                'success' => true,
                'data' => [
                    'added_participants' => $addedParticipants,
                    'total_added' => count($addedParticipants),
                    'errors' => $errors
                ],
                'message' => count($addedParticipants) . ' participants ajoutés avec succès' .
                            (count($errors) > 0 ? ' (' . count($errors) . ' erreurs)' : '')
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'ajout multiple des participants', [
                'reunion_id' => $reunionId,
                'user_id' => $user->id,
                'participants_count' => count($participantsData),
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'ajout multiple des participants',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer les statistiques des participants
     */
    public function getParticipantStats(int $reunionId, User $user): array
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

            $participants = ReunionParticipant::where('reunion_id', $reunionId)->get();

            $stats = [
                'total' => $participants->count(),
                'invites' => $participants->where('statut_presence', 'INVITE')->count(),
                'confirmes' => $participants->where('statut_presence', 'CONFIRME')->count(),
                'presents' => $participants->where('statut_presence', 'PRESENT')->count(),
                'absents' => $participants->where('statut_presence', 'ABSENT')->count(),
                'excuses' => $participants->where('statut_presence', 'EXCUSE')->count(),
                'internes' => $participants->where('type', 'INTERNE')->count(),
                'externes' => $participants->where('type', 'EXTERNE')->count(),
                'organisateurs' => $participants->where('role', 'ORGANISATEUR')->count(),
                'participants' => $participants->where('role', 'PARTICIPANT')->count(),
                'observateurs' => $participants->where('role', 'OBSERVATEUR')->count(),
            ];

            return [
                'success' => true,
                'data' => $stats,
                'message' => 'Statistiques des participants récupérées avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques des participants', [
                'reunion_id' => $reunionId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques des participants',
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
     * Vérifier si l'utilisateur peut modifier la réunion
     */
    private function canModifyReunion(Reunion $reunion, User $user): bool
    {
        // L'utilisateur peut toujours modifier les réunions qu'il a créées
        if ($reunion->creer_par === $user->id) {
            return true;
        }

        // Vérifier les permissions de modification
        if ($user->hasPermission('update_reunions')) {
            return true;
        }

        // Vérifier si l'utilisateur peut gérer le type de réunion
        return $user->peutGererTypeReunion($reunion->type_reunion_id);
    }
}
