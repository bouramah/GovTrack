<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscussionProjet extends Model
{
    public $timestamps = false;

    protected $table = 'discussion_projets';

    protected $fillable = [
        'projet_id',
        'user_id',
        'parent_id',
        'message',
        'est_modifie',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par',
    ];

    protected $casts = [
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
        'est_modifie' => 'boolean',
    ];

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DiscussionProjet::class, 'parent_id');
    }

    public function reponses(): HasMany
    {
        return $this->hasMany(DiscussionProjet::class, 'parent_id');
    }

    public function scopeMessagesRacine($query)
    {
        return $query->whereNull('parent_id');
    }
}
