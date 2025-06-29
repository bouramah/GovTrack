<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'matricule',
        'nom',
        'prenom',
        'name',
        'email',
        'telephone',
        'adresse',
        'photo',
        'statut',
        'password',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'statut' => 'boolean',
            'date_creation' => 'datetime',
            'date_modification' => 'datetime',
        ];
    }

    /**
     * Relations avec les historiques d'affectation
     */
    public function affectations(): HasMany
    {
        return $this->hasMany(UtilisateurEntiteHistory::class, 'user_id');
    }

    /**
     * Relations avec les entités dirigées
     */
    public function entitesDigees(): HasMany
    {
        return $this->hasMany(EntiteChefHistory::class, 'user_id');
    }

    /**
     * Relations many-to-many avec les rôles
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'utilisateur_role', 'user_id', 'role_id')
                    ->withPivot('date_creation');
    }
}
