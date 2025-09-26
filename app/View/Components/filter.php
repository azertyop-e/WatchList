<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Gender;

class Filter extends Component
{
    public $genres;
    public $selectedGenre;
    public $currentRoute;
    public $currentParams;
    public $urls;
    public $context;

    /**
     * Create a new component instance.
     */
    public function __construct($selectedGenre = '', $currentRoute = 'home', $currentParams = [], $context = 'all')
    {
        $this->selectedGenre = $selectedGenre;
        $this->currentRoute = $currentRoute;
        $this->currentParams = $currentParams;
        $this->context = $context;
        
        $this->genres = $this->getAvailableGenres();
        $this->urls = $this->generateUrls();
    }

    /**
     * Récupère les genres des films et séries présents dans la liste
     * 
     * @return array Les genres disponibles pour le filtrage
     */
    private function getAvailableGenres(): array
    {
        $genres = Gender::where(function ($query) {
            $query->whereHas('movies')->orWhereHas('series');
        })
        ->orderBy('name')
        ->get()
        ->unique('id')
        ->values()
        ->all();
        
        return $genres;
    }

    /**
     * Génère toutes les URLs nécessaires
     * 
     * @return array Les URLs générées
     */
    private function generateUrls(): array
    {
        $urls = [
            'all_genres' => $this->buildUrl(''),
        ];
        
        foreach ($this->genres as $genre) {
            $urls['genre_' . $genre->id] = $this->buildUrl($genre->id);
        }
        return $urls;
    }

    /**
     * Génère une URL avec les paramètres de filtrage
     * 
     * @param string|null $genre Le genre à filtrer
     * @return string L'URL générée
     */
    private function buildUrl(?string $genre = null): string
    {
        $params = $this->currentParams;
        
        if (!empty($genre)) {
            $params['genre'] = $genre;
        } else {
            unset($params['genre']);
        }
        
        $params = array_filter($params, fn($value) => $value !== '' && $value !== null);
        
        return route($this->currentRoute, $params);
    }

    /**
     * Get the view / contents that represent the component
     * 
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render(): View|Closure|string
    {
        return view('components.filter');
    }
}