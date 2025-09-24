@extends('base')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            Médias vus
        </h1>
        <p class="text-gray-600 mb-6">
            Voici tous les films et séries que vous avez marqués comme vus.
        </p>
    </div>

    @if(isset($allMedia) && count($allMedia) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($allMedia as $media)
                <x-media-card :media="$media" :showSaveButton="false" :showMarkUnseenButton="true" />
            @endforeach
        </div>
    @else
        <div class="text-center py-16">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">
                Aucun média vu
            </h3>
            <p class="text-gray-600 mb-8">
                Vous n'avez encore marqué aucun film ou série comme vu.
            </p>
            <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                Retour à l'accueil
            </a>
        </div>
    @endif
</div>
@endsection
