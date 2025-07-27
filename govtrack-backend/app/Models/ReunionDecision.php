<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class ReunionDecision extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'reunion_decisions';

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
     * Constantes pour les types
     */
    public const TYPE_PROVISOIRE = 'PROVISOIRE';
    public const TYPE_DEFINITIVE = 'DEFINITIVE';

    public const TYPES = [
        self::TYPE_PROVISOIRE => 'Provisoire',
        self::TYPE_DEFINITIVE => 'Définitive',
    ];

    /**
     * Constantes pour les statuts
     */
    public const STATUT_EN_ATTENTE = 'EN_ATTENTE';
    public const STATUT_EN_COURS = 'EN_COURS';
    public const STATUT_TERMINEE = 'TERMINEE';

    public const STATUTS = [
        self::STATUT_EN_ATTENTE => 'En attente',
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_TERMINEE => 'Terminée',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'reunion_id',
        'reunion_sujet_id',
        'texte_decision',
        'type',
        'responsables_ids',
        'date_limite',
        'statut',
        'priorite',
        'commentaire',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'responsables_ids' => 'array',
        'date_limite' => 'date',
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
     * Relations avec le sujet de réunion
     */
    public function sujet(): BelongsTo
    {
        return $this->belongsTo(ReunionSujet::class, 'reunion_sujet_id');
    }

    /**
     * Relations avec l'utilisateur créateur
     */
    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creer_par');
    }

    /**
     * Relations avec l'utilisateur modificateur
     */
    public function modificateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modifier_par');
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
     * Scope pour les décisions en retard
     */
    public function scopeEnRetard($query)
    {
        return $query->where('date_limite', '<', now()->toDateString())
                     ->whereNotIn('statut', [self::STATUT_TERMINEE]);
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
     * Vérifier si la décision est en retard
     */
    public function getEstEnRetardAttribute(): bool
    {
        return $this->date_limite && $this->date_limite < now()->toDateString()
               && !in_array($this->statut, [self::STATUT_TERMINEE]);
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
}
