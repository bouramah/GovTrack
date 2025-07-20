<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class ReunionGeneree extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'reunion_generees';

    /**
     * Constantes pour les statuts
     */
    public const STATUT_GENERE = 'GENERE';
    public const STATUT_PLANIFIEE = 'PLANIFIEE';
    public const STATUT_ANNULEE = 'ANNULEE';

    public const STATUTS = [
        self::STATUT_GENERE => 'Généré',
        self::STATUT_PLANIFIEE => 'Planifiée',
        self::STATUT_ANNULEE => 'Annulée',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'serie_id',
        'reunion_id',
        'date_generation',
        'statut',
        'configuration_generation',
        'date_creation',
        'date_modification',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'date_generation' => 'datetime',
        'configuration_generation' => 'array',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    /**
     * Relations avec la série
     */
    public function serie(): BelongsTo
    {
        return $this->belongsTo(ReunionSerie::class, 'serie_id');
    }

    /**
     * Relations avec la réunion
     */
    public function reunion(): BelongsTo
    {
        return $this->belongsTo(Reunion::class, 'reunion_id');
    }

    /**
     * Scope par statut
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope par série
     */
    public function scopeBySerie($query, $serieId)
    {
        return $query->where('serie_id', $serieId);
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }
}
