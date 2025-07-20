<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class ReunionSerie extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'reunion_series';

    /**
     * Constantes pour les périodicités
     */
    public const PERIODICITE_HEBDOMADAIRE = 'HEBDOMADAIRE';
    public const PERIODICITE_BIHEBDOMADAIRE = 'BIHEBDOMADAIRE';
    public const PERIODICITE_MENSUELLE = 'MENSUELLE';

    public const PERIODICITES = [
        self::PERIODICITE_HEBDOMADAIRE => 'Hebdomadaire',
        self::PERIODICITE_BIHEBDOMADAIRE => 'Bi-hebdomadaire',
        self::PERIODICITE_MENSUELLE => 'Mensuelle',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'nom',
        'description',
        'type_reunion_id',
        'periodicite',
        'jour_semaine',
        'jour_mois',
        'heure_debut',
        'duree_minutes',
        'lieu_defaut',
        'actif',
        'date_debut_serie',
        'date_fin_serie',
        'suspendue',
        'prochaine_generation',
        'configuration_recurrence',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'jour_semaine' => 'integer',
        'jour_mois' => 'integer',
        'heure_debut' => 'datetime:H:i:s',
        'duree_minutes' => 'integer',
        'actif' => 'boolean',
        'date_debut_serie' => 'date',
        'date_fin_serie' => 'date',
        'suspendue' => 'boolean',
        'prochaine_generation' => 'datetime',
        'configuration_recurrence' => 'array',
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
     * Relations avec les réunions générées
     */
    public function reunionsGenerees(): HasMany
    {
        return $this->hasMany(ReunionGeneree::class, 'serie_id');
    }

    /**
     * Relations avec les réunions
     */
    public function reunions(): HasMany
    {
        return $this->hasMany(Reunion::class, 'serie_id');
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
     * Scope pour les séries actives
     */
    public function scopeActives($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope pour les séries non suspendues
     */
    public function scopeNonSuspendues($query)
    {
        return $query->where('suspendue', false);
    }

    /**
     * Scope par périodicité
     */
    public function scopeByPeriodicite($query, $periodicite)
    {
        return $query->where('periodicite', $periodicite);
    }

    /**
     * Scope par type de réunion
     */
    public function scopeByTypeReunion($query, $typeReunionId)
    {
        return $query->where('type_reunion_id', $typeReunionId);
    }

    /**
     * Obtenir le libellé de la périodicité
     */
    public function getPeriodiciteLibelleAttribute(): string
    {
        return self::PERIODICITES[$this->periodicite] ?? $this->periodicite;
    }

    /**
     * Vérifier si la série est en cours
     */
    public function getEstEnCoursAttribute(): bool
    {
        $aujourdhui = now()->toDateString();
        return $this->date_debut_serie <= $aujourdhui && $this->date_fin_serie >= $aujourdhui;
    }

    /**
     * Vérifier si la série est terminée
     */
    public function getEstTermineeAttribute(): bool
    {
        return now()->toDateString() > $this->date_fin_serie;
    }

    /**
     * Vérifier si la série est à venir
     */
    public function getEstAVenirAttribute(): bool
    {
        return now()->toDateString() < $this->date_debut_serie;
    }

    /**
     * Obtenir la durée formatée
     */
    public function getDureeFormateeAttribute(): string
    {
        $heures = intval($this->duree_minutes / 60);
        $minutes = $this->duree_minutes % 60;

        if ($heures > 0) {
            return "{$heures}h" . ($minutes > 0 ? " {$minutes}min" : "");
        }

        return "{$minutes}min";
    }

    /**
     * Obtenir l'heure de fin calculée
     */
    public function getHeureFinAttribute(): string
    {
        $heureDebut = \Carbon\Carbon::parse($this->heure_debut);
        $heureFin = $heureDebut->addMinutes($this->duree_minutes);
        return $heureFin->format('H:i');
    }

    /**
     * Vérifier si la génération automatique est activée
     */
    public function getGenerationAutomatiqueActiveAttribute(): bool
    {
        return $this->configuration_recurrence['generer_automatiquement'] ?? false;
    }

    /**
     * Obtenir le template de titre
     */
    public function getTemplateTitreAttribute(): string
    {
        return $this->configuration_recurrence['template_titre'] ?? '{nom} - {mois} {annee}';
    }

    /**
     * Obtenir la limite de générations
     */
    public function getLimiteGenerationsAttribute(): int
    {
        return $this->configuration_recurrence['limite_generations'] ?? 0;
    }

    /**
     * Obtenir les générations restantes
     */
    public function getGenerationsRestantesAttribute(): int
    {
        return $this->configuration_recurrence['generations_restantes'] ?? 0;
    }
}
