<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class ReunionAction extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'reunion_actions';

    /**
     * Constantes pour les priorités
     */
    public const PRIORITE_FAIBLE = 'FAIBLE';
    public const PRIORITE_NORMALE = 'NORMALE';
    public const PRIORITE_ELEVEE = 'ELEVEE';
    public const PRIORITE_CRITIQUE = 'CRITIQUE';

    public const PRIORITES = [
        self::PRIORITE_FAIBLE => 'Faible',
        self::PRIORITE_NORMALE => 'Normale',
        self::PRIORITE_ELEVEE => 'Élevée',
        self::PRIORITE_CRITIQUE => 'Critique',
    ];

    /**
     * Constantes pour les statuts
     */
    public const STATUT_A_FAIRE = 'A_FAIRE';
    public const STATUT_EN_COURS = 'EN_COURS';
    public const STATUT_TERMINE = 'TERMINE';
    public const STATUT_ANNULE = 'ANNULE';

    public const STATUTS = [
        self::STATUT_A_FAIRE => 'À faire',
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_TERMINE => 'Terminé',
        self::STATUT_ANNULE => 'Annulé',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'reunion_id',
        'titre',
        'description',
        'responsable_id',
        'date_limite',
        'progression',
        'priorite',
        'statut',
        'pieces_jointes',
        'commentaires',
        'date_creation',
        'date_modification',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'date_limite' => 'date',
        'progression' => 'integer',
        'pieces_jointes' => 'array',
        'commentaires' => 'array',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    /**
     * Relations avec la réunion
     */
    public function reunion(): BelongsTo
    {
        return $this->belongsTo(Reunion::class, 'reunion_id');
    }

    /**
     * Relations avec le responsable
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    /**
     * Scope par statut
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope par priorité
     */
    public function scopeByPriorite($query, $priorite)
    {
        return $query->where('priorite', $priorite);
    }

    /**
     * Scope pour les actions en retard
     */
    public function scopeEnRetard($query)
    {
        return $query->where('date_limite', '<', now()->toDateString())
                     ->whereNotIn('statut', [self::STATUT_TERMINE, self::STATUT_ANNULE]);
    }

    /**
     * Obtenir le libellé de la priorité
     */
    public function getPrioriteLibelleAttribute(): string
    {
        return self::PRIORITES[$this->priorite] ?? $this->priorite;
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    /**
     * Vérifier si l'action est en retard
     */
    public function getEstEnRetardAttribute(): bool
    {
        return $this->date_limite && $this->date_limite < now()->toDateString()
               && !in_array($this->statut, [self::STATUT_TERMINE, self::STATUT_ANNULE]);
    }

    /**
     * Obtenir la couleur de la priorité
     */
    public function getPrioriteCouleurAttribute(): string
    {
        return match($this->priorite) {
            self::PRIORITE_FAIBLE => 'gray',
            self::PRIORITE_NORMALE => 'blue',
            self::PRIORITE_ELEVEE => 'orange',
            self::PRIORITE_CRITIQUE => 'red',
            default => 'gray',
        };
    }

    /**
     * Obtenir l'icône de la priorité
     */
    public function getPrioriteIconeAttribute(): string
    {
        return match($this->priorite) {
            self::PRIORITE_FAIBLE => 'minus',
            self::PRIORITE_NORMALE => 'circle',
            self::PRIORITE_ELEVEE => 'alert-triangle',
            self::PRIORITE_CRITIQUE => 'alert-octagon',
            default => 'circle',
        };
    }

    /**
     * Obtenir le pourcentage de progression formaté
     */
    public function getProgressionFormateeAttribute(): string
    {
        return "{$this->progression}%";
    }

    /**
     * Vérifier si l'action est terminée
     */
    public function getEstTermineeAttribute(): bool
    {
        return $this->statut === self::STATUT_TERMINE || $this->progression >= 100;
    }
}
