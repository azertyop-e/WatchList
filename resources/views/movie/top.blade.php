@extends('base')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            Top des films
        </h1>

        @if(session('success'))
            <x-toast type="success" message="{{ session('success') }}" />
        @endif

        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            Découvrez les films les mieux notés du moment, sélectionnés pour vous
        </p>
    </div>

    <div class="mb-8">
        <x-search-bar />
    </div>

    @if(isset($moviesData['results']) && count($moviesData['results']) > 0)
        @if(count($moviesData['results']))
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Les autres films du top</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($moviesData['results'] as $index => $movie)
                    <div class="relative">
                        <div class="absolute top-2 left-2 z-10 bg-white text-black px-3 py-1.5 rounded-full shadow-lg border border-gray-200">
                            <span class="text-sm font-bold">#{{ $index + 1 }}</span>
                        </div>
                        <x-movie-card :movie="$movie" :showSaveButton="true" />
                    </div>
                @endforeach
            </div>
        @endif

    @else
        <div class="text-center py-16">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">
                Aucun film trouvé
            </h3>
            <p class="text-gray-600 mb-8">
                Nous n'avons trouvé aucun film pour le moment.
            </p>
            <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                Retour à l'accueil
            </a>
        </div>
    @endif
</div>
@endsection