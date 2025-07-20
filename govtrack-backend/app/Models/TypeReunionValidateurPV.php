<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class TypeReunionValidateurPV extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'type_reunion_validateur_pvs';

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'type_reunion_id',
        'user_id',
        'niveau_validation',
        'actif',
        'date_creation',
        'date_modification',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'niveau_validation' => 'integer',
        'actif' => 'boolean',
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
     * Relations avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope pour les validateurs actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope par niveau de validation
     */
    public function scopeByNiveauValidation($query, $niveau)
    {
        return $query->where('niveau_validation', $niveau);
    }

    /**
     * Scope par type de réunion
     */
    public function scopeByTypeReunion($query, $typeReunionId)
    {
        return $query->where('type_reunion_id', $typeReunionId);
    }
}
