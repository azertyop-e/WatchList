<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Series;
use App\Models\Gender;
use App\Models\ProductionCompany;
use App\Models\ProductionCountry;
use App\Models\SpokenLanguage;
use App\Models\Collection;
use App\Models\Actor;
use App\Models\MovieRole;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class MovieController extends MediaController
{
    /**
     * Récupère tous les médias vus (films et séries) pour la page /seen
     * 
     * @param Request $request
     * @return \Illuminate\View\View Vue 'seen' avec tous les médias vus
     */
    public function getAllSeenMedia(Request $request)
    {
        // Récupération des films vus
        $movies = Movie::with('genders')->where('is_seen', true)->orderBy('updated_at', 'desc')->get();
        
        // Récupération des séries vues
        $series = Series::with('genders')->where('is_watched', true)->orderBy('updated_at', 'desc')->get();
        
        // Ajout du type de média à chaque élément
        $movies->each(function($movie) {
            $movie->media_type = 'movie';
        });
        
        $series->each(function($serie) {
            $serie->media_type = 'series';
        });
        
        // Fusion et tri des médias
        $allMedia = $movies->concat($series)->sortByDesc('updated_at');
        
        return view('seen', [
            'allMedia' => $allMedia,
            'movies' => $movies,
            'series' => $series
        ]);
    }

    /**
     * Récupère les films sauvegardés marqués comme vus avec filtrage par genre
     * 
     * Cette méthode récupère tous les films sauvegardés en base de données
     * qui ont été marqués comme vus, avec possibilité de filtrer par genre.
     * Elle récupère également la liste des genres disponibles pour le filtrage.
     * 
     * @param Request $request Contient le paramètre optionnel 'genre' pour le filtrage
     * 
     * @return \Illuminate\View\View Vue 'movie.seen' avec les films vus et genres
     */
    public function getSeenMovies(Request $request){
        $query = Movie::with('genders')->where('is_seen', true);
        
        if ($request->has('genre') && $request->input('genre') != '') {
            $genreId = $request->input('genre');
            $query->whereHas('genders', function($q) use ($genreId) {
                $q->where('gender.id', $genreId);
            });
        }
        
        $selectedType = $request->input('type', 'film');
        
        $movies = $query->orderBy('updated_at', 'desc')->get();
        
        $genres = Gender::whereHas('movies', function($q) {
            $q->where('is_seen', true);
        })->orderBy('name')->get();
        
        return view('movie.seen', [
            'movies' => $movies,
            'genres' => $genres,
            'selectedGenre' => $request->input('genre', ''),
            'selectedType' => $selectedType
        ]);
    }

    /**
     * Affiche les détails d'un film
     * 
     * Cette méthode récupère les détails complets d'un film, soit depuis
     * la base de données locale s'il est sauvegardé, soit depuis l'API TMDB.
     * Elle inclut les genres, sociétés de production, pays, langues,
     * collection et casting.
     * 
     * @param int $id L'identifiant TMDB du film
     * 
     * @return \Illuminate\View\View Vue 'movie.detail' avec les détails du film
     * 
     * @throws \Exception En cas d'erreur lors de l'appel à l'API TMDB
     */
    public function getMovieDetails(int $id)
    {
        $movie = Movie::with([
            'genders',
            'productionCompanies',
            'productionCountries', 
            'spokenLanguages',
            'collection',
            'roles.actor'
        ])->find($id);
        
        if (!$movie) {
            $url = "/movie/".$id."?language=fr-FR&include_adult=false";
            $movieData = $this->getCurlData($url);
            
            $creditsUrl = "/movie/".$id."/credits?language=fr-FR";
            $creditsData = $this->getCurlData($creditsUrl);
            
            if (isset($creditsData['cast']) && is_array($creditsData['cast'])) {
                $movieData['cast'] = array_slice($creditsData['cast'], 0, 20);
            }
            
            return view('movie.detail', ['movieData' => $movieData]);
        }
        
        $movieData = $movie->toArray();
        
        $movieData['genres'] = $movie->genders->map(function($genre) {
            return ['name' => $genre->name];
        })->toArray();
        
        $movieData['production_companies'] = $movie->productionCompanies->map(function($company) {
            return [
                'name' => $company->name,
                'logo_path' => $company->logo_path
            ];
        })->toArray();
        
        $movieData['production_countries'] = $movie->productionCountries->map(function($country) {
            return ['name' => $country->name];
        })->toArray();
        
        $movieData['spoken_languages'] = $movie->spokenLanguages->map(function($language) {
            return [
                'name' => $language->name,
                'english_name' => $language->english_name
            ];
        })->toArray();
        
        if ($movie->collection) {
            $movieData['belongs_to_collection'] = [
                'name' => $movie->collection->name,
                'poster_path' => $movie->collection->poster_path
            ];
        }
        
        $movieData['cast'] = $movie->roles->map(function($role) {
            return [
                'name' => $role->actor->name,
                'character' => $role->character_name,
                'profile_path' => $role->actor->profile_path,
                'order' => $role->order
            ];
        })->toArray();
        
        return view('movie.detail', ['movieData' => $movieData]);
    }

    /**
     * Sauvegarde un film en base de données
     * 
     * Cette méthode récupère les données complètes d'un film depuis l'API TMDB
     * et les sauvegarde en base de données locale, incluant toutes les relations :
     * genres, sociétés de production, pays, langues, collection et casting.
     * Elle télécharge également les images (poster et photos de profil des acteurs).
     * 
     * @param Request $request Contient le paramètre 'movie_id' (ID TMDB du film)
     * 
     * @return \Illuminate\Http\RedirectResponse Redirection avec message de succès/erreur
     * 
     * @throws \Exception En cas d'erreur lors de l'appel à l'API TMDB ou de sauvegarde
     */
    public function saveMovie(Request $request)
    {
        if ($request->has ('movie_id') && $request->input ('movie_id') > 0) {
            $movieId = $request->input('movie_id');
            
            $existingMovie = Movie::where('id', $movieId)->first();
            if ($existingMovie) {
                return Redirect::back()->with('error', 'Ce film est déjà dans votre liste');
            }
            
            $movieData = $this->getCurlData ('/movie/' . $movieId. '?language=fr-FR&include_adult=false');
            $creditsData = $this->getCurlData('/movie/' . $movieId . '/credits?language=fr-FR');

            $movie = new Movie();
            $movie->title = $movieData['title'];
            $movie->overview = $movieData['overview'];
            $movie->poster_path = $movieData['poster_path'];
            $movie->release_date = $movieData['release_date'];
            $movie->id = $movieData['id'];
                
            $movie->original_title = $movieData['original_title'] ?? null;
            $movie->tagline = $movieData['tagline'] ?? null;
            $movie->vote_average = $movieData['vote_average'] ?? null;
            $movie->vote_count = $movieData['vote_count'] ?? null;
            $movie->runtime = $movieData['runtime'] ?? null;
            $movie->original_language = $movieData['original_language'] ?? null;
            $movie->status = $movieData['status'] ?? null;
            $movie->budget = $movieData['budget'] ?? null;
            $movie->revenue = $movieData['revenue'] ?? null;
            $movie->popularity = $movieData['popularity'] ?? null;
            
            $movie->save();
            
            if (isset($movieData['genres']) && is_array($movieData['genres'])) {
                foreach ($movieData['genres'] as $genreData) {
                    $genre = Gender::firstOrCreate(
                        ['tmdb_id' => $genreData['id']], 
                        ['name' => $genreData['name']] 
                    );
                    $movie->genders()->attach($genre->id);
                }
            }

            if (isset($movieData['production_companies']) && is_array($movieData['production_companies'])) {
                foreach ($movieData['production_companies'] as $companyData) {
                    $company = ProductionCompany::firstOrCreate(
                        ['tmdb_id' => $companyData['id']],
                        [
                            'name' => $companyData['name'],
                            'logo_path' => $companyData['logo_path'] ?? null,
                            'origin_country' => $companyData['origin_country'] ?? null
                        ]
                    );
                    $movie->productionCompanies()->attach($company->id);
                }
            }

            if (isset($movieData['production_countries']) && is_array($movieData['production_countries'])) {
                foreach ($movieData['production_countries'] as $countryData) {
                    $country = ProductionCountry::firstOrCreate(
                        ['iso_3166_1' => $countryData['iso_3166_1']],
                        ['name' => $countryData['name']]
                    );
                    $movie->productionCountries()->attach($country->id);
                }
            }

            if (isset($movieData['spoken_languages']) && is_array($movieData['spoken_languages'])) {
                foreach ($movieData['spoken_languages'] as $languageData) {
                    $language = SpokenLanguage::firstOrCreate(
                        ['iso_639_1' => $languageData['iso_639_1']],
                        [
                            'name' => $languageData['name'],
                            'english_name' => $languageData['english_name']
                        ]
                    );
                    $movie->spokenLanguages()->attach($language->id);
                }
            }

            if (isset($movieData['belongs_to_collection']) && $movieData['belongs_to_collection']) {
                $collectionData = $movieData['belongs_to_collection'];
                $collection = Collection::firstOrCreate(
                    ['tmdb_id' => $collectionData['id']],
                    [
                        'name' => $collectionData['name'],
                        'poster_path' => $collectionData['poster_path'] ?? null,
                        'backdrop_path' => $collectionData['backdrop_path'] ?? null
                    ]
                );
                $movie->collection_id = $collection->id;
                $movie->save();
            }

            if (isset($creditsData['cast']) && is_array($creditsData['cast'])) {
                foreach ($creditsData['cast'] as $index => $castMember) {
                    if ($index >= 20) break;
                    
                    $actor = Actor::firstOrCreate(
                        ['tmdb_id' => $castMember['id']],
                        [
                            'name' => $castMember['name'],
                            'profile_path' => $castMember['profile_path'] ?? null,
                            'popularity' => $castMember['popularity'] ?? null
                        ]
                    );
                    
                    if (isset($castMember['profile_path']) && $castMember['profile_path']) {
                        $profilePath = "profile/" . $castMember['profile_path'];
                        $localPath = Storage::disk('public')->path($profilePath);
                        
                        if (!Storage::disk('public')->exists($profilePath)) {
                            try {
                                $response = Http::get("https://image.tmdb.org/t/p/w185" . $castMember['profile_path']);
                                if ($response->successful()) {
                                    Storage::disk('public')->put($profilePath, $response->body());
                                }
                            } catch (\Exception $e) {
                                \Log::warning("Erreur lors du téléchargement de l'image de profil pour l'acteur {$actor->name}: " . $e->getMessage());
                            }
                        }
                    }
                    
                    MovieRole::create([
                        'movie_id' => $movie->id,
                        'actor_id' => $actor->id,
                        'character_name' => $castMember['character'] ?? 'Inconnu',
                        'order' => $index + 1
                    ]);
                }
            }

            if (isset($movieData['poster_path'])) {
                $path = "poster/".$movieData['poster_path'];
                $response = Http::get("https://image.tmdb.org/t/p/w500".$movieData['poster_path']);
                Storage::disk('public')->put($path, $response->body());
            }
            $movie->save();

            return Redirect::back()->with('success', 'Film ajouté avec succès');
        }
    }

    /**
     * Marque un film comme vu
     * 
     * Cette méthode met à jour le statut d'un film sauvegardé
     * pour le marquer comme vu.
     * 
     * @param Request $request Contient le paramètre 'movie_id' (ID du film)
     * 
     * @return \Illuminate\Http\RedirectResponse Redirection avec message de succès/erreur
     */
    public function markAsSeen(Request $request)
    {
        $movieId = $request->input('movie_id');
        $movie = Movie::find($movieId);
        
        if ($movie) {
            $movie->is_seen = true;
            $movie->save();
            return Redirect::back()->with('success', 'Film marqué comme vu');
        }
        
        return Redirect::back()->with('error', 'Film non trouvé');
    }

    /**
     * Marque un film comme non vu
     * 
     * Cette méthode met à jour le statut d'un film sauvegardé
     * pour le marquer comme non vu.
     * 
     * @param Request $request Contient le paramètre 'movie_id' (ID du film)
     * 
     * @return \Illuminate\Http\RedirectResponse Redirection avec message de succès/erreur
     */
    public function markAsUnseen(Request $request)
    {
        $movieId = $request->input('movie_id');
        $movie = Movie::find($movieId);
        
        if ($movie) {
            $movie->is_seen = false;
            $movie->save();
            return Redirect::back()->with('success', 'Film marqué comme non vu');
        }
        
        return Redirect::back()->with('error', 'Film non trouvé');
    }

    /**
     * Retourne le type de média pour les films
     * 
     * @return string Le type de média
     */
    protected function getMediaType(): string
    {
        return 'movie';
    }

    /**
     * Retourne l'URL de recherche pour les films
     * 
     * @param string $query Le terme de recherche
     * @param int $page Le numéro de page
     * 
     * @return string L'URL de recherche
     */
    protected function getSearchUrl(string $query, int $page): string
    {
        return "/search/multi?query=".urlencode($query)."&include_adult=false&language=fr-FR&page=".$page;
    }

    /**
     * Vérifie si un élément de résultat est un film ou une série
     * 
     * @param array $item L'élément à vérifier
     * 
     * @return bool True si l'élément est valide
     */
    protected function isValidMediaType(array $item): bool
    {
        return $item['media_type'] === 'movie' || $item['media_type'] === 'tv';
    }

    /**
     * Retourne le nom de la vue pour l'affichage des résultats de recherche de films
     * 
     * @return string Le nom de la vue
     */
    protected function getSearchView(): string
    {
        return 'movie.search';
    }

    /**
     * Ajoute le statut de sauvegarde aux résultats de recherche de films
     * 
     * @param array $results Les résultats à enrichir
     * 
     * @return array Les résultats enrichis
     */
    protected function addSavedStatusToResults(array $results): array
    {
        $savedMovieIds = Movie::pluck('id')->toArray();
        $savedSeriesIds = Series::pluck('tmdb_id')->toArray();
        
        foreach ($results as &$item) {
            if ($item['media_type'] === 'movie') {
                $item['is_saved'] = in_array($item['id'], $savedMovieIds);
            } elseif ($item['media_type'] === 'tv') {
                $item['is_saved'] = in_array($item['id'], $savedSeriesIds);
            }
        }
        
        return $results;
    }

    /**
     * Retourne l'URL pour récupérer les films populaires
     * 
     * @param int $page Le numéro de page
     * 
     * @return string L'URL pour les films populaires
     */
    protected function getPopularUrl(int $page): string
    {
        return "/movie/popular?language=fr-FR&include_adult=false&page=" . $page;
    }

    /**
     * Retourne l'URL pour récupérer les films populaires en cartes
     * 
     * @return string L'URL pour les films populaires en cartes
     */
    protected function getPopularCardsUrl(): string
    {
        return "/movie/popular?language=fr-FR&include_adult=false&page=1";
    }

    /**
     * Retourne l'URL pour récupérer les films les mieux notés
     * 
     * @param int $page Le numéro de page
     * 
     * @return string L'URL pour les films les mieux notés
     */
    protected function getTopRatedUrl(int $page): string
    {
        return "/movie/top_rated?language=fr-FR&include_adult=false&page=" . $page;
    }

    /**
     * Retourne le nom de la vue pour l'affichage des films populaires
     * 
     * @return string Le nom de la vue
     */
    protected function getPopularView(): string
    {
        return 'movie.popular';
    }

    /**
     * Retourne le nom de la vue pour l'affichage des films populaires en cartes
     * 
     * @return string Le nom de la vue
     */
    protected function getPopularCardsView(): string
    {
        return 'movie.popular-cards';
    }

    /**
     * Retourne le nom de la vue pour l'affichage des films les mieux notés
     * 
     * @return string Le nom de la vue
     */
    protected function getTopView(): string
    {
        return 'movie.top';
    }

    /**
     * Ajoute le statut de sauvegarde aux films
     * 
     * @param array|null $mediaData Les données des films depuis l'API TMDB
     * 
     * @return array|null Les données enrichies avec le statut de sauvegarde
     */
    protected function addSavedStatusToMedia(?array $mediaData): ?array
    {
        if (!$mediaData || !isset($mediaData['results'])) {
            return $mediaData;
        }
        
        $savedMovieIds = Movie::pluck('id')->toArray();
        
        foreach ($mediaData['results'] as &$movie) {
            $movie['is_saved'] = in_array($movie['id'], $savedMovieIds);
        }
        
        return $mediaData;
    }

    /**
     * Retourne le nom de la variable de données pour les vues de films
     * 
     * @return string Le nom de la variable
     */
    protected function getDataVariableName(): string
    {
        return 'moviesData';
    }
}
