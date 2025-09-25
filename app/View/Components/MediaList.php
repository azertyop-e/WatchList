<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MediaList extends Component
{
    public $movies;
    public $series;
    public $moviesTitle;
    public $seriesTitle;
    public $showSaveButtons;
    public $maxItems;
    public $showRanking;

    /**
     * Create a new component instance.
     */
    public function __construct($movies = [], $series = [], $moviesTitle = 'Films', $seriesTitle = 'Séries', $showSaveButtons = true, $maxItems = null, $showRanking = false)
    {
        $this->movies = $movies;
        $this->series = $series;
        $this->moviesTitle = $moviesTitle;
        $this->seriesTitle = $seriesTitle;
        $this->showSaveButtons = $showSaveButtons;
        $this->maxItems = $maxItems;
        $this->showRanking = $showRanking;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.media-list');
    }
}
