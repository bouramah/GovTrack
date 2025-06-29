<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $table = 'roles';

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
     * Relations many-to-many avec les utilisateurs
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'utilisateur_role', 'role_id', 'user_id')
                    ->withPivot('date_creation');
    }

    /**
     * Relations many-to-many avec les permissions
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission', 'role_id', 'permission_id')
                    ->withPivot('date_creation');
    }
}
