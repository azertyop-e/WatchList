<div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden group h-full flex flex-col">
    <div class="h-64 bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center relative overflow-hidden flex-shrink-0">
        @if(isset($posterPath) && $posterPath)
            <img src="{{ \App\Helpers\ImageHelper::getPosterUrl($posterPath) }}" 
                alt="{{ $title }}" 
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @else
            <div class="text-white text-6xl opacity-50">
                @if($mediaType === 'tv')
                    📺
                @else
                    🎬
                @endif
            </div>
        @endif
        
        <!-- Badge du type de média -->
        <div class="absolute top-3 left-3 z-10">
            <span class="bg-{{ $mediaType === 'tv' ? 'green' : 'blue' }}-600 text-white text-xs px-2 py-1 rounded-full font-medium shadow-lg">
                {{ $getMediaTypeLabel() }}
            </span>
        </div>
        
        @if($isObject && isset($media) && $media->id)
        <form action="{{ $getMarkSeenRoute() }}" method="POST" class="absolute top-3 right-3 z-10">
            @csrf
            <input type="hidden" name="{{ $mediaType === 'tv' ? 'series_id' : 'movie_id' }}" value="{{ $media->id }}">
            <button type="submit" 
                    class="bg-white text-black px-3 py-1.5 rounded-full shadow-lg border border-gray-200 transition-all duration-200 hover:bg-gray-50 hover:shadow-xl hover:scale-105 hover:border-gray-300"
                    title="Marquer comme vu">
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-xs font-medium">Vu</span>
                </div>
            </button>
        </form>
        @endif
    </div>

    <div class="p-6 flex flex-col flex-1">
        <div class="flex-1">
            <h2 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">
                {{ $title }}
            </h2>
            
            @if(isset($releaseDate) && $releaseDate)
                <p class="text-sm text-gray-500 mb-3">
                    📅 {{ \Carbon\Carbon::parse($releaseDate)->format('Y') }}
                </p>
            @endif

            @if(isset($voteAverage) && $voteAverage)
                <x-vote-average-stars :voteAverage="$voteAverage" />
            @endif

            @if($isObject && $media->genders && $media->genders->count() > 0)
                <div class="mb-3">
                    <div class="flex flex-wrap gap-1">
                        @foreach($media->genders as $genre)
                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-medium">
                                {{ \App\Helpers\TranslationHelper::translateGenre($genre->name) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-4 space-y-3">
            @if($showSaveButton)
            <form action="{{ $getSaveRoute() }}" method="POST">
                @csrf
                <input type="hidden" name="{{ $mediaType === 'tv' ? 'series_id' : 'movie_id' }}" value="{{ $id }}">
                <button type="submit" {{ $isSaved ? 'disabled' : '' }}
                        class="w-full border border-blue-600 text-blue-600 font-semibold py-2 mb-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center {{$isSaved ? 'bg-gray-100 text-gray-400 border-gray-300 cursor-not-allowed' : 'hover:bg-blue-50'}}">
                    @if($isSaved)
                        Déjà dans ma liste
                    @else
                        Ajouter à ma liste
                    @endif
                </button>
            </form>
            @endif

            <a href="{{ $getDetailRoute() }}" 
               class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center">
                <span>Voir les détails</span>
            </a>
        </div>
    </div>
</div>
