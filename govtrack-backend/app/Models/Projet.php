<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'titre',
        'description',
        'type_projet_id',
        'porteur_id',
        'donneur_ordre_id',
        'statut',
        'niveau_execution',
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
    ];

    /**
     * Relation avec le type de projet
     */
    public function typeProjet(): BelongsTo
    {
        return $this->belongsTo(TypeProjet::class, 'type_projet_id');
    }

    /**
     * Relation avec le porteur du projet
     */
    public function porteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'porteur_id');
    }

    /**
     * Relation avec le donneur d'ordre
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
     * Scope par statut
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope par porteur
     */
    public function scopeByPorteur($query, $userId)
    {
        return $query->where('porteur_id', $userId);
    }

    /**
     * Scope par donneur d'ordre
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
}
