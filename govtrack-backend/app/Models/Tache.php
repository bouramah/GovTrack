<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class Tache extends Model
{
    use HasFactory, Auditable;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'taches';

    /**
     * Constantes pour les statuts (identiques aux projets)
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
        'projet_id',
        'responsable_id',
        'statut',
        'niveau_execution',
        'date_debut_previsionnelle',
        'date_fin_previsionnelle',
        'date_debut_reelle',
        'date_fin_reelle',
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
     * Relation avec le projet
     */
    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }

    /**
     * Relation avec le responsable
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    /**
     * Relation avec les pièces jointes
     */
    public function piecesJointes(): HasMany
    {
        return $this->hasMany(PieceJointeTache::class, 'tache_id');
    }

    /**
     * Relation avec les discussions
     */
    public function discussions(): HasMany
    {
        return $this->hasMany(DiscussionTache::class, 'tache_id');
    }

    /**
     * Relation avec l'historique des statuts
     */
    public function historiqueStatuts(): HasMany
    {
        return $this->hasMany(TacheHistoriqueStatut::class, 'tache_id')->orderBy('date_changement', 'desc');
    }

    /**
     * Scope par statut
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope par responsable
     */
    public function scopeByResponsable($query, $userId)
    {
        return $query->where('responsable_id', $userId);
    }

    /**
     * Scope par projet
     */
    public function scopeByProjet($query, $projetId)
    {
        return $query->where('projet_id', $projetId);
    }

    /**
     * Scope tâches en retard
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
     * Accesseur pour savoir si la tâche est en retard
     */
    public function getEstEnRetardAttribute()
    {
        return $this->date_fin_previsionnelle &&
               $this->date_fin_previsionnelle < now() &&
               !in_array($this->statut, [self::STATUT_TERMINE]);
    }

    /**
     * Méthode pour mettre à jour le niveau d'exécution du projet parent
     */
    public function mettreAJourNiveauProjet()
    {
        $projet = $this->projet;
        if ($projet) {
            $taches = $projet->taches;
            $niveauMoyen = $taches->avg('niveau_execution') ?? 0;

            $projet->update([
                'niveau_execution' => round($niveauMoyen),
                'date_modification' => now(),
            ]);
        }
    }
}
