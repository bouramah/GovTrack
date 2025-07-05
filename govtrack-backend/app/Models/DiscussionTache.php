<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class DiscussionTache extends Model
{
    use HasFactory, Auditable;

    public $timestamps = false;

    protected $table = 'discussion_taches';

    protected $fillable = [
        'tache_id',
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

    public function tache(): BelongsTo
    {
        return $this->belongsTo(Tache::class, 'tache_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DiscussionTache::class, 'parent_id');
    }

    public function reponses(): HasMany
    {
        return $this->hasMany(DiscussionTache::class, 'parent_id');
    }

    public function scopeMessagesRacine($query)
    {
        return $query->whereNull('parent_id');
    }
}
