<?php

namespace App\Services\Reunion;

use App\Models\TypeReunionValidateurPV;
use App\Models\TypeReunion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TypeReunionValidateurPVService
{
    /**
     * Créer un validateur de PV pour un type de réunion
     */
    public function createValidateur(array $data, int $userId): TypeReunionValidateurPV
    {
        try {
            DB::beginTransaction();

            $validateur = TypeReunionValidateurPV::create([
                'type_reunion_id' => $data['type_reunion_id'],
                'role_validateur' => $data['role_validateur'],
                'user_id' => $data['user_id'] ?? null,
                'ordre_priorite' => $data['ordre_priorite'],
                'actif' => $data['actif'] ?? true,
                'creer_par' => $userId,
                'modifier_par' => $userId,
            ]);

            Log::info('Validateur de PV créé', [
                'validateur_id' => $validateur->id,
                'type_reunion_id' => $data['type_reunion_id'],
                'role_validateur' => $data['role_validateur'],
                'user_id' => $userId
            ]);

            DB::commit();
            return $validateur;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du validateur de PV', [
                'type_reunion_id' => $data['type_reunion_id'] ?? null,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Mettre à jour un validateur de PV
     */
    public function updateValidateur(int $validateurId, array $data, int $userId): TypeReunionValidateurPV
    {
        try {
            DB::beginTransaction();

            $validateur = TypeReunionValidateurPV::findOrFail($validateurId);

            $validateur->update([
                'role_validateur' => $data['role_validateur'] ?? $validateur->role_validateur,
                'user_id' => $data['user_id'] ?? $validateur->user_id,
                'ordre_priorite' => $data['ordre_priorite'] ?? $validateur->ordre_priorite,
                'actif' => $data['actif'] ?? $validateur->actif,
                'modifier_par' => $userId,
            ]);

            Log::info('Validateur de PV mis à jour', [
                'validateur_id' => $validateur->id,
                'type_reunion_id' => $validateur->type_reunion_id,
                'user_id' => $userId
            ]);

            DB::commit();
            return $validateur;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du validateur de PV', [
                'validateur_id' => $validateurId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Supprimer un validateur de PV
     */
    public function deleteValidateur(int $validateurId, int $userId): bool
    {
        try {
            DB::beginTransaction();

            $validateur = TypeReunionValidateurPV::findOrFail($validateurId);
            $typeReunionId = $validateur->type_reunion_id;

            $validateur->delete();

            Log::info('Validateur de PV supprimé', [
                'validateur_id' => $validateurId,
                'type_reunion_id' => $typeReunionId,
                'user_id' => $userId
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du validateur de PV', [
                'validateur_id' => $validateurId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer les validateurs d'un type de réunion
     */
    public function getValidateurs(int $typeReunionId, array $filters = []): array
    {
        try {
            $query = TypeReunionValidateurPV::with(['typeReunion', 'user'])
                ->where('type_reunion_id', $typeReunionId);

            // Filtres
            if (isset($filters['role_validateur'])) {
                $query->where('role_validateur', $filters['role_validateur']);
            }

            if (isset($filters['actif'])) {
                $query->where('actif', $filters['actif']);
            }

            if (isset($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }

            $validateurs = $query->orderBy('ordre_priorite')->get();

            return [
                'validateurs' => $validateurs,
                'total' => $validateurs->count(),
                'filters_applied' => $filters
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des validateurs de PV', [
                'type_reunion_id' => $typeReunionId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer un validateur spécifique
     */
    public function getValidateur(int $validateurId): TypeReunionValidateurPV
    {
        try {
            return TypeReunionValidateurPV::with(['typeReunion', 'user'])->findOrFail($validateurId);
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération du validateur de PV', [
                'validateur_id' => $validateurId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * Activer/Désactiver un validateur
     */
    public function toggleActif(int $validateurId, bool $actif, int $userId): TypeReunionValidateurPV
    {
        try {
            DB::beginTransaction();

            $validateur = TypeReunionValidateurPV::findOrFail($validateurId);
            $ancienStatut = $validateur->actif;

            $validateur->update([
                'actif' => $actif,
                'updated_by' => $userId,
            ]);

            Log::info('Statut de validateur de PV changé', [
                'validateur_id' => $validateur->id,
                'ancien_statut' => $ancienStatut,
                'nouveau_statut' => $actif,
                'user_id' => $userId
            ]);

            DB::commit();
            return $validateur;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du changement de statut du validateur', [
                'validateur_id' => $validateurId,
                'actif' => $actif,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Réorganiser l'ordre des validateurs
     */
    public function reorderValidateurs(int $typeReunionId, array $ordreValidateurs, int $userId): bool
    {
        try {
            DB::beginTransaction();

            foreach ($ordreValidateurs as $index => $validateurId) {
                TypeReunionValidateurPV::where('id', $validateurId)
                    ->where('type_reunion_id', $typeReunionId)
                    ->update([
                        'ordre_priorite' => $index + 1,
                        'updated_by' => $userId,
                    ]);
            }

            Log::info('Ordre des validateurs de PV réorganisé', [
                'type_reunion_id' => $typeReunionId,
                'nombre_validateurs' => count($ordreValidateurs),
                'user_id' => $userId
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la réorganisation des validateurs', [
                'type_reunion_id' => $typeReunionId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Obtenir les statistiques des validateurs
     */
    public function getStats(int $typeReunionId = null): array
    {
        try {
            $query = TypeReunionValidateurPV::query();

            if ($typeReunionId) {
                $query->where('type_reunion_id', $typeReunionId);
            }

            $stats = $query->selectRaw('
                COUNT(*) as total_validateurs,
                COUNT(CASE WHEN actif = 1 THEN 1 END) as actifs,
                COUNT(CASE WHEN actif = 0 THEN 1 END) as inactifs,
                COUNT(CASE WHEN role_validateur = "SECRETAIRE" THEN 1 END) as secretaires,
                COUNT(CASE WHEN role_validateur = "PRESIDENT" THEN 1 END) as presidents,
                COUNT(CASE WHEN role_validateur = "AUTRE" THEN 1 END) as autres,
                COUNT(CASE WHEN user_id IS NOT NULL THEN 1 END) as validateurs_specifiques
            ')
            ->first();

            return [
                'total_validateurs' => $stats->total_validateurs,
                'actifs' => $stats->actifs,
                'inactifs' => $stats->inactifs,
                'par_role' => [
                    'secretaires' => $stats->secretaires,
                    'presidents' => $stats->presidents,
                    'autres' => $stats->autres,
                ],
                'validateurs_specifiques' => $stats->validateurs_specifiques
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors du calcul des statistiques des validateurs', [
                'type_reunion_id' => $typeReunionId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * Copier les validateurs d'un type de réunion vers un autre
     */
    public function copierValidateurs(int $sourceTypeReunionId, int $destinationTypeReunionId, int $userId): int
    {
        try {
            DB::beginTransaction();

            $validateursSource = TypeReunionValidateurPV::where('type_reunion_id', $sourceTypeReunionId)->get();
            $nombreCopie = 0;

            foreach ($validateursSource as $validateurSource) {
                TypeReunionValidateurPV::create([
                    'type_reunion_id' => $destinationTypeReunionId,
                    'role_validateur' => $validateurSource->role_validateur,
                    'user_id' => $validateurSource->user_id,
                    'ordre_priorite' => $validateurSource->ordre_priorite,
                    'actif' => $validateurSource->actif,
                    'conditions_validation' => $validateurSource->conditions_validation,
                    'delai_validation_jours' => $validateurSource->delai_validation_jours,
                    'notifications_actives' => $validateurSource->notifications_actives,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
                $nombreCopie++;
            }

            Log::info('Validateurs de PV copiés', [
                'source_type_reunion_id' => $sourceTypeReunionId,
                'destination_type_reunion_id' => $destinationTypeReunionId,
                'nombre_copie' => $nombreCopie,
                'user_id' => $userId
            ]);

            DB::commit();
            return $nombreCopie;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la copie des validateurs', [
                'source_type_reunion_id' => $sourceTypeReunionId,
                'destination_type_reunion_id' => $destinationTypeReunionId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }
}
