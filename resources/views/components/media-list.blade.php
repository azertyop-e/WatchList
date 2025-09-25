<div class="movies-and-series-list">
    <div class="grid grid-cols-1 {{ !empty($moviesTitle) && !empty($seriesTitle) ? 'lg:grid-cols-2' : 'lg:grid-cols-1' }} gap-8">
        @if(!empty($moviesTitle))
        <div class="movies-column">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                    {{ $moviesTitle }}
                </h2>
            </div>
            
            @if(count($movies) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2 gap-4 items-stretch">
                    @foreach($movies->take($maxItems ?? $movies->count()) as $index => $movie)
                        <div class="relative">
                            @if($showRanking)
                                <div class="absolute top-2 left-2 z-10 bg-white text-black px-3 py-1.5 rounded-full shadow-lg border border-gray-200">
                                    <span class="text-sm font-bold">#{{ $index + 1 }}</span>
                                </div>
                            @endif
                            <x-movie-card :movie="$movie" :showSaveButton="$showSaveButtons" />
                        </div>
                    @endforeach
                </div>
                
                @if($maxItems && count($movies) > $maxItems)
                    <div class="mt-6 text-center">
                        <a href="{{ route('movie.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                            <span>Voir tous les films</span>
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center py-12 bg-gray-50 rounded-lg">
                    <div class="text-6xl mb-4 opacity-50">🎬</div>
                    <p class="text-gray-500 text-lg">Aucun film trouvé</p>
                    <p class="text-gray-400 text-sm mt-2">Les films apparaîtront ici une fois ajoutés</p>
                </div>
            @endif
        </div>
        @endif

        @if(!empty($seriesTitle))
        <div class="series-column">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                    {{ $seriesTitle }}
                </h2>
            </div>
            
            @if(count($series) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2 gap-4 items-stretch">
                    @foreach($series->take($maxItems ?? $series->count()) as $index => $seriesItem)
                        <div class="relative">
                            @if($showRanking)
                                <div class="absolute top-2 left-2 z-10 bg-white text-black px-3 py-1.5 rounded-full shadow-lg border border-gray-200">
                                    <span class="text-sm font-bold">#{{ $index + 1 }}</span>
                                </div>
                            @endif
                            <x-media-card :media="$seriesItem" :showSaveButton="$showSaveButtons" />
                        </div>
                    @endforeach
                </div>
                
                @if($maxItems && count($series) > $maxItems)
                    <div class="mt-6 text-center">
                        <a href="{{ route('series.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                            <span>Voir toutes les séries</span>
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center py-12 bg-gray-50 rounded-lg">
                    <div class="text-6xl mb-4 opacity-50">📺</div>
                    <p class="text-gray-500 text-lg">Aucune série trouvée</p>
                    <p class="text-gray-400 text-sm mt-2">Les séries apparaîtront ici une fois ajoutées</p>
                </div>
            @endif
        </div>
        @endif
    </div>
</div>
