<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $fillable = ['tmdb_id', 'name', 'poster_path', 'backdrop_path'];
    
    public function movies()
    {
        return $this->hasMany(Movie::class, 'collection_id');
    }
}
