<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class ReunionPV extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'reunion_pvs';

    /**
     * Constantes pour les statuts
     */
    public const STATUT_BROUILLON = 'BROUILLON';
    public const STATUT_VALIDE = 'VALIDE';
    public const STATUT_PUBLIE = 'PUBLIE';

    public const STATUTS = [
        self::STATUT_BROUILLON => 'Brouillon',
        self::STATUT_VALIDE => 'Validé',
        self::STATUT_PUBLIE => 'Publié',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'reunion_id',
        'contenu',
        'redige_par_id',
        'redige_le',
        'modifie_le',
        'version',
        'valide_par_id',
        'valide_le',
        'statut',
        'commentaire_validation',
        'notifications_envoyees',
        'date_creation',
        'date_modification',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'redige_le' => 'datetime',
        'modifie_le' => 'datetime',
        'version' => 'integer',
        'valide_le' => 'datetime',
        'notifications_envoyees' => 'boolean',
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
     * Relations avec le rédacteur
     */
    public function redacteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redige_par_id');
    }

    /**
     * Relations avec le validateur
     */
    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par_id');
    }

    /**
     * Scope pour les PV brouillons
     */
    public function scopeBrouillons($query)
    {
        return $query->where('statut', self::STATUT_BROUILLON);
    }

    /**
     * Scope pour les PV validés
     */
    public function scopeValides($query)
    {
        return $query->where('statut', self::STATUT_VALIDE);
    }

    /**
     * Scope pour les PV publiés
     */
    public function scopePublies($query)
    {
        return $query->where('statut', self::STATUT_PUBLIE);
    }

    /**
     * Scope par version
     */
    public function scopeByVersion($query, $version)
    {
        return $query->where('version', $version);
    }

    /**
     * Scope pour les dernières versions
     */
    public function scopeDernieresVersions($query)
    {
        return $query->whereIn('id', function ($subquery) {
            $subquery->selectRaw('MAX(id)')
                     ->from('reunion_pvs')
                     ->groupBy('reunion_id');
        });
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    /**
     * Vérifier si le PV est un brouillon
     */
    public function getEstBrouillonAttribute(): bool
    {
        return $this->statut === self::STATUT_BROUILLON;
    }

    /**
     * Vérifier si le PV est validé
     */
    public function getEstValideAttribute(): bool
    {
        return $this->statut === self::STATUT_VALIDE;
    }

    /**
     * Vérifier si le PV est publié
     */
    public function getEstPublieAttribute(): bool
    {
        return $this->statut === self::STATUT_PUBLIE;
    }

    /**
     * Vérifier si le PV peut être modifié
     */
    public function getPeutEtreModifieAttribute(): bool
    {
        return $this->statut === self::STATUT_BROUILLON;
    }

    /**
     * Vérifier si le PV peut être validé
     */
    public function getPeutEtreValideAttribute(): bool
    {
        return $this->statut === self::STATUT_BROUILLON;
    }

    /**
     * Vérifier si le PV peut être publié
     */
    public function getPeutEtrePublieAttribute(): bool
    {
        return $this->statut === self::STATUT_VALIDE;
    }

    /**
     * Obtenir la couleur du statut
     */
    public function getStatutCouleurAttribute(): string
    {
        return match($this->statut) {
            self::STATUT_BROUILLON => 'gray',
            self::STATUT_VALIDE => 'blue',
            self::STATUT_PUBLIE => 'green',
            default => 'gray',
        };
    }

    /**
     * Obtenir l'icône du statut
     */
    public function getStatutIconeAttribute(): string
    {
        return match($this->statut) {
            self::STATUT_BROUILLON => 'file-text',
            self::STATUT_VALIDE => 'check-circle',
            self::STATUT_PUBLIE => 'globe',
            default => 'file',
        };
    }

    /**
     * Obtenir le nombre de mots du contenu
     */
    public function getNombreMotsAttribute(): int
    {
        return str_word_count(strip_tags($this->contenu));
    }

    /**
     * Obtenir le nombre de caractères du contenu
     */
    public function getNombreCaracteresAttribute(): int
    {
        return strlen(strip_tags($this->contenu));
    }

    /**
     * Obtenir un extrait du contenu
     */
    public function getExtraitAttribute(): string
    {
        $contenu = strip_tags($this->contenu);
        return strlen($contenu) > 200 ? substr($contenu, 0, 200) . '...' : $contenu;
    }

    /**
     * Obtenir la durée de rédaction
     */
    public function getDureeRedactionAttribute(): string
    {
        if (!$this->redige_le || !$this->modifie_le) {
            return 'N/A';
        }

        $duree = $this->redige_le->diffInMinutes($this->modifie_le);

        if ($duree < 60) {
            return "{$duree} min";
        }

        $heures = intval($duree / 60);
        $minutes = $duree % 60;

        return "{$heures}h" . ($minutes > 0 ? " {$minutes}min" : "");
    }

    /**
     * Obtenir le temps de validation
     */
    public function getTempsValidationAttribute(): string
    {
        if (!$this->modifie_le || !$this->valide_le) {
            return 'N/A';
        }

        $duree = $this->modifie_le->diffInMinutes($this->valide_le);

        if ($duree < 60) {
            return "{$duree} min";
        }

        $heures = intval($duree / 60);
        $minutes = $duree % 60;

        return "{$heures}h" . ($minutes > 0 ? " {$minutes}min" : "");
    }

    /**
     * Vérifier si le PV a été modifié après rédaction
     */
    public function getAeteModifieAttribute(): bool
    {
        return $this->modifie_le && $this->modifie_le->gt($this->redige_le);
    }

    /**
     * Obtenir le nom du fichier suggéré
     */
    public function getNomFichierAttribute(): string
    {
        $reunion = $this->reunion;
        $date = $reunion->date_debut->format('Y-m-d');
        $titre = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $reunion->titre);
        return "PV_{$titre}_{$date}_v{$this->version}.pdf";
    }
}
