<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

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

    /**
     * Vérifier si l'utilisateur a une permission spécifique
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('nom', $permissionName);
            })
            ->exists();
    }

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('nom', $roleName)->exists();
    }

    /**
     * Obtenir toutes les permissions de l'utilisateur
     */
    public function getAllPermissions()
    {
        return Permission::whereHas('roles.users', function ($query) {
            $query->where('user_id', $this->id);
        })->get();
    }

    /**
     * Vérifier si l'utilisateur est chef d'une entité spécifique
     */
    public function isChefOf(int $entiteId): bool
    {
        return $this->entitesDigees()
            ->where('entite_id', $entiteId)
            ->whereNull('date_fin')
            ->exists();
    }

    /**
     * Obtenir l'affectation actuelle de l'utilisateur
     */
    public function affectationActuelle()
    {
        return $this->affectations()
            ->with(['poste', 'entite'])
            ->where('statut', true)
            ->whereNull('date_fin')
            ->first();
    }
}
