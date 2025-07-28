<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReunionSujetAvis extends Model
{
    use HasFactory;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Le nom de la table associée au modèle
     */
    protected $table = 'reunion_sujet_avis';

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'reunion_sujet_id',
        'participant_id',
        'type_avis',
        'commentaire',
        'statut',
        'date_creation',
        'date_modification',
        'creer_par',
        'modifier_par',
    ];

    /**
     * Les attributs à caster
     */
    protected $casts = [
        'date_creation' => 'datetime',
        'date_modification' => 'datetime',
    ];

    /**
     * Constantes pour les types d'avis
     */
    public const TYPE_FAVORABLE = 'FAVORABLE';
    public const TYPE_DEFAVORABLE = 'DEFAVORABLE';
    public const TYPE_RESERVE = 'RESERVE';
    public const TYPE_NEUTRE = 'NEUTRE';

    /**
     * Constantes pour les statuts
     */
    public const STATUT_EN_ATTENTE = 'EN_ATTENTE';
    public const STATUT_SOUMIS = 'SOUMIS';
    public const STATUT_MODIFIE = 'MODIFIE';

    /**
     * Liste des types d'avis disponibles
     */
    public static function getTypesAvis(): array
    {
        return [
            self::TYPE_FAVORABLE => 'Favorable',
            self::TYPE_DEFAVORABLE => 'Défavorable',
            self::TYPE_RESERVE => 'Réservé',
            self::TYPE_NEUTRE => 'Neutre',
        ];
    }

    /**
     * Liste des statuts disponibles
     */
    public static function getStatuts(): array
    {
        return [
            self::STATUT_EN_ATTENTE => 'En attente',
            self::STATUT_SOUMIS => 'Soumis',
            self::STATUT_MODIFIE => 'Modifié',
        ];
    }

    /**
     * Relation avec le sujet de réunion
     */
    public function sujet(): BelongsTo
    {
        return $this->belongsTo(ReunionSujet::class, 'reunion_sujet_id');
    }

    /**
     * Relation avec le participant
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(ReunionParticipant::class, 'participant_id');
    }

    /**
     * Relation avec le créateur
     */
    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creer_par');
    }

    /**
     * Relation avec le modificateur
     */
    public function modificateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modifier_par');
    }

    /**
     * Scope pour filtrer par type d'avis
     */
    public function scopeByTypeAvis($query, string $typeAvis)
    {
        return $query->where('type_avis', $typeAvis);
    }

    /**
     * Scope pour filtrer par statut
     */
    public function scopeByStatut($query, string $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope pour les avis en attente
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', self::STATUT_EN_ATTENTE);
    }

    /**
     * Scope pour les avis soumis
     */
    public function scopeSoumis($query)
    {
        return $query->where('statut', self::STATUT_SOUMIS);
    }
}
