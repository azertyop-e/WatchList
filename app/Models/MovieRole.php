<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovieRole extends Model
{
    protected $fillable = [
        'movie_id', 'actor_id', 'character_name', 'order'
    ];

    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movie_id');
    }

    public function actor()
    {
        return $this->belongsTo(Actor::class, 'actor_id');
    }
}
