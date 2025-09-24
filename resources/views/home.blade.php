@extends('base')

@section('content')

    @if((isset($movies) && count($movies) > 0) || (isset($series) && count($series) > 0))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Ma Liste 
            </h1>
            <p class="text-gray-600 mb-6">
                Voici les films et séries que vous avez enregistrés et que vous n'avez pas encore regardés. Parfait pour planifier vos prochaines séances cinéma !
            </p>
               
            <x-filter 
                :selectedGenre="$selectedGenre ?? ''" 
                :selectedType="$selectedType ?? 'all'" 
                :currentParams="[]" 
                currentRoute="home" 
                context="unseen"
            />
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($movies as $movie)
                <x-media-card 
                    :media="$movie"
                    :mediaType="'movie'"
                    :title="$movie->title"
                    :posterPath="$movie->poster_path"
                    :releaseDate="$movie->release_date"
                    :voteAverage="$movie->vote_average"
                    :id="$movie->id"
                    :isObject="true"
                    :showSaveButton="false"
                />
            @endforeach
            
            @foreach ($series as $serie)
                <x-media-card 
                    :media="$serie"
                    :mediaType="'tv'"
                    :title="$serie->name"
                    :posterPath="$serie->poster_path"
                    :releaseDate="$serie->first_air_date"
                    :voteAverage="$serie->vote_average"
                    :id="$serie->id"
                    :isObject="true"
                    :showSaveButton="false"
                />
            @endforeach
        </div>
    </div>
    @else
        <div class="text-center py-16">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">
                Découvrez nos films et séries
            </h3>
            <p class="text-gray-600 mb-8">
                Découvrez nos films et séries et ajoutez-les à votre liste pour ne pas les oublier.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('movie.popular') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                    Découvrir les films populaires
                </a>
                <a href="{{ route('series.popular') }}" class="inline-flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors duration-200">
                    Découvrir les séries populaires
                </a>
            </div>
        </div>
    @endif

@endsection