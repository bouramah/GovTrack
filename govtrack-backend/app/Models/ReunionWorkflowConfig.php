<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class ReunionWorkflowConfig extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'reunion_workflow_configs';

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'type_reunion_id',
        'nom_workflow',
        'etapes',
        'actif',
        'obligatoire',
        'configuration',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'etapes' => 'array',
        'configuration' => 'array',
        'actif' => 'boolean',
        'obligatoire' => 'boolean',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    /**
     * Relations avec le type de réunion
     */
    public function typeReunion(): BelongsTo
    {
        return $this->belongsTo(TypeReunion::class, 'type_reunion_id');
    }

    /**
     * Relations avec les exécutions de workflow
     */
    public function executions(): HasMany
    {
        return $this->hasMany(ReunionWorkflowExecution::class, 'workflow_config_id');
    }

    /**
     * Relations avec le créateur
     */
    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creer_par');
    }

    /**
     * Relations avec le modificateur
     */
    public function modificateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modifier_par');
    }

    /**
     * Scope pour les workflows actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope par type de réunion
     */
    public function scopeByTypeReunion($query, $typeReunionId)
    {
        return $query->where('type_reunion_id', $typeReunionId);
    }

    /**
     * Obtenir le nombre d'étapes
     */
    public function getNombreEtapesAttribute(): int
    {
        return count($this->etapes ?? []);
    }

    /**
     * Obtenir les étapes formatées
     */
    public function getEtapesFormateesAttribute(): array
    {
        $etapes = [];
        foreach ($this->etapes ?? [] as $index => $etape) {
            $etapes[] = [
                'ordre' => $index + 1,
                'nom' => $etape['nom'] ?? "Étape " . ($index + 1),
                'description' => $etape['description'] ?? '',
                'responsable' => $etape['responsable'] ?? '',
                'duree_estimee' => $etape['duree_estimee'] ?? 0,
                'conditions' => $etape['conditions'] ?? [],
            ];
        }
        return $etapes;
    }
}
