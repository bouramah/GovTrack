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
    public const STATUT_SUCCES = 'SUCCES';
    public const STATUT_ERREUR = 'ERREUR';

    public const STATUTS = [
        self::STATUT_SUCCES => 'Succès',
        self::STATUT_ERREUR => 'Erreur',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'serie_id',
        'reunion_id',
        'genere_le',
        'statut_generation',
        'message_erreur',
        'configuration_utilisee',
        'date_creation',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'genere_le' => 'datetime',
        'statut_generation' => 'string',
        'message_erreur' => 'string',
        'configuration_utilisee' => 'array',
        'date_creation' => 'datetime',
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
        return $query->where('statut_generation', $statut);
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
        return self::STATUTS[$this->statut_generation] ?? $this->statut_generation;
    }
}
