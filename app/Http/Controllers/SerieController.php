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
    public function saveSeason(int $seriesTmdbId, int $seasonNumber)
    {
        return $this->saveSeasonEpisodes($seriesTmdbId, $seasonNumber);
    }

    /**
     * Enregistre les épisodes d'une saison donnée d'une série depuis l'API TMDB
     */
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
     * Récupère les séries stockées par l'utilisateur (non vues)
     */
    public function getSeriesStored()
    {
        $series = Series::where('is_watched', false)
                        ->with(['genders', 'networks'])
                        ->orderBy('created_at', 'desc')
                        ->get();
        
        return view('series.home', ['series' => $series]);
    }

    /**
     * Récupère les séries vues par l'utilisateur
     */
    public function getSeenMedia()
    {
        $seenSeries = Series::where('is_watched', true)->get();
        return view('series.seen', ['seriesData' => $seenSeries]);
    }

    /**
     * Récupère les détails d'une série
     */
    public function getMediaDetails(int $id)
    {
        $series = Series::with(['genders', 'productionCompanies', 'productionCountries', 'spokenLanguages', 'networks', 'creators', 'roles.actor', 'seasons.episodes'])
                        ->find($id);
        
            if (!$series) {
            abort(404, 'Série non trouvée');
        }

        return view('series.detail', ['seriesData' => $series]);
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
        foreach ($cast as $actorData) {
            $actor = Actor::firstOrCreate(
                ['tmdb_id' => $actorData['id']],
                [
                    'name' => $actorData['name'],
                    'profile_path' => $actorData['profile_path'] ?? null,
                    'gender' => $actorData['gender'] ?? null,
                    'known_for_department' => $actorData['known_for_department'] ?? null,
                    'popularity' => $actorData['popularity'] ?? null
                ]
            );

            SeriesRole::create([
                'series_id' => $series->id,
                'actor_id' => $actor->id,
                'character_name' => $actorData['character'] ?? null,
                'order' => $actorData['order'] ?? 0
            ]);
        }
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