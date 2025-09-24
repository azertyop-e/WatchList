@extends('base')

@section('content')

    @if(isset($series) && count($series) > 0)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Ma Liste de Séries
            </h1>
            <p class="text-gray-600 mb-6">
                Voici les séries que vous avez enregistrées et que vous n'avez pas encore regardées. Parfait pour planifier vos prochaines sessions de binge-watching !
            </p>
               
            <x-filter 
                :selectedGenre="$selectedGenre ?? ''" 
                :selectedType="$selectedType ?? 'serie'" 
                :currentParams="[]" 
                currentRoute="series.home" 
                context="unseen"
            />
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($series as $serie)
                <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden group h-full flex flex-col">
                    <div class="h-64 bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center relative overflow-hidden flex-shrink-0">
                        @if(isset($serie->poster_path) && $serie->poster_path)
                            <img src="{{ \App\Helpers\ImageHelper::getPosterUrl($serie->poster_path) }}" 
                                    alt="{{ $serie->name }}" 
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="text-white text-6xl opacity-50">📺</div>
                        @endif
                    </div>

                    <div class="p-6 flex flex-col flex-1">
                        <div class="flex-1">
                            <h2 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">
                                {{ $serie->name }}
                            </h2>
                            
                            @if(isset($serie->first_air_date) && $serie->first_air_date)
                                <p class="text-sm text-gray-500 mb-3">
                                    📅 {{ \Carbon\Carbon::parse($serie->first_air_date)->format('Y') }}
                                </p>
                            @endif

                            @if(isset($serie->vote_average) && $serie->vote_average)
                                <x-vote-average-stars :voteAverage="$serie->vote_average" />
                            @endif

                            @if($serie->genders && $serie->genders->count() > 0)
                                <div class="mb-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($serie->genders as $genre)
                                            <span class="inline-block bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full font-medium">
                                                {{ $genre->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(isset($serie->number_of_seasons) && $serie->number_of_seasons)
                                <p class="text-sm text-gray-600 mb-2">
                                    📺 {{ $serie->number_of_seasons }} saison{{ $serie->number_of_seasons > 1 ? 's' : '' }}
                                </p>
                            @endif
                        </div>

                        <div class="mt-4 space-y-3">
                            <form action="{{ route('series.mark-seen') }}" method="POST">
                                @csrf
                                <input type="hidden" name="series_id" value="{{ $serie->id }}">
                                <button type="submit" 
                                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 mb-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center">
                                    Marquer comme vue
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
    </div>
    @else
        <div class="text-center py-16">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">
                Découvrez nos séries
            </h3>
            <p class="text-gray-600 mb-8">
                Découvrez nos séries et ajoutez-les à votre liste pour ne pas les oublier.
            </p>
            <a href="{{ route('series.popular') }}" class="inline-flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors duration-200">
                Aller découvrir les séries populaires
            </a>
        </div>
    @endif

@endsection
