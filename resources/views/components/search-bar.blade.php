<div class="max-w-md mx-auto">
    <form action="{{ $action ?: route('movie.search') }}" method="{{ $method }}" class="flex gap-2">
        <input type="text" 
               name="query" 
               placeholder="{{ $placeholder ?: 'Rechercher un film...' }}" 
               value="{{ $value ?: request('query') }}"
               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
        <button type="submit" 
                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
            Rechercher
        </button>
    </form>
</div>