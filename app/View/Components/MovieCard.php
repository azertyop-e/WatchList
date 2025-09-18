<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MovieCard extends Component
{
    public $movie;
    public $showSaveButton;
    public $title;
    public $posterPath;
    public $voteAverage;
    public $releaseDate;
    public $overview;
    public $id;
    public $isObject;
    public $isSaved = false;

    public function __construct($movie, $showSaveButton = true)
    {
        $this->movie = $movie;
        $this->showSaveButton = $showSaveButton;
        $this->isObject = is_object($movie);
        $this->title = $this->isObject ? $movie->title : ($movie['title'] ?? '');
        $this->posterPath = $this->isObject ? $movie->poster_path : ($movie['poster_path'] ?? null);
        $this->voteAverage = $this->isObject ? $movie->vote_average : ($movie['vote_average'] ?? null);
        $this->releaseDate = $this->isObject ? $movie->release_date : ($movie['release_date'] ?? null);
        $this->overview = $this->isObject ? $movie->overview : ($movie['overview'] ?? '');
        $this->id = $this->isObject ? $movie->id : ($movie['id'] ?? null);
        $this->isSaved = $this->isObject ? false : (isset($movie['is_saved']) ? $movie['is_saved'] : false);

    }

    public function getLocalPosterUrl()
    {
        return \App\Helpers\ImageHelper::getPosterUrl($this->posterPath);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.movie-card');
    }
}
