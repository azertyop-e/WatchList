<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    protected $fillable = [
        'tmdb_id', 'name', 'profile_path', 'birthday', 'place_of_birth', 
        'biography', 'known_for_department', 'popularity'
    ];

    public function movies()
    {
        return $this->belongsToMany(MovieModel::class, 'movie_roles', 'actor_id', 'movie_id')
                    ->withPivot('character_name', 'order')
                    ->withTimestamps();
    }

    public function roles()
    {
        return $this->hasMany(MovieRole::class, 'actor_id');
    }
}
