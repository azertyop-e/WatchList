<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Episode extends Model
{
    protected $fillable = [
        'tmdb_id',
        'season_id',
        'name',
        'overview',
        'still_path',
        'air_date',
        'episode_number',
        'episode_type',
        'production_code',
        'runtime',
        'vote_average',
        'vote_count',
        'is_watched'
    ];

    protected $casts = [
        'air_date' => 'date',
        'is_watched' => 'boolean',
        'vote_average' => 'decimal:1',
    ];

    /**
     * Relation avec la saison parente
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * Relation avec la série (via la saison)
     */
    public function series()
    {
        return $this->hasOneThrough(Series::class, Season::class, 'id', 'id', 'season_id', 'series_id');
    }

    /**
     * Accessor pour obtenir la durée formatée
     */
    public function getFormattedRuntimeAttribute(): string
    {
        if (!$this->runtime) {
            return 'Durée inconnue';
        }

        $hours = floor($this->runtime / 60);
        $minutes = $this->runtime % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'min';
        }

        return $minutes . 'min';
    }

    /**
     * Accessor pour vérifier si l'épisode est diffusé
     */
    public function getIsAiredAttribute(): bool
    {
        return $this->air_date && $this->air_date <= now();
    }

    /**
     * Accessor pour vérifier si l'épisode est à venir
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->air_date && $this->air_date > now();
    }

    /**
     * Scope pour les épisodes vus
     */
    public function scopeWatched($query)
    {
        return $query->where('is_watched', true);
    }

    /**
     * Scope pour les épisodes non vus
     */
    public function scopeUnwatched($query)
    {
        return $query->where('is_watched', false);
    }

    /**
     * Scope pour les épisodes diffusés
     */
    public function scopeAired($query)
    {
        return $query->where('air_date', '<=', now());
    }

    /**
     * Scope pour les épisodes à venir
     */
    public function scopeUpcoming($query)
    {
        return $query->where('air_date', '>', now());
    }

    /**
     * Scope pour les épisodes finaux
     */
    public function scopeFinale($query)
    {
        return $query->where('episode_type', 'finale');
    }

    /**
     * Scope pour les épisodes spéciaux
     */
    public function scopeSpecial($query)
    {
        return $query->where('episode_type', 'special');
    }
}