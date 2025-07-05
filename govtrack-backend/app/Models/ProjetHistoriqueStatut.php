<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class ProjetHistoriqueStatut extends Model
{
    use HasFactory, Auditable;

    public $timestamps = false;

    protected $table = 'projet_historique_statuts';

    protected $fillable = [
        'projet_id',
        'user_id',
        'ancien_statut',
        'nouveau_statut',
        'commentaire',
        'justificatif_path',
        'date_changement',
    ];

    protected $casts = [
        'date_changement' => 'datetime',
    ];

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
