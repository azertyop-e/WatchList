@props(['series'])

<div class="series-progress">
    @if($nextEpisode)
        <div class="flex items-center justify-center gap-3">
            <form action="{{ route('series.mark-episode-watched', $series->id) }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="previous">
                <button type="submit" 
                        class="text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full p-1 transition-all duration-200"
                        title="Épisode précédent">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
            </form>
            
            <span class="text-sm font-medium text-gray-900">
                S{{ $nextEpisode->season->season_number }}E{{ $nextEpisode->episode_number }}
            </span>
            
            <form action="{{ route('series.mark-episode-watched', $series->id) }}" method="POST">
                @csrf
                <input type="hidden" name="action" value="next">
                <button type="submit" 
                        class="text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full p-1 transition-all duration-200"
                        title="Épisode suivant">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </form>
        </div>
    @endif
</div>
