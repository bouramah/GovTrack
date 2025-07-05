<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class EntiteChefHistory extends Model
{
    use HasFactory, Auditable;

    protected $table = 'entite_chef_histories';

    public $timestamps = false;

    protected $fillable = [
        'entite_id',
        'user_id',
        'date_debut',
        'date_fin',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    /**
     * Relation avec l'entité
     */
    public function entite(): BelongsTo
    {
        return $this->belongsTo(Entite::class, 'entite_id');
    }

    /**
     * Relation avec l'utilisateur (chef)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
