<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TypeReunionMembrePermanent extends Pivot
{
    use HasFactory;

    /**
     * Table associée au modèle
     */
    protected $table = 'type_reunion_membres_permanents';

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
        'role_defaut',
        'actif',
        'notifications_par_defaut',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'role_defaut' => 'string',
        'actif' => 'boolean',
        'notifications_par_defaut' => 'array',
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
