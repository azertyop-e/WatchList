<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Series extends Model
{
    protected $fillable = [
        'tmdb_id',
        'name',
        'original_name',
        'overview',
        'poster_path',
        'backdrop_path',
        'first_air_date',
        'last_air_date',
        'status',
        'type',
        'tagline',
        'vote_average',
        'vote_count',
        'popularity',
        'original_language',
        'homepage',
        'in_production',
        'number_of_episodes',
        'number_of_seasons',
        'episode_run_time',
        'languages',
        'origin_country',
        'is_watched'
    ];

    protected $casts = [
        'first_air_date' => 'date',
        'last_air_date' => 'date',
        'in_production' => 'boolean',
        'is_watched' => 'boolean',
        'episode_run_time' => 'array',
        'languages' => 'array',
        'origin_country' => 'array',
        'vote_average' => 'decimal:1',
        'popularity' => 'decimal:2',
    ];

    /**
     * Relation avec les saisons
     */
    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class)->orderBy('season_number');
    }

    /**
     * Relation avec les créateurs
     */
    public function creators(): BelongsToMany
    {
        return $this->belongsToMany(Creator::class, 'series_creators', 'series_id', 'creator_id')
                    ->withPivot('credit_id')
                    ->withTimestamps();
    }

    /**
     * Relation avec les chaînes de diffusion
     */
    public function networks(): BelongsToMany
    {
        return $this->belongsToMany(Network::class, 'series_networks', 'series_id', 'network_id')
                    ->withTimestamps();
    }

    /**
     * Relation avec les genres
     */
    public function genders(): BelongsToMany
    {
        return $this->belongsToMany(GendersModel::class, 'series_genders', 'series_id', 'gender_id')
                    ->withTimestamps();
    }

    /**
     * Relation avec les sociétés de production
     */
    public function productionCompanies(): BelongsToMany
    {
        return $this->belongsToMany(ProductionCompany::class, 'series_production_companies', 'series_id', 'production_company_id')
                    ->withTimestamps();
    }

    /**
     * Relation avec les pays de production
     */
    public function productionCountries(): BelongsToMany
    {
        return $this->belongsToMany(ProductionCountry::class, 'series_production_countries', 'series_id', 'production_country_id')
                    ->withTimestamps();
    }

    /**
     * Relation avec les langues parlées
     */
    public function spokenLanguages(): BelongsToMany
    {
        return $this->belongsToMany(SpokenLanguage::class, 'series_spoken_languages', 'series_id', 'spoken_language_id')
                    ->withTimestamps();
    }

    /**
     * Relation avec les acteurs
     */
    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(Actor::class, 'series_roles', 'series_id', 'actor_id')
                    ->withPivot('character_name', 'order')
                    ->withTimestamps()
                    ->orderBy('series_roles.order');
    }

    /**
     * Relation avec les rôles
     */
    public function roles(): HasMany
    {
        return $this->hasMany(SeriesRole::class, 'series_id')->orderBy('order');
    }

    /**
     * Accessor pour obtenir le dernier épisode diffusé
     */
    public function getLastEpisodeToAirAttribute()
    {
        return $this->seasons()
            ->with('episodes')
            ->get()
            ->pluck('episodes')
            ->flatten()
            ->where('air_date', '!=', null)
            ->sortByDesc('air_date')
            ->first();
    }

    /**
     * Accessor pour obtenir le prochain épisode à diffuser
     */
    public function getNextEpisodeToAirAttribute()
    {
        return $this->seasons()
            ->with('episodes')
            ->get()
            ->pluck('episodes')
            ->flatten()
            ->where('air_date', '>', now())
            ->sortBy('air_date')
            ->first();
    }

    /**
     * Scope pour les séries en cours de production
     */
    public function scopeInProduction($query)
    {
        return $query->where('in_production', true);
    }

    /**
     * Scope pour les séries terminées
     */
    public function scopeEnded($query)
    {
        return $query->where('status', 'Ended');
    }

    /**
     * Scope pour les séries vues
     */
    public function scopeWatched($query)
    {
        return $query->where('is_watched', true);
    }

    /**
     * Scope pour les séries non vues
     */
    public function scopeUnwatched($query)
    {
        return $query->where('is_watched', false);
    }
}