<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SearchBar extends Component
{
    public $placeholder;
    public $value;
    public $action;
    public $method;

    /**
     * Create a new component instance.
     */
    public function __construct($placeholder = 'Rechercher des films...', $value = '', $action = '', $method = 'GET')
    {
        $this->placeholder = $placeholder;
        $this->value = $value;
        $this->action = $action;
        $this->method = $method;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.search-bar');
    }
}
