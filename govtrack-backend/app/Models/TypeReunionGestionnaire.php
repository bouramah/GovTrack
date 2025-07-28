<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TypeReunionGestionnaire extends Pivot
{
    use HasFactory;

    /**
     * Table associée au modèle
     */
    protected $table = 'type_reunion_gestionnaires';

    /**
     * Désactiver les timestamps automatiques
     */
    public $timestamps = false;

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'type_reunion_id',
        'user_id',
        'permissions',
        'actif',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'permissions' => 'array',
        'actif' => 'boolean',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    /**
     * Relation avec TypeReunion
     */
    public function typeReunion()
    {
        return $this->belongsTo(TypeReunion::class, 'type_reunion_id');
    }

    /**
     * Relation avec User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
