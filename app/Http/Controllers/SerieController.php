<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Series;

class SerieController extends MediaController
{
    /**
     * Retourne le type de média pour les séries
     * 
     * @return string Le type de média
     */
    protected function getMediaType(): string
    {
        return 'tv';
    }

    /**
     * Retourne l'URL de recherche pour les séries
     * 
     * @param string $query Le terme de recherche
     * @param int $page Le numéro de page
     * 
     * @return string L'URL de recherche
     */
    protected function getSearchUrl(string $query, int $page): string
    {
        return "/search/tv?query=".urlencode($query)."&include_adult=false&language=fr-FR&page=".$page;
    }

    /**
     * Vérifie si un élément de résultat est une série
     * 
     * @param array $item L'élément à vérifier
     * 
     * @return bool True si l'élément est valide
     */
    protected function isValidMediaType(array $item): bool
    {
        return $item['media_type'] === 'tv';
    }

    /**
     * Retourne le nom de la vue pour l'affichage des résultats de recherche de séries
     * 
     * @return string Le nom de la vue
     */
    protected function getSearchView(): string
    {
        return 'series.search';
    }

    /**
     * Ajoute le statut de sauvegarde aux résultats de recherche de séries
     * 
     * @param array $results Les résultats à enrichir
     * 
     * @return array Les résultats enrichis
     */
    protected function addSavedStatusToResults(array $results): array
    {
        $savedSeriesIds = Series::pluck('tmdb_id')->toArray();
        
        foreach ($results as &$item) {
            $item['is_saved'] = in_array($item['id'], $savedSeriesIds);
        }
        
        return $results;
    }

    /**
     * Retourne l'URL pour récupérer les séries populaires
     * 
     * @param int $page Le numéro de page
     * 
     * @return string L'URL pour les séries populaires
     */
    protected function getPopularUrl(int $page): string
    {
        return "/tv/popular?language=fr-FR&include_adult=false&page=" . $page;
    }

    /**
     * Retourne l'URL pour récupérer les séries populaires en cartes
     * 
     * @return string L'URL pour les séries populaires en cartes
     */
    protected function getPopularCardsUrl(): string
    {
        return "/tv/popular?language=fr-FR&include_adult=false&page=1";
    }

    /**
     * Retourne l'URL pour récupérer les séries les mieux notées
     * 
     * @param int $page Le numéro de page
     * 
     * @return string L'URL pour les séries les mieux notées
     */
    protected function getTopRatedUrl(int $page): string
    {
        return "/tv/top_rated?language=fr-FR&include_adult=false&page=" . $page;
    }

    /**
     * Retourne le nom de la vue pour l'affichage des séries populaires
     * 
     * @return string Le nom de la vue
     */
    protected function getPopularView(): string
    {
        return 'series.popular';
    }

    /**
     * Retourne le nom de la vue pour l'affichage des séries populaires en cartes
     * 
     * @return string Le nom de la vue
     */
    protected function getPopularCardsView(): string
    {
        return 'series.popular-cards';
    }

    /**
     * Retourne le nom de la vue pour l'affichage des séries les mieux notées
     * 
     * @return string Le nom de la vue
     */
    protected function getTopView(): string
    {
        return 'series.top';
    }

    /**
     * Ajoute le statut de sauvegarde aux séries
     * 
     * @param array|null $mediaData Les données des séries depuis l'API TMDB
     * 
     * @return array|null Les données enrichies avec le statut de sauvegarde
     */
    protected function addSavedStatusToMedia(?array $mediaData): ?array
    {
        if (!$mediaData || !isset($mediaData['results'])) {
            return $mediaData;
        }
        
        $savedSeriesIds = Series::pluck('tmdb_id')->toArray();
        
        foreach ($mediaData['results'] as &$series) {
            $series['is_saved'] = in_array($series['id'], $savedSeriesIds);
        }
        
        return $mediaData;
    }

    /**
     * Retourne le nom de la variable de données pour les vues de séries
     * 
     * @return string Le nom de la variable
     */
    protected function getDataVariableName(): string
    {
        return 'seriesData';
    }

    /**
     * Affiche les détails d'une série
     * 
     * Cette méthode récupère les détails d'une série soit depuis la base de données locale
     * soit depuis l'API TMDB si elle n'est pas sauvegardée localement.
     * 
     * @param int $id L'ID TMDB de la série
     * 
     * @return \Illuminate\View\View La vue des détails de la série
     * 
     * @throws \Exception En cas d'erreur lors de l'appel à l'API TMDB
     */
    public function getMediaDetails(int $id)
    {
        $series = Series::with([
            'genders',
            'productionCompanies',
            'productionCountries', 
            'spokenLanguages',
            'networks',
            'roles.actor'
        ])->where('tmdb_id', $id)->first();
        
        if (!$series) {
            $url = "/tv/".$id."?language=fr-FR&include_adult=false";
            $seriesData = $this->getCurlData($url);
            
            $creditsUrl = "/tv/".$id."/credits?language=fr-FR";
            $creditsData = $this->getCurlData($creditsUrl);
            
            if (isset($creditsData['cast']) && is_array($creditsData['cast'])) {
                $seriesData['cast'] = array_slice($creditsData['cast'], 0, 20);
            }
            
            return view('series.detail', ['seriesData' => $seriesData]);
        }
        
        $seriesData = $series->toArray();
        
        $seriesData['genres'] = $series->genders->map(function($genre) {
            return ['name' => $genre->name];
        })->toArray();
        
        $seriesData['production_companies'] = $series->productionCompanies->map(function($company) {
            return [
                'name' => $company->name,
                'logo_path' => $company->logo_path
            ];
        })->toArray();
        
        $seriesData['production_countries'] = $series->productionCountries->map(function($country) {
            return ['name' => $country->name];
        })->toArray();
        
        $seriesData['spoken_languages'] = $series->spokenLanguages->map(function($language) {
            return [
                'name' => $language->name,
                'english_name' => $language->english_name
            ];
        })->toArray();
        
        $seriesData['networks'] = $series->networks->map(function($network) {
            return [
                'name' => $network->name,
                'logo_path' => $network->logo_path
            ];
        })->toArray();
        
        $seriesData['cast'] = $series->roles->map(function($role) {
            return [
                'name' => $role->actor->name,
                'character' => $role->character_name,
                'profile_path' => $role->actor->profile_path,
                'order' => $role->order
            ];
        })->toArray();
        
        return view('series.detail', ['seriesData' => $seriesData]);
    }
}
