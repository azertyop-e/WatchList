<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionCountry extends Model
{
    protected $fillable = ['iso_3166_1', 'name'];
    
    public function movies()
    {
        return $this->belongsToMany(MovieModel::class, 'movie_production_countries', 'production_country_id', 'movie_id');
    }
}
