<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Network extends Model
{
    protected $fillable = [
        'tmdb_id',
        'name',
        'logo_path',
        'origin_country'
    ];

    /**
     * Relation avec les séries diffusées
     */
    public function series(): BelongsToMany
    {
        return $this->belongsToMany(Series::class, 'series_networks', 'network_id', 'series_id')
                    ->withTimestamps();
    }

    /**
     * Accessor pour obtenir l'URL complète du logo
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }

        return 'https://image.tmdb.org/t/p/w500' . $this->logo_path;
    }

    /**
     * Scope pour les chaînes avec des séries
     */
    public function scopeWithSeries($query)
    {
        return $query->whereHas('series');
    }

    /**
     * Scope pour les chaînes par pays d'origine
     */
    public function scopeByCountry($query, string $country)
    {
        return $query->where('origin_country', $country);
    }
}