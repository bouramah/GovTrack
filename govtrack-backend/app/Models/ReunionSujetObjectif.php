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
    public const STATUT_EN_COURS = 'EN_COURS';
    public const STATUT_ATTEINT = 'ATTEINT';
    public const STATUT_EN_RETARD = 'EN_RETARD';

    public const STATUTS = [
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_ATTEINT => 'Atteint',
        self::STATUT_EN_RETARD => 'En retard',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'reunion_sujet_id',
        'titre',
        'description',
        'cible',
        'taux_realisation',
        'pourcentage_decaissement',
        'date_objectif',
        'statut',
        'ordre',
        'actif',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'taux_realisation' => 'integer',
        'pourcentage_decaissement' => 'decimal:2',
        'date_objectif' => 'date',
        'ordre' => 'integer',
        'actif' => 'boolean',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    /**
     * Relations avec le sujet
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
     * Scope pour les objectifs en retard
     */
    public function scopeEnRetard($query)
    {
        return $query->where('date_objectif', '<', now()->toDateString())
                     ->whereNotIn('statut', [self::STATUT_ATTEINT]);
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
        return $this->date_objectif && $this->date_objectif < now()->toDateString()
               && !in_array($this->statut, [self::STATUT_ATTEINT]);
    }

    /**
     * Obtenir le taux de réalisation formaté
     */
    public function getTauxRealisationFormateAttribute(): string
    {
        return "{$this->taux_realisation}%";
    }

    /**
     * Vérifier si l'objectif est atteint
     */
    public function getEstAtteintAttribute(): bool
    {
        return $this->statut === self::STATUT_ATTEINT || $this->taux_realisation >= 100;
    }

    /**
     * Obtenir la couleur du statut
     */
    public function getStatutCouleurAttribute(): string
    {
        return match($this->statut) {
            self::STATUT_EN_COURS => 'blue',
            self::STATUT_ATTEINT => 'green',
            self::STATUT_EN_RETARD => 'red',
            default => 'gray',
        };
    }

    /**
     * Obtenir l'icône du statut
     */
    public function getStatutIconeAttribute(): string
    {
        return match($this->statut) {
            self::STATUT_EN_COURS => 'play',
            self::STATUT_ATTEINT => 'check-circle',
            self::STATUT_EN_RETARD => 'x-circle',
            default => 'help-circle',
        };
    }
}
