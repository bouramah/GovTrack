<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class TacheHistoriqueStatut extends Model
{
    use HasFactory, Auditable;

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
