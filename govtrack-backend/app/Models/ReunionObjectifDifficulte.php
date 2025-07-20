<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class ReunionObjectifDifficulte extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'reunion_objectif_difficultes';

    /**
     * Constantes pour les niveaux d'impact
     */
    public const IMPACT_FAIBLE = 'FAIBLE';
    public const IMPACT_MOYEN = 'MOYEN';
    public const IMPACT_ELEVE = 'ELEVE';
    public const IMPACT_CRITIQUE = 'CRITIQUE';

    public const IMPACTS = [
        self::IMPACT_FAIBLE => 'Faible',
        self::IMPACT_MOYEN => 'Moyen',
        self::IMPACT_ELEVE => 'Élevé',
        self::IMPACT_CRITIQUE => 'Critique',
    ];

    /**
     * Constantes pour les statuts de résolution
     */
    public const STATUT_A_RESOUDRE = 'A_RESOUDRE';
    public const STATUT_EN_COURS = 'EN_COURS';
    public const STATUT_RESOLU = 'RESOLU';
    public const STATUT_ANNULE = 'ANNULE';

    public const STATUTS = [
        self::STATUT_A_RESOUDRE => 'À résoudre',
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_RESOLU => 'Résolu',
        self::STATUT_ANNULE => 'Annulé',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'objectif_id',
        'entite_id',
        'description',
        'niveau_impact',
        'statut_resolution',
        'commentaires',
        'date_creation',
        'date_modification',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'commentaires' => 'array',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    /**
     * Relations avec l'objectif
     */
    public function objectif(): BelongsTo
    {
        return $this->belongsTo(ReunionSujetObjectif::class, 'objectif_id');
    }

    /**
     * Relations avec l'entité
     */
    public function entite(): BelongsTo
    {
        return $this->belongsTo(Entite::class, 'entite_id');
    }

    /**
     * Scope par niveau d'impact
     */
    public function scopeByImpact($query, $impact)
    {
        return $query->where('niveau_impact', $impact);
    }

    /**
     * Scope par statut de résolution
     */
    public function scopeByStatutResolution($query, $statut)
    {
        return $query->where('statut_resolution', $statut);
    }

    /**
     * Scope pour les difficultés non résolues
     */
    public function scopeNonResolues($query)
    {
        return $query->whereNotIn('statut_resolution', [self::STATUT_RESOLU, self::STATUT_ANNULE]);
    }

    /**
     * Obtenir le libellé de l'impact
     */
    public function getImpactLibelleAttribute(): string
    {
        return self::IMPACTS[$this->niveau_impact] ?? $this->niveau_impact;
    }

    /**
     * Obtenir le libellé du statut de résolution
     */
    public function getStatutResolutionLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut_resolution] ?? $this->statut_resolution;
    }

    /**
     * Obtenir la couleur de l'impact
     */
    public function getImpactCouleurAttribute(): string
    {
        return match($this->niveau_impact) {
            self::IMPACT_FAIBLE => 'green',
            self::IMPACT_MOYEN => 'yellow',
            self::IMPACT_ELEVE => 'orange',
            self::IMPACT_CRITIQUE => 'red',
            default => 'gray',
        };
    }

    /**
     * Obtenir l'icône de l'impact
     */
    public function getImpactIconeAttribute(): string
    {
        return match($this->niveau_impact) {
            self::IMPACT_FAIBLE => 'minus',
            self::IMPACT_MOYEN => 'alert-circle',
            self::IMPACT_ELEVE => 'alert-triangle',
            self::IMPACT_CRITIQUE => 'alert-octagon',
            default => 'help-circle',
        };
    }

    /**
     * Obtenir la couleur du statut de résolution
     */
    public function getStatutResolutionCouleurAttribute(): string
    {
        return match($this->statut_resolution) {
            self::STATUT_A_RESOUDRE => 'gray',
            self::STATUT_EN_COURS => 'blue',
            self::STATUT_RESOLU => 'green',
            self::STATUT_ANNULE => 'red',
            default => 'gray',
        };
    }

    /**
     * Obtenir l'icône du statut de résolution
     */
    public function getStatutResolutionIconeAttribute(): string
    {
        return match($this->statut_resolution) {
            self::STATUT_A_RESOUDRE => 'clock',
            self::STATUT_EN_COURS => 'play',
            self::STATUT_RESOLU => 'check-circle',
            self::STATUT_ANNULE => 'x-circle',
            default => 'help-circle',
        };
    }

    /**
     * Vérifier si la difficulté est résolue
     */
    public function getEstResolueAttribute(): bool
    {
        return $this->statut_resolution === self::STATUT_RESOLU;
    }

    /**
     * Vérifier si la difficulté est critique
     */
    public function getEstCritiqueAttribute(): bool
    {
        return $this->niveau_impact === self::IMPACT_CRITIQUE;
    }
}
