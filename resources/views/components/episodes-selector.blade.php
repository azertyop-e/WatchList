@props(['series'])

@if($series->seasons && $series->seasons->count() > 0)
<div class="bg-white bg-opacity-95 backdrop-blur-sm rounded-lg shadow-xl p-8 mt-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Épisodes</h2>
        <div class="flex items-center space-x-4">
            <select id="season-selector" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @foreach($series->seasons as $index => $season)
                    <option value="{{ $index }}" {{ $index === 0 ? 'selected' : '' }}>
                        {{ $season->name }} 
                        @if($season->episode_count || $season->episodes->count())
                            ({{ $season->episode_count ?? $season->episodes->count() }} épisode{{ ($season->episode_count ?? $season->episodes->count()) > 1 ? 's' : '' }})
                        @endif
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    
    <!-- Conteneur pour les épisodes -->
    <div id="episodes-container">
        @foreach($series->seasons as $index => $season)
            <div class="season-episodes {{ $index === 0 ? '' : 'hidden' }}" data-season="{{ $index }}">
                @if($season->episodes && $season->episodes->count() > 0)
                    <div class="space-y-3">
                        @foreach($season->episodes->sortBy('episode_number') as $episode)
                            <div class="border border-gray-200 rounded-lg overflow-hidden hover:bg-gray-50 transition-colors duration-200">
                                <div class="flex flex-col sm:hidden">
                                    <!-- Image mobile -->
                                    <div class="w-full h-48 flex-shrink-0">
                                        @if($episode->still_path)
                                            <img src="{{ \App\Helpers\ImageHelper::getStillUrl($episode->still_path) }}" 
                                                 alt="{{ $episode->name }}" 
                                                 class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Contenu mobile -->
                                    <div class="flex-1 p-4 flex flex-col justify-between">
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="bg-purple-100 text-purple-800 text-sm font-semibold px-3 py-1 rounded-full">
                                                    Épisode {{ $episode->episode_number }}
                                                </span>
                                                <div class="flex items-center space-x-4">
                                                    @if($episode->runtime)
                                                        <span class="text-sm text-gray-500">{{ $episode->runtime }} min</span>
                                                    @endif
                                                    @if(isset($episode->id) && isset($series->is_saved) && $series->is_saved)
                                                        @if($episode->is_watched)
                                                            <form action="{{ route('series.episodes.mark-unwatched') }}" method="POST" class="inline">
                                                                @csrf
                                                                <input type="hidden" name="episode_id" value="{{ $episode->id }}">
                                                                <button type="submit" 
                                                                        class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full transition-all duration-200 hover:bg-gray-200 hover:scale-105"
                                                                        title="Marquer comme non vu">
                                                                    <div class="flex items-center gap-1.5">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                                        </svg>
                                                                        <span class="text-xs font-medium">Vu</span>
                                                                    </div>
                                                                </button>
                                                            </form>
                                                        @else
                                                            <form action="{{ route('series.episodes.mark-watched') }}" method="POST" class="inline">
                                                                @csrf
                                                                <input type="hidden" name="episode_id" value="{{ $episode->id }}">
                                                                <button type="submit" 
                                                                        class="bg-purple-100 text-purple-600 px-3 py-1.5 rounded-full transition-all duration-200 hover:bg-purple-200 hover:scale-105"
                                                                        title="Marquer comme vu">
                                                                        <div class="flex items-center gap-1.5">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                                        </svg>
                                                                        <span class="text-xs font-medium">Vu</span>
                                                                    </div>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <h4 class="text-lg font-semibold text-gray-900 mb-2">
                                                {{ $episode->name ?: "Épisode {$episode->episode_number}" }}
                                            </h4>
                                            
                                            @if($episode->overview)
                                                <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $episode->overview }}</p>
                                            @endif
                                        </div>
                                        
                                    </div>
                                </div>

                                <div class="hidden sm:flex">
                                    <div class="w-48 h-32 flex-shrink-0">
                                        @if($episode->still_path)
                                            <img src="{{ \App\Helpers\ImageHelper::getStillUrl($episode->still_path) }}" 
                                                 alt="{{ $episode->name }}" 
                                                 class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex-1 p-4 flex flex-col justify-between">
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="bg-purple-100 text-purple-800 text-sm font-semibold px-3 py-1 rounded-full">
                                                    Épisode {{ $episode->episode_number }}
                                                </span>
                                                <div class="flex items-center space-x-4">
                                                    @if($episode->runtime)
                                                        <span class="text-sm text-gray-500">{{ $episode->runtime }} min</span>
                                                    @endif
                                                    @if(isset($episode->id) && isset($series->is_saved) && $series->is_saved)
                                                        @if($episode->is_watched)
                                                            <form action="{{ route('series.episodes.mark-unwatched') }}" method="POST" class="inline">
                                                                @csrf
                                                                <input type="hidden" name="episode_id" value="{{ $episode->id }}">
                                                                <button type="submit" 
                                                                        class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full transition-all duration-200 hover:bg-purple-200 hover:scale-105"
                                                                        title="Marquer comme non vu">
                                                                    <div class="flex items-center gap-1.5">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                                        </svg>
                                                                        <span class="text-xs font-medium">Vu</span>
                                                                    </div>
                                                                </button>
                                                            </form>
                                                        @else
                                                            <form action="{{ route('series.episodes.mark-watched') }}" method="POST" class="inline">
                                                                @csrf
                                                                <input type="hidden" name="episode_id" value="{{ $episode->id }}">
                                                                <button type="submit" 
                                                                        class="bg-purple-100 text-purple-600 px-3 py-1.5 rounded-full transition-all duration-200 hover:bg-purple-200 hover:scale-105"
                                                                        title="Marquer comme vu">
                                                                    <div class="flex items-center gap-1.5">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                                        </svg>
                                                                        <span class="text-xs font-medium">Vu</span>
                                                                    </div>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <h4 class="text-lg font-semibold text-gray-900 mb-2">
                                                {{ $episode->name ?: "Épisode {$episode->episode_number}" }}
                                            </h4>
                                            
                                            @if($episode->overview)
                                                <p class="text-gray-600 text-sm mb-3 line-clamp-3">{{ $episode->overview }}</p>
                                            @endif
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const seasonSelector = document.getElementById('season-selector');
    const episodesContainer = document.getElementById('episodes-container');
    
    if (seasonSelector && episodesContainer) {
        seasonSelector.addEventListener('change', function() {
            const selectedSeason = this.value;
            const allSeasonDivs = episodesContainer.querySelectorAll('.season-episodes');
            
            allSeasonDivs.forEach(div => {
                div.classList.add('hidden');
            });
            
            const selectedSeasonDiv = episodesContainer.querySelector(`[data-season="${selectedSeason}"]`);
            if (selectedSeasonDiv) {
                selectedSeasonDiv.classList.remove('hidden');
            }
        });
    }
});
</script>
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