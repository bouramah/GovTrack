<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UtilisateurEntiteHistory extends Model
{
    protected $table = 'utilisateur_entite_histories';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'poste_id',
        'service_id',
        'statut',
        'date_debut',
        'date_fin',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par'
    ];

    protected $casts = [
        'statut' => 'boolean',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation avec le poste
     */
    public function poste(): BelongsTo
    {
        return $this->belongsTo(Poste::class, 'poste_id');
    }

    /**
     * Relation avec l'entité (service)
     */
    public function entite(): BelongsTo
    {
        return $this->belongsTo(Entite::class, 'service_id');
    }
}
