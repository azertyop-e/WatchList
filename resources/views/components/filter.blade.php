<div class="mb-8">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Type de contenu</h3>
        <div class="flex flex-wrap gap-2">
            <a href="{{ $urls['film'] }}" 
                class="inline-block text-sm px-4 py-2 rounded-full font-medium transition-all duration-200 border-2 {{ $selectedType == 'film' ? 'bg-blue-100 text-blue-800 border-blue-300 shadow-sm' : 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200 hover:border-gray-300' }}">
                Films
            </a>
            <a href="{{ $urls['serie'] }}" 
                class="inline-block text-sm px-4 py-2 rounded-full font-medium transition-all duration-200 border-2 {{ $selectedType == 'serie' ? 'bg-blue-100 text-blue-800 border-blue-300 shadow-sm' : 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200 hover:border-gray-300' }}">
                Séries
            </a>
        </div>
    </div>

    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Genres</h3>
        <div class="flex flex-wrap gap-2">
            <a href="{{ $urls['all_genres'] }}" 
                class="inline-block text-sm px-4 py-2 rounded-full font-medium transition-all duration-200 border-2 {{ $selectedGenre == '' ? 'bg-blue-100 text-blue-800 border-blue-300 shadow-sm' : 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200 hover:border-gray-300' }}">
                Tous les genres
            </a>
            
            @if($genres && count($genres) > 0)
                @foreach($genres as $genre)
                    <a href="{{ $urls['genre_' . $genre->id] }}" 
                        class="inline-block text-sm px-4 py-2 rounded-full font-medium transition-all duration-200 border-2 {{ $selectedGenre == $genre->id ? 'bg-blue-100 text-blue-800 border-blue-300 shadow-sm' : 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200 hover:border-gray-300' }}">
                        {{ $genre->name }}
                    </a>
                @endforeach
            @endif
        </div>
    </div>

    @if($hasActiveFilters)
        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-medium text-gray-700">Filtres actifs :</h4>
                <a href="{{ $urls['remove_all'] }}" class="text-xs text-gray-500 hover:text-red-600 transition-colors duration-200">
                    Supprimer tous les filtres
                </a>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($selectedType != 'film')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                        Type: {{ ucfirst($selectedType) }}
                        <a href="{{ $urls['remove_type'] }}" class="ml-2 text-current hover:text-red-600 transition-colors duration-200 font-bold text-sm leading-none">×</a>
                    </span>
                @endif
                
                @if($selectedGenre != '')
                    @php
                        $selectedGenreName = $genres->where('id', $selectedGenre)->first()->name ?? 'Genre inconnu';
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                        Genre: {{ $selectedGenreName }}
                        <a href="{{ $urls['remove_genre'] }}" class="ml-2 text-current hover:text-red-600 transition-colors duration-200 font-bold text-sm leading-none">×</a>
                    </span>
                @endif
            </div>
        </div>
    @endif
</div>