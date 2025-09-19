<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MovieModel;
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

/**
 * Contrôleur pour la gestion des films
 * 
 * Ce contrôleur gère toutes les opérations liées aux films :
 * - Récupération des films populaires et les mieux notés
 * - Recherche de films
 * - Sauvegarde et gestion des films en base de données
 * - Affichage des détails des films
 * - Gestion du statut "vu/non vu" des films
 * 
 * @package App\Http\Controllers
 * @author Votre Nom
 * @version 1.0
 */
class MovieController extends Controller
{
    /**
     * Récupère et affiche les films populaires avec pagination
     * 
     * Cette méthode récupère les films populaires depuis l'API TMDB,
     * applique une pagination avec validation des paramètres,
     * et ajoute le statut de sauvegarde pour chaque film.
     * 
     * @param Request $request Contient les paramètres de pagination :
     *                        - page : numéro de page (1-10, défaut: 1)
     *                        - per_page : nombre d'éléments par page (10, 20, 50, défaut: 20)
     * 
     * @return \Illuminate\View\View Vue 'movie.popular' avec les données des films
     * 
     * @throws \Exception En cas d'erreur lors de l'appel à l'API TMDB
     */
    public function getPopular(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);
        
        if ($page > 10) {
            $page = 10;
        }
        if ($page < 1) {
            $page = 1;
        }
        
        $allowedPerPage = [10, 20, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 20;
        }
        
        $url = "/movie/popular?language=fr-FR&include_adult=false&page=" . $page;
        $moviesData = $this->getCurlData($url);
        $moviesData = $this->addSavedStatusToMovies($moviesData);
        
        $totalResults = $moviesData['total_results'] ?? 0;
        $totalPages = min($moviesData['total_pages'] ?? 10, 10);
        $itemsPerPage = 20;
        $startItem = ($page - 1) * $itemsPerPage + 1;
        $endItem = min($page * $itemsPerPage, $totalResults);

        return view('movie.popular', [
            'moviesData' => $moviesData,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'totalResults' => $totalResults,
            'startItem' => $startItem,
            'endItem' => $endItem,
            'allowedPerPage' => $allowedPerPage
        ]);
    }

    /**
     * Récupère les films populaires pour l'affichage en cartes
     * 
     * Cette méthode récupère la première page des films populaires
     * depuis l'API TMDB pour un affichage sous forme de cartes.
     * 
     * @return \Illuminate\View\View Vue 'movie.popular-cards' avec les données des films
     * 
     * @throws \Exception En cas d'erreur lors de l'appel à l'API TMDB
     */
    public function getPopularCards()
    {
        $url = "/movie/popular?language=fr-FR&include_adult=false&page=1";
        $moviesData = $this->getCurlData($url);

        return view('movie.popular-cards', ['moviesData' => $moviesData]);
    }

    /**
     * Récupère et affiche les films les mieux notés
     * 
     * Cette méthode récupère les 5 premières pages des films les mieux notés
     * depuis l'API TMDB, les combine en une seule liste, et ajoute
     * le statut de sauvegarde pour chaque film.
     * 
     * @return \Illuminate\View\View Vue 'movie.top' avec les données des films
     * 
     * @throws \Exception En cas d'erreur lors de l'appel à l'API TMDB
     */
    public function getTop()
    {
        $allMovies = [];
        $totalResults = 0;
        $totalPages = 0;
        
        for ($page = 1; $page <= 5; $page++) {
            $url = "/movie/top_rated?language=fr-FR&include_adult=false&page=" . $page;
            $pageData = $this->getCurlData($url);
            
            if ($pageData && isset($pageData['results'])) {
                $allMovies = array_merge($allMovies, $pageData['results']);
                
                if ($page === 1) {
                    $totalResults = $pageData['total_results'] ?? 0;
                    $totalPages = $pageData['total_pages'] ?? 0;
                }
            }
        }
        
        $moviesData = [
            'results' => $allMovies,
            'total_results' => $totalResults,
            'total_pages' => $totalPages,
            'page' => 1
        ];
        
        $moviesData = $this->addSavedStatusToMovies($moviesData);
        
        return view('movie.top', ['moviesData' => $moviesData]);
    }

    /**
     * Effectue une recherche de films
     * 
     * Cette méthode recherche des films selon un terme de recherche
     * via l'API TMDB et ajoute le statut de sauvegarde pour chaque résultat.
     * 
     * @param Request $request Contient le paramètre 'query' pour la recherche
     * 
     * @return \Illuminate\View\View Vue 'movie.search' avec les résultats de recherche
     * 
     * @throws \Exception En cas d'erreur lors de l'appel à l'API TMDB
     */
    public function getSearch(Request $request)
    {
        $url = "/search/movie?query=".$request->query('query')."&language=fr-FR&include_adult=false&page=1";
        $moviesData = $this->getCurlData($url);
        $moviesData = $this->addSavedStatusToMovies($moviesData);
        
        return view('movie.search', ['moviesData' => $moviesData]);
    }

    /**
     * Récupère les films sauvegardés non vus avec filtrage par genre
     * 
     * Cette méthode récupère tous les films sauvegardés en base de données
     * qui n'ont pas encore été marqués comme vus, avec possibilité de
     * filtrer par genre. Elle récupère également la liste des genres
     * disponibles pour le filtrage.
     * 
     * @param Request $request Contient le paramètre optionnel 'genre' pour le filtrage
     * 
     * @return \Illuminate\View\View Vue 'home' avec les films et genres
     */
    public function getMovieStored(Request $request){
        $query = MovieModel::with('genders')->where('is_seen', false);
        
        if ($request->has('genre') && $request->input('genre') != '') {
            $genreId = $request->input('genre');
            $query->whereHas('genders', function($q) use ($genreId) {
                $q->where('gender.id', $genreId);
            });
        }
        
        $selectedType = $request->input('type', 'film');
        
        $movies = $query->orderBy('created_at', 'desc')->get();
        
        $genres = Gender::whereHas('movies', function($q) {
            $q->where('is_seen', false);
        })->orderBy('name')->get();
        
        return view('home', [
            'movies' => $movies,
            'genres' => $genres,
            'selectedGenre' => $request->input('genre', ''),
            'selectedType' => $selectedType
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
        $query = MovieModel::with('genders')->where('is_seen', true);
        
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
        $movie = MovieModel::with([
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
            
            $existingMovie = MovieModel::where('id', $movieId)->first();
            if ($existingMovie) {
                return Redirect::back()->with('error', 'Ce film est déjà dans votre liste');
            }
            
            $movieData = $this->getCurlData ('/movie/' . $movieId. '?language=fr-FR&include_adult=false');
            $creditsData = $this->getCurlData('/movie/' . $movieId . '/credits?language=fr-FR');

            $movie = new MovieModel();
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
        $movie = MovieModel::find($movieId);
        
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
        $movie = MovieModel::find($movieId);
        
        if ($movie) {
            $movie->is_seen = false;
            $movie->save();
            return Redirect::back()->with('success', 'Film marqué comme non vu');
        }
        
        return Redirect::back()->with('error', 'Film non trouvé');
    }

    /**
     * Effectue un appel cURL vers l'API TMDB
     * 
     * Cette méthode utilitaire effectue des requêtes HTTP vers l'API TMDB
     * en utilisant cURL avec authentification Bearer token.
     * 
     * @param string $url L'endpoint de l'API TMDB (sans le domaine de base)
     * 
     * @return array|null Les données JSON décodées ou null en cas d'erreur
     * 
     * @throws \Exception En cas d'erreur cURL
     */
    public function getCurlData(string $url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.themoviedb.org/3".$url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiIzYjZiOTA0ODUwOTAwMmI0OGFhNjE3OGFmOTg3OTdmOCIsIm5iZiI6MTUyNjg5MjY4Mi4xMTksInN1YiI6IjViMDI4ODhhMGUwYTI2MjNlMzAxM2NiNiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.U__GCj6NGxqJW_3jGpP29dEbdjeLh0eJ7a5CCmAJzlk",
                "accept: application/json"
            ],
        ]);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if (!$err) {
            return json_decode($response, true);
        }
    }

    /**
     * Ajoute le statut de sauvegarde aux films
     * 
     * Cette méthode privée enrichit les données des films récupérées
     * depuis l'API TMDB en ajoutant un champ 'is_saved' indiquant
     * si le film est déjà sauvegardé en base de données locale.
     * 
     * @param array|null $moviesData Les données des films depuis l'API TMDB
     * 
     * @return array|null Les données enrichies avec le statut de sauvegarde
     */
    private function addSavedStatusToMovies(?array $moviesData): ?array
    {
        if (!$moviesData || !isset($moviesData['results'])) {
            return $moviesData;
        }
        
        $savedMovieIds = MovieModel::pluck('id')->toArray();
        
        foreach ($moviesData['results'] as &$movie) {
            $movie['is_saved'] = in_array($movie['id'], $savedMovieIds);
        }
        
        return $moviesData;
    }
}
