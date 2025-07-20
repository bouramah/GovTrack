<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class TypeReunion extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'type_reunions';

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
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'nom',
        'description',
        'couleur',
        'icone',
        'actif',
        'ordre',
        'niveau_complexite',
        'fonctionnalites_actives',
        'configuration_notifications',
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
        'ordre' => 'integer',
        'fonctionnalites_actives' => 'array',
        'configuration_notifications' => 'array',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    /**
     * Relations avec les gestionnaires
     */
    public function gestionnaires(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'type_reunion_gestionnaires', 'type_reunion_id', 'user_id')
                    ->withPivot('permissions', 'actif', 'date_creation', 'date_modification')
                    ->withTimestamps();
    }

    /**
     * Relations avec les membres permanents
     */
    public function membresPermanents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'type_reunion_membres_permanents', 'type_reunion_id', 'user_id')
                    ->withPivot('role_defaut', 'actif', 'notifications_par_defaut', 'date_creation', 'date_modification')
                    ->withTimestamps();
    }

    /**
     * Relations avec les séries de réunions
     */
    public function series(): HasMany
    {
        return $this->hasMany(ReunionSerie::class, 'type_reunion_id');
    }

    /**
     * Relations avec les réunions
     */
    public function reunions(): HasMany
    {
        return $this->hasMany(Reunion::class, 'type_reunion_id');
    }

    /**
     * Relations avec les workflows de configuration
     */
    public function workflowConfigs(): HasMany
    {
        return $this->hasMany(ReunionWorkflowConfig::class, 'type_reunion_id');
    }

    /**
     * Relations avec les validateurs de PV
     */
    public function validateursPV(): HasMany
    {
        return $this->hasMany(TypeReunionValidateurPV::class, 'type_reunion_id');
    }

    /**
     * Relations avec les configurations de notifications
     */
    public function notificationConfigs(): HasMany
    {
        return $this->hasMany(ReunionNotificationConfig::class, 'type_reunion_id');
    }

    /**
     * Scope pour les types actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope par niveau de complexité
     */
    public function scopeByNiveauComplexite($query, $niveau)
    {
        return $query->where('niveau_complexite', $niveau);
    }

    /**
     * Scope par ordre
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre');
    }

    /**
     * Vérifier si une fonctionnalité est active
     */
    public function hasFonctionnalite(string $fonctionnalite): bool
    {
        return $this->fonctionnalites_actives[$fonctionnalite] ?? false;
    }

    /**
     * Vérifier si une notification est configurée
     */
    public function hasNotification(string $notification): bool
    {
        return $this->configuration_notifications[$notification] ?? false;
    }

    /**
     * Obtenir le libellé du niveau de complexité
     */
    public function getNiveauComplexiteLibelleAttribute(): string
    {
        return self::NIVEAUX[$this->niveau_complexite] ?? $this->niveau_complexite;
    }

    /**
     * Obtenir la couleur du niveau de complexité
     */
    public function getNiveauComplexiteCouleurAttribute(): string
    {
        return match($this->niveau_complexite) {
            self::NIVEAU_SIMPLE => 'green',
            self::NIVEAU_INTERMEDIAIRE => 'yellow',
            self::NIVEAU_COMPLEXE => 'red',
            default => 'gray',
        };
    }

    /**
     * Obtenir l'icône du niveau de complexité
     */
    public function getNiveauComplexiteIconeAttribute(): string
    {
        return match($this->niveau_complexite) {
            self::NIVEAU_SIMPLE => 'circle',
            self::NIVEAU_INTERMEDIAIRE => 'square',
            self::NIVEAU_COMPLEXE => 'star',
            default => 'circle',
        };
    }

    /**
     * Vérifier si le type a des fonctionnalités complexes
     */
    public function getEstComplexeAttribute(): bool
    {
        return $this->niveau_complexite === self::NIVEAU_COMPLEXE;
    }

    /**
     * Vérifier si le type a des fonctionnalités intermédiaires
     */
    public function getEstIntermediaireAttribute(): bool
    {
        return $this->niveau_complexite === self::NIVEAU_INTERMEDIAIRE;
    }

    /**
     * Vérifier si le type est simple
     */
    public function getEstSimpleAttribute(): bool
    {
        return $this->niveau_complexite === self::NIVEAU_SIMPLE;
    }
}
