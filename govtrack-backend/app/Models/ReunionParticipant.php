<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class ReunionParticipant extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'reunion_participants';

    /**
     * Constantes pour les rôles
     */
    public const ROLE_PRESIDENT = 'PRESIDENT';
    public const ROLE_SECRETAIRE = 'SECRETAIRE';
    public const ROLE_PARTICIPANT = 'PARTICIPANT';
    public const ROLE_OBSERVATEUR = 'OBSERVATEUR';
        public const ROLE_VALIDATEUR_PV = 'VALIDATEUR_PV';

    public const ROLES = [
        self::ROLE_PRESIDENT => 'Président',
        self::ROLE_SECRETAIRE => 'Secrétaire',
        self::ROLE_PARTICIPANT => 'Participant',
        self::ROLE_OBSERVATEUR => 'Observateur',
        self::ROLE_VALIDATEUR_PV => 'Validateur PV',
    ];

    /**
     * Constantes pour les types
     */
    public const TYPE_PERMANENT = 'PERMANENT';
    public const TYPE_INVITE = 'INVITE';

    public const TYPES = [
        self::TYPE_PERMANENT => 'Permanent',
        self::TYPE_INVITE => 'Invité',
    ];

    /**
     * Constantes pour les statuts de présence
     */
    public const STATUT_PRESENCE_CONFIRME = 'CONFIRME';
    public const STATUT_PRESENCE_ABSENT = 'ABSENT';
    public const STATUT_PRESENCE_EN_ATTENTE = 'EN_ATTENTE';

    public const STATUTS_PRESENCE = [
        self::STATUT_PRESENCE_CONFIRME => 'Confirmé',
        self::STATUT_PRESENCE_ABSENT => 'Absent',
        self::STATUT_PRESENCE_EN_ATTENTE => 'En attente',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'reunion_id',
        'user_id',
        'role',
        'type',
        'statut_presence',
        'present_le',
        'absent_le',
        'commentaire_absence',
        'notifie_absence',
        'notifications_actives',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'present_le' => 'datetime',
        'absent_le' => 'datetime',
        'notifie_absence' => 'boolean',
        'notifications_actives' => 'array',
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
     * Relations avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
     * Scope pour les participants confirmés
     */
    public function scopeConfirmes($query)
    {
        return $query->where('statut_presence', self::STATUT_PRESENCE_CONFIRME);
    }

    /**
     * Scope pour les participants absents
     */
    public function scopeAbsents($query)
    {
        return $query->where('statut_presence', self::STATUT_PRESENCE_ABSENT);
    }

    /**
     * Scope pour les participants en attente
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut_presence', self::STATUT_PRESENCE_EN_ATTENTE);
    }

    /**
     * Scope par rôle
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope par type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Obtenir le libellé du rôle
     */
    public function getRoleLibelleAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    /**
     * Obtenir le libellé du type
     */
    public function getTypeLibelleAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Obtenir le libellé du statut de présence
     */
    public function getStatutPresenceLibelleAttribute(): string
    {
        return self::STATUTS_PRESENCE[$this->statut_presence] ?? $this->statut_presence;
    }

    /**
     * Vérifier si le participant est absent
     */
    public function getEstAbsentAttribute(): bool
    {
        return $this->statut_presence === self::STATUT_PRESENCE_ABSENT;
    }

    /**
     * Vérifier si le participant a confirmé
     */
    public function getEstConfirmeAttribute(): bool
    {
        return $this->statut_presence === self::STATUT_PRESENCE_CONFIRME;
    }

    /**
     * Vérifier si le participant est en attente
     */
    public function getEstEnAttenteAttribute(): bool
    {
        return $this->statut_presence === self::STATUT_PRESENCE_EN_ATTENTE;
    }

    /**
     * Vérifier si le participant est président
     */
    public function getEstPresidentAttribute(): bool
    {
        return $this->role === self::ROLE_PRESIDENT;
    }

    /**
     * Vérifier si le participant est secrétaire
     */
    public function getEstSecretaireAttribute(): bool
    {
        return $this->role === self::ROLE_SECRETAIRE;
    }

    /**
     * Vérifier si le participant est permanent
     */
    public function getEstPermanentAttribute(): bool
    {
        return $this->type === self::TYPE_PERMANENT;
    }

    /**
     * Vérifier si le participant est invité
     */
    public function getEstInviteAttribute(): bool
    {
        return $this->type === self::TYPE_INVITE;
    }

    /**
     * Obtenir la couleur du statut de présence
     */
    public function getStatutPresenceCouleurAttribute(): string
    {
        return match($this->statut_presence) {
            self::STATUT_PRESENCE_CONFIRME => 'blue',
            self::STATUT_PRESENCE_ABSENT => 'red',
            self::STATUT_PRESENCE_EN_ATTENTE => 'gray',
            default => 'gray',
        };
    }

    /**
     * Obtenir l'icône du statut de présence
     */
    public function getStatutPresenceIconeAttribute(): string
    {
        return match($this->statut_presence) {
            self::STATUT_PRESENCE_CONFIRME => 'check-circle',
            self::STATUT_PRESENCE_ABSENT => 'user-x',
            self::STATUT_PRESENCE_EN_ATTENTE => 'clock',
            default => 'help-circle',
        };
    }

    /**
     * Vérifier si une notification est active
     */
    public function hasNotification(string $notification): bool
    {
        return $this->notifications_actives[$notification] ?? false;
    }
}
