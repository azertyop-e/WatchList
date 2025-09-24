@props(['series'])

@if($series->seasons && $series->seasons->count() > 0)
<div class="bg-white bg-opacity-95 backdrop-blur-sm rounded-lg shadow-xl p-8 mt-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Saisons et Épisodes</h2>
        <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
            {{ $series->seasons->count() }} saison{{ $series->seasons->count() > 1 ? 's' : '' }}
        </span>
    </div>
    
    <div class="space-y-8">
        @foreach($series->seasons as $season)
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <!-- En-tête de la saison -->
                <div class="bg-gradient-to-r from-purple-500 to-pink-500 text-white p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold">{{ $season->name }}</h3>
                            @if($season->overview)
                                <p class="text-purple-100 text-sm mt-1 line-clamp-2">{{ $season->overview }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-purple-100">
                                {{ $season->episode_count ?? $season->episodes->count() }} épisode{{ ($season->episode_count ?? $season->episodes->count()) > 1 ? 's' : '' }}
                            </div>
                            @if($season->air_date)
                                <div class="text-sm text-purple-100">
                                    {{ \Carbon\Carbon::parse($season->air_date)->format('Y') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Liste des épisodes -->
                @if($season->episodes && $season->episodes->count() > 0)
                    <div class="bg-gray-50">
                        @foreach($season->episodes->sortBy('episode_number') as $episode)
                            <div class="border-b border-gray-200 last:border-b-0 p-4 hover:bg-gray-100 transition-colors duration-200">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-2 py-1 rounded-full mr-3">
                                                Épisode {{ $episode->episode_number }}
                                            </span>
                                            @if($episode->is_watched)
                                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">
                                                    ✓ Vu
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <h4 class="text-lg font-semibold text-gray-900 mb-1">
                                            {{ $episode->name ?: "Épisode {$episode->episode_number}" }}
                                        </h4>
                                        
                                        @if($episode->overview)
                                            <p class="text-gray-600 text-sm mb-2 line-clamp-2">{{ $episode->overview }}</p>
                                        @endif
                                        
                                        <div class="flex items-center text-sm text-gray-500 space-x-4">
                                            @if($episode->air_date)
                                                <span>📅 {{ \Carbon\Carbon::parse($episode->air_date)->format('d/m/Y') }}</span>
                                            @endif
                                            @if($episode->runtime)
                                                <span>⏱️ {{ $episode->runtime }} min</span>
                                            @endif
                                            @if($episode->vote_average)
                                                <div class="flex items-center">
                                                    <span class="mr-1">⭐</span>
                                                    <span>{{ number_format($episode->vote_average, 1) }}/10</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="ml-4 flex flex-col space-y-2">
                                        @if($episode->still_path)
                                            <img src="{{ \App\Helpers\ImageHelper::getStillUrl($episode->still_path) }}" 
                                                 alt="{{ $episode->name }}" 
                                                 class="w-20 h-12 object-cover rounded">
                                        @endif
                                        
                                        @if($episode->is_watched)
                                            <form action="{{ route('series.episodes.mark-unwatched') }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="episode_id" value="{{ $episode->id }}">
                                                <button type="submit" 
                                                        class="text-xs px-3 py-1 bg-gray-200 text-gray-600 hover:bg-gray-300 rounded-full transition-colors duration-200">
                                                    Marquer non vu
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('series.episodes.mark-watched') }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="episode_id" value="{{ $episode->id }}">
                                                <button type="submit" 
                                                        class="text-xs px-3 py-1 bg-green-600 text-white hover:bg-green-700 rounded-full transition-colors duration-200">
                                                    Marquer vu
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-gray-50 p-8 text-center">
                        <div class="text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-lg font-medium">Aucun épisode disponible</p>
                            <p class="text-sm">Les épisodes de cette saison n'ont pas encore été chargés.</p>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@else
<div class="bg-white bg-opacity-95 backdrop-blur-sm rounded-lg shadow-xl p-8 mt-8">
    <div class="text-center">
        <div class="text-gray-500">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Aucune saison disponible</h3>
            <p class="text-gray-600 mb-6">Les saisons et épisodes de cette série n'ont pas encore été chargés.</p>
            <form action="{{ route('series.seasons.save-all', ['seriesTmdbId' => $series->tmdb_id]) }}" method="POST">
                @csrf
                <button type="submit" 
                        class="inline-flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Charger toutes les saisons
                </button>
            </form>
        </div>
    </div>
</div>
@endif
