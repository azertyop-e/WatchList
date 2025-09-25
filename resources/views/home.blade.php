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
        
        <x-media-list 
            :movies="$movies" 
            :series="$series" 
            moviesTitle="Mes Films" 
            seriesTitle="Mes Séries" 
            :showSaveButtons="false" 
        />
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