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
