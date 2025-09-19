<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpokenLanguage extends Model
{
    protected $fillable = ['iso_639_1', 'name', 'english_name'];
    
    public function movies()
    {
        return $this->belongsToMany(Movie::class, 'movie_spoken_languages', 'spoken_language_id', 'movie_id');
    }

    /**
     * Relation avec les séries
     */
    public function series()
    {
        return $this->belongsToMany(Series::class, 'series_spoken_languages', 'spoken_language_id', 'series_id');
    }
}
