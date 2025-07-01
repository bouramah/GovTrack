<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TacheHistoriqueStatut extends Model
{
    public $timestamps = false;

    protected $table = 'tache_historique_statuts';

    protected $guarded = [];

    protected $casts = [
        'date_changement' => 'datetime',
    ];

    public function tache(): BelongsTo
    {
        return $this->belongsTo(Tache::class, 'tache_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
} 