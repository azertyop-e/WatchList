<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    protected $fillable = [
        'tmdb_id',
        'series_id',
        'name',
        'overview',
        'poster_path',
        'air_date',
        'episode_count',
        'season_number',
        'vote_average'
    ];

    protected $casts = [
        'air_date' => 'date',
        'vote_average' => 'decimal:1',
    ];

    /**
     * Relation avec la série parente
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    /**
     * Relation avec les épisodes
     */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class)->orderBy('episode_number');
    }

    /**
     * Accessor pour vérifier si la saison est complètement vue
     */
    public function getIsWatchedAttribute(): bool
    {
        return $this->episodes()->where('is_watched', false)->count() === 0;
    }

    /**
     * Accessor pour obtenir le nombre d'épisodes vus
     */
    public function getWatchedEpisodesCountAttribute(): int
    {
        return $this->episodes()->where('is_watched', true)->count();
    }

    /**
     * Accessor pour obtenir le pourcentage de progression
     */
    public function getProgressPercentageAttribute(): float
    {
        $totalEpisodes = $this->episodes()->count();
        if ($totalEpisodes === 0) {
            return 0;
        }
        
        $watchedEpisodes = $this->getWatchedEpisodesCountAttribute();
        return round(($watchedEpisodes / $totalEpisodes) * 100, 2);
    }

    /**
     * Scope pour les saisons avec des épisodes
     */
    public function scopeWithEpisodes($query)
    {
        return $query->whereHas('episodes');
    }

    /**
     * Scope pour les saisons vues
     */
    public function scopeWatched($query)
    {
        return $query->whereDoesntHave('episodes', function ($q) {
            $q->where('is_watched', false);
        });
    }

    /**
     * Scope pour les saisons non vues
     */
    public function scopeUnwatched($query)
    {
        return $query->whereHas('episodes', function ($q) {
            $q->where('is_watched', false);
        });
    }
}