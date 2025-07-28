<?php

namespace App\Services\Reunion;

use App\Models\TypeReunion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TypeReunionGestionnaireService
{
    /**
     * Ajouter un gestionnaire à un type de réunion
     */
    public function addGestionnaire(int $typeReunionId, array $data, int $userId): array
    {
        try {
            DB::beginTransaction();

            $typeReunion = TypeReunion::findOrFail($typeReunionId);
            $gestionnaire = User::findOrFail($data['user_id']);

            // Vérifier si le gestionnaire existe déjà
            $existingGestionnaire = $typeReunion->gestionnaires()
                ->where('user_id', $data['user_id'])
                ->first();

            if ($existingGestionnaire) {
                return [
                    'success' => false,
                    'message' => 'Cet utilisateur est déjà gestionnaire de ce type de réunion'
                ];
            }

            $typeReunion->gestionnaires()->attach($data['user_id'], [
                'permissions' => $data['permissions'] ?? [],
                'actif' => $data['actif'] ?? true,
                'creer_par' => $userId,
                'modifier_par' => $userId,
            ]);

            Log::info('Gestionnaire ajouté au type de réunion', [
                'type_reunion_id' => $typeReunionId,
                'gestionnaire_id' => $data['user_id'],
                'permissions' => $data['permissions'] ?? [],
                'user_id' => $userId
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Gestionnaire ajouté avec succès',
                'data' => [
                    'type_reunion_id' => $typeReunionId,
                    'gestionnaire_id' => $data['user_id'],
                    'gestionnaire' => $gestionnaire
                ]
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'ajout du gestionnaire', [
                'type_reunion_id' => $typeReunionId,
                'gestionnaire_id' => $data['user_id'] ?? null,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du gestionnaire',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour un gestionnaire
     */
    public function updateGestionnaire(int $typeReunionId, int $gestionnaireId, array $data, int $userId): array
    {
        try {
            DB::beginTransaction();

            $typeReunion = TypeReunion::findOrFail($typeReunionId);

            // Vérifier si le gestionnaire existe
            $existingGestionnaire = $typeReunion->gestionnaires()
                ->where('user_id', $gestionnaireId)
                ->first();

            if (!$existingGestionnaire) {
                return [
                    'success' => false,
                    'message' => 'Gestionnaire non trouvé pour ce type de réunion'
                ];
            }

            $typeReunion->gestionnaires()->updateExistingPivot($gestionnaireId, [
                'permissions' => $data['permissions'] ?? $existingGestionnaire->pivot->permissions,
                'actif' => $data['actif'] ?? $existingGestionnaire->pivot->actif,
                'modifier_par' => $userId,
            ]);

            Log::info('Gestionnaire mis à jour', [
                'type_reunion_id' => $typeReunionId,
                'gestionnaire_id' => $gestionnaireId,
                'permissions' => $data['permissions'] ?? $existingGestionnaire->pivot->permissions,
                'user_id' => $userId
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Gestionnaire mis à jour avec succès'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du gestionnaire', [
                'type_reunion_id' => $typeReunionId,
                'gestionnaire_id' => $gestionnaireId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du gestionnaire',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer un gestionnaire
     */
    public function removeGestionnaire(int $typeReunionId, int $gestionnaireId, int $userId): array
    {
        try {
            DB::beginTransaction();

            $typeReunion = TypeReunion::findOrFail($typeReunionId);

            // Vérifier si le gestionnaire existe
            $existingGestionnaire = $typeReunion->gestionnaires()
                ->where('user_id', $gestionnaireId)
                ->first();

            if (!$existingGestionnaire) {
                return [
                    'success' => false,
                    'message' => 'Gestionnaire non trouvé pour ce type de réunion'
                ];
            }

            $typeReunion->gestionnaires()->detach($gestionnaireId);

            Log::info('Gestionnaire supprimé', [
                'type_reunion_id' => $typeReunionId,
                'gestionnaire_id' => $gestionnaireId,
                'user_id' => $userId
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Gestionnaire supprimé avec succès'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du gestionnaire', [
                'type_reunion_id' => $typeReunionId,
                'gestionnaire_id' => $gestionnaireId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression du gestionnaire',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer les gestionnaires d'un type de réunion
     */
    public function getGestionnaires(int $typeReunionId, array $filters = []): array
    {
        try {
            $typeReunion = TypeReunion::with(['gestionnaires'])->findOrFail($typeReunionId);

            $query = $typeReunion->gestionnaires();

            // Filtres
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

            $gestionnaires = $query->orderBy('nom')->orderBy('prenom')->get();

            return [
                'success' => true,
                'data' => $gestionnaires,
                'total' => $gestionnaires->count(),
                'filters_applied' => $filters
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des gestionnaires', [
                'type_reunion_id' => $typeReunionId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des gestionnaires',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier si un utilisateur est gestionnaire d'un type de réunion
     */
    public function isGestionnaire(int $typeReunionId, int $userId): bool
    {
        try {
            $typeReunion = TypeReunion::findOrFail($typeReunionId);

            return $typeReunion->gestionnaires()
                ->where('user_id', $userId)
                ->wherePivot('actif', true)
                ->exists();

        } catch (Exception $e) {
            Log::error('Erreur lors de la vérification du statut gestionnaire', [
                'type_reunion_id' => $typeReunionId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtenir les permissions d'un gestionnaire
     */
    public function getGestionnairePermissions(int $typeReunionId, int $userId): array
    {
        try {
            $typeReunion = TypeReunion::findOrFail($typeReunionId);

            $gestionnaire = $typeReunion->gestionnaires()
                ->where('user_id', $userId)
                ->wherePivot('actif', true)
                ->first();

            if (!$gestionnaire) {
                return [];
            }

            return $gestionnaire->pivot->permissions ?? [];

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des permissions', [
                'type_reunion_id' => $typeReunionId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Obtenir les statistiques des gestionnaires
     */
    public function getStats(int $typeReunionId = null): array
    {
        try {
            $query = DB::table('type_reunion_gestionnaires');

            if ($typeReunionId) {
                $query->where('type_reunion_id', $typeReunionId);
            }

            $stats = $query->selectRaw('
                COUNT(*) as total_gestionnaires,
                COUNT(CASE WHEN actif = 1 THEN 1 END) as actifs,
                COUNT(CASE WHEN actif = 0 THEN 1 END) as inactifs,
                COUNT(DISTINCT type_reunion_id) as types_reunion_avec_gestionnaires,
                COUNT(DISTINCT user_id) as gestionnaires_uniques
            ')
            ->first();

            return [
                'success' => true,
                'data' => [
                    'total_gestionnaires' => $stats->total_gestionnaires,
                    'actifs' => $stats->actifs,
                    'inactifs' => $stats->inactifs,
                    'types_reunion_avec_gestionnaires' => $stats->types_reunion_avec_gestionnaires,
                    'gestionnaires_uniques' => $stats->gestionnaires_uniques
                ]
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors du calcul des statistiques des gestionnaires', [
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
     * Copier les gestionnaires d'un type de réunion vers un autre
     */
    public function copierGestionnaires(int $sourceTypeReunionId, int $destinationTypeReunionId, int $userId): array
    {
        try {
            DB::beginTransaction();

            $sourceTypeReunion = TypeReunion::with(['gestionnaires'])->findOrFail($sourceTypeReunionId);
            $destinationTypeReunion = TypeReunion::findOrFail($destinationTypeReunionId);

            $gestionnairesSource = $sourceTypeReunion->gestionnaires;
            $nombreCopie = 0;

            foreach ($gestionnairesSource as $gestionnaire) {
                // Vérifier si le gestionnaire n'existe pas déjà dans la destination
                $existingGestionnaire = $destinationTypeReunion->gestionnaires()
                    ->where('user_id', $gestionnaire->id)
                    ->first();

                if (!$existingGestionnaire) {
                    $destinationTypeReunion->gestionnaires()->attach($gestionnaire->id, [
                        'permissions' => $gestionnaire->pivot->permissions,
                        'actif' => $gestionnaire->pivot->actif,
                        'creer_par' => $userId,
                        'modifier_par' => $userId,
                    ]);
                    $nombreCopie++;
                }
            }

            Log::info('Gestionnaires copiés', [
                'source_type_reunion_id' => $sourceTypeReunionId,
                'destination_type_reunion_id' => $destinationTypeReunionId,
                'nombre_copie' => $nombreCopie,
                'user_id' => $userId
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Gestionnaires copiés avec succès',
                'data' => [
                    'nombre_copie' => $nombreCopie
                ]
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la copie des gestionnaires', [
                'source_type_reunion_id' => $sourceTypeReunionId,
                'destination_type_reunion_id' => $destinationTypeReunionId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la copie des gestionnaires',
                'error' => $e->getMessage()
            ];
        }
    }
}
