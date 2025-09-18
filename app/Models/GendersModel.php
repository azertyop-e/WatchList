<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GendersModel extends Model
{
    protected $table = 'gendersModel';
    protected $fillable = ['id', 'tmdb_id', 'name'];
    
    public function movies()
    {
        return $this->belongsToMany(MovieModel::class, 'gender_movie', 'gender_id', 'movie_id');
    }
}
