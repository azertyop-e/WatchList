<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Toast extends Component
{
    public $type;
    public $message;
    public $duration;
    public $position;
    public $dismissible;

    /**
     * Create a new component instance.
     */
    public function __construct($type, $message, $duration = 5000, $position = 'top-right', $dismissible = true)
    {
        $this->type = $type;
        $this->message = $message;
        $this->duration = $duration;
        $this->position = $position;
        $this->dismissible = $dismissible;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.toast', [
            'type' => $this->type,
            'message' => $this->message,
            'duration' => $this->duration,
            'position' => $this->position,
            'dismissible' => $this->dismissible
        ]);
    }
}
