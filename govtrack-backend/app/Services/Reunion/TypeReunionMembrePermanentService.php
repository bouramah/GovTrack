<?php

namespace App\Services\Reunion;

use App\Models\TypeReunion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TypeReunionMembrePermanentService
{
    /**
     * Ajouter un membre permanent à un type de réunion
     */
    public function addMembrePermanent(int $typeReunionId, array $data, int $userId): array
    {
        try {
            DB::beginTransaction();

            $typeReunion = TypeReunion::findOrFail($typeReunionId);
            $membre = User::findOrFail($data['user_id']);

            // Vérifier si le membre existe déjà
            $existingMembre = $typeReunion->membresPermanents()
                ->where('user_id', $data['user_id'])
                ->first();

            if ($existingMembre) {
                return [
                    'success' => false,
                    'message' => 'Cet utilisateur est déjà membre permanent de ce type de réunion'
                ];
            }

            $typeReunion->membresPermanents()->attach($data['user_id'], [
                'role_defaut' => $data['role_defaut'] ?? 'PARTICIPANT',
                'actif' => $data['actif'] ?? true,
                'notifications_par_defaut' => $data['notifications_par_defaut'] ?? [],
                'creer_par' => $userId,
                'modifier_par' => $userId,
            ]);

            Log::info('Membre permanent ajouté au type de réunion', [
                'type_reunion_id' => $typeReunionId,
                'membre_id' => $data['user_id'],
                'role_defaut' => $data['role_defaut'] ?? 'PARTICIPANT',
                'user_id' => $userId
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Membre permanent ajouté avec succès',
                'data' => [
                    'type_reunion_id' => $typeReunionId,
                    'membre_id' => $data['user_id'],
                    'membre' => $membre
                ]
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'ajout du membre permanent', [
                'type_reunion_id' => $typeReunionId,
                'membre_id' => $data['user_id'] ?? null,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du membre permanent',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour un membre permanent
     */
    public function updateMembrePermanent(int $typeReunionId, int $membreId, array $data, int $userId): array
    {
        try {
            DB::beginTransaction();

            $typeReunion = TypeReunion::findOrFail($typeReunionId);

            // Vérifier si le membre existe
            $existingMembre = $typeReunion->membresPermanents()
                ->where('user_id', $membreId)
                ->first();

            if (!$existingMembre) {
                return [
                    'success' => false,
                    'message' => 'Membre permanent non trouvé pour ce type de réunion'
                ];
            }

            $typeReunion->membresPermanents()->updateExistingPivot($membreId, [
                'role_defaut' => $data['role_defaut'] ?? $existingMembre->pivot->role_defaut,
                'actif' => $data['actif'] ?? $existingMembre->pivot->actif,
                'notifications_par_defaut' => $data['notifications_par_defaut'] ?? $existingMembre->pivot->notifications_par_defaut,
                'modifier_par' => $userId,
            ]);

            Log::info('Membre permanent mis à jour', [
                'type_reunion_id' => $typeReunionId,
                'membre_id' => $membreId,
                'role_defaut' => $data['role_defaut'] ?? $existingMembre->pivot->role_defaut,
                'user_id' => $userId
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Membre permanent mis à jour avec succès'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du membre permanent', [
                'type_reunion_id' => $typeReunionId,
                'membre_id' => $membreId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du membre permanent',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer un membre permanent
     */
    public function removeMembrePermanent(int $typeReunionId, int $membreId, int $userId): array
    {
        try {
            DB::beginTransaction();

            $typeReunion = TypeReunion::findOrFail($typeReunionId);

            // Vérifier si le membre existe
            $existingMembre = $typeReunion->membresPermanents()
                ->where('user_id', $membreId)
                ->first();

            if (!$existingMembre) {
                return [
                    'success' => false,
                    'message' => 'Membre permanent non trouvé pour ce type de réunion'
                ];
            }

            $typeReunion->membresPermanents()->detach($membreId);

            Log::info('Membre permanent supprimé', [
                'type_reunion_id' => $typeReunionId,
                'membre_id' => $membreId,
                'user_id' => $userId
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Membre permanent supprimé avec succès'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du membre permanent', [
                'type_reunion_id' => $typeReunionId,
                'membre_id' => $membreId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression du membre permanent',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer les membres permanents d'un type de réunion
     */
    public function getMembresPermanents(int $typeReunionId, array $filters = []): array
    {
        try {
            $typeReunion = TypeReunion::with(['membresPermanents'])->findOrFail($typeReunionId);

            $query = $typeReunion->membresPermanents();

            // Filtres
            if (isset($filters['role_defaut'])) {
                $query->wherePivot('role_defaut', $filters['role_defaut']);
            }

            if (isset($filters['actif'])) {
                $query->wherePivot('actif', $filters['actif']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('nom', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('prenom', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('email', 'like', '%' . $filters['search'] . '%');
                });
            }

            $membres = $query->orderBy('nom')->orderBy('prenom')->get();

            return [
                'success' => true,
                'data' => $membres,
                'total' => $membres->count(),
                'filters_applied' => $filters
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des membres permanents', [
                'type_reunion_id' => $typeReunionId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des membres permanents',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier si un utilisateur est membre permanent d'un type de réunion
     */
    public function isMembrePermanent(int $typeReunionId, int $userId): bool
    {
        try {
            $typeReunion = TypeReunion::findOrFail($typeReunionId);

            return $typeReunion->membresPermanents()
                ->where('user_id', $userId)
                ->wherePivot('actif', true)
                ->exists();

        } catch (Exception $e) {
            Log::error('Erreur lors de la vérification du statut membre permanent', [
                'type_reunion_id' => $typeReunionId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtenir le rôle par défaut d'un membre permanent
     */
    public function getMembreRoleDefaut(int $typeReunionId, int $userId): ?string
    {
        try {
            $typeReunion = TypeReunion::findOrFail($typeReunionId);

            $membre = $typeReunion->membresPermanents()
                ->where('user_id', $userId)
                ->wherePivot('actif', true)
                ->first();

            if (!$membre) {
                return null;
            }

            return $membre->pivot->role_defaut;

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération du rôle par défaut', [
                'type_reunion_id' => $typeReunionId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Obtenir les notifications par défaut d'un membre permanent
     */
    public function getMembreNotificationsDefaut(int $typeReunionId, int $userId): array
    {
        try {
            $typeReunion = TypeReunion::findOrFail($typeReunionId);

            $membre = $typeReunion->membresPermanents()
                ->where('user_id', $userId)
                ->wherePivot('actif', true)
                ->first();

            if (!$membre) {
                return [];
            }

            return $membre->pivot->notifications_par_defaut ?? [];

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des notifications par défaut', [
                'type_reunion_id' => $typeReunionId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Obtenir les statistiques des membres permanents
     */
    public function getStats(int $typeReunionId = null): array
    {
        try {
            $query = DB::table('type_reunion_membres_permanents');

            if ($typeReunionId) {
                $query->where('type_reunion_id', $typeReunionId);
            }

            $stats = $query->selectRaw('
                COUNT(*) as total_membres,
                COUNT(CASE WHEN actif = 1 THEN 1 END) as actifs,
                COUNT(CASE WHEN actif = 0 THEN 1 END) as inactifs,
                COUNT(CASE WHEN role_defaut = "PRESIDENT" THEN 1 END) as presidents,
                COUNT(CASE WHEN role_defaut = "SECRETAIRE" THEN 1 END) as secretaires,
                COUNT(CASE WHEN role_defaut = "PARTICIPANT" THEN 1 END) as participants,
                COUNT(CASE WHEN role_defaut = "OBSERVATEUR" THEN 1 END) as observateurs,
                COUNT(DISTINCT type_reunion_id) as types_reunion_avec_membres,
                COUNT(DISTINCT user_id) as membres_uniques
            ')
            ->first();

            return [
                'success' => true,
                'data' => [
                    'total_membres' => $stats->total_membres,
                    'actifs' => $stats->actifs,
                    'inactifs' => $stats->inactifs,
                    'par_role' => [
                        'presidents' => $stats->presidents,
                        'secretaires' => $stats->secretaires,
                        'participants' => $stats->participants,
                        'observateurs' => $stats->observateurs,
                    ],
                    'types_reunion_avec_membres' => $stats->types_reunion_avec_membres,
                    'membres_uniques' => $stats->membres_uniques
                ]
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors du calcul des statistiques des membres permanents', [
                'type_reunion_id' => $typeReunionId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Copier les membres permanents d'un type de réunion vers un autre
     */
    public function copierMembresPermanents(int $sourceTypeReunionId, int $destinationTypeReunionId, int $userId): array
    {
        try {
            DB::beginTransaction();

            $sourceTypeReunion = TypeReunion::with(['membresPermanents'])->findOrFail($sourceTypeReunionId);
            $destinationTypeReunion = TypeReunion::findOrFail($destinationTypeReunionId);

            $membresSource = $sourceTypeReunion->membresPermanents;
            $nombreCopie = 0;

            foreach ($membresSource as $membre) {
                // Vérifier si le membre n'existe pas déjà dans la destination
                $existingMembre = $destinationTypeReunion->membresPermanents()
                    ->where('user_id', $membre->id)
                    ->first();

                if (!$existingMembre) {
                    $destinationTypeReunion->membresPermanents()->attach($membre->id, [
                        'role_defaut' => $membre->pivot->role_defaut,
                        'actif' => $membre->pivot->actif,
                        'notifications_par_defaut' => $membre->pivot->notifications_par_defaut,
                        'creer_par' => $userId,
                        'modifier_par' => $userId,
                    ]);
                    $nombreCopie++;
                }
            }

            Log::info('Membres permanents copiés', [
                'source_type_reunion_id' => $sourceTypeReunionId,
                'destination_type_reunion_id' => $destinationTypeReunionId,
                'nombre_copie' => $nombreCopie,
                'user_id' => $userId
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Membres permanents copiés avec succès',
                'data' => [
                    'nombre_copie' => $nombreCopie
                ]
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la copie des membres permanents', [
                'source_type_reunion_id' => $sourceTypeReunionId,
                'destination_type_reunion_id' => $destinationTypeReunionId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la copie des membres permanents',
                'error' => $e->getMessage()
            ];
        }
    }
}
