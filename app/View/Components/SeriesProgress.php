<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Series;

class SeriesProgress extends Component
{
    public Series $series;
    public $nextEpisode;
    public $watchedEpisodes;
    public $totalEpisodes;
    public $isCompleted;

    /**
     * Create a new component instance.
     */
    public function __construct(Series $series)
    {
        $this->series = $series;
        $this->nextEpisode = $series->getNextEpisodeToWatch();
        $this->watchedEpisodes = $series->getWatchedEpisodesCount();
        $this->totalEpisodes = $series->getTotalEpisodesCount();
        $this->isCompleted = $this->watchedEpisodes === $this->totalEpisodes && $this->totalEpisodes > 0;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.series-progress');
    }
}
