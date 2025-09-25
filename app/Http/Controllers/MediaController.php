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
use Illuminate\Support\Facades\Log;

abstract class MediaController extends Controller
{
    protected const DEFAULT_MAX_CAST_SIZE = 20;

    /**
     * Récupère et affiche les médias populaires avec pagination
     * 
     * Cette méthode générique récupère les médias populaires depuis l'API TMDB,
     * applique une pagination avec validation des paramètres,
     * et ajoute le statut de sauvegarde pour chaque média.
     * 
     * @param Request $request Contient les paramètres de pagination :
     *                        - page : numéro de page (1-10, défaut: 1)
     *                        - per_page : nombre d'éléments par page (10, 20, 50, défaut: 20)
     * 
     * @return \Illuminate\View\View Vue avec les données des médias
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
        
        $url = $this->getPopularUrl($page);
        $mediaData = $this->getCurlData($url);
        $mediaData = $this->addSavedStatusToMedia($mediaData);
        
        $totalResults = $mediaData['total_results'] ?? 0;
        $totalPages = min($mediaData['total_pages'] ?? 10, 10);
        $itemsPerPage = 20;
        $startItem = ($page - 1) * $itemsPerPage + 1;
        $endItem = min($page * $itemsPerPage, $totalResults);

        $viewData = [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'totalResults' => $totalResults,
            'startItem' => $startItem,
            'endItem' => $endItem,
            'allowedPerPage' => $allowedPerPage
        ];
        
        $viewData[$this->getDataVariableName()] = $mediaData;
        
        return view($this->getPopularView(), $viewData);
    }

    /**
     * Récupère les médias populaires pour l'affichage en cartes
     * 
     * Cette méthode générique récupère la première page des médias populaires
     * depuis l'API TMDB pour un affichage sous forme de cartes.
     * 
     * @return \Illuminate\View\View Vue avec les données des médias
     * 
     * @throws \Exception En cas d'erreur lors de l'appel à l'API TMDB
     */
    public function getPopularCards()
    {
        $url = $this->getPopularCardsUrl();
        $mediaData = $this->getCurlData($url);

        return view($this->getPopularCardsView(), [$this->getDataVariableName() => $mediaData]);
    }

    /**
     * Récupère et affiche les médias les mieux notés
     * 
     * Cette méthode générique récupère les 5 premières pages des médias les mieux notés
     * depuis l'API TMDB, les combine en une seule liste, et ajoute
     * le statut de sauvegarde pour chaque média.
     * 
     * @return \Illuminate\View\View Vue avec les données des médias
     * 
     * @throws \Exception En cas d'erreur lors de l'appel à l'API TMDB
     */
    public function getTop()
    {
        $allMedia = [];
        $totalResults = 0;
        $totalPages = 0;
        
        for ($page = 1; $page <= 5; $page++) {
            $url = $this->getTopRatedUrl($page);
            $pageData = $this->getCurlData($url);
            
            if ($pageData && isset($pageData['results'])) {
                $allMedia = array_merge($allMedia, $pageData['results']);
                
                if ($page === 1) {
                    $totalResults = $pageData['total_results'] ?? 0;
                    $totalPages = $pageData['total_pages'] ?? 0;
                }
            }
        }
        
        $mediaData = [
            'results' => $allMedia,
            'total_results' => $totalResults,
            'total_pages' => $totalPages,
            'page' => 1
        ];
        
        $mediaData = $this->addSavedStatusToMedia($mediaData);
        
        return view($this->getTopView(), [$this->getDataVariableName() => $mediaData]);
    }

    /**
     * Effectue une recherche de médias avec pagination
     * 
     * Cette méthode générique recherche des médias selon un terme de recherche
     * via l'API TMDB avec support de la pagination et ajoute le statut 
     * de sauvegarde pour chaque résultat.
     * 
     * @param Request $request Contient les paramètres :
     *                        - 'query' : terme de recherche (requis)
     *                        - 'page' : numéro de page (défaut: 1)
     * 
     * @return \Illuminate\View\View Vue de recherche avec les résultats et pagination
     * 
     * @throws \Exception En cas d'erreur lors de l'appel à l'API TMDB
     */
    public function getSearch(Request $request)
    {
        $query = $request->query('query');
        $page = $request->query('page', 1);
        
        if ($page < 1) {
            $page = 1;
        }
        if ($page > 1000) { 
            $page = 1000;
        }
        
        // Utiliser un cache simple pour éviter les doublons entre les pages
        $cacheKey = 'search_' . $this->getMediaType() . '_' . md5($query);
        $lastQueryKey = 'last_search_query_' . $this->getMediaType();
        $lastQuery = session($lastQueryKey);
        
        // Si c'est une nouvelle recherche, nettoyer le cache
        if ($lastQuery !== $query) {
            session()->forget($cacheKey);
            session([$lastQueryKey => $query]);
        }
        
        $allCachedResults = session($cacheKey, []);
        $resultsPerPage = 20;
        
        // Si on n'a pas de cache ou qu'on demande une page plus loin que ce qu'on a en cache
        if (empty($allCachedResults) || count($allCachedResults) < ($page * $resultsPerPage)) {
            // Récupérer les pages manquantes
            $startApiPage = empty($allCachedResults) ? 1 : intval(count($allCachedResults) / 20) + 1;
            $maxApiPages = min($startApiPage + 5, 1000); // Récupérer 5 pages max à la fois
            
            for ($currentApiPage = $startApiPage; $currentApiPage <= $maxApiPages; $currentApiPage++) {
                $url = $this->getSearchUrl($query, $currentApiPage);
                $searchData = $this->getCurlData($url);
                
                if (isset($searchData['results']) && is_array($searchData['results'])) {
                    foreach ($searchData['results'] as $item) {
                        if ($this->isValidMediaType($item)) {
                            $allCachedResults[] = $item;
                        }
                    }
                }
                
                // Si on n'a plus de résultats, on s'arrête
                if (!isset($searchData['results']) || count($searchData['results']) === 0) {
                    break;
                }
            }
            
            // Sauvegarder le cache
            session([$cacheKey => $allCachedResults]);
        }
        
        // Extraire les résultats pour la page demandée
        $startIndex = ($page - 1) * $resultsPerPage;
        $filteredResults = array_slice($allCachedResults, $startIndex, $resultsPerPage);
        
        // Ajouter le statut de sauvegarde pour tous les résultats
        $filteredResults = $this->addSavedStatusToResults($filteredResults);
        
        // Calculer les informations de pagination basées sur le cache
        $totalCachedResults = count($allCachedResults);
        $totalPages = max(1, intval(ceil($totalCachedResults / $resultsPerPage)));
        
        // Structure pour l'affichage
        $unifiedData = [
            'results' => $filteredResults,
            'total_results' => $totalCachedResults,
            'total_pages' => $totalPages,
            'page' => $page
        ];
        
        // Calcul des informations de pagination
        $currentPage = $page;
        $actualResultsPerPage = count($filteredResults);
        
        // Calcul des éléments affichés
        $startItem = ($currentPage - 1) * $resultsPerPage + 1;
        $endItem = ($currentPage - 1) * $resultsPerPage + $actualResultsPerPage;
        
        return view($this->getSearchView(), [
            'unifiedData' => $unifiedData,
            'query' => $query,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalResults' => $totalCachedResults,
            'startItem' => $startItem,
            'endItem' => $endItem,
            'resultsPerPage' => $actualResultsPerPage
        ]);
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
     * Retourne le type de média (movie, tv, etc.)
     * 
     * @return string Le type de média
     */
    abstract protected function getMediaType(): string;

    /**
     * Retourne l'URL de recherche pour l'API TMDB
     * 
     * @param string $query Le terme de recherche
     * @param int $page Le numéro de page
     * 
     * @return string L'URL de recherche
     */
    abstract protected function getSearchUrl(string $query, int $page): string;

    /**
     * Vérifie si un élément de résultat est du bon type de média
     * 
     * @param array $item L'élément à vérifier
     * 
     * @return bool True si l'élément est valide
     */
    abstract protected function isValidMediaType(array $item): bool;

    /**
     * Retourne le nom de la vue pour l'affichage des résultats de recherche
     * 
     * @return string Le nom de la vue
     */
    abstract protected function getSearchView(): string;

    /**
     * Ajoute le statut de sauvegarde aux résultats de recherche
     * 
     * @param array $results Les résultats à enrichir
     * 
     * @return array Les résultats enrichis
     */
    abstract protected function addSavedStatusToResults(array $results): array;

    /**
     * Retourne l'URL pour récupérer les médias populaires
     * 
     * @param int $page Le numéro de page
     * 
     * @return string L'URL pour les médias populaires
     */
    abstract protected function getPopularUrl(int $page): string;

    /**
     * Retourne l'URL pour récupérer les médias populaires en cartes
     * 
     * @return string L'URL pour les médias populaires en cartes
     */
    abstract protected function getPopularCardsUrl(): string;

    /**
     * Retourne l'URL pour récupérer les médias les mieux notés
     * 
     * @param int $page Le numéro de page
     * 
     * @return string L'URL pour les médias les mieux notés
     */
    abstract protected function getTopRatedUrl(int $page): string;

    /**
     * Retourne le nom de la vue pour l'affichage des médias populaires
     * 
     * @return string Le nom de la vue
     */
    abstract protected function getPopularView(): string;

    /**
     * Retourne le nom de la vue pour l'affichage des médias populaires en cartes
     * 
     * @return string Le nom de la vue
     */
    abstract protected function getPopularCardsView(): string;

    /**
     * Retourne le nom de la vue pour l'affichage des médias les mieux notés
     * 
     * @return string Le nom de la vue
     */
    abstract protected function getTopView(): string;

    /**
     * Ajoute le statut de sauvegarde aux médias
     * 
     * @param array|null $mediaData Les données des médias depuis l'API TMDB
     * 
     * @return array|null Les données enrichies avec le statut de sauvegarde
     */
    abstract protected function addSavedStatusToMedia(?array $mediaData): ?array;

    /**
     * Retourne le nom de la variable de données pour les vues
     * 
     * @return string Le nom de la variable (ex: 'moviesData', 'seriesData')
     */
    abstract protected function getDataVariableName(): string;

    /**
     * Récupère les films et séries sauvegardés non vus avec filtrage par genre
     * 
     * Cette méthode récupère tous les films et séries sauvegardés en base de données
     * qui n'ont pas encore été marqués comme vus, avec possibilité de
     * filtrer par genre. Elle récupère également la liste des genres
     * disponibles pour le filtrage.
     * 
     * @param Request $request Contient le paramètre optionnel 'genre' pour le filtrage
     * 
     * @return \Illuminate\View\View Vue 'home' avec les films, séries et genres
     */
    public function getMediaStored(Request $request){
        // Récupération des films non vus
        $movieQuery = Movie::with('genders')->where('is_seen', false);
        
        // Récupération des séries non vues
        $seriesQuery = Series::with('genders')->where('is_watched', false);
        
        if ($request->has('genre') && $request->input('genre') != '') {
            $genreId = $request->input('genre');
            
            $movieQuery->whereHas('genders', function($q) use ($genreId) {
                $q->where('gender.id', $genreId);
            });
            
            $seriesQuery->whereHas('genders', function($q) use ($genreId) {
                $q->where('gender.id', $genreId);
            });
        }
        
        $selectedType = $request->input('type', 'all');
        
        // Récupérer les données selon le type sélectionné
        if ($selectedType === 'film') {
            $movies = $movieQuery->orderBy('created_at', 'desc')->get();
            $series = collect(); // Pas de séries
        } elseif ($selectedType === 'serie') {
            $movies = collect(); // Pas de films
            $series = $seriesQuery->orderBy('created_at', 'desc')->get();
        } else {
            // Si 'all', on garde les deux
            $movies = $movieQuery->orderBy('created_at', 'desc')->get();
            $series = $seriesQuery->orderBy('created_at', 'desc')->get();
        }
        
        // Récupérer les genres selon le type sélectionné
        if ($selectedType === 'film') {
            $genres = Gender::whereHas('movies', function($q) {
                $q->where('is_seen', false);
            })->orderBy('name')->get();
        } elseif ($selectedType === 'serie') {
            $genres = Gender::whereHas('series', function($q) {
                $q->where('is_watched', false);
            })->orderBy('name')->get();
        } else {
            // Si 'all', on combine les genres des deux types
            $movieGenres = Gender::whereHas('movies', function($q) {
                $q->where('is_seen', false);
            })->get();
            
            $seriesGenres = Gender::whereHas('series', function($q) {
                $q->where('is_watched', false);
            })->get();
            
            $genres = $movieGenres->merge($seriesGenres)->unique('id')->sortBy('name');
        }
        
        return view('home', [
            'movies' => $movies,
            'series' => $series,
            'genres' => $genres,
            'selectedGenre' => $request->input('genre', ''),
            'selectedType' => $selectedType
        ]);
    }

    /**
     * Sauvegarde le casting d'un média avec téléchargement des photos de profil
     * 
     * Cette méthode optimisée sauvegarde le casting d'un média en créant
     * les acteurs avec seulement les informations nécessaires, télécharge
     * leurs photos de profil et crée leurs rôles associés.
     * 
     * @param array $cast Les données du casting depuis l'API TMDB
     * @param callable $createRoleCallback Fonction pour créer le rôle (film ou série)
     * @param int|null $maxCastSize Nombre maximum d'acteurs à sauvegarder (défaut: DEFAULT_MAX_CAST_SIZE)
     * 
     * @return void
     */
    protected function saveMediaCast(array $cast, callable $createRoleCallback, ?int $maxCastSize = null): void
    {
        $cast = array_slice($cast, 0, $maxCastSize ?? self::DEFAULT_MAX_CAST_SIZE);
        
        foreach ($cast as $actorData) {
            if (!isset($actorData['id']) || !isset($actorData['name'])) {
                continue;
            }
            
            $actor = Actor::firstOrCreate(
                ['tmdb_id' => $actorData['id']],
                [
                    'name' => $actorData['name'],
                    'profile_path' => $actorData['profile_path'] ?? null,
                    'known_for_department' => $actorData['known_for_department'] ?? null,
                    'popularity' => $actorData['popularity'] ?? null
                ]
            );

            if (isset($actorData['profile_path']) && $actorData['profile_path']) {
                $this->downloadActorProfile($actor, $actorData['profile_path']);
            }

            $createRoleCallback($actor, $actorData);
        }
    }

    /**
     * Télécharge et stocke la photo de profil d'un acteur (méthode publique)
     * 
     * @param Actor $actor L'acteur dont on veut télécharger la photo
     * @param string $profilePath Le chemin de la photo de profil depuis TMDB
     * 
     * @return bool True si le téléchargement a réussi, false sinon
     */
    protected function downloadActorProfile(Actor $actor, string $profilePath): bool
    {
        try {
            $path = "profile/" . $profilePath;
            
            if (Storage::disk('public')->exists($path)) {
                return true;
            }
            
            $response = Http::get("https://image.tmdb.org/t/p/w185" . $profilePath);
            
            if ($response->successful()) {
                Storage::disk('public')->put($path, $response->body());
                Log::info("Photo de profil téléchargée pour l'acteur {$actor->name}", [
                    'actor_id' => $actor->id,
                    'profile_path' => $profilePath
                ]);
                return true;
            } else {
                Log::warning("Échec du téléchargement de la photo de profil", [
                    'actor_id' => $actor->id,
                    'profile_path' => $profilePath,
                    'status_code' => $response->status()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::warning('Impossible de télécharger la photo de profil de l\'acteur', [
                'actor_id' => $actor->id,
                'actor_name' => $actor->name,
                'profile_path' => $profilePath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    //? A revoir
    /**
     * Récupère les films et séries populaires
     * 
     * @param Request $request
     * @return \Illuminate\View\View Vue avec les films et séries populaires
     */
    public function getPopularList(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);
        
        // Limiter à 5 pages maximum
        if ($page > 5) {
            $page = 5;
        }
        if ($page < 1) {
            $page = 1;
        }

        // Récupération des films populaires
        $moviesUrl = "/movie/popular?language=fr-FR&include_adult=false&page=" . $page;
        $moviesData = $this->getCurlData($moviesUrl);
        $moviesData = $this->addSavedStatusToMedia($moviesData);

        // Récupération des séries populaires
        $seriesUrl = "/tv/popular?language=fr-FR&include_adult=false&page=" . $page;
        $seriesData = $this->getCurlData($seriesUrl);
        $seriesData = $this->addSavedStatusToMedia($seriesData);

        $totalResults = max($moviesData['total_results'] ?? 0, $seriesData['total_results'] ?? 0);
        $totalPages = min(max($moviesData['total_pages'] ?? 5, $seriesData['total_pages'] ?? 5), 5);
        $itemsPerPage = 10;
        $startItem = ($page - 1) * $itemsPerPage + 1;
        $endItem = min($page * $itemsPerPage, $totalResults);

        $viewData = [
            'moviesData' => $moviesData,
            'seriesData' => $seriesData,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'totalResults' => $totalResults,
            'startItem' => $startItem,
            'endItem' => $endItem,
        ];

        return view('media.popular', $viewData);
    }
}
