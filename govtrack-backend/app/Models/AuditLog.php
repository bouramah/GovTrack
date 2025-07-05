<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'action',
        'table_name',
        'record_id',
        'record_type',
        'deleted_data',
        'deleted_data_summary',
        'user_id',
        'user_name',
        'user_email',
        'ip_address',
        'user_agent',
        'request_url',
        'request_method',
        'reason',
        'metadata'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deleted_data' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur qui a effectué l'action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour filtrer par action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope pour filtrer par table
     */
    public function scopeByTable($query, $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    /**
     * Scope pour filtrer par utilisateur
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope pour filtrer par type d'enregistrement
     */
    public function scopeByRecordType($query, $recordType)
    {
        return $query->where('record_type', $recordType);
    }

    /**
     * Scope pour filtrer par date
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Obtenir le nom formaté de l'action
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'delete' => 'Suppression',
            'restore' => 'Restauration',
            'force_delete' => 'Suppression définitive',
            default => ucfirst($this->action)
        };
    }

    /**
     * Obtenir le nom formaté de la table
     */
    public function getTableLabelAttribute(): string
    {
        return match($this->table_name) {
            'users' => 'Utilisateurs',
            'entites' => 'Entités',
            'postes' => 'Postes',
            'roles' => 'Rôles',
            'permissions' => 'Permissions',
            'type_entites' => 'Types d\'entités',
            'type_projets' => 'Types de projets',
            'projets' => 'Projets',
            'taches' => 'Tâches',
            default => ucfirst(str_replace('_', ' ', $this->table_name))
        };
    }

    /**
     * Obtenir le nom complet de l'utilisateur
     */
    public function getUserFullNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->prenom . ' ' . $this->user->nom;
        }
        return $this->user_name ?? 'Utilisateur inconnu';
    }

    /**
     * Obtenir la date formatée
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d/m/Y H:i:s');
    }

    /**
     * Obtenir un résumé de l'action
     */
    public function getActionSummaryAttribute(): string
    {
        $user = $this->getUserFullNameAttribute();
        $action = $this->getActionLabelAttribute();
        $table = $this->getTableLabelAttribute();
        $date = $this->getFormattedDateAttribute();

        return "{$user} a {$action} un(e) {$table} le {$date}";
    }
}
