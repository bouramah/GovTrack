<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\Auditable;

class Permission extends Model
{
    use HasFactory, Auditable;

    protected $table = 'permissions';

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
     * Relations many-to-many avec les rôles
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission', 'permission_id', 'role_id')
                    ->withPivot('date_creation');
    }
}
