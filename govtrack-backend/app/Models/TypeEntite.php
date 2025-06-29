<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeEntite extends Model
{
    protected $table = 'type_entites';

    public $timestamps = false;

    protected $fillable = [
        'nom',
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
     * Relation avec les entités
     */
    public function entites(): HasMany
    {
        return $this->hasMany(Entite::class, 'type_entite_id');
    }
}
