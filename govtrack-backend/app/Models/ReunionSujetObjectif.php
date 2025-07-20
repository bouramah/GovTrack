<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class ReunionSujetObjectif extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'reunion_sujet_objectifs';

    /**
     * Constantes pour les statuts
     */
    public const STATUT_A_ATTEINDRE = 'A_ATTEINDRE';
    public const STATUT_EN_COURS = 'EN_COURS';
    public const STATUT_ATTEINT = 'ATTEINT';
    public const STATUT_ANNULE = 'ANNULE';

    public const STATUTS = [
        self::STATUT_A_ATTEINDRE => 'À atteindre',
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_ATTEINT => 'Atteint',
        self::STATUT_ANNULE => 'Annulé',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'sujet_id',
        'titre',
        'description',
        'progression',
        'date_limite',
        'statut',
        'commentaires',
        'date_creation',
        'date_modification',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'progression' => 'integer',
        'date_limite' => 'date',
        'commentaires' => 'array',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    /**
     * Relations avec le sujet
     */
    public function sujet(): BelongsTo
    {
        return $this->belongsTo(ReunionSujet::class, 'sujet_id');
    }

    /**
     * Relations avec les difficultés
     */
    public function difficultes(): HasMany
    {
        return $this->hasMany(ReunionObjectifDifficulte::class, 'objectif_id');
    }

    /**
     * Scope par statut
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope pour les objectifs en retard
     */
    public function scopeEnRetard($query)
    {
        return $query->where('date_limite', '<', now()->toDateString())
                     ->whereNotIn('statut', [self::STATUT_ATTEINT, self::STATUT_ANNULE]);
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    /**
     * Vérifier si l'objectif est en retard
     */
    public function getEstEnRetardAttribute(): bool
    {
        return $this->date_limite && $this->date_limite < now()->toDateString()
               && !in_array($this->statut, [self::STATUT_ATTEINT, self::STATUT_ANNULE]);
    }

    /**
     * Obtenir le pourcentage de progression formaté
     */
    public function getProgressionFormateeAttribute(): string
    {
        return "{$this->progression}%";
    }

    /**
     * Vérifier si l'objectif est atteint
     */
    public function getEstAtteintAttribute(): bool
    {
        return $this->statut === self::STATUT_ATTEINT || $this->progression >= 100;
    }

    /**
     * Obtenir la couleur du statut
     */
    public function getStatutCouleurAttribute(): string
    {
        return match($this->statut) {
            self::STATUT_A_ATTEINDRE => 'gray',
            self::STATUT_EN_COURS => 'blue',
            self::STATUT_ATTEINT => 'green',
            self::STATUT_ANNULE => 'red',
            default => 'gray',
        };
    }

    /**
     * Obtenir l'icône du statut
     */
    public function getStatutIconeAttribute(): string
    {
        return match($this->statut) {
            self::STATUT_A_ATTEINDRE => 'target',
            self::STATUT_EN_COURS => 'play',
            self::STATUT_ATTEINT => 'check-circle',
            self::STATUT_ANNULE => 'x-circle',
            default => 'help-circle',
        };
    }
}
