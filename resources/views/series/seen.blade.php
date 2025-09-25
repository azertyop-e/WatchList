@extends('base')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            Séries vues
        </h1>
        <p class="text-gray-600 mb-6">
            Voici toutes les séries que vous avez marquées comme vues.
        </p>
        
        <x-filter 
            :selectedGenre="$selectedGenre ?? ''" 
            :selectedType="$selectedType ?? 'serie'" 
            currentRoute="series.seen" 
            :currentParams="[]" 
            context="seen"
        />
    </div>

    @if(isset($series) && count($series) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($series as $serie)
                <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden group h-full flex flex-col">
                    <div class="h-64 bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center relative overflow-hidden flex-shrink-0">
                        @if(isset($serie->poster_path) && $serie->poster_path)
                            <img src="{{ \App\Helpers\ImageHelper::getPosterUrl($serie->poster_path) }}" 
                                    alt="{{ $serie->name }}" 
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="text-white text-6xl opacity-50">Série</div>
                        @endif
                    </div>

                    <div class="p-6 flex flex-col flex-1">
                        <div class="flex-1">
                            <h2 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">
                                {{ $serie->name }}
                            </h2>
                            
                            @if(isset($serie->first_air_date) && $serie->first_air_date)
                                <p class="text-sm text-gray-500 mb-3">
                                    {{ \Carbon\Carbon::parse($serie->first_air_date)->format('Y') }}
                                </p>
                            @endif

                            @if(isset($serie->vote_average) && $serie->vote_average)
                                <x-vote-average-stars :voteAverage="$serie->vote_average" />
                            @endif

                            @if($serie->genders && $serie->genders->count() > 0)
                                <div class="mb-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($serie->genders as $genre)
                                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-medium">
                                                {{ $genre->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 space-y-3">
                            <form action="{{ route('series.mark-unseen') }}" method="POST">
                                @csrf
                                <input type="hidden" name="media_id" value="{{ $serie->id }}">
                                <button type="submit" 
                                        class="w-full border border-gray-600 text-gray-600 font-semibold py-2 mb-2 px-4 rounded-lg transition-colors duration-200 hover:bg-gray-50 flex items-center justify-center">
                                    Marquer comme non vue
                                </button>
                            </form>

                            <a href="{{ route('series.detail', ['id' => $serie->id]) }}" 
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
                Aucune série vue
            </h3>
            <p class="text-gray-600 mb-8">
                Vous n'avez encore marqué aucune série comme vue.
            </p>
            <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                Retour à l'accueil
            </a>
        </div>
    @endif
</div>
@endsection
