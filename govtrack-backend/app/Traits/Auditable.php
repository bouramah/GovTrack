<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    /**
     * Boot the trait
     */
    protected static function bootAuditable()
    {
        // Écouter l'événement de suppression
        static::deleting(function ($model) {
            static::logDeletion($model);
        });
    }

    /**
     * Logger une suppression
     */
    protected static function logDeletion($model)
    {
        $user = Auth::user();

        AuditLog::create([
            'action' => 'delete',
            'table_name' => $model->getTable(),
            'record_id' => $model->getKey(),
            'record_type' => get_class($model),
            'deleted_data' => $model->getAttributes(),
            'deleted_data_summary' => static::generateDataSummary($model),
            'user_id' => $user?->id,
            'user_name' => $user ? $user->prenom . ' ' . $user->nom : null,
            'user_email' => $user?->email,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'request_url' => Request::fullUrl(),
            'request_method' => Request::method(),
            'reason' => Request::input('reason'),
            'metadata' => [
                'deleted_at' => now()->toISOString(),
                'model_class' => get_class($model),
                'primary_key' => $model->getKeyName(),
                'primary_key_value' => $model->getKey(),
            ]
        ]);
    }

    /**
     * Logger une suppression définitive
     */
    protected static function logForceDeletion($model)
    {
        $user = Auth::user();

        AuditLog::create([
            'action' => 'force_delete',
            'table_name' => $model->getTable(),
            'record_id' => $model->getKey(),
            'record_type' => get_class($model),
            'deleted_data' => $model->getAttributes(),
            'deleted_data_summary' => static::generateDataSummary($model),
            'user_id' => $user?->id,
            'user_name' => $user ? $user->prenom . ' ' . $user->nom : null,
            'user_email' => $user?->email,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'request_url' => Request::fullUrl(),
            'request_method' => Request::method(),
            'reason' => Request::input('reason'),
            'metadata' => [
                'force_deleted_at' => now()->toISOString(),
                'model_class' => get_class($model),
                'primary_key' => $model->getKeyName(),
                'primary_key_value' => $model->getKey(),
            ]
        ]);
    }

    /**
     * Logger une restauration
     */
    protected static function logRestoration($model)
    {
        $user = Auth::user();

        AuditLog::create([
            'action' => 'restore',
            'table_name' => $model->getTable(),
            'record_id' => $model->getKey(),
            'record_type' => get_class($model),
            'deleted_data' => null,
            'deleted_data_summary' => 'Enregistrement restauré',
            'user_id' => $user?->id,
            'user_name' => $user ? $user->prenom . ' ' . $user->nom : null,
            'user_email' => $user?->email,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'request_url' => Request::fullUrl(),
            'request_method' => Request::method(),
            'reason' => Request::input('reason'),
            'metadata' => [
                'restored_at' => now()->toISOString(),
                'model_class' => get_class($model),
                'primary_key' => $model->getKeyName(),
                'primary_key_value' => $model->getKey(),
            ]
        ]);
    }

    /**
     * Générer un résumé des données supprimées
     */
    protected static function generateDataSummary($model): string
    {
        $attributes = $model->getAttributes();
        $summary = [];

        // Champs importants à inclure dans le résumé
        $importantFields = ['nom', 'prenom', 'name', 'titre', 'email', 'matricule', 'description'];

        foreach ($importantFields as $field) {
            if (isset($attributes[$field]) && !empty($attributes[$field])) {
                $summary[] = ucfirst($field) . ': ' . $attributes[$field];
            }
        }

        // Si aucun champ important n'est trouvé, inclure l'ID
        if (empty($summary)) {
            $summary[] = 'ID: ' . $model->getKey();
        }

        return implode(' | ', $summary);
    }

    /**
     * Méthode statique pour logger manuellement une action
     */
    public static function logAction($action, $reason = null, $metadata = [])
    {
        $user = Auth::user();

        return AuditLog::create([
            'action' => $action,
            'table_name' => (new static)->getTable(),
            'record_id' => 0, // Pour les actions générales
            'record_type' => get_class(new static),
            'deleted_data' => null,
            'deleted_data_summary' => null,
            'user_id' => $user?->id,
            'user_name' => $user ? $user->prenom . ' ' . $user->nom : null,
            'user_email' => $user?->email,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'request_url' => Request::fullUrl(),
            'request_method' => Request::method(),
            'reason' => $reason,
            'metadata' => $metadata
        ]);
    }
}
