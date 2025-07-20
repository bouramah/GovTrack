<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class ReunionSujet extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'reunion_sujets';

    /**
     * Constantes pour les niveaux de difficulté
     */
    public const DIFFICULTE_FACILE = 'FACILE';
    public const DIFFICULTE_MOYENNE = 'MOYENNE';
    public const DIFFICULTE_DIFFICILE = 'DIFFICILE';

    public const DIFFICULTES = [
        self::DIFFICULTE_FACILE => 'Facile',
        self::DIFFICULTE_MOYENNE => 'Moyenne',
        self::DIFFICULTE_DIFFICILE => 'Difficile',
    ];

    /**
     * Constantes pour les statuts
     */
    public const STATUT_A_DISCUTER = 'A_DISCUTER';
    public const STATUT_EN_DISCUSSION = 'EN_DISCUSSION';
    public const STATUT_RESOLU = 'RESOLU';
    public const STATUT_REPORTE = 'REPORTE';

    public const STATUTS = [
        self::STATUT_A_DISCUTER => 'À discuter',
        self::STATUT_EN_DISCUSSION => 'En discussion',
        self::STATUT_RESOLU => 'Résolu',
        self::STATUT_REPORTE => 'Reporté',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'reunion_id',
        'titre',
        'description',
        'niveau_difficulte',
        'recommandations',
        'statut',
        'pieces_jointes',
        'a_objectifs',
        'a_difficultes',
        'commentaires',
        'date_creation',
        'date_modification',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'pieces_jointes' => 'array',
        'a_objectifs' => 'boolean',
        'a_difficultes' => 'boolean',
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
     * Relations avec les objectifs
     */
    public function objectifs(): HasMany
    {
        return $this->hasMany(ReunionSujetObjectif::class, 'sujet_id');
    }

    /**
     * Scope par niveau de difficulté
     */
    public function scopeByDifficulte($query, $difficulte)
    {
        return $query->where('niveau_difficulte', $difficulte);
    }

    /**
     * Scope par statut
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope pour les sujets avec objectifs
     */
    public function scopeAvecObjectifs($query)
    {
        return $query->where('a_objectifs', true);
    }

    /**
     * Scope pour les sujets avec difficultés
     */
    public function scopeAvecDifficultes($query)
    {
        return $query->where('a_difficultes', true);
    }

    /**
     * Obtenir le libellé de la difficulté
     */
    public function getDifficulteLibelleAttribute(): string
    {
        return self::DIFFICULTES[$this->niveau_difficulte] ?? $this->niveau_difficulte;
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    /**
     * Obtenir la couleur de la difficulté
     */
    public function getDifficulteCouleurAttribute(): string
    {
        return match($this->niveau_difficulte) {
            self::DIFFICULTE_FACILE => 'green',
            self::DIFFICULTE_MOYENNE => 'yellow',
            self::DIFFICULTE_DIFFICILE => 'red',
            default => 'gray',
        };
    }

    /**
     * Obtenir l'icône de la difficulté
     */
    public function getDifficulteIconeAttribute(): string
    {
        return match($this->niveau_difficulte) {
            self::DIFFICULTE_FACILE => 'check-circle',
            self::DIFFICULTE_MOYENNE => 'alert-circle',
            self::DIFFICULTE_DIFFICILE => 'x-circle',
            default => 'help-circle',
        };
    }

    /**
     * Obtenir la couleur du statut
     */
    public function getStatutCouleurAttribute(): string
    {
        return match($this->statut) {
            self::STATUT_A_DISCUTER => 'gray',
            self::STATUT_EN_DISCUSSION => 'blue',
            self::STATUT_RESOLU => 'green',
            self::STATUT_REPORTE => 'orange',
            default => 'gray',
        };
    }

    /**
     * Obtenir l'icône du statut
     */
    public function getStatutIconeAttribute(): string
    {
        return match($this->statut) {
            self::STATUT_A_DISCUTER => 'clock',
            self::STATUT_EN_DISCUSSION => 'message-circle',
            self::STATUT_RESOLU => 'check-circle',
            self::STATUT_REPORTE => 'calendar',
            default => 'help-circle',
        };
    }
}
