<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class ReunionNotification extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'reunion_notifications';

    /**
     * Constantes pour les types de notifications
     */
    public const TYPE_CONFIRMATION_PRESENCE = 'CONFIRMATION_PRESENCE';
    public const TYPE_RAPPEL_24H = 'RAPPEL_24H';
    public const TYPE_RAPPEL_1H = 'RAPPEL_1H';
    public const TYPE_RAPPEL_15MIN = 'RAPPEL_15MIN';
    public const TYPE_PV_DISPONIBLE = 'PV_DISPONIBLE';
    public const TYPE_RAPPEL_ACTIONS = 'RAPPEL_ACTIONS';

    public const TYPES = [
        self::TYPE_CONFIRMATION_PRESENCE => 'Confirmation de présence',
        self::TYPE_RAPPEL_24H => 'Rappel 24h avant',
        self::TYPE_RAPPEL_1H => 'Rappel 1h avant',
        self::TYPE_RAPPEL_15MIN => 'Rappel 15min avant',
        self::TYPE_PV_DISPONIBLE => 'PV disponible',
        self::TYPE_RAPPEL_ACTIONS => 'Rappel actions',
    ];

    /**
     * Constantes pour les statuts
     */
    public const STATUT_ENVOYE = 'ENVOYE';
    public const STATUT_LU = 'LU';
    public const STATUT_ERREUR = 'ERREUR';

    public const STATUTS = [
        self::STATUT_ENVOYE => 'Envoyé',
        self::STATUT_LU => 'Lu',
        self::STATUT_ERREUR => 'Erreur',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'reunion_id',
        'type',
        'envoye_a',
        'envoye_le',
        'statut',
        'contenu_email',
        'configuration_type',
        'date_creation',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'envoye_le' => 'datetime',
        'configuration_type' => 'array',
        'date_creation' => 'datetime',
    ];

    /**
     * Relations avec la réunion
     */
    public function reunion(): BelongsTo
    {
        return $this->belongsTo(Reunion::class, 'reunion_id');
    }

    /**
     * Relations avec le destinataire
     */
    public function destinataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'envoye_a');
    }

    /**
     * Scope par type de notification
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
     * Scope pour les notifications envoyées
     */
    public function scopeEnvoyees($query)
    {
        return $query->where('statut', self::STATUT_ENVOYE);
    }

    /**
     * Scope pour les notifications lues
     */
    public function scopeLues($query)
    {
        return $query->where('statut', self::STATUT_LU);
    }

    /**
     * Scope pour les notifications en erreur
     */
    public function scopeErreurs($query)
    {
        return $query->where('statut', self::STATUT_ERREUR);
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
     * Vérifier si la notification a été envoyée
     */
    public function getEstEnvoyeeAttribute(): bool
    {
        return $this->statut === self::STATUT_ENVOYE;
    }

    /**
     * Vérifier si la notification a été lue
     */
    public function getEstLueAttribute(): bool
    {
        return $this->statut === self::STATUT_LU;
    }

    /**
     * Vérifier si la notification a échoué
     */
    public function getEstErreurAttribute(): bool
    {
        return $this->statut === self::STATUT_ERREUR;
    }

    /**
     * Obtenir la couleur du statut
     */
    public function getStatutCouleurAttribute(): string
    {
        return match($this->statut) {
            self::STATUT_ENVOYE => 'blue',
            self::STATUT_LU => 'green',
            self::STATUT_ERREUR => 'red',
            default => 'gray',
        };
    }

    /**
     * Obtenir l'icône du statut
     */
    public function getStatutIconeAttribute(): string
    {
        return match($this->statut) {
            self::STATUT_ENVOYE => 'mail',
            self::STATUT_LU => 'check-circle',
            self::STATUT_ERREUR => 'x-circle',
            default => 'help-circle',
        };
    }

    /**
     * Obtenir le temps de lecture
     */
    public function getTempsLectureAttribute(): string
    {
        if (!$this->date_envoi || !$this->date_lecture) {
            return 'N/A';
        }

        $minutes = $this->date_envoi->diffInMinutes($this->date_lecture);

        if ($minutes < 60) {
            return "{$minutes} min";
        }

        $heures = intval($minutes / 60);
        $minutesRestantes = $minutes % 60;

        return "{$heures}h" . ($minutesRestantes > 0 ? " {$minutesRestantes}min" : "");
    }
}
