<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class TacheResponsable extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'tache_responsables';

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'tache_id',
        'user_id',
        'date_assignation',
        'date_fin_assignation',
        'statut',
        'commentaire',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'date_assignation' => 'datetime',
        'date_fin_assignation' => 'datetime',
        'statut' => 'boolean',
    ];

    /**
     * Relation avec la tâche
     */
    public function tache(): BelongsTo
    {
        return $this->belongsTo(Tache::class, 'tache_id');
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope pour les assignations actives
     */
    public function scopeActif($query)
    {
        return $query->where('statut', true);
    }

    /**
     * Scope pour les assignations actuelles (actives et sans date de fin)
     */
    public function scopeActuel($query)
    {
        return $query->where('statut', true)
                    ->whereNull('date_fin_assignation');
    }

    /**
     * Accesseur pour savoir si l'assignation est active
     */
    public function getEstActiveAttribute(): bool
    {
        return $this->statut && is_null($this->date_fin_assignation);
    }
}
