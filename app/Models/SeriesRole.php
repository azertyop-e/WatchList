<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeriesRole extends Model
{
    protected $table = 'series_roles';
    
    protected $fillable = [
        'series_id',
        'actor_id',
        'character_name',
        'order'
    ];

    /**
     * Relation avec la série
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    /**
     * Relation avec l'acteur
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(Actor::class);
    }
}