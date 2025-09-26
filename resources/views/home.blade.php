@extends('base')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            Ma Liste 
        </h1>
        <p class="text-gray-600 mb-6">
            Gérez vos films et séries : découvrez ce qui vous attend et revivez vos favoris déjà visionnés.
        </p>
           
        <x-filter 
            :selectedGenre="$selectedGenre ?? ''" 
            :selectedType="$selectedType ?? 'all'" 
            :currentParams="[]" 
            currentRoute="home" 
            context="all"
        />
    </div>
    
    @if((isset($movies) && count($movies) > 0) || (isset($series) && count($series) > 0))
        <div class="mb-12">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-2 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    À regarder
                </h2>
                <p class="text-gray-600">
                    Films et séries en attente dans votre liste
                </p>
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
        <div class="text-center py-12 mb-12 bg-gray-50 rounded-lg">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            <h3 class="text-xl font-bold text-gray-900 mb-2">
                Aucun média en attente
            </h3>
            <p class="text-gray-600 mb-6">
                Découvrez des films et séries pour alimenter votre liste !
            </p>
            <a href="{{ route('popular') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                Découvrir les films et séries populaires
            </a>
        </div>
    @endif

    @if(isset($allSeenMedia) && count($allSeenMedia) > 0)
        <div class="border-t border-gray-200 pt-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-2 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Déjà vus
                </h2>
                <p class="text-gray-600">
                    Films et séries que vous avez déjà visionnés
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($allSeenMedia as $media)
                    <x-media-card :media="$media" :showSaveButton="false" :showMarkUnseenButton="true" />
                @endforeach
            </div>
        </div>
    @else
        <div class="border-t border-gray-200 pt-8">
            <div class="text-center py-12 bg-gray-50 rounded-lg">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2">
                    Aucun média vu
                </h3>
                <p class="text-gray-600">
                    Commencez à regarder vos films et séries pour les voir apparaître ici !
                </p>
            </div>
        </div>
    @endif
</div>
@endsection