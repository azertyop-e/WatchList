<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Gender;
use App\Models\Movie;

class filter extends Component
{
    public $genres;
    public $selectedGenre;
    public $selectedType;
    public $currentRoute;
    public $currentParams;
    public $urls;
    public $hasActiveFilters;
    public $context = 'unseen'|'seen';

    /**
     * Create a new component instance.
     */
    public function __construct($selectedGenre = '', $selectedType = 'film', $currentRoute = 'home', $currentParams = [], $context = 'unseen')
    {
        $this->selectedGenre = $selectedGenre;
        $this->selectedType = $selectedType;
        $this->currentRoute = $currentRoute;
        $this->currentParams = $currentParams;
        $this->context = $context;
        
        $this->genres = $this->getAvailableGenres();
        
        $this->urls = $this->generateUrls();
        $this->hasActiveFilters = $this->selectedGenre != '' || ($this->selectedType != 'all');
    }

    /**
     * Récupère les genres des films et séries présents dans la liste selon le type sélectionné
     */
    private function getAvailableGenres()
    {
        $genres = collect();
        
        // Si le type est 'all', on récupère les genres des deux types
        if ($this->selectedType === 'all') {
            if ($this->context === 'seen') {
                $movieGenres = Gender::whereHas('movies', function($query) {
                    $query->where('is_seen', true);
                })->get();
                
                $seriesGenres = Gender::whereHas('series', function($query) {
                    $query->where('is_watched', true);
                })->get();
            } else {
                $movieGenres = Gender::whereHas('movies', function($query) {
                    $query->where('is_seen', false);
                })->get();
                
                $seriesGenres = Gender::whereHas('series', function($query) {
                    $query->where('is_watched', false);
                })->get();
            }
            
            $genres = $movieGenres->merge($seriesGenres);
        } 
        // Si le type est 'film', on récupère seulement les genres des films
        elseif ($this->selectedType === 'film') {
            if ($this->context === 'seen') {
                $genres = Gender::whereHas('movies', function($query) {
                    $query->where('is_seen', true);
                })->get();
            } else {
                $genres = Gender::whereHas('movies', function($query) {
                    $query->where('is_seen', false);
                })->get();
            }
        } 
        // Si le type est 'serie', on récupère seulement les genres des séries
        elseif ($this->selectedType === 'serie') {
            if ($this->context === 'seen') {
                $genres = Gender::whereHas('series', function($query) {
                    $query->where('is_watched', true);
                })->get();
            } else {
                $genres = Gender::whereHas('series', function($query) {
                    $query->where('is_watched', false);
                })->get();
            }
        }
        
        return $genres->unique('id')->sortBy('name');
    }

    /**
     * Génère toutes les URLs nécessaires
     */
    private function generateUrls()
    {
        $urls = [
            'all' => $this->buildUrl($this->selectedGenre, 'all'),
            'film' => $this->buildUrl($this->selectedGenre, 'film'),
            'serie' => $this->buildUrl($this->selectedGenre, 'serie'),
            
            'all_genres' => $this->buildUrl('', $this->selectedType),
            
            'remove_type' => $this->buildUrl($this->selectedGenre, 'all'),
            'remove_genre' => $this->buildUrl('', $this->selectedType),
            'remove_all' => $this->buildUrl('', 'all'),
        ];
        
        foreach ($this->genres as $genre) {
            $urls['genre_' . $genre->id] = $this->buildUrl($genre->id, $this->selectedType);
        }
        
        return $urls;
    }

    /**
     * Génère une URL avec les paramètres de filtrage
     */
    private function buildUrl($genre = null, $type = null)
    {
        $params = $this->currentParams;
        
        if ($genre !== null) {
            $params['genre'] = $genre;
        }
        
        if ($type !== null) {
            $params['type'] = $type;
        }
        
        $params = array_filter($params, function($value) {
            return $value !== '' && $value !== null;
        });
        
        return route($this->currentRoute, $params);
    }

    /**
     * Get the view / contents that represent the component
     */
    public function render(): View|Closure|string
    {
        return view('components.filter');
    }
}