<?php

namespace App\Services\Reunion;

use App\Models\ReunionNotificationConfig;
use App\Models\TypeReunion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ReunionNotificationConfigService
{
    /**
     * Créer une configuration de notification
     */
    public function createConfig(array $data, int $userId): ReunionNotificationConfig
    {
        try {
            DB::beginTransaction();

            $config = ReunionNotificationConfig::create([
                'type_reunion_id' => $data['type_reunion_id'],
                'type_notification' => $data['type_notification'],
                'actif' => $data['actif'] ?? true,
                'delai_jours' => $data['delai_jours'] ?? null,
                'template_email' => $data['template_email'],
                'destinataires_par_defaut' => $data['destinataires_par_defaut'] ?? [],
                'configuration_avancee' => $data['configuration_avancee'] ?? [],
                'creer_par' => $userId,
                'modifier_par' => $userId,
            ]);

            Log::info('Configuration de notification créée', [
                'config_id' => $config->id,
                'type_reunion_id' => $data['type_reunion_id'],
                'type_notification' => $data['type_notification'],
                'user_id' => $userId
            ]);

            DB::commit();
            return $config;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la configuration de notification', [
                'type_reunion_id' => $data['type_reunion_id'] ?? null,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Mettre à jour une configuration de notification
     */
    public function updateConfig(int $configId, array $data, int $userId): ReunionNotificationConfig
    {
        try {
            DB::beginTransaction();

            $config = ReunionNotificationConfig::findOrFail($configId);

            $config->update([
                'type_notification' => $data['type_notification'] ?? $config->type_notification,
                'actif' => $data['actif'] ?? $config->actif,
                'delai_jours' => $data['delai_jours'] ?? $config->delai_jours,
                'template_email' => $data['template_email'] ?? $config->template_email,
                'destinataires_par_defaut' => $data['destinataires_par_defaut'] ?? $config->destinataires_par_defaut,
                'configuration_avancee' => $data['configuration_avancee'] ?? $config->configuration_avancee,
                'modifier_par' => $userId,
            ]);

            Log::info('Configuration de notification mise à jour', [
                'config_id' => $config->id,
                'type_reunion_id' => $config->type_reunion_id,
                'user_id' => $userId
            ]);

            DB::commit();
            return $config;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de la configuration de notification', [
                'config_id' => $configId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Supprimer une configuration de notification
     */
    public function deleteConfig(int $configId, int $userId): bool
    {
        try {
            DB::beginTransaction();

            $config = ReunionNotificationConfig::findOrFail($configId);
            $typeReunionId = $config->type_reunion_id;

            $config->delete();

            Log::info('Configuration de notification supprimée', [
                'config_id' => $configId,
                'type_reunion_id' => $typeReunionId,
                'user_id' => $userId
            ]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de la configuration de notification', [
                'config_id' => $configId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer les configurations d'un type de réunion
     */
    public function getConfigs(int $typeReunionId, array $filters = []): array
    {
        try {
            $query = ReunionNotificationConfig::with(['typeReunion'])
                ->where('type_reunion_id', $typeReunionId);

            // Filtres
            if (isset($filters['type_notification'])) {
                $query->where('type_notification', $filters['type_notification']);
            }

            if (isset($filters['actif'])) {
                $query->where('actif', $filters['actif']);
            }

            $configs = $query->orderBy('type_notification')->get();

            return [
                'configs' => $configs,
                'total' => $configs->count(),
                'filters_applied' => $filters
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des configurations de notification', [
                'type_reunion_id' => $typeReunionId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer une configuration spécifique
     */
    public function getConfig(int $configId): ReunionNotificationConfig
    {
        try {
            return ReunionNotificationConfig::with(['typeReunion'])->findOrFail($configId);
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération de la configuration de notification', [
                'config_id' => $configId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * Activer/Désactiver une configuration
     */
    public function toggleActif(int $configId, bool $actif, int $userId): ReunionNotificationConfig
    {
        try {
            DB::beginTransaction();

            $config = ReunionNotificationConfig::findOrFail($configId);
            $ancienStatut = $config->actif;

            $config->update([
                'actif' => $actif,
                'updated_by' => $userId,
            ]);

            Log::info('Statut de configuration de notification changé', [
                'config_id' => $config->id,
                'ancien_statut' => $ancienStatut,
                'nouveau_statut' => $actif,
                'user_id' => $userId
            ]);

            DB::commit();
            return $config;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du changement de statut de la configuration', [
                'config_id' => $configId,
                'actif' => $actif,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Obtenir les statistiques des configurations
     */
    public function getStats(int $typeReunionId = null): array
    {
        try {
            $query = ReunionNotificationConfig::query();

            if ($typeReunionId) {
                $query->where('type_reunion_id', $typeReunionId);
            }

            $stats = $query->selectRaw('
                COUNT(*) as total_configs,
                COUNT(CASE WHEN actif = 1 THEN 1 END) as actives,
                COUNT(CASE WHEN actif = 0 THEN 1 END) as inactives,
                COUNT(CASE WHEN type_notification = "CONFIRMATION_PRESENCE" THEN 1 END) as confirmation_presence,
                COUNT(CASE WHEN type_notification = "RAPPEL" THEN 1 END) as rappel,
                COUNT(CASE WHEN type_notification = "PV_DISPONIBLE" THEN 1 END) as pv_disponible,
                COUNT(CASE WHEN type_notification = "RAPPEL_ACTIONS" THEN 1 END) as rappel_actions
            ')
            ->first();

            return [
                'total_configs' => $stats->total_configs,
                'actives' => $stats->actives,
                'inactives' => $stats->inactives,
                'par_type' => [
                    'confirmation_presence' => $stats->confirmation_presence,
                    'rappel' => $stats->rappel,
                    'pv_disponible' => $stats->pv_disponible,
                    'rappel_actions' => $stats->rappel_actions,
                ]
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors du calcul des statistiques des configurations', [
                'type_reunion_id' => $typeReunionId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * Copier les configurations d'un type de réunion vers un autre
     */
    public function copierConfigs(int $sourceTypeReunionId, int $destinationTypeReunionId, int $userId): int
    {
        try {
            DB::beginTransaction();

            $configsSource = ReunionNotificationConfig::where('type_reunion_id', $sourceTypeReunionId)->get();
            $nombreCopie = 0;

            foreach ($configsSource as $configSource) {
                ReunionNotificationConfig::create([
                    'type_reunion_id' => $destinationTypeReunionId,
                    'type_notification' => $configSource->type_notification,
                    'actif' => $configSource->actif,
                    'delai_jours' => $configSource->delai_jours,
                    'template_email' => $configSource->template_email,
                    'sujet_email' => $configSource->sujet_email,
                    'contenu_email' => $configSource->contenu_email,
                    'destinataires' => $configSource->destinataires,
                    'conditions_envoi' => $configSource->conditions_envoi,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
                $nombreCopie++;
            }

            Log::info('Configurations de notification copiées', [
                'source_type_reunion_id' => $sourceTypeReunionId,
                'destination_type_reunion_id' => $destinationTypeReunionId,
                'nombre_copie' => $nombreCopie,
                'user_id' => $userId
            ]);

            DB::commit();
            return $nombreCopie;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la copie des configurations', [
                'source_type_reunion_id' => $sourceTypeReunionId,
                'destination_type_reunion_id' => $destinationTypeReunionId,
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }
}
