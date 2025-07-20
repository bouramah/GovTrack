<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class ReunionOrdreJour extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'reunion_ordre_jours';

    /**
     * Constantes pour les types
     */
    public const TYPE_PRESENTATION = 'PRESENTATION';
    public const TYPE_DISCUSSION = 'DISCUSSION';
    public const TYPE_DECISION = 'DECISION';
    public const TYPE_INFORMATION = 'INFORMATION';
    public const TYPE_PAUSE = 'PAUSE';

    public const TYPES = [
        self::TYPE_PRESENTATION => 'Présentation',
        self::TYPE_DISCUSSION => 'Discussion',
        self::TYPE_DECISION => 'Décision',
        self::TYPE_INFORMATION => 'Information',
        self::TYPE_PAUSE => 'Pause',
    ];

    /**
     * Constantes pour les statuts
     */
    public const STATUT_PLANIFIE = 'PLANIFIE';
    public const STATUT_EN_COURS = 'EN_COURS';
    public const STATUT_TERMINE = 'TERMINE';
    public const STATUT_REPORTE = 'REPORTE';

    public const STATUTS = [
        self::STATUT_PLANIFIE => 'Planifié',
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_TERMINE => 'Terminé',
        self::STATUT_REPORTE => 'Reporté',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'reunion_id',
        'ordre',
        'titre',
        'description',
        'type',
        'duree_estimee_minutes',
        'responsable_id',
        'statut',
        'niveau_detail',
        'commentaires',
        'date_creation',
        'date_modification',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'ordre' => 'integer',
        'duree_estimee_minutes' => 'integer',
        'niveau_detail' => 'integer',
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
     * Scope par type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope par statut
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope par ordre
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre');
    }

    /**
     * Obtenir le libellé du type
     */
    public function getTypeLibelleAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    /**
     * Obtenir la durée formatée
     */
    public function getDureeFormateeAttribute(): string
    {
        if (!$this->duree_estimee_minutes) {
            return 'Non définie';
        }

        $heures = intval($this->duree_estimee_minutes / 60);
        $minutes = $this->duree_estimee_minutes % 60;

        if ($heures > 0) {
            return "{$heures}h" . ($minutes > 0 ? " {$minutes}min" : "");
        }

        return "{$minutes}min";
    }

    /**
     * Obtenir la couleur du type
     */
    public function getTypeCouleurAttribute(): string
    {
        return match($this->type) {
            self::TYPE_PRESENTATION => 'blue',
            self::TYPE_DISCUSSION => 'yellow',
            self::TYPE_DECISION => 'green',
            self::TYPE_INFORMATION => 'gray',
            self::TYPE_PAUSE => 'orange',
            default => 'gray',
        };
    }

    /**
     * Obtenir l'icône du type
     */
    public function getTypeIconeAttribute(): string
    {
        return match($this->type) {
            self::TYPE_PRESENTATION => 'presentation',
            self::TYPE_DISCUSSION => 'message-circle',
            self::TYPE_DECISION => 'check-square',
            self::TYPE_INFORMATION => 'info',
            self::TYPE_PAUSE => 'coffee',
            default => 'circle',
        };
    }
}
