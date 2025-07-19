<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class Projet extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'projets';

    /**
     * Constantes pour les statuts
     */
    public const STATUT_A_FAIRE = 'a_faire';
    public const STATUT_EN_COURS = 'en_cours';
    public const STATUT_BLOQUE = 'bloque';
    public const STATUT_DEMANDE_CLOTURE = 'demande_de_cloture';
    public const STATUT_TERMINE = 'termine';

    public const STATUTS = [
        self::STATUT_A_FAIRE => 'À faire',
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_BLOQUE => 'Bloqué',
        self::STATUT_DEMANDE_CLOTURE => 'Demande de clôture',
        self::STATUT_TERMINE => 'Terminé',
    ];

    /**
     * Constantes pour les priorités
     */
    public const PRIORITE_FAIBLE = 'faible';
    public const PRIORITE_NORMALE = 'normale';
    public const PRIORITE_ELEVEE = 'elevee';
    public const PRIORITE_CRITIQUE = 'critique';

    public const PRIORITES = [
        self::PRIORITE_FAIBLE => 'Faible',
        self::PRIORITE_NORMALE => 'Normale',
        self::PRIORITE_ELEVEE => 'Élevée',
        self::PRIORITE_CRITIQUE => 'Critique',
    ];

    public const PRIORITES_COULEURS = [
        self::PRIORITE_FAIBLE => 'gray',
        self::PRIORITE_NORMALE => 'blue',
        self::PRIORITE_ELEVEE => 'orange',
        self::PRIORITE_CRITIQUE => 'red',
    ];

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'titre',
        'description',
        'type_projet_id',
        'donneur_ordre_id',
        'statut',
        'niveau_execution',
        'priorite',
        'est_favori',
        'date_debut_previsionnelle',
        'date_fin_previsionnelle',
        'date_debut_reelle',
        'date_fin_reelle',
        'justification_modification_dates',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
        'date_debut_previsionnelle' => 'date',
        'date_fin_previsionnelle' => 'date',
        'date_debut_reelle' => 'date',
        'date_fin_reelle' => 'date',
        'niveau_execution' => 'integer',
        'est_favori' => 'boolean',
    ];

    /**
     * Relation avec le type de projet
     */
    public function typeProjet(): BelongsTo
    {
        return $this->belongsTo(TypeProjet::class, 'type_projet_id');
    }

    /**
     * Relation avec le porteur principal du projet (pour compatibilité avec l'ancien système)
     * @deprecated Utiliser porteurs() à la place
     */
    public function porteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'porteur_principal_id');
    }

    /**
     * Relation avec tous les porteurs du projet
     */
    public function porteurs(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'projet_porteurs', 'projet_id', 'user_id')
                    ->withPivot(['date_assignation', 'date_fin_assignation', 'statut', 'commentaire'])
                    ->wherePivot('statut', true)
                    ->whereNull('date_fin_assignation');
    }

    /**
     * Relation avec tous les porteurs du projet (incluant l'historique)
     */
    public function porteursHistorique(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'projet_porteurs', 'projet_id', 'user_id')
                    ->withPivot(['date_assignation', 'date_fin_assignation', 'statut', 'commentaire']);
    }

    /**
     * Relation avec le Ordonnateur de l'instruction
     */
    public function donneurOrdre(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donneur_ordre_id');
    }

    /**
     * Relation avec les tâches
     */
    public function taches(): HasMany
    {
        return $this->hasMany(Tache::class, 'projet_id');
    }

    /**
     * Relation avec l'historique des statuts
     */
    public function historiqueStatuts(): HasMany
    {
        return $this->hasMany(ProjetHistoriqueStatut::class, 'projet_id');
    }

    /**
     * Relation avec les pièces jointes
     */
    public function piecesJointes(): HasMany
    {
        return $this->hasMany(PieceJointeProjet::class, 'projet_id');
    }

    /**
     * Relation avec les discussions
     */
    public function discussions(): HasMany
    {
        return $this->hasMany(DiscussionProjet::class, 'projet_id');
    }

    /**
     * Relation avec les utilisateurs qui ont mis ce projet en favori
     */
    public function favoris(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'projet_favoris', 'projet_id', 'user_id')
                    ->withPivot('date_ajout');
    }

    /**
     * Vérifier si un projet est en favori pour un utilisateur donné
     */
    public function estFavoriPour($userId): bool
    {
        return $this->favoris()->where('user_id', $userId)->exists();
    }

    /**
     * Scope par statut
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope par porteur principal (pour compatibilité avec l'ancien système)
     * @deprecated Utiliser scopeByPorteurMultiple() à la place
     */
    public function scopeByPorteur($query, $userId)
    {
        return $query->where('porteur_principal_id', $userId);
    }

    /**
     * Scope par porteur (nouveau système)
     */
    public function scopeByPorteurMultiple($query, $userId)
    {
        return $query->whereHas('porteurs', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope par Ordonnateur de l'instruction
     */
    public function scopeByDonneurOrdre($query, $userId)
    {
        return $query->where('donneur_ordre_id', $userId);
    }

    /**
     * Scope projets en retard
     */
    public function scopeEnRetard($query)
    {
        return $query->where('date_fin_previsionnelle', '<', now())
                    ->whereNotIn('statut', [self::STATUT_TERMINE]);
    }

    /**
     * Scope par priorité
     */
    public function scopeByPriorite($query, $priorite)
    {
        return $query->where('priorite', $priorite);
    }

    /**
     * Scope projets favoris pour un utilisateur
     */
    public function scopeFavorisPour($query, $userId)
    {
        return $query->whereHas('favoris', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope projets non favoris pour un utilisateur
     */
    public function scopeNonFavorisPour($query, $userId)
    {
        return $query->whereDoesntHave('favoris', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Accesseur pour le libellé du statut
     */
    public function getStatutLibelleAttribute()
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    /**
     * Accesseur pour savoir si le projet est en retard
     */
    public function getEstEnRetardAttribute()
    {
        return $this->date_fin_previsionnelle < now() &&
               !in_array($this->statut, [self::STATUT_TERMINE]);
    }

    /**
     * Méthode pour changer le statut avec historique
     */
    public function changerStatut($nouveauStatut, $userId, $commentaire = null, $justificatifPath = null)
    {
        $ancienStatut = $this->statut;

        // Créer l'entrée d'historique
        $this->historiqueStatuts()->create([
            'ancien_statut' => $ancienStatut,
            'nouveau_statut' => $nouveauStatut,
            'user_id' => $userId,
            'commentaire' => $commentaire,
            'justificatif_path' => $justificatifPath,
            'date_changement' => now(),
        ]);

        // Mettre à jour le statut
        $this->update([
            'statut' => $nouveauStatut,
            'date_modification' => now(),
        ]);

        return $this;
    }

    /**
     * Calculer et mettre à jour le niveau d'exécution basé sur les tâches
     */
    public function calculerNiveauExecution()
    {
        $taches = $this->taches;

        if ($taches->count() > 0) {
            // Projet avec tâches : calculer la moyenne
            $niveauMoyen = $taches->avg('niveau_execution') ?? 0;
            $this->update([
                'niveau_execution' => round($niveauMoyen),
                'date_modification' => now(),
            ]);
        } else {
            // Projet sans tâches : réinitialiser à 0
            $this->update([
                'niveau_execution' => 0,
                'date_modification' => now(),
            ]);
        }
    }

    /**
     * Vérifier si le projet a des tâches
     */
    public function aDesTaches(): bool
    {
        return $this->taches()->count() > 0;
    }

    /**
     * Accesseur pour savoir si le niveau d'exécution est automatique
     */
    public function getNiveauExecutionAutomatiqueAttribute(): bool
    {
        return $this->aDesTaches();
    }

    /**
     * Accesseur pour le libellé de la priorité
     */
    public function getPrioriteLibelleAttribute(): string
    {
        return self::PRIORITES[$this->priorite] ?? $this->priorite;
    }

    /**
     * Accesseur pour la couleur de la priorité
     */
    public function getPrioriteCouleurAttribute(): string
    {
        return self::PRIORITES_COULEURS[$this->priorite] ?? 'gray';
    }

    /**
     * Accesseur pour l'icône de la priorité
     */
    public function getPrioriteIconeAttribute(): string
    {
        $icones = [
            self::PRIORITE_FAIBLE => 'arrow-down',
            self::PRIORITE_NORMALE => 'minus',
            self::PRIORITE_ELEVEE => 'arrow-up',
            self::PRIORITE_CRITIQUE => 'alert-triangle',
        ];

        return $icones[$this->priorite] ?? 'minus';
    }
}
