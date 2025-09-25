<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Series;
use App\Models\Season;
use App\Models\Episode;
use App\Models\Actor;
use App\Models\Creator;
use App\Models\Network;
use App\Models\Gender;
use App\Models\ProductionCompany;
use App\Models\ProductionCountry;
use App\Models\SpokenLanguage;
use App\Models\SeriesRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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
     * Sauvegarde une saison complète d'une série (saison + épisodes)
     * 
     * Cette méthode utilise saveSeasonEpisodes pour sauvegarder une saison
     * complète avec tous ses épisodes depuis l'API TMDB.
     * 
     * @param int $seriesTmdbId L'ID TMDB de la série
     * @param int $seasonNumber Le numéro de la saison
     * 
     * @return \Illuminate\Http\RedirectResponse Redirection avec message de succès ou d'erreur
     */
    public function saveSeries(Request $request)
    {
        try {
            $seriesId = $request->input('series_id');
            
            if (!$seriesId || !is_numeric($seriesId)) {
                return redirect()->back()->with('error', 'ID de série manquant ou invalide.');
            }

            $existingSeries = Series::where('tmdb_id', $seriesId)->first();
            if ($existingSeries) {
                return redirect()->back()->with('error', 'Cette série est déjà dans votre liste.');
            }

            $seriesData = $this->getCurlData("/tv/{$seriesId}?language=fr-FR&include_adult=false");
            $creditsData = $this->getCurlData("/tv/{$seriesId}/credits?language=fr-FR");

            if (!$seriesData) {
                return redirect()->back()->with('error', 'Impossible de récupérer les données de la série depuis l\'API TMDB.');
            }

            DB::beginTransaction();

            try {
                $series = $this->createSeries($seriesData);
                $this->saveSeriesGenres($series, $seriesData['genres'] ?? []);
                $this->saveSeriesProductionCompanies($series, $seriesData['production_companies'] ?? []);
                $this->saveSeriesProductionCountries($series, $seriesData['production_countries'] ?? []);
                $this->saveSeriesSpokenLanguages($series, $seriesData['spoken_languages'] ?? []);
                $this->saveSeriesNetworks($series, $seriesData['networks'] ?? []);
                $this->saveSeriesCreators($series, $seriesData['created_by'] ?? []);
                $this->saveSeriesCast($series, $creditsData['cast'] ?? []);
                $this->downloadSeriesPoster($series, $seriesData['poster_path'] ?? null);
                
                // Sauvegarder toutes les saisons et épisodes
                $this->saveAllSeasons($series->tmdb_id, false, 20, false);

                    DB::commit();
                return redirect()->back()->with('success', "Série '{$series->name}' ajoutée avec succès avec toutes ses informations.");

            } catch (\Exception $transactionError) {
                DB::rollBack();
                throw $transactionError;
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de la sauvegarde de la série', [
                'series_id' => $seriesId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Une erreur est survenue lors de la sauvegarde de la série: ' . $e->getMessage());
        }
    }

    /**
     * Sauvegarde toutes les saisons d'une série avec leurs épisodes
     */
    //? À revoir
    public function saveAllSeasons(int $seriesTmdbId, bool $includeSpecials = false, int $maxSeasons = 20, bool $returnResponse = true)
    {
        try {
            $series = Series::where('tmdb_id', $seriesTmdbId)->first();
            if (!$series) {
                if ($returnResponse) {
                    return redirect()->back()->with('error', 'Série non trouvée en base de données.');
                }
                throw new \Exception('Série non trouvée en base de données.');
            }

            $url = "/tv/{$seriesTmdbId}?language=fr-FR";
            $seriesData = $this->getCurlData($url);

            if (!$seriesData || !isset($seriesData['seasons']) || !is_array($seriesData['seasons'])) {
                if ($returnResponse) {
                    return redirect()->back()->with('error', 'Impossible de récupérer les données de la série depuis l\'API TMDB.');
                }
                throw new \Exception('Impossible de récupérer les données de la série depuis l\'API TMDB.');
            }

            $seasonsToProcess = collect($seriesData['seasons'])
                ->filter(function ($seasonData) use ($includeSpecials) {
                    $seasonNumber = $seasonData['season_number'] ?? -1;
                    if (!$includeSpecials && $seasonNumber === 0) {
                        return false;
                    }
                    return $seasonNumber >= 0 && isset($seasonData['episode_count']);
                })
                ->sortBy('season_number')
                ->take($maxSeasons)
                ->values();

            if ($seasonsToProcess->isEmpty()) {
                if ($returnResponse) {
                    return redirect()->back()->with('warning', 'Aucune saison valide trouvée pour cette série.');
                }
                return; // Pas d'erreur, juste aucune saison à traiter
            }

            $seasonsProcessed = 0;
            $seasonsFailed = 0;
            $totalSeasons = $seasonsToProcess->count();
            $errors = [];

                foreach ($seasonsToProcess as $seasonData) {
                    $seasonNumber = $seasonData['season_number'];
                    
                    try {
                    $result = $this->saveSeasonEpisodesInternal($seriesTmdbId, $seasonNumber);
                        
                    if ($result['success']) {
                            $seasonsProcessed++;
                        } else {
                        $errors[] = "Saison {$seasonNumber}: {$result['error']}";
                            $seasonsFailed++;
                        }
                        
                    } catch (\Exception $seasonError) {
                        $errors[] = "Saison {$seasonNumber}: " . $seasonError->getMessage();
                        $seasonsFailed++;
                }
            }

            if ($returnResponse) {
                $message = "Série {$series->name} mise à jour avec succès. ";
                $message .= "{$seasonsProcessed} saison(s) traitée(s) sur {$totalSeasons}.";

                if ($seasonsFailed > 0) {
                    $message .= " {$seasonsFailed} saison(s) ont échoué.";
                }

                if ($seasonsProcessed > 0) {
                    return redirect()->back()->with('success', $message);
                } else {
                    return redirect()->back()->with('error', $message);
                }
            }

        } catch (\Exception $e) {
            Log::error('Erreur critique lors de la sauvegarde de toutes les saisons', [
                'series_id' => $seriesTmdbId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($returnResponse) {
            return redirect()->back()->with('error', 'Une erreur critique est survenue lors de la sauvegarde des saisons: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Sauvegarde une saison complète d'une série
     */
    //? À revoir
    public function saveSeason(int $seriesTmdbId, int $seasonNumber)
    {
        return $this->saveSeasonEpisodes($seriesTmdbId, $seasonNumber);
    }

    /**
     * Enregistre les épisodes d'une saison donnée d'une série depuis l'API TMDB
     */
    //? À revoir
    public function saveSeasonEpisodes(int $seriesId, int $seasonNumber)
    {
        try {
            $series = Series::where('tmdb_id', $seriesId)->first();
            if (!$series) {
                return redirect()->back()->with('error', 'Série non trouvée en base de données.');
            }

            $url = "/tv/{$seriesId}/season/{$seasonNumber}?language=fr-FR";
            $seasonData = $this->getCurlData($url);

            if (!$seasonData || !isset($seasonData['episodes'])) {
                return redirect()->back()->with('error', 'Impossible de récupérer les données de la saison depuis l\'API TMDB.');
            }

            $season = $this->createOrUpdateSeason($series, $seasonData, $seasonNumber);
            $episodeResults = $this->processSeasonEpisodes($season, $seasonData['episodes']);

            $message = "Saison {$seasonNumber} de {$series->name} mise à jour avec succès. ";
            if ($episodeResults['created'] > 0) {
                $message .= "{$episodeResults['created']} épisode(s) créé(s). ";
            }
            if ($episodeResults['updated'] > 0) {
                $message .= "{$episodeResults['updated']} épisode(s) mis à jour. ";
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement des épisodes de la saison', [
                'series_id' => $seriesId,
                'season_number' => $seasonNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Une erreur est survenue lors de l\'enregistrement des épisodes: ' . $e->getMessage());
        }
    }

    /**
     * Version interne de saveSeasonEpisodes qui retourne un tableau au lieu d'une redirection
     */
    //? À revoir
    private function saveSeasonEpisodesInternal(int $seriesId, int $seasonNumber): array
    {
        try {
            $series = Series::where('tmdb_id', $seriesId)->first();
            if (!$series) {
                return ['success' => false, 'error' => 'Série non trouvée en base de données.'];
            }

            $url = "/tv/{$seriesId}/season/{$seasonNumber}?language=fr-FR";
            $seasonData = $this->getCurlData($url);

            if (!$seasonData || !isset($seasonData['episodes'])) {
                return ['success' => false, 'error' => 'Impossible de récupérer les données de la saison depuis l\'API TMDB.'];
            }

            $season = $this->createOrUpdateSeason($series, $seasonData, $seasonNumber);
            $this->processSeasonEpisodes($season, $seasonData['episodes']);

            return ['success' => true, 'message' => "Saison {$seasonNumber} sauvegardée avec succès."];

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement des épisodes de la saison', [
                'series_id' => $seriesId,
                'season_number' => $seasonNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Marque une série comme vue
     */
    public function markAsSeen(Request $request)
    {
        $seriesId = $request->input('series_id');
        $series = Series::find($seriesId);
        
        if ($series) {
            $series->is_watched = true;
            $series->save();
            return redirect()->back()->with('success', 'Série marquée comme vue');
        }
        
        return redirect()->back()->with('error', 'Série non trouvée');
    }

    /**
     * Marque une série comme non vue
     */
    public function markAsUnseen(Request $request)
    {
        $seriesId = $request->input('series_id');
        $series = Series::find($seriesId);
        
        if ($series) {
            $series->is_watched = false;
            $series->save();
            return redirect()->back()->with('success', 'Série marquée comme non vue');
        }
        
        return redirect()->back()->with('error', 'Série non trouvée');
    }

    /**
     * Récupère les séries vues par l'utilisateur avec filtrage par genre
     * 
     * Cette méthode récupère toutes les séries sauvegardées en base de données
     * qui ont été marquées comme vues, avec possibilité de filtrer par genre.
     * Elle récupère également la liste des genres disponibles pour le filtrage.
     * 
     * @param Request $request Contient le paramètre optionnel 'genre' pour le filtrage
     * 
     * @return \Illuminate\View\View Vue 'series.seen' avec les séries vues et genres
     */
    public function getSeenMedia(Request $request)
    {
        $query = Series::with('genders')->where('is_watched', true);
        
        if ($request->has('genre') && $request->input('genre') != '') {
            $genreId = $request->input('genre');
            $query->whereHas('genders', function($q) use ($genreId) {
                $q->where('gender.id', $genreId);
            });
        }
        
        $selectedType = $request->input('type', 'serie');
        
        $series = $query->orderBy('updated_at', 'desc')->get();
        
        $genres = Gender::whereHas('series', function($q) {
            $q->where('is_watched', true);
        })->orderBy('name')->get();
        
        return view('series.seen', [
            'series' => $series,
            'genres' => $genres,
            'selectedGenre' => $request->input('genre', ''),
            'selectedType' => $selectedType
        ]);
    }

    /**
     * Récupère les détails d'une série
     * 
     * Cette méthode récupère les détails complets d'une série, soit depuis
     * la base de données locale si elle est sauvegardée, soit depuis l'API TMDB.
     * Elle inclut les genres, sociétés de production, pays, langues,
     * réseaux, créateurs et casting.
     * 
     * @param int $id L'identifiant TMDB de la série
     * 
     * @return \Illuminate\View\View Vue 'series.detail' avec les détails de la série
     * 
     * @throws \Exception En cas d'erreur lors de l'appel à l'API TMDB
     */
    public function getMediaDetails(int $id)
    {
        $series = Series::with(['genders', 'productionCompanies', 'productionCountries', 'spokenLanguages', 'networks', 'creators', 'roles.actor', 'seasons.episodes'])
                        ->find($id);
        
        if (!$series) {
            // Récupération des données depuis l'API TMDB
            $url = "/tv/".$id."?language=fr-FR&include_adult=false";
            $seriesData = $this->getCurlData($url);
            
            $creditsUrl = "/tv/".$id."/credits?language=fr-FR";
            $creditsData = $this->getCurlData($creditsUrl);
            
            if (isset($creditsData['cast']) && is_array($creditsData['cast'])) {
                $seriesData['cast'] = array_slice($creditsData['cast'], 0, 20);
            }
            
            $seriesData = $this->loadSeasonsWithEpisodes($seriesData, $id);
            $seriesData = $this->transformApiDataForTemplate($seriesData, $id);
            
            $seriesData['is_saved'] = false;
            
            return view('series.detail', ['seriesData' => $seriesData]);
        }

        // Conversion des données de la base vers le format attendu par la vue
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
        
        $seriesData['created_by'] = $series->creators->map(function($creator) {
            return [
                'name' => $creator->name,
                'profile_path' => $creator->profile_path
            ];
        })->toArray();
        
        // Ajout du casting depuis les rôles
        $seriesData['cast'] = $series->roles->map(function($role) {
            return [
                'name' => $role->actor->name,
                'character' => $role->character_name,
                'profile_path' => $role->actor->profile_path,
                'order' => $role->order
            ];
        })->sortBy('order')->values()->toArray();

        // Récupération du prochain épisode à regarder
        $nextEpisodeToWatch = $series->getNextEpisodeToWatch();
        if ($nextEpisodeToWatch) {
            $seriesData['next_episode_to_watch'] = $nextEpisodeToWatch;
        }

        // Ajout de l'information que la série est enregistrée
        $seriesData['is_saved'] = true;

        // Transformation des données pour qu'elles correspondent au format attendu par le template
        $seriesData = $this->transformDatabaseDataForTemplate($seriesData, $series);

        return view('series.detail', ['seriesData' => $seriesData]);
    }

    /**
     * Charge les détails des saisons avec leurs épisodes depuis l'API TMDB
     * 
     * @param array $seriesData Les données de base de la série
     * @param int $tmdbId L'ID TMDB de la série
     * 
     * @return array Les données de la série avec les saisons et épisodes détaillés
     */
    private function loadSeasonsWithEpisodes(array $seriesData, int $tmdbId): array
    {
        if (!isset($seriesData['seasons']) || !is_array($seriesData['seasons'])) {
            return $seriesData;
        }
        
        $seasonsWithEpisodes = [];
        
        foreach ($seriesData['seasons'] as $season) {
            $seasonNumber = $season['season_number'] ?? 0;
            
            // Ignorer la saison 0 (spéciales) et les saisons futures
            if ($seasonNumber <= 0) {
                continue;
            }
            
            // Récupération des détails de la saison avec épisodes
            $seasonUrl = "/tv/".$tmdbId."/season/".$seasonNumber."?language=fr-FR";
            $seasonData = $this->getCurlData($seasonUrl);
            
            if ($seasonData && isset($seasonData['episodes'])) {
                // Ajout des épisodes à la saison
                $season['episodes'] = $seasonData['episodes'];
                $seasonsWithEpisodes[] = $season;
            } else {
                // Si pas de détails, garder la saison de base
                $seasonsWithEpisodes[] = $season;
            }
        }
        
        $seriesData['seasons'] = $seasonsWithEpisodes;
        return $seriesData;
    }

    /**
     * Transforme les données de la base de données pour qu'elles correspondent au format attendu par le template
     * 
     * @param array $seriesData Les données de la série depuis la base
     * @param \App\Models\Series $series L'objet série Eloquent
     * 
     * @return mixed Les données transformées sous forme d'objet hybride
     */
    private function transformDatabaseDataForTemplate(array $seriesData, $series)
    {
        // Création d'un objet hybride qui peut être utilisé comme tableau ET comme objet
        $hybridObject = new \ArrayObject($seriesData, \ArrayObject::ARRAY_AS_PROPS);
        
        // Ajout des saisons depuis la relation Eloquent
        $hybridObject->seasons = $series->seasons;
        
        return $hybridObject;
    }

    /**
     * Transforme les données de l'API TMDB pour qu'elles correspondent au format attendu par le template
     * 
     * @param array $apiData Les données de l'API TMDB
     * @param int $tmdbId L'ID TMDB de la série
     * 
     * @return mixed Les données transformées sous forme d'objet hybride
     */
    //? A revoir
    private function transformApiDataForTemplate(array $apiData, int $tmdbId)
    {
        // Création d'un tableau qui peut aussi être utilisé comme objet
        $transformedData = $apiData;
        
        // Ajout de l'ID TMDB
        $transformedData['tmdb_id'] = $tmdbId;
        
        // Transformation des saisons pour qu'elles soient compatibles avec le template
        if (isset($apiData['seasons']) && is_array($apiData['seasons'])) {
            $seasons = collect($apiData['seasons'])->map(function($season) {
                $seasonObject = new \stdClass();
                $seasonObject->id = $season['id'] ?? null;
                $seasonObject->name = $season['name'] ?? 'Saison ' . ($season['season_number'] ?? 0);
                $seasonObject->season_number = $season['season_number'] ?? 0;
                $seasonObject->episode_count = $season['episode_count'] ?? 0;
                $seasonObject->overview = $season['overview'] ?? '';
                $seasonObject->poster_path = $season['poster_path'] ?? null;
                $seasonObject->air_date = $season['air_date'] ?? null;
                
                // Transformation des épisodes si disponibles
                if (isset($season['episodes']) && is_array($season['episodes'])) {
                    $episodes = collect($season['episodes'])->map(function($episode) {
                        $episodeObject = new \stdClass();
                        $episodeObject->id = $episode['id'] ?? null;
                        $episodeObject->name = $episode['name'] ?? '';
                        $episodeObject->episode_number = $episode['episode_number'] ?? 0;
                        $episodeObject->overview = $episode['overview'] ?? '';
                        $episodeObject->still_path = $episode['still_path'] ?? null;
                        $episodeObject->air_date = $episode['air_date'] ?? null;
                        $episodeObject->runtime = $episode['runtime'] ?? null;
                        $episodeObject->vote_average = $episode['vote_average'] ?? 0;
                        $episodeObject->vote_count = $episode['vote_count'] ?? 0;
                        $episodeObject->is_watched = false; // Par défaut non vu
                        return $episodeObject;
                    });
                    $seasonObject->episodes = $episodes;
                } else {
                    $seasonObject->episodes = collect([]);
                }
                
                return $seasonObject;
            });
            
            $transformedData['seasons'] = $seasons;
        } else {
            $transformedData['seasons'] = collect([]);
        }
        
        // Transformation des genres
        if (isset($apiData['genres']) && is_array($apiData['genres'])) {
            $transformedData['genres'] = $apiData['genres'];
        }
        
        // Transformation des sociétés de production
        if (isset($apiData['production_companies']) && is_array($apiData['production_companies'])) {
            $transformedData['production_companies'] = $apiData['production_companies'];
        }
        
        // Transformation des pays de production
        if (isset($apiData['production_countries']) && is_array($apiData['production_countries'])) {
            $transformedData['production_countries'] = $apiData['production_countries'];
        }
        
        // Transformation des langues parlées
        if (isset($apiData['spoken_languages']) && is_array($apiData['spoken_languages'])) {
            $transformedData['spoken_languages'] = $apiData['spoken_languages'];
        }
        
        // Transformation des réseaux
        if (isset($apiData['networks']) && is_array($apiData['networks'])) {
            $transformedData['networks'] = $apiData['networks'];
        }
        
        // Transformation des créateurs
        if (isset($apiData['created_by']) && is_array($apiData['created_by'])) {
            $transformedData['created_by'] = $apiData['created_by'];
        }
        
        // Pour les séries non sauvegardées, le prochain épisode est le premier épisode de la première saison
        if (isset($transformedData['seasons']) && $transformedData['seasons']->isNotEmpty()) {
            $firstSeason = $transformedData['seasons']->first();
            if ($firstSeason && isset($firstSeason->episodes) && $firstSeason->episodes->isNotEmpty()) {
                $transformedData['next_episode_to_watch'] = $firstSeason->episodes->first();
            }
        }
        
        // Création d'un objet hybride qui peut être utilisé comme tableau ET comme objet
        $hybridObject = new \ArrayObject($transformedData, \ArrayObject::ARRAY_AS_PROPS);
        
        return $hybridObject;
    }

    /**
     * Récupère les détails d'une saison
     */
    public function getSeasonDetails(int $seriesTmdbId, int $seasonNumber)
    {
        $series = Series::where('tmdb_id', $seriesTmdbId)->first();
        
        if (!$series) {
            abort(404, 'Série non trouvée');
            }

            $season = Season::where('series_id', $series->id)
                           ->where('season_number', $seasonNumber)
                           ->with('episodes')
                           ->first();

            if (!$season) {
            abort(404, 'Saison non trouvée');
        }

        return view('series.season-detail', [
            'series' => $series,
            'season' => $season
        ]);
    }

    /**
     * Marque un épisode comme vu
     */
    public function markEpisodeAsWatched(Request $request)
    {
        $episodeId = $request->input('episode_id');
        $episode = Episode::find($episodeId);
        
        if ($episode) {
            $episode->is_watched = true;
            $episode->save();
            return redirect()->back()->with('success', 'Épisode marqué comme vu');
        }
        
        return redirect()->back()->with('error', 'Épisode non trouvé');
    }

    /**
     * Marque un épisode comme non vu
     */
    public function markEpisodeAsUnwatched(Request $request)
    {
        $episodeId = $request->input('episode_id');
        $episode = Episode::find($episodeId);
        
        if ($episode) {
            $episode->is_watched = false;
            $episode->save();
            return redirect()->back()->with('success', 'Épisode marqué comme non vu');
        }
        
        return redirect()->back()->with('error', 'Épisode non trouvé');
    }

    // Méthodes utilitaires privées

    private function createSeries(array $seriesData): Series
    {
        $series = new Series();
        $series->tmdb_id = $seriesData['id'];
        $series->name = $seriesData['name'];
        $series->overview = $seriesData['overview'];
        $series->poster_path = $seriesData['poster_path'];
        $series->first_air_date = $seriesData['first_air_date'];
        $series->last_air_date = $seriesData['last_air_date'] ?? null;
        $series->original_name = $seriesData['original_name'] ?? null;
        $series->original_language = $seriesData['original_language'] ?? null;
        $series->status = $seriesData['status'] ?? null;
        $series->vote_average = $seriesData['vote_average'] ?? null;
        $series->vote_count = $seriesData['vote_count'] ?? null;
        $series->popularity = $seriesData['popularity'] ?? null;
        $series->number_of_episodes = $seriesData['number_of_episodes'] ?? null;
        $series->number_of_seasons = $seriesData['number_of_seasons'] ?? null;
        $series->in_production = $seriesData['in_production'] ?? false;
        $series->type = $seriesData['type'] ?? null;
        $series->homepage = $seriesData['homepage'] ?? null;
        $series->tagline = $seriesData['tagline'] ?? null;
        $series->save();

        return $series;
    }

    private function saveSeriesGenres(Series $series, array $genres): void
    {
        foreach ($genres as $genreData) {
            $genre = Gender::firstOrCreate(
                ['tmdb_id' => $genreData['id']],
                ['name' => $genreData['name']]
            );
            $series->genders()->attach($genre->id);
        }
    }

    private function saveSeriesProductionCompanies(Series $series, array $companies): void
    {
        foreach ($companies as $companyData) {
            $company = ProductionCompany::firstOrCreate(
                ['tmdb_id' => $companyData['id']],
                [
                    'name' => $companyData['name'],
                    'logo_path' => $companyData['logo_path'] ?? null,
                    'origin_country' => $companyData['origin_country'] ?? null
                ]
            );
            $series->productionCompanies()->attach($company->id);
        }
    }

    private function saveSeriesProductionCountries(Series $series, array $countries): void
    {
        foreach ($countries as $countryData) {
            $country = ProductionCountry::firstOrCreate(
                ['iso_3166_1' => $countryData['iso_3166_1']],
                ['name' => $countryData['name']]
            );
            $series->productionCountries()->attach($country->id);
        }
    }

    private function saveSeriesSpokenLanguages(Series $series, array $languages): void
    {
        foreach ($languages as $languageData) {
            $language = SpokenLanguage::firstOrCreate(
                ['iso_639_1' => $languageData['iso_639_1']],
                [
                    'name' => $languageData['name'],
                    'english_name' => $languageData['english_name'] ?? null
                ]
            );
            $series->spokenLanguages()->attach($language->id);
        }
    }

    private function saveSeriesNetworks(Series $series, array $networks): void
    {
        foreach ($networks as $networkData) {
            $network = Network::firstOrCreate(
                ['tmdb_id' => $networkData['id']],
                [
                    'name' => $networkData['name'],
                    'logo_path' => $networkData['logo_path'] ?? null,
                    'origin_country' => $networkData['origin_country'] ?? null
                ]
            );
            $series->networks()->attach($network->id);
        }
    }

    private function saveSeriesCreators(Series $series, array $creators): void
    {
        foreach ($creators as $creatorData) {
            $creator = Creator::firstOrCreate(
                ['tmdb_id' => $creatorData['id']],
                [
                    'name' => $creatorData['name'],
                    'profile_path' => $creatorData['profile_path'] ?? null
                ]
            );
            $series->creators()->attach($creator->id);
        }
    }

    private function saveSeriesCast(Series $series, array $cast): void
    {
        $series->roles()->delete();
        
        $this->saveMediaCast($cast, function($actor, $actorData) use ($series) {
            SeriesRole::create([
                'series_id' => $series->id,
                'actor_id' => $actor->id,
                'character_name' => $actorData['character'] ?? null,
                'order' => $actorData['order'] ?? 0
            ]);
        });
    }

    private function downloadSeriesPoster(Series $series, ?string $posterPath): void
    {
        if ($posterPath) {
            try {
                $path = "poster/" . $posterPath;
                $response = Http::get("https://image.tmdb.org/t/p/w500" . $posterPath);
                Storage::disk('public')->put($path, $response->body());
            } catch (\Exception $e) {
                Log::warning('Impossible de télécharger le poster de la série', [
                    'series_id' => $series->id,
                    'poster_path' => $posterPath,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }


    /**
     * Force le téléchargement des photos de profil pour tous les acteurs d'une série
     * 
     * @param int $id L'ID de la série
     * 
     * @return \Illuminate\Http\JsonResponse Résultat du téléchargement
     */
    public function downloadCastPhotos(int $id)
    {
        $series = Series::with(['roles.actor'])->findOrFail($id);
        
        $downloaded = 0;
        $errors = 0;
        $alreadyExists = 0;
        
        foreach ($series->roles as $role) {
            if ($role->actor->profile_path) {
                $path = "profile/" . $role->actor->profile_path;
                
                if (Storage::disk('public')->exists($path)) {
                    $alreadyExists++;
                } else {
                    if ($this->downloadActorProfile($role->actor, $role->actor->profile_path)) {
                        $downloaded++;
                    } else {
                        $errors++;
                    }
                }
            }
        }
        
        return response()->json([
            'series_name' => $series->name,
            'total_actors' => $series->roles->count(),
            'downloaded' => $downloaded,
            'already_exists' => $alreadyExists,
            'errors' => $errors
        ]);
    }

    private function createOrUpdateSeason(Series $series, array $seasonData, int $seasonNumber): Season
    {
        $season = Season::where('series_id', $series->id)
                       ->where('season_number', $seasonNumber)
                       ->first();

        $seasonAttributes = [
                    'tmdb_id' => $seasonData['id'] ?? null,
                    'series_id' => $series->id,
                    'name' => $seasonData['name'] ?? "Saison {$seasonNumber}",
                    'overview' => $seasonData['overview'] ?? null,
                    'poster_path' => $seasonData['poster_path'] ?? null,
                    'air_date' => $seasonData['air_date'] ?? null,
                    'episode_count' => $seasonData['episode_count'] ?? count($seasonData['episodes']),
                    'season_number' => $seasonNumber,
                    'vote_average' => $seasonData['vote_average'] ?? null,
        ];

        if ($season) {
            $season->update($seasonAttributes);
        } else {
            $season = Season::create($seasonAttributes);
        }

        return $season;
    }

    private function processSeasonEpisodes(Season $season, array $episodesData): array
    {
            $episodesCreated = 0;
            $episodesUpdated = 0;

        foreach ($episodesData as $episodeData) {
                $episode = Episode::where('season_id', $season->id)
                                 ->where('episode_number', $episodeData['episode_number'])
                                 ->first();

                $episodeAttributes = [
                    'tmdb_id' => $episodeData['id'] ?? null,
                    'season_id' => $season->id,
                    'name' => $episodeData['name'] ?? null,
                    'overview' => $episodeData['overview'] ?? null,
                    'still_path' => $episodeData['still_path'] ?? null,
                    'air_date' => $episodeData['air_date'] ?? null,
                    'episode_number' => $episodeData['episode_number'],
                    'episode_type' => $episodeData['episode_type'] ?? 'standard',
                    'production_code' => $episodeData['production_code'] ?? null,
                    'runtime' => $episodeData['runtime'] ?? null,
                    'vote_average' => $episodeData['vote_average'] ?? null,
                    'vote_count' => $episodeData['vote_count'] ?? null,
                    'is_watched' => false,
                ];

                if ($episode) {
                    $episode->update($episodeAttributes);
                    $episodesUpdated++;
                } else {
                    Episode::create($episodeAttributes);
                    $episodesCreated++;
                }
            }

        return [
            'created' => $episodesCreated,
            'updated' => $episodesUpdated
        ];
    }
}