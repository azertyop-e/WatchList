<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gender extends Model
{
    protected $table = 'gender';
    protected $fillable = ['id', 'tmdb_id', 'name'];
    
    public function movies()
    {
        return $this->belongsToMany(Movie::class, 'gender_movie', 'gender_id', 'movie_id');
    }
}
