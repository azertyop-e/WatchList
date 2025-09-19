<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MediaCard extends Component
{
    public $media;
    public $showSaveButton;
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

    public function __construct($media, $showSaveButton = true)
    {
        $this->media = $media;
        $this->isObject = is_object($media);
        
        // Déterminer le type de média
        $this->mediaType = $this->isObject ? 'movie' : ($media['media_type'] ?? 'movie');
        
        // Désactiver le bouton de sauvegarde pour les séries pour l'instant
        $this->showSaveButton = $showSaveButton && $this->mediaType === 'movie';
        
        // Adapter les propriétés selon le type de média
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
        // Pour l'instant, utiliser les routes de films pour les séries aussi
        // TODO: Créer des routes spécifiques pour les séries
        return route('movie.detail', ['id' => $this->id]);
    }

    public function getSaveRoute()
    {
        // Pour l'instant, utiliser les routes de films pour les séries aussi
        // TODO: Créer des routes spécifiques pour les séries
        return route('movie.save');
    }

    public function getMarkSeenRoute()
    {
        // Pour l'instant, utiliser les routes de films pour les séries aussi
        // TODO: Créer des routes spécifiques pour les séries
        return route('movie.mark-seen');
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
