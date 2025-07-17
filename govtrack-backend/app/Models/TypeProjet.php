<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class TypeProjet extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques car on utilise date_creation/date_modification
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'type_projets';

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'nom',
        'description',
        'duree_previsionnelle_jours',
        'description_sla',
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
        'duree_previsionnelle_jours' => 'integer',
    ];

    /**
     * Relation avec les instructions
     */
    public function projets(): HasMany
    {
        return $this->hasMany(Projet::class, 'type_projet_id');
    }

    /**
     * Scope pour rechercher par nom
     */
    public function scopeByNom($query, $nom)
    {
        return $query->where('nom', 'like', "%{$nom}%");
    }

    /**
     * Accesseur pour obtenir la durée formatée
     */
    public function getDureeFormatteeAttribute()
    {
        $jours = $this->duree_previsionnelle_jours;
        if ($jours < 7) {
            return $jours . ' jour' . ($jours > 1 ? 's' : '');
        } elseif ($jours < 30) {
            $semaines = round($jours / 7);
            return $semaines . ' semaine' . ($semaines > 1 ? 's' : '');
        } else {
            $mois = round($jours / 30);
            return $mois . ' mois';
        }
    }
}
