@extends('base')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            Résultats de recherche
        </h1>

        @if(isset($moviesData['results']) && count($moviesData['results']) > 0)
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                {{ count($moviesData['results']) }} film(s) trouvé(s) pour "{{ request('query') }}"
            </p>
        @else
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Aucun résultat trouvé pour "{{ request('query') }}"
            </p>
        @endif
    </div>

    <div class="mb-8">
        <x-search-bar />
    </div>

    @if(isset($moviesData['results']) && count($moviesData['results']) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($moviesData['results'] as $movie)
                <x-movie-card :movie="$movie" :showSaveButton="true" />
            @endforeach
        </div>

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
