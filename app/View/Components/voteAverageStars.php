<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class voteAverageStars extends Component
{
    public $voteAverage;
    
    /**
     * Create a new component instance.
     */
    public function __construct($voteAverage)
    {
        $this->voteAverage = $voteAverage;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.vote-average-stars');
    }
}
