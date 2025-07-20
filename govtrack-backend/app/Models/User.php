<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use App\Traits\Auditable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, Auditable;

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

    // === RELATIONS PARTIE 2 - PROJETS ET INSTRUCTIONS ===

    /**
     * Projets dont l'utilisateur est porteur principal (compatibilité)
     */
    public function projetsPortes(): HasMany
    {
        return $this->hasMany(Projet::class, 'porteur_principal_id');
    }

    /**
     * Projets dont l'utilisateur est porteur (nouveau système)
     */
    public function projetsPortesMultiple(): BelongsToMany
    {
        return $this->belongsToMany(Projet::class, 'projet_porteurs', 'user_id', 'projet_id')
                    ->withPivot(['date_assignation', 'date_fin_assignation', 'statut', 'commentaire'])
                    ->wherePivot('statut', true)
                    ->whereNull('pivot_date_fin_assignation');
    }

    /**
     * Projets dont l'utilisateur est Ordonnateur de l'instruction
     */
    public function projetsDonnes(): HasMany
    {
        return $this->hasMany(Projet::class, 'donneur_ordre_id');
    }

    /**
     * Tâches assignées à l'utilisateur (compatibilité)
     */
    public function tachesAssignees(): HasMany
    {
        return $this->hasMany(Tache::class, 'responsable_principal_id');
    }

    /**
     * Tâches assignées à l'utilisateur (nouveau système)
     */
    public function tachesAssigneesMultiple(): BelongsToMany
    {
        return $this->belongsToMany(Tache::class, 'tache_responsables', 'user_id', 'tache_id')
                    ->withPivot(['date_assignation', 'date_fin_assignation', 'statut', 'commentaire'])
                    ->wherePivot('statut', true)
                    ->whereNull('pivot_date_fin_assignation');
    }

    /**
     * Historique des changements de statuts de projets effectués par l'utilisateur
     */
    public function changementsStatutProjets(): HasMany
    {
        return $this->hasMany(ProjetHistoriqueStatut::class, 'user_id');
    }

    /**
     * Projets favoris de l'utilisateur
     */
    public function projetsFavoris(): BelongsToMany
    {
        return $this->belongsToMany(Projet::class, 'projet_favoris', 'user_id', 'projet_id')
                    ->withPivot('date_ajout');
    }

    /**
     * Pièces jointes ajoutées par l'utilisateur sur les projets
     */
    public function piecesJointesProjets(): HasMany
    {
        return $this->hasMany(PieceJointeProjet::class, 'user_id');
    }

    /**
     * Pièces jointes ajoutées par l'utilisateur sur les tâches
     */
    public function piecesJointesTaches(): HasMany
    {
        return $this->hasMany(PieceJointeTache::class, 'user_id');
    }

    /**
     * Messages de discussion sur les projets
     */
    public function discussionsProjets(): HasMany
    {
        return $this->hasMany(DiscussionProjet::class, 'user_id');
    }

    /**
     * Messages de discussion sur les tâches
     */
    public function discussionsTaches(): HasMany
    {
        return $this->hasMany(DiscussionTache::class, 'user_id');
    }

    /**
     * Obtenir tous les projets où l'utilisateur est impliqué (porteur, Ordonnateur de l'instruction ou tâches)
     */
    public function projetsImpliques()
    {
        $projetIds = collect();

        // Projets portés
        $projetIds = $projetIds->merge($this->projetsPortes()->pluck('id'));

        // Projets donnés
        $projetIds = $projetIds->merge($this->projetsDonnes()->pluck('id'));

        // Projets via tâches assignées
        $projetIds = $projetIds->merge(
            $this->tachesAssignees()->with('projet')->get()->pluck('projet.id')
        );

        return Projet::whereIn('id', $projetIds->unique())->get();
    }

    /**
     * Obtenir l'équipe complète d'un projet (tous les utilisateurs impliqués)
     * Implémente la logique : "quiconque ayant fait une tâche dans le projet rejoint l'équipe projet"
     */
    public static function equipeProjet($projetId)
    {
        $userIds = collect();

        $projet = Projet::with(['porteur', 'donneurOrdre', 'taches.responsable'])->findOrFail($projetId);

        // Porteur du projet
        if ($projet->porteur_id) {
            $userIds->push($projet->porteur_id);
        }

        // Ordonnateur de l'instruction
        if ($projet->donneur_ordre_id) {
            $userIds->push($projet->donneur_ordre_id);
        }

        // Responsables des tâches (= équipe automatique)
        foreach ($projet->taches as $tache) {
            if ($tache->responsable_id) {
                $userIds->push($tache->responsable_id);
            }
        }

        return User::whereIn('id', $userIds->unique())->get();
    }

    /**
     * Vérifier si l'utilisateur fait partie de l'équipe d'un projet
     */
    public function estDansEquipeProjet($projetId): bool
    {
        return $this->projetsImpliques()->contains('id', $projetId);
    }

    /**
     * Obtenir l'URL complète de la photo de profil
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo) {
            return null;
        }

        // Si l'URL est déjà complète, la retourner telle quelle
        if (str_starts_with($this->photo, 'http')) {
            return $this->photo;
        }

        // Générer l'URL complète depuis le storage public
        return Storage::disk('public')->url($this->photo);
    }

    // ========================================
    // RELATIONS AVEC LES RÉUNIONS
    // ========================================

    /**
     * Types de réunions gérés par l'utilisateur
     */
    public function typesReunionsGeres(): BelongsToMany
    {
        return $this->belongsToMany(TypeReunion::class, 'type_reunion_gestionnaires', 'user_id', 'type_reunion_id')
                    ->withPivot('permissions', 'actif', 'date_creation', 'date_modification')
                    ->withTimestamps();
    }

    /**
     * Types de réunions où l'utilisateur est membre permanent
     */
    public function typesReunionsMembrePermanent(): BelongsToMany
    {
        return $this->belongsToMany(TypeReunion::class, 'type_reunion_membres_permanents', 'user_id', 'type_reunion_id')
                    ->withPivot('role_defaut', 'actif', 'notifications_par_defaut', 'date_creation', 'date_modification')
                    ->withTimestamps();
    }

    /**
     * Types de réunions où l'utilisateur peut valider les PV
     */
    public function typesReunionsValidateurPV(): HasMany
    {
        return $this->hasMany(TypeReunionValidateurPV::class, 'user_id');
    }

    /**
     * Séries de réunions créées par l'utilisateur
     */
    public function seriesReunionsCreees(): HasMany
    {
        return $this->hasMany(ReunionSerie::class, 'creer_par');
    }

    /**
     * Réunions créées par l'utilisateur
     */
    public function reunionsCreees(): HasMany
    {
        return $this->hasMany(Reunion::class, 'creer_par');
    }

    /**
     * Réunions modifiées par l'utilisateur
     */
    public function reunionsModifiees(): HasMany
    {
        return $this->hasMany(Reunion::class, 'modifier_par');
    }

    /**
     * Réunions où l'utilisateur est participant
     */
    public function reunionsParticipant(): HasMany
    {
        return $this->hasMany(ReunionParticipant::class, 'user_id');
    }

    /**
     * Réunions où l'utilisateur est président
     */
    public function reunionsPresident(): HasMany
    {
        return $this->hasMany(ReunionParticipant::class, 'user_id')
                    ->where('role', ReunionParticipant::ROLE_PRESIDENT);
    }

    /**
     * Réunions où l'utilisateur est secrétaire
     */
    public function reunionsSecretaire(): HasMany
    {
        return $this->hasMany(ReunionParticipant::class, 'user_id')
                    ->where('role', ReunionParticipant::ROLE_SECRETAIRE);
    }

    /**
     * PV rédigés par l'utilisateur
     */
    public function pvsRediges(): HasMany
    {
        return $this->hasMany(ReunionPV::class, 'redige_par_id');
    }

    /**
     * PV validés par l'utilisateur
     */
    public function pvsValides(): HasMany
    {
        return $this->hasMany(ReunionPV::class, 'valide_par_id');
    }

    /**
     * Réunions où l'utilisateur a validé le PV
     */
    public function reunionsPVValidees(): HasMany
    {
        return $this->hasMany(Reunion::class, 'pv_valide_par_id');
    }

    /**
     * Notifications de réunions reçues par l'utilisateur
     */
    public function notificationsReunions(): HasMany
    {
        return $this->hasMany(ReunionNotification::class, 'destinataire_id');
    }

    /**
     * Configurations de notifications créées par l'utilisateur
     */
    public function configsNotificationsCreees(): HasMany
    {
        return $this->hasMany(ReunionNotificationConfig::class, 'creer_par');
    }

    /**
     * Configurations de notifications modifiées par l'utilisateur
     */
    public function configsNotificationsModifiees(): HasMany
    {
        return $this->hasMany(ReunionNotificationConfig::class, 'modifier_par');
    }

    /**
     * Workflows de réunions créés par l'utilisateur
     */
    public function workflowsReunionsCrees(): HasMany
    {
        return $this->hasMany(ReunionWorkflowConfig::class, 'creer_par');
    }

    /**
     * Workflows de réunions modifiés par l'utilisateur
     */
    public function workflowsReunionsModifies(): HasMany
    {
        return $this->hasMany(ReunionWorkflowConfig::class, 'modifier_par');
    }

    /**
     * Ordres du jour où l'utilisateur est responsable
     */
    public function ordresJourResponsable(): HasMany
    {
        return $this->hasMany(ReunionOrdreJour::class, 'responsable_id');
    }

    /**
     * Décisions de réunions où l'utilisateur est responsable
     */
    public function decisionsReunionsResponsable(): HasMany
    {
        return $this->hasMany(ReunionDecision::class, 'responsable_id');
    }

    /**
     * Actions de réunions où l'utilisateur est responsable
     */
    public function actionsReunionsResponsable(): HasMany
    {
        return $this->hasMany(ReunionAction::class, 'responsable_id');
    }

    /**
     * Obtenir toutes les réunions où l'utilisateur est impliqué
     */
    public function reunionsImpliquees()
    {
        $reunionIds = collect();

        // Réunions créées
        $reunionIds = $reunionIds->merge($this->reunionsCreees()->pluck('id'));

        // Réunions où l'utilisateur est participant
        $reunionIds = $reunionIds->merge($this->reunionsParticipant()->pluck('reunion_id'));

        // Réunions où l'utilisateur a validé le PV
        $reunionIds = $reunionIds->merge($this->reunionsPVValidees()->pluck('id'));

        return Reunion::whereIn('id', $reunionIds->unique())->get();
    }

    /**
     * Vérifier si l'utilisateur peut gérer un type de réunion
     */
    public function peutGererTypeReunion($typeReunionId): bool
    {
        return $this->typesReunionsGeres()
                    ->where('type_reunion_id', $typeReunionId)
                    ->where('actif', true)
                    ->exists();
    }

    /**
     * Vérifier si l'utilisateur est membre permanent d'un type de réunion
     */
    public function estMembrePermanentTypeReunion($typeReunionId): bool
    {
        return $this->typesReunionsMembrePermanent()
                    ->where('type_reunion_id', $typeReunionId)
                    ->where('actif', true)
                    ->exists();
    }

    /**
     * Vérifier si l'utilisateur peut valider les PV d'un type de réunion
     */
    public function peutValiderPVTypeReunion($typeReunionId): bool
    {
        return $this->typesReunionsValidateurPV()
                    ->where('type_reunion_id', $typeReunionId)
                    ->where('actif', true)
                    ->exists();
    }
}
