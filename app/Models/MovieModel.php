<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovieModel extends Model
{
    protected $table = 'movie';
    protected $fillable = [
        'title', 'overview', 'poster_path', 'release_date', 'vote_average', 'is_seen',
        'original_title', 'tagline', 'vote_count', 'runtime', 'original_language',
        'status', 'budget', 'revenue', 'popularity', 'collection_id'
    ];

    public function genders()
    {
        return $this->belongsToMany(GendersModel::class, 'gender_movie', 'movie_id', 'gender_id');
    }

    public function productionCompanies()
    {
        return $this->belongsToMany(ProductionCompany::class, 'movie_production_companies', 'movie_id', 'production_company_id');
    }

    public function productionCountries()
    {
        return $this->belongsToMany(ProductionCountry::class, 'movie_production_countries', 'movie_id', 'production_country_id');
    }

    public function spokenLanguages()
    {
        return $this->belongsToMany(SpokenLanguage::class, 'movie_spoken_languages', 'movie_id', 'spoken_language_id');
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class, 'collection_id');
    }

    public function actors()
    {
        return $this->belongsToMany(Actor::class, 'movie_roles', 'movie_id', 'actor_id')
                    ->withPivot('character_name', 'order')
                    ->withTimestamps()
                    ->orderBy('movie_roles.order');
    }

    public function roles()
    {
        return $this->hasMany(MovieRole::class, 'movie_id')->orderBy('order');
    }
}
