@extends('base')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            Top des Films et Séries
        </h1>

        @if(session('success'))
            <x-toast type="success" message="{{ session('success') }}" />
        @endif

        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Découvrez les films et séries les mieux notés du moment, organisés en deux colonnes pour une meilleure expérience de navigation
        </p>
    </div>

    <div class="mb-8">
        <x-search-bar />
    </div>

    @if((isset($moviesData['results']) && count($moviesData['results']) > 0) || (isset($seriesData['results']) && count($seriesData['results']) > 0))
        <x-media-list 
            :movies="collect($moviesData['results'] ?? [])" 
            :series="collect($seriesData['results'] ?? [])" 
            moviesTitle="Top des Films" 
            seriesTitle="Top des Séries" 
            :showSaveButtons="true" 
            :showRanking="true"
        />

        <div class="mt-8 text-center">
            <div class="text-sm text-gray-600">
                Affichage des 100 premiers films et séries les mieux notés
            </div>
        </div>
    @else
        <div class="text-center py-16">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">
                Aucun contenu trouvé
            </h3>
            <p class="text-gray-600 mb-8">
                Nous n'avons trouvé aucun film ou série dans le top pour le moment.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                    Retour à l'accueil
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
