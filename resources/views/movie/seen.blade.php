@extends('base')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            Films vus
        </h1>
        <p class="text-gray-600 mb-6">
            Voici tous les films que vous avez marqués comme vus.
        </p>
        
        <x-filter 
            :selectedGenre="$selectedGenre ?? ''" 
            :selectedType="$selectedType ?? 'film'" 
            currentRoute="movie.seen" 
            :currentParams="[]" 
            context="seen"
        />
    </div>

    @if(isset($movies) && count($movies) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($movies as $movie)
                <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden group h-full flex flex-col">
                    <div class="h-64 bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center relative overflow-hidden flex-shrink-0">
                        @if(isset($movie->poster_path) && $movie->poster_path)
                            <img src="{{ \App\Helpers\ImageHelper::getPosterUrl($movie->poster_path) }}" 
                                    alt="{{ $movie->title }}" 
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="text-white text-6xl opacity-50"></div>
                        @endif
                    </div>

                    <div class="p-6 flex flex-col flex-1">
                        <div class="flex-1">
                            <h2 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">
                                {{ $movie->title }}
                            </h2>
                            
                            @if(isset($movie->release_date) && $movie->release_date)
                                <p class="text-sm text-gray-500 mb-3">
                                    {{ \Carbon\Carbon::parse($movie->release_date)->format('Y') }}
                                </p>
                            @endif

                            @if(isset($movie->vote_average) && $movie->vote_average)
                                <x-vote-average-stars :voteAverage="$movie->vote_average" />
                            @endif

                            @if($movie->genders && $movie->genders->count() > 0)
                                <div class="mb-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($movie->genders as $genre)
                                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-medium">
                                                {{ $genre->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 space-y-3">
                            <form action="{{ route('movie.mark-unseen') }}" method="POST">
                                @csrf
                                <input type="hidden" name="movie_id" value="{{ $movie->id }}">
                                <button type="submit" 
                                        class="w-full border border-gray-600 text-gray-600 font-semibold py-2 mb-2 px-4 rounded-lg transition-colors duration-200 hover:bg-gray-50 flex items-center justify-center">
                                    Marquer comme non vu
                                </button>
                            </form>
                            
                            <!-- Bouton de suppression -->
                            <form action="{{ route('movie.delete') }}" method="POST" 
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce film ? Cette action est irréversible.')">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="movie_id" value="{{ $movie->id }}">
                                <button type="submit" 
                                        class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Supprimer définitivement
                                </button>
                            </form>

                            <a href="{{ route('movie.detail', ['id' => $movie->id]) }}" 
                               class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center">
                                <span>Voir les détails</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-16">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">
                Aucun film vu
            </h3>
            <p class="text-gray-600 mb-8">
                Vous n'avez encore marqué aucun film comme vu.
            </p>
            <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                Retour à l'accueil
            </a>
        </div>
    @endif
</div>
@endsection
