<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PieceJointeTache extends Model
{
    public $timestamps = false;

    protected $table = 'piece_jointe_taches';

    protected $fillable = [
        'tache_id',
        'user_id',
        'fichier_path',
        'nom_original',
        'mime_type',
        'taille',
        'description',
        'est_justificatif',
        'type_document',
        'date_creation',
    ];

    protected $casts = [
        'date_creation' => 'datetime',
        'taille' => 'integer',
        'est_justificatif' => 'boolean',
    ];

    public function tache(): BelongsTo
    {
        return $this->belongsTo(Tache::class, 'tache_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getTailleFormatteeAttribute()
    {
        $bytes = $this->taille;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
