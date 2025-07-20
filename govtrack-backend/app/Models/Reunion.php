<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class Reunion extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'reunions';

    /**
     * Constantes pour les niveaux de complexité
     */
    public const NIVEAU_SIMPLE = 'SIMPLE';
    public const NIVEAU_INTERMEDIAIRE = 'INTERMEDIAIRE';
    public const NIVEAU_COMPLEXE = 'COMPLEXE';

    public const NIVEAUX = [
        self::NIVEAU_SIMPLE => 'Simple',
        self::NIVEAU_INTERMEDIAIRE => 'Intermédiaire',
        self::NIVEAU_COMPLEXE => 'Complexe',
    ];

    /**
     * Constantes pour les types de lieu
     */
    public const TYPE_LIEU_PHYSIQUE = 'PHYSIQUE';
    public const TYPE_LIEU_VIRTUEL = 'VIRTUEL';
    public const TYPE_LIEU_HYBRIDE = 'HYBRIDE';

    public const TYPES_LIEU = [
        self::TYPE_LIEU_PHYSIQUE => 'Physique',
        self::TYPE_LIEU_VIRTUEL => 'Virtuel',
        self::TYPE_LIEU_HYBRIDE => 'Hybride',
    ];

    /**
     * Constantes pour les périodicités
     */
    public const PERIODICITE_PONCTUELLE = 'PONCTUELLE';
    public const PERIODICITE_HEBDOMADAIRE = 'HEBDOMADAIRE';
    public const PERIODICITE_BIHEBDOMADAIRE = 'BIHEBDOMADAIRE';
    public const PERIODICITE_MENSUELLE = 'MENSUELLE';

    public const PERIODICITES = [
        self::PERIODICITE_PONCTUELLE => 'Ponctuelle',
        self::PERIODICITE_HEBDOMADAIRE => 'Hebdomadaire',
        self::PERIODICITE_BIHEBDOMADAIRE => 'Bi-hebdomadaire',
        self::PERIODICITE_MENSUELLE => 'Mensuelle',
    ];

    /**
     * Constantes pour les types d'ordre du jour
     */
    public const ORDRE_JOUR_EXPLICITE = 'EXPLICITE';
    public const ORDRE_JOUR_IMPLICITE = 'IMPLICITE';
    public const ORDRE_JOUR_HYBRIDE = 'HYBRIDE';

    public const TYPES_ORDRE_JOUR = [
        self::ORDRE_JOUR_EXPLICITE => 'Explicite',
        self::ORDRE_JOUR_IMPLICITE => 'Implicite',
        self::ORDRE_JOUR_HYBRIDE => 'Hybride',
    ];

    /**
     * Constantes pour les statuts
     */
    public const STATUT_PLANIFIEE = 'PLANIFIEE';
    public const STATUT_EN_COURS = 'EN_COURS';
    public const STATUT_TERMINEE = 'TERMINEE';
    public const STATUT_ANNULEE = 'ANNULEE';

    public const STATUTS = [
        self::STATUT_PLANIFIEE => 'Planifiée',
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_TERMINEE => 'Terminée',
        self::STATUT_ANNULEE => 'Annulée',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'titre',
        'description',
        'type_reunion_id',
        'niveau_complexite_actuel',
        'date_debut',
        'date_fin',
        'lieu',
        'type_lieu',
        'lien_virtuel',
        'periodicite',
        'serie_id',
        'suspendue',
        'reprogrammee_le',
        'fonctionnalites_actives',
        'quorum_minimum',
        'ordre_du_jour_type',
        'statut',
        'pv_valide_par_id',
        'pv_valide_le',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'suspendue' => 'boolean',
        'reprogrammee_le' => 'datetime',
        'fonctionnalites_actives' => 'array',
        'quorum_minimum' => 'integer',
        'pv_valide_le' => 'datetime',
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
     * Relations avec la série
     */
    public function serie(): BelongsTo
    {
        return $this->belongsTo(ReunionSerie::class, 'serie_id');
    }

    /**
     * Relations avec les participants
     */
    public function participants(): HasMany
    {
        return $this->hasMany(ReunionParticipant::class, 'reunion_id');
    }

    /**
     * Relations avec l'ordre du jour
     */
    public function ordreJours(): HasMany
    {
        return $this->hasMany(ReunionOrdreJour::class, 'reunion_id');
    }

    /**
     * Relations avec les décisions
     */
    public function decisions(): HasMany
    {
        return $this->hasMany(ReunionDecision::class, 'reunion_id');
    }

    /**
     * Relations avec les procès-verbaux
     */
    public function pvs(): HasMany
    {
        return $this->hasMany(ReunionPV::class, 'reunion_id');
    }

    /**
     * Relations avec les notifications
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(ReunionNotification::class, 'reunion_id');
    }

    /**
     * Relations avec les exécutions de workflow
     */
    public function workflowExecutions(): HasMany
    {
        return $this->hasMany(ReunionWorkflowExecution::class, 'reunion_id');
    }

    /**
     * Relations avec le validateur du PV
     */
    public function validateurPV(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pv_valide_par_id');
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
     * Relations avec les participants (many-to-many)
     */
    public function participantsUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'reunion_participants', 'reunion_id', 'user_id')
                    ->withPivot('role', 'type', 'statut_presence', 'present_le', 'absent_le', 'commentaire_absence', 'notifie_absence', 'notifications_actives')
                    ->withTimestamps();
    }

    /**
     * Scope pour les réunions planifiées
     */
    public function scopePlanifiees($query)
    {
        return $query->where('statut', self::STATUT_PLANIFIEE);
    }

    /**
     * Scope pour les réunions en cours
     */
    public function scopeEnCours($query)
    {
        return $query->where('statut', self::STATUT_EN_COURS);
    }

    /**
     * Scope pour les réunions terminées
     */
    public function scopeTerminees($query)
    {
        return $query->where('statut', self::STATUT_TERMINEE);
    }

    /**
     * Scope pour les réunions annulées
     */
    public function scopeAnnulees($query)
    {
        return $query->where('statut', self::STATUT_ANNULEE);
    }

    /**
     * Scope par type de réunion
     */
    public function scopeByTypeReunion($query, $typeReunionId)
    {
        return $query->where('type_reunion_id', $typeReunionId);
    }

    /**
     * Scope par niveau de complexité
     */
    public function scopeByNiveauComplexite($query, $niveau)
    {
        return $query->where('niveau_complexite_actuel', $niveau);
    }

    /**
     * Scope pour les réunions à venir
     */
    public function scopeAVenir($query)
    {
        return $query->where('date_debut', '>', now());
    }

    /**
     * Scope pour les réunions passées
     */
    public function scopePassees($query)
    {
        return $query->where('date_fin', '<', now());
    }

    /**
     * Scope pour les réunions aujourd'hui
     */
    public function scopeAujourdhui($query)
    {
        return $query->whereDate('date_debut', now()->toDateString());
    }

    /**
     * Scope pour les réunions non suspendues
     */
    public function scopeNonSuspendues($query)
    {
        return $query->where('suspendue', false);
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    /**
     * Obtenir le libellé du niveau de complexité
     */
    public function getNiveauComplexiteLibelleAttribute(): string
    {
        return self::NIVEAUX[$this->niveau_complexite_actuel] ?? $this->niveau_complexite_actuel;
    }

    /**
     * Obtenir le libellé du type de lieu
     */
    public function getTypeLieuLibelleAttribute(): string
    {
        return self::TYPES_LIEU[$this->type_lieu] ?? $this->type_lieu;
    }

    /**
     * Obtenir le libellé de la périodicité
     */
    public function getPeriodiciteLibelleAttribute(): string
    {
        return self::PERIODICITES[$this->periodicite] ?? $this->periodicite;
    }

    /**
     * Obtenir le libellé du type d'ordre du jour
     */
    public function getOrdreJourTypeLibelleAttribute(): string
    {
        return self::TYPES_ORDRE_JOUR[$this->ordre_du_jour_type] ?? $this->ordre_du_jour_type;
    }

    /**
     * Vérifier si la réunion est à venir
     */
    public function getEstAVenirAttribute(): bool
    {
        return $this->date_debut > now();
    }

    /**
     * Vérifier si la réunion est en cours
     */
    public function getEstEnCoursAttribute(): bool
    {
        $maintenant = now();
        return $this->date_debut <= $maintenant && $this->date_fin >= $maintenant;
    }

    /**
     * Vérifier si la réunion est passée
     */
    public function getEstPasseeAttribute(): bool
    {
        return $this->date_fin < now();
    }

    /**
     * Vérifier si la réunion est aujourd'hui
     */
    public function getEstAujourdhuiAttribute(): bool
    {
        return $this->date_debut->toDateString() === now()->toDateString();
    }

    /**
     * Obtenir la durée en minutes
     */
    public function getDureeMinutesAttribute(): int
    {
        return $this->date_debut->diffInMinutes($this->date_fin);
    }

    /**
     * Obtenir la durée formatée
     */
    public function getDureeFormateeAttribute(): string
    {
        $minutes = $this->duree_minutes;
        $heures = intval($minutes / 60);
        $minutesRestantes = $minutes % 60;

        if ($heures > 0) {
            return "{$heures}h" . ($minutesRestantes > 0 ? " {$minutesRestantes}min" : "");
        }

        return "{$minutesRestantes}min";
    }

    /**
     * Vérifier si le quorum est atteint
     */
    public function getQuorumAtteintAttribute(): bool
    {
        if (!$this->quorum_minimum) {
            return true;
        }

        $participantsPresents = $this->participants()
            ->where('statut_presence', ReunionParticipant::STATUT_PRESENCE_CONFIRME)
            ->count();

        return $participantsPresents >= $this->quorum_minimum;
    }

    /**
     * Obtenir le nombre de participants présents
     */
    public function getNombreParticipantsPresentsAttribute(): int
    {
        return $this->participants()
            ->where('statut_presence', ReunionParticipant::STATUT_PRESENCE_CONFIRME)
            ->count();
    }

    /**
     * Obtenir le nombre total de participants
     */
    public function getNombreTotalParticipantsAttribute(): int
    {
        return $this->participants()->count();
    }

    /**
     * Vérifier si une fonctionnalité est active
     */
    public function hasFonctionnalite(string $fonctionnalite): bool
    {
        return $this->fonctionnalites_actives[$fonctionnalite] ?? false;
    }

    /**
     * Obtenir le PV le plus récent
     */
    public function getPvRecenteAttribute()
    {
        return $this->pvs()->latest('version')->first();
    }

    /**
     * Vérifier si la réunion a un PV validé
     */
    public function getAPvValideAttribute(): bool
    {
        return $this->pvs()->where('statut', ReunionPV::STATUT_VALIDE)->exists();
    }
}
