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
    public const TYPE_RAPPEL = 'RAPPEL';
    public const TYPE_PV_DISPONIBLE = 'PV_DISPONIBLE';
    public const TYPE_RAPPEL_ACTIONS = 'RAPPEL_ACTIONS';

    public const TYPES = [
        self::TYPE_CONFIRMATION_PRESENCE => 'Confirmation de présence',
        self::TYPE_RAPPEL => 'Rappel',
        self::TYPE_PV_DISPONIBLE => 'PV disponible',
        self::TYPE_RAPPEL_ACTIONS => 'Rappel actions',
    ];

    /**
     * Constantes pour les statuts
     */
    public const STATUT_ENVOYEE = 'ENVOYEE';
    public const STATUT_ECHEC = 'ECHEC';
    public const STATUT_LUE = 'LUE';

    public const STATUTS = [
        self::STATUT_ENVOYEE => 'Envoyée',
        self::STATUT_ECHEC => 'Échec',
        self::STATUT_LUE => 'Lue',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'reunion_id',
        'type_notification',
        'destinataire_id',
        'statut',
        'date_envoi',
        'date_lecture',
        'contenu',
        'date_creation',
        'date_modification',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'date_envoi' => 'datetime',
        'date_lecture' => 'datetime',
        'contenu' => 'array',
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
     * Relations avec le destinataire
     */
    public function destinataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destinataire_id');
    }

    /**
     * Scope par type de notification
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type_notification', $type);
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
        return $query->where('statut', self::STATUT_ENVOYEE);
    }

    /**
     * Scope pour les notifications lues
     */
    public function scopeLues($query)
    {
        return $query->where('statut', self::STATUT_LUE);
    }

    /**
     * Scope pour les notifications en échec
     */
    public function scopeEchecs($query)
    {
        return $query->where('statut', self::STATUT_ECHEC);
    }

    /**
     * Obtenir le libellé du type
     */
    public function getTypeLibelleAttribute(): string
    {
        return self::TYPES[$this->type_notification] ?? $this->type_notification;
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
        return $this->statut === self::STATUT_ENVOYEE;
    }

    /**
     * Vérifier si la notification a été lue
     */
    public function getEstLueAttribute(): bool
    {
        return $this->statut === self::STATUT_LUE;
    }

    /**
     * Vérifier si la notification a échoué
     */
    public function getEstEchecAttribute(): bool
    {
        return $this->statut === self::STATUT_ECHEC;
    }

    /**
     * Obtenir la couleur du statut
     */
    public function getStatutCouleurAttribute(): string
    {
        return match($this->statut) {
            self::STATUT_ENVOYEE => 'blue',
            self::STATUT_LUE => 'green',
            self::STATUT_ECHEC => 'red',
            default => 'gray',
        };
    }

    /**
     * Obtenir l'icône du statut
     */
    public function getStatutIconeAttribute(): string
    {
        return match($this->statut) {
            self::STATUT_ENVOYEE => 'mail',
            self::STATUT_LUE => 'check-circle',
            self::STATUT_ECHEC => 'x-circle',
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
