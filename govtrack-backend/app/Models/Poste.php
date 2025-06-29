<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poste extends Model
{
    protected $table = 'postes';

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
     * Relations avec les historiques d'affectation
     */
    public function affectations(): HasMany
    {
        return $this->hasMany(UtilisateurEntiteHistory::class, 'poste_id');
    }
}
