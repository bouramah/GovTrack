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
    public const STATUT_RESOLU = 'RESOLU';
    public const STATUT_EN_COURS_DE_RESOLUTION = 'EN_COURS_DE_RESOLUTION';
    public const STATUT_BLOQUE = 'BLOQUE';
    public const STATUT_AVIS = 'AVIS';
    public const STATUT_APPROUVE = 'APPROVE';
    public const STATUT_REJETE = 'REJETE';
    public const STATUT_EN_ATTENTE = 'EN_ATTENTE';

    public const STATUTS = [
        self::STATUT_RESOLU => 'Résolu',
        self::STATUT_EN_COURS_DE_RESOLUTION => 'En cours de résolution',
        self::STATUT_BLOQUE => 'Bloqué',
        self::STATUT_AVIS => 'Avis',
        self::STATUT_APPROUVE => 'Approuvé',
        self::STATUT_REJETE => 'Rejeté',
        self::STATUT_EN_ATTENTE => 'En attente',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'reunion_ordre_jour_id',
        'titre',
        'description',
        'difficulte_globale',
        'recommandation',
        'statut',
        'commentaire',
        // pieces_jointes gérées via relation avec PieceJointeSujet
        'projet_id',
        'entite_id',
        'niveau_detail',
        'objectifs_actifs',
        'difficultes_actives',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'pieces_jointes' => 'array',
        'objectifs_actifs' => 'boolean',
        'difficultes_actives' => 'boolean',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    /**
     * Relations avec l'ordre du jour de la réunion
     */
    public function ordreJour(): BelongsTo
    {
        return $this->belongsTo(ReunionOrdreJour::class, 'reunion_ordre_jour_id');
    }

    /**
     * Relations avec le projet
     */
    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'projet_id');
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
     * Relations avec les objectifs
     */
    public function objectifs(): HasMany
    {
        return $this->hasMany(ReunionSujetObjectif::class, 'sujet_id');
    }

    /**
     * Relations avec les pièces jointes
     */
    public function piecesJointes(): HasMany
    {
        return $this->hasMany(PieceJointeSujet::class, 'reunion_sujet_id');
    }

    /**
     * Relations avec les avis
     */
    public function avis(): HasMany
    {
        return $this->hasMany(ReunionSujetAvis::class, 'reunion_sujet_id');
    }

    /**
     * Scope par niveau de détail
     */
    public function scopeByNiveauDetail($query, $niveauDetail)
    {
        return $query->where('niveau_detail', $niveauDetail);
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
        return $query->where('objectifs_actifs', true);
    }

    /**
     * Scope pour les sujets avec difficultés
     */
    public function scopeAvecDifficultes($query)
    {
        return $query->where('difficultes_actives', true);
    }

    /**
     * Obtenir le libellé du niveau de détail
     */
    public function getNiveauDetailLibelleAttribute(): string
    {
        return $this->niveau_detail === 'SIMPLE' ? 'Simple' : 'Détaillé';
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    /**
     * Obtenir la couleur du niveau de détail
     */
    public function getNiveauDetailCouleurAttribute(): string
    {
        return match($this->niveau_detail) {
            'SIMPLE' => 'green',
            'DETAILLE' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Obtenir l'icône du niveau de détail
     */
    public function getNiveauDetailIconeAttribute(): string
    {
        return match($this->niveau_detail) {
            'SIMPLE' => 'file-text',
            'DETAILLE' => 'file-text',
            default => 'file',
        };
    }

    /**
     * Obtenir la couleur du statut
     */
    public function getStatutCouleurAttribute(): string
    {
        return match($this->statut) {
            self::STATUT_RESOLU => 'green',
            self::STATUT_EN_COURS_DE_RESOLUTION => 'blue',
            self::STATUT_BLOQUE => 'red',
            self::STATUT_AVIS => 'yellow',
            self::STATUT_APPROUVE => 'green',
            self::STATUT_REJETE => 'red',
            self::STATUT_EN_ATTENTE => 'gray',
            default => 'gray',
        };
    }

    /**
     * Obtenir l'icône du statut
     */
    public function getStatutIconeAttribute(): string
    {
        return match($this->statut) {
            self::STATUT_RESOLU => 'check-circle',
            self::STATUT_EN_COURS_DE_RESOLUTION => 'clock',
            self::STATUT_BLOQUE => 'x-circle',
            self::STATUT_AVIS => 'message-circle',
            self::STATUT_APPROUVE => 'check-circle',
            self::STATUT_REJETE => 'x-circle',
            self::STATUT_EN_ATTENTE => 'clock',
            default => 'help-circle',
        };
    }
}
