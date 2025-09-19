<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionCompany extends Model
{
    protected $fillable = ['tmdb_id', 'name', 'logo_path', 'origin_country'];
    
    public function movies()
    {
        return $this->belongsToMany(Movie::class, 'movie_production_companies', 'production_company_id', 'movie_id');
    }

    /**
     * Relation avec les séries
     */
    public function series()
    {
        return $this->belongsToMany(Series::class, 'series_production_companies', 'production_company_id', 'series_id');
    }
}
