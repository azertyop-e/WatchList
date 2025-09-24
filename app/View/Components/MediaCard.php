<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MediaCard extends Component
{
    public $media;
    public $showSaveButton;
    public $showMarkUnseenButton;
    public $title;
    public $posterPath;
    public $voteAverage;
    public $releaseDate;
    public $overview;
    public $id;
    public $isObject;
    public $isSaved = false;
    public $mediaType;
    public $firstAirDate;

    public function __construct($media, $showSaveButton = true, $showMarkUnseenButton = false)
    {
        $this->media = $media;
        $this->isObject = is_object($media);
        
        if ($this->isObject) {
            $this->mediaType = get_class($media) === 'App\Models\Series' ? 'tv' : 'movie';
        } else {
            if (isset($media['media_type'])) {
                // Normaliser 'series' vers 'tv' pour la cohérence
                $this->mediaType = $media['media_type'] === 'series' ? 'tv' : $media['media_type'];
            } elseif (isset($media['name']) && !isset($media['title'])) {
                $this->mediaType = 'tv';
            } elseif (isset($media['first_air_date']) && !isset($media['release_date'])) {
                $this->mediaType = 'tv'; 
            } else {
                $this->mediaType = 'movie';
            }
        }
        
        $this->showSaveButton = $showSaveButton;
        $this->showMarkUnseenButton = $showMarkUnseenButton;
        
        if ($this->mediaType === 'tv') {
            $this->title = $this->isObject ? $media->name : ($media['name'] ?? '');
            $this->releaseDate = $this->isObject ? $media->first_air_date : ($media['first_air_date'] ?? null);
            $this->firstAirDate = $this->releaseDate;
        } else {
            $this->title = $this->isObject ? $media->title : ($media['title'] ?? '');
            $this->releaseDate = $this->isObject ? $media->release_date : ($media['release_date'] ?? null);
        }
        
        $this->posterPath = $this->isObject ? $media->poster_path : ($media['poster_path'] ?? null);
        $this->voteAverage = $this->isObject ? $media->vote_average : ($media['vote_average'] ?? null);
        $this->overview = $this->isObject ? $media->overview : ($media['overview'] ?? '');
        $this->id = $this->isObject ? $media->id : ($media['id'] ?? null);
        $this->isSaved = $this->isObject ? false : (isset($media['is_saved']) ? $media['is_saved'] : false);
    }

    public function getLocalPosterUrl()
    {
        return \App\Helpers\ImageHelper::getPosterUrl($this->posterPath);
    }

    public function getDetailRoute()
    {
        if ($this->mediaType === 'tv') {
            return route('series.detail', ['id' => $this->id]);
        }
        return route('movie.detail', ['id' => $this->id]);
    }

    public function getSaveRoute()
    {
        if ($this->mediaType === 'tv') {
            return route('series.save');
        }
        return route('movie.save');
    }

    public function getMarkSeenRoute()
    {
        if ($this->mediaType === 'tv') {
            return route('series.mark-seen');
        }
        return route('movie.mark-seen');
    }

    public function getMarkUnseenRoute()
    {
        if ($this->mediaType === 'tv') {
            return route('series.mark-unseen');
        }
        return route('movie.mark-unseen');
    }

    public function getMediaTypeLabel()
    {
        return $this->mediaType === 'tv' ? 'Série' : 'Film';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.media-card');
    }
}
