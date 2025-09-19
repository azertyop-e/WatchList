<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Creator extends Model
{
    protected $fillable = [
        'tmdb_id',
        'name',
        'original_name',
        'profile_path',
        'gender'
    ];

    /**
     * Relation avec les séries créées
     */
    public function series(): BelongsToMany
    {
        return $this->belongsToMany(Series::class, 'series_creators', 'creator_id', 'series_id')
                    ->withPivot('credit_id')
                    ->withTimestamps();
    }

    /**
     * Accessor pour obtenir le genre formaté
     */
    public function getGenderNameAttribute(): string
    {
        return match($this->gender) {
            0 => 'Non spécifié',
            1 => 'Femme',
            2 => 'Homme',
            3 => 'Non-binaire',
            default => 'Inconnu'
        };
    }

    /**
     * Accessor pour obtenir l'URL complète de l'image de profil
     */
    public function getProfileUrlAttribute(): ?string
    {
        if (!$this->profile_path) {
            return null;
        }

        return 'https://image.tmdb.org/t/p/w500' . $this->profile_path;
    }

    /**
     * Scope pour les créateurs avec des séries
     */
    public function scopeWithSeries($query)
    {
        return $query->whereHas('series');
    }

    /**
     * Scope pour les créateurs par genre
     */
    public function scopeByGender($query, int $gender)
    {
        return $query->where('gender', $gender);
    }
}