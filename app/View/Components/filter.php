<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Gender;
use App\Models\MovieModel;

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
        $this->hasActiveFilters = $this->selectedGenre != '' || $this->selectedType != 'film';
    }

    /**
     * Récupère uniquement les genres des films présents dans la liste
     */
    private function getAvailableGenres()
    {
        if ($this->context === 'seen') {
            return Gender::whereHas('movies', function($query) {
                $query->where('is_seen', true);
            })->orderBy('name')->get();
        } else {
            return Gender::whereHas('movies', function($query) {
                $query->where('is_seen', false);
            })->orderBy('name')->get();
        }
    }

    /**
     * Génère toutes les URLs nécessaires
     */
    private function generateUrls()
    {
        $urls = [
            'film' => $this->buildUrl($this->selectedGenre, 'film'),
            'serie' => $this->buildUrl($this->selectedGenre, 'serie'),
            
            'all_genres' => $this->buildUrl('', $this->selectedType),
            
            'remove_type' => $this->buildUrl($this->selectedGenre, 'film'),
            'remove_genre' => $this->buildUrl('', $this->selectedType),
            'remove_all' => $this->buildUrl('', 'film'),
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