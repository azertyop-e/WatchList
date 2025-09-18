<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CastCard extends Component
{
    public $actor;
    public $index;

    /**
     * Create a new component instance.
     */
    public function __construct($actor, $index = 0)
    {
        $this->actor = $actor;
        $this->index = $index;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.cast-card');
    }
}
