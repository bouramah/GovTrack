<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class TypeTache extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'type_taches';

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'nom',
        'description',
        'couleur',
        'actif',
        'ordre',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
        'actif' => 'boolean',
        'ordre' => 'integer',
    ];

    /**
     * Relation avec les tâches
     */
    public function taches(): HasMany
    {
        return $this->hasMany(Tache::class, 'type_tache_id');
    }

    /**
     * Scope pour les types actifs
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope par ordre
     */
    public function scopeByOrdre($query)
    {
        return $query->orderBy('ordre')->orderBy('nom');
    }

    /**
     * Accesseur pour le nombre de tâches
     */
    public function getNombreTachesAttribute()
    {
        return $this->taches()->count();
    }

    /**
     * Accesseur pour les statistiques
     */
    public function getStatistiquesAttribute()
    {
        $taches = $this->taches;

        return [
            'total_taches' => $taches->count(),
            'taches_par_statut' => $taches->groupBy('statut')->map->count(),
            'niveau_execution_moyen' => $taches->avg('niveau_execution') ?? 0,
            'taches_en_retard' => $taches->filter(function ($tache) {
                return $tache->est_en_retard;
            })->count(),
        ];
    }
}
