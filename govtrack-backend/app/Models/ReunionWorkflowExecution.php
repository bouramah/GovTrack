<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class ReunionWorkflowExecution extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'reunion_workflow_executions';

    /**
     * Constantes pour les statuts
     */
    public const STATUT_EN_COURS = 'EN_COURS';
    public const STATUT_TERMINE = 'TERMINE';
    public const STATUT_BLOQUE = 'BLOQUE';
    public const STATUT_ANNULE = 'ANNULE';

    public const STATUTS = [
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_TERMINE => 'Terminé',
        self::STATUT_BLOQUE => 'Bloqué',
        self::STATUT_ANNULE => 'Annulé',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'reunion_id',
        'workflow_config_id',
        'etape_actuelle',
        'statut',
        'date_debut',
        'date_fin',
        'historique_execution',
        'commentaires',
        'date_creation',
        'date_modification',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'etape_actuelle' => 'integer',
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'historique_execution' => 'array',
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
     * Relations avec la configuration du workflow
     */
    public function workflowConfig(): BelongsTo
    {
        return $this->belongsTo(ReunionWorkflowConfig::class, 'workflow_config_id');
    }

    /**
     * Scope par statut
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope pour les workflows en cours
     */
    public function scopeEnCours($query)
    {
        return $query->where('statut', self::STATUT_EN_COURS);
    }

    /**
     * Scope pour les workflows terminés
     */
    public function scopeTermines($query)
    {
        return $query->where('statut', self::STATUT_TERMINE);
    }

    /**
     * Scope pour les workflows bloqués
     */
    public function scopeBloques($query)
    {
        return $query->where('statut', self::STATUT_BLOQUE);
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    /**
     * Vérifier si le workflow est en cours
     */
    public function getEstEnCoursAttribute(): bool
    {
        return $this->statut === self::STATUT_EN_COURS;
    }

    /**
     * Vérifier si le workflow est terminé
     */
    public function getEstTermineAttribute(): bool
    {
        return $this->statut === self::STATUT_TERMINE;
    }

    /**
     * Vérifier si le workflow est bloqué
     */
    public function getEstBloqueAttribute(): bool
    {
        return $this->statut === self::STATUT_BLOQUE;
    }

    /**
     * Vérifier si le workflow est annulé
     */
    public function getEstAnnuleAttribute(): bool
    {
        return $this->statut === self::STATUT_ANNULE;
    }

    /**
     * Obtenir la couleur du statut
     */
    public function getStatutCouleurAttribute(): string
    {
        return match($this->statut) {
            self::STATUT_EN_COURS => 'blue',
            self::STATUT_TERMINE => 'green',
            self::STATUT_BLOQUE => 'red',
            self::STATUT_ANNULE => 'gray',
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
            self::STATUT_TERMINE => 'check-circle',
            self::STATUT_BLOQUE => 'pause',
            self::STATUT_ANNULE => 'x-circle',
            default => 'help-circle',
        };
    }

    /**
     * Obtenir la durée d'exécution
     */
    public function getDureeExecutionAttribute(): string
    {
        if (!$this->date_debut) {
            return 'N/A';
        }

        $dateFin = $this->date_fin ?? now();
        $minutes = $this->date_debut->diffInMinutes($dateFin);

        if ($minutes < 60) {
            return "{$minutes} min";
        }

        $heures = intval($minutes / 60);
        $minutesRestantes = $minutes % 60;

        return "{$heures}h" . ($minutesRestantes > 0 ? " {$minutesRestantes}min" : "");
    }

    /**
     * Obtenir le pourcentage de progression
     */
    public function getProgressionAttribute(): int
    {
        if (!$this->workflowConfig) {
            return 0;
        }

        $nombreEtapes = $this->workflowConfig->nombre_etapes;
        if ($nombreEtapes === 0) {
            return 0;
        }

        return min(100, intval(($this->etape_actuelle / $nombreEtapes) * 100));
    }

    /**
     * Obtenir l'étape actuelle formatée
     */
    public function getEtapeActuelleFormateeAttribute(): string
    {
        if (!$this->workflowConfig) {
            return "Étape {$this->etape_actuelle}";
        }

        $etapes = $this->workflowConfig->etapes_formatees;
        if (isset($etapes[$this->etape_actuelle - 1])) {
            return $etapes[$this->etape_actuelle - 1]['nom'];
        }

        return "Étape {$this->etape_actuelle}";
    }
}
