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
     * Constantes pour les niveaux de difficulté
     */
    public const NIVEAU_FAIBLE = 'FAIBLE';
    public const NIVEAU_MOYEN = 'MOYEN';
    public const NIVEAU_ELEVE = 'ELEVE';
    public const NIVEAU_CRITIQUE = 'CRITIQUE';

    public const NIVEAUX = [
        self::NIVEAU_FAIBLE => 'Faible',
        self::NIVEAU_MOYEN => 'Moyen',
        self::NIVEAU_ELEVE => 'Élevé',
        self::NIVEAU_CRITIQUE => 'Critique',
    ];

    /**
     * Constantes pour les statuts
     */
    public const STATUT_IDENTIFIEE = 'IDENTIFIEE';
    public const STATUT_EN_COURS_RESOLUTION = 'EN_COURS_RESOLUTION';
    public const STATUT_RESOLUE = 'RESOLUE';

    public const STATUTS = [
        self::STATUT_IDENTIFIEE => 'Identifiée',
        self::STATUT_EN_COURS_RESOLUTION => 'En cours de résolution',
        self::STATUT_RESOLUE => 'Résolue',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'objectif_id',
        'entite_id',
        'description_difficulte',
        'niveau_difficulte',
        'impact',
        'solution_proposee',
        'statut',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
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
     * Scope par niveau de difficulté
     */
    public function scopeByNiveauDifficulte($query, $niveau)
    {
        return $query->where('niveau_difficulte', $niveau);
    }

    /**
     * Scope par statut
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope pour les difficultés non résolues
     */
    public function scopeNonResolues($query)
    {
        return $query->whereNotIn('statut', [self::STATUT_RESOLUE]);
    }

    /**
     * Obtenir le libellé du niveau de difficulté
     */
    public function getNiveauDifficulteLibelleAttribute(): string
    {
        return self::NIVEAUX[$this->niveau_difficulte] ?? $this->niveau_difficulte;
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    /**
     * Obtenir la couleur du niveau de difficulté
     */
    public function getNiveauDifficulteCouleurAttribute(): string
    {
        return match($this->niveau_difficulte) {
            self::NIVEAU_FAIBLE => 'green',
            self::NIVEAU_MOYEN => 'yellow',
            self::NIVEAU_ELEVE => 'orange',
            self::NIVEAU_CRITIQUE => 'red',
            default => 'gray',
        };
    }

    /**
     * Obtenir l'icône du niveau de difficulté
     */
    public function getNiveauDifficulteIconeAttribute(): string
    {
        return match($this->niveau_difficulte) {
            self::NIVEAU_FAIBLE => 'minus',
            self::NIVEAU_MOYEN => 'alert-circle',
            self::NIVEAU_ELEVE => 'alert-triangle',
            self::NIVEAU_CRITIQUE => 'alert-octagon',
            default => 'help-circle',
        };
    }

    /**
     * Obtenir la couleur du statut
     */
    public function getStatutCouleurAttribute(): string
    {
        return match($this->statut) {
            self::STATUT_IDENTIFIEE => 'gray',
            self::STATUT_EN_COURS_RESOLUTION => 'blue',
            self::STATUT_RESOLUE => 'green',
            default => 'gray',
        };
    }

    /**
     * Obtenir l'icône du statut
     */
    public function getStatutIconeAttribute(): string
    {
        return match($this->statut) {
            self::STATUT_IDENTIFIEE => 'clock',
            self::STATUT_EN_COURS_RESOLUTION => 'play',
            self::STATUT_RESOLUE => 'check-circle',
            default => 'help-circle',
        };
    }

    /**
     * Vérifier si la difficulté est résolue
     */
    public function getEstResolueAttribute(): bool
    {
        return $this->statut === self::STATUT_RESOLUE;
    }

    /**
     * Vérifier si la difficulté est critique
     */
    public function getEstCritiqueAttribute(): bool
    {
        return $this->niveau_difficulte === self::NIVEAU_CRITIQUE;
    }
}
