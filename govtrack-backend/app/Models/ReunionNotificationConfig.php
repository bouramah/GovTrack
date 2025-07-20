<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class ReunionNotificationConfig extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'reunion_notification_configs';

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
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'type_reunion_id',
        'type_notification',
        'actif',
        'delai_jours',
        'template_email',
        'destinataires_par_defaut',
        'configuration_avancee',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'actif' => 'boolean',
        'delai_jours' => 'integer',
        'destinataires_par_defaut' => 'array',
        'configuration_avancee' => 'array',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    /**
     * Relations avec le type de réunion
     */
    public function typeReunion(): BelongsTo
    {
        return $this->belongsTo(TypeReunion::class, 'type_reunion_id');
    }

    /**
     * Relations avec le créateur
     */
    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creer_par');
    }

    /**
     * Relations avec le modificateur
     */
    public function modificateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modifier_par');
    }

    /**
     * Scope pour les configurations actives
     */
    public function scopeActives($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope par type de notification
     */
    public function scopeByTypeNotification($query, $type)
    {
        return $query->where('type_notification', $type);
    }

    /**
     * Scope par type de réunion
     */
    public function scopeByTypeReunion($query, $typeReunionId)
    {
        return $query->where('type_reunion_id', $typeReunionId);
    }

    /**
     * Obtenir le libellé du type de notification
     */
    public function getTypeNotificationLibelleAttribute(): string
    {
        return self::TYPES[$this->type_notification] ?? $this->type_notification;
    }

    /**
     * Vérifier si la configuration est pour les rappels
     */
    public function getEstRappelAttribute(): bool
    {
        return $this->type_notification === self::TYPE_RAPPEL;
    }

    /**
     * Vérifier si la configuration est pour les confirmations
     */
    public function getEstConfirmationAttribute(): bool
    {
        return $this->type_notification === self::TYPE_CONFIRMATION_PRESENCE;
    }

    /**
     * Vérifier si la configuration est pour les PV
     */
    public function getEstPVAttribute(): bool
    {
        return $this->type_notification === self::TYPE_PV_DISPONIBLE;
    }

    /**
     * Vérifier si la configuration est pour les rappels d'actions
     */
    public function getEstRappelActionsAttribute(): bool
    {
        return $this->type_notification === self::TYPE_RAPPEL_ACTIONS;
    }

    /**
     * Obtenir le délai formaté
     */
    public function getDelaiFormateAttribute(): string
    {
        if (!$this->delai_jours) {
            return 'Immédiat';
        }

        if ($this->delai_jours === 1) {
            return '1 jour avant';
        }

        return "{$this->delai_jours} jours avant";
    }

    /**
     * Obtenir les destinataires par défaut formatés
     */
    public function getDestinatairesFormatesAttribute(): array
    {
        $destinataires = [];

        foreach ($this->destinataires_par_defaut as $type => $actif) {
            if ($actif) {
                $destinataires[] = ucfirst($type);
            }
        }

        return $destinataires;
    }

    /**
     * Vérifier si un type de destinataire est actif
     */
    public function hasDestinataire(string $type): bool
    {
        return $this->destinataires_par_defaut[$type] ?? false;
    }

    /**
     * Obtenir la configuration avancée
     */
    public function getConfigurationAvancee(string $cle, $defaut = null)
    {
        return $this->configuration_avancee[$cle] ?? $defaut;
    }
}
