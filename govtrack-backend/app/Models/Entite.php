<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entite extends Model
{
    protected $table = 'entites';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'type_entite_id',
        'parent_id',
        'description',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par'
    ];

    protected $casts = [
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    /**
     * Relation avec le type d'entité
     */
    public function typeEntite(): BelongsTo
    {
        return $this->belongsTo(TypeEntite::class, 'type_entite_id');
    }

    /**
     * Relation avec l'entité parent
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Entite::class, 'parent_id');
    }

    /**
     * Relation avec les entités enfants
     */
    public function enfants(): HasMany
    {
        return $this->hasMany(Entite::class, 'parent_id');
    }

    /**
     * Relations avec les historiques d'affectation
     */
    public function affectations(): HasMany
    {
        return $this->hasMany(UtilisateurEntiteHistory::class, 'service_id');
    }

    /**
     * Relations avec les historiques de chefs
     */
    public function chefs(): HasMany
    {
        return $this->hasMany(EntiteChefHistory::class, 'entite_id');
    }
}
