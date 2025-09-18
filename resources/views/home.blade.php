@extends('base')

@section('content')

    @if(isset($movies) && count($movies) > 0)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Ma Liste 
            </h1>
            <p class="text-gray-600 mb-6">
                Voici les films que vous avez enregistrés et que vous n'avez pas encore regardés. Parfait pour planifier vos prochaines séances cinéma !
            </p>
               
            <div class="flex flex-wrap gap-2 mb-6">
                <a href="{{ route('home') }}" 
                    class="inline-block text-sm px-3 py-2 rounded-full font-medium transition-colors duration-200 {{ !isset($selectedGenre) || $selectedGenre == '' ? 'bg-blue-100 text-blue-800' : 'bg-gray-200 text-gray-600 hover:bg-gray-300' }}">
                    Tous les genres
                </a>
                
                @if(isset($genres))
                    @foreach($genres as $genre)
                        <a href="{{ route('home', ['genre' => $genre->id]) }}" 
                            class="inline-block text-sm px-3 py-2 rounded-full font-medium transition-colors duration-200 {{ (isset($selectedGenre) && $selectedGenre == $genre->id) ? 'bg-blue-100 text-blue-800' : 'bg-gray-200 text-gray-600 hover:bg-gray-300' }}">
                            {{ $genre->name }}
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($movies as $movie)
                <x-movie-card :movie="$movie" :showSaveButton="false" />
            @endforeach
        </div>
    </div>
    @else
        <div class="text-center py-16">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">
                Découvrez nos films
            </h3>
            <p class="text-gray-600 mb-8">
                Découvrez nos films et ajoutez-les à votre liste pour ne pas les oublier.
            </p>
            <a href="{{ route('movie.popular') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                Aller découvrir les films populaires
            </a>
        </div>
    @endif

@endsection