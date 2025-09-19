@extends('base')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            Résultats de recherche
        </h1>

        @if(isset($unifiedData['results']) && count($unifiedData['results']) > 0)
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                {{ $totalResults }} résultat(s) trouvé(s) pour "{{ $query }}"
                @if($totalPages > 1)
                    (Page {{ $currentPage }} sur {{ $totalPages }})
                @endif
            </p>
        @else
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Aucun résultat trouvé pour "{{ $query }}"
            </p>
        @endif
    </div>

    <div class="mb-8">
        <x-search-bar />
    </div>

    @if(isset($unifiedData['results']) && count($unifiedData['results']) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($unifiedData['results'] as $media)
                <x-media-card :media="$media" :showSaveButton="true" />
            @endforeach
        </div>

        <!-- Informations de pagination -->
        <div class="mt-6 mb-4 text-center text-gray-600">
            <p class="text-sm">
                Affichage de {{ $startItem }} à {{ $endItem }} sur {{ $totalResults }} résultat(s)
            </p>
        </div>

        <!-- Pagination -->
        @if($totalPages > 1)
            <div class="mt-8 mb-4 flex flex-col lg:flex-row justify-between items-center space-y-4 lg:space-y-0">
                <nav aria-label="Pagination" class="flex justify-center items-center text-gray-600">
                    @if($currentPage > 1)
                        <a href="{{ route('movie.search', ['query' => $query, 'page' => $currentPage - 1]) }}" 
                            class="p-2 mr-4 rounded hover:bg-gray-100 transition-colors duration-200"
                            aria-label="Page précédente">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    @else
                        <span class="p-2 mr-4 rounded text-gray-600 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </span>
                    @endif

                    @php
                        $showFirst = true;
                        $showLast = $totalPages > 1;
                        $showPrevious = $currentPage > 1 && $currentPage > 2;
                        $showNext = $currentPage < $totalPages && $currentPage < $totalPages - 1;
                    @endphp

                    @if($showFirst)
                        @if($currentPage == 1)
                            <span class="px-4 py-2 rounded bg-gray-200 text-gray-900 font-medium cursor-default">1</span>
                        @else
                            <a href="{{ route('movie.search', ['query' => $query, 'page' => 1]) }}" 
                                class="px-4 py-2 rounded hover:bg-gray-100 transition-colors duration-200">1</a>
                        @endif
                    @endif

                    @if($currentPage > 3)
                        <span class="px-4 py-2 text-gray-400">...</span>
                    @endif

                    @if($showPrevious)
                        <a href="{{ route('movie.search', ['query' => $query, 'page' => $currentPage - 1]) }}" 
                            class="px-4 py-2 rounded hover:bg-gray-100 transition-colors duration-200">{{ $currentPage - 1 }}</a>
                    @endif

                    @if($currentPage != 1 && $currentPage != $totalPages)
                        <span class="px-4 py-2 rounded bg-gray-200 text-gray-900 font-medium cursor-default">{{ $currentPage }}</span>
                    @endif

                    @if($showNext)
                        <a href="{{ route('movie.search', ['query' => $query, 'page' => $currentPage + 1]) }}" 
                            class="px-4 py-2 rounded hover:bg-gray-100 transition-colors duration-200">{{ $currentPage + 1 }}</a>
                    @endif

                    @if($currentPage < $totalPages - 2)
                        <span class="px-4 py-2 text-gray-400">...</span>
                    @endif

                    @if($showLast && $totalPages != 1)
                        @if($currentPage == $totalPages)
                            <span class="px-4 py-2 rounded bg-gray-200 text-gray-900 font-medium cursor-default">{{ $totalPages }}</span>
                        @else
                            <a href="{{ route('movie.search', ['query' => $query, 'page' => $totalPages]) }}" 
                                class="px-4 py-2 rounded hover:bg-gray-100 transition-colors duration-200">{{ $totalPages }}</a>
                        @endif
                    @endif

                    @if($currentPage < $totalPages)
                        <a href="{{ route('movie.search', ['query' => $query, 'page' => $currentPage + 1]) }}" 
                            class="p-2 ml-4 rounded hover:bg-gray-100 transition-colors duration-200"
                            aria-label="Page suivante">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @else
                        <span class="p-2 ml-4 rounded text-gray-300 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    @endif
                </nav>
            </div>
        @endif

    @else
        <div class="text-center py-16">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">
                Aucun film trouvé
            </h3>
            <p class="text-gray-600 mb-8">
                Essayez avec d'autres mots-clés ou vérifiez l'orthographe.
            </p>
            <div class="space-x-4">
                <a href="{{ route('movie.popular') }}" 
                   class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                    Films populaires
                </a>
                <a href="{{ route('home') }}" 
                   class="inline-flex items-center border border-blue-600 px-6 py-3 hover:border-blue-700 text-blue-700 font-semibold rounded-lg transition-colors duration-200">
                    Retour à l'accueil
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
