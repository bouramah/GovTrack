<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PieceJointeSujet extends Model
{
    use HasFactory;

    // Désactiver les timestamps automatiques
    public $timestamps = false;

    /**
     * Table associée au modèle
     */
    protected $table = 'piece_jointe_sujets';

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'reunion_sujet_id',
        'user_id',
        'fichier_path',
        'nom_original',
        'mime_type',
        'taille',
        'description',
        'type_document',
        'date_creation',
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'taille' => 'integer',
        'date_creation' => 'datetime',
    ];

    /**
     * Relations avec le sujet de réunion
     */
    public function sujet(): BelongsTo
    {
        return $this->belongsTo(ReunionSujet::class, 'reunion_sujet_id');
    }

    /**
     * Relations avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Obtenir la taille formatée
     */
    public function getTailleFormateeAttribute(): string
    {
        $bytes = $this->taille;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Obtenir l'icône selon le type MIME
     */
    public function getIconeAttribute(): string
    {
        $mimeType = $this->mime_type;

        return match(true) {
            str_contains($mimeType, 'pdf') => 'file-text',
            str_contains($mimeType, 'image') => 'image',
            str_contains($mimeType, 'video') => 'video',
            str_contains($mimeType, 'audio') => 'music',
            str_contains($mimeType, 'word') || str_contains($mimeType, 'document') => 'file-text',
            str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet') => 'bar-chart-2',
            str_contains($mimeType, 'powerpoint') || str_contains($mimeType, 'presentation') => 'presentation',
            str_contains($mimeType, 'zip') || str_contains($mimeType, 'rar') || str_contains($mimeType, 'archive') => 'archive',
            default => 'file',
        };
    }

    /**
     * Obtenir la couleur selon le type MIME
     */
    public function getCouleurAttribute(): string
    {
        $mimeType = $this->mime_type;

        return match(true) {
            str_contains($mimeType, 'pdf') => 'red',
            str_contains($mimeType, 'image') => 'green',
            str_contains($mimeType, 'video') => 'purple',
            str_contains($mimeType, 'audio') => 'blue',
            str_contains($mimeType, 'word') || str_contains($mimeType, 'document') => 'blue',
            str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet') => 'green',
            str_contains($mimeType, 'powerpoint') || str_contains($mimeType, 'presentation') => 'orange',
            str_contains($mimeType, 'zip') || str_contains($mimeType, 'rar') || str_contains($mimeType, 'archive') => 'gray',
            default => 'gray',
        };
    }
}
