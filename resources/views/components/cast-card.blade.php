<div class="text-center group cursor-pointer transform hover:-translate-y-1 transition-all duration-300">
    <div class="mb-4 relative overflow-hidden rounded-xl shadow-lg group-hover:shadow-xl transition-all duration-300">
        @if(isset($actor['profile_path']) && $actor['profile_path'])
            <img src="{{ \App\Helpers\ImageHelper::getProfileUrl($actor['profile_path']) }}" 
                 alt="{{ $actor['name'] }}" 
                 class="w-full h-40 object-cover group-hover:scale-110 transition-transform duration-500">
        @else
            <div class="w-full h-40 bg-gradient-to-br from-blue-100 via-purple-50 to-pink-100 flex items-center justify-center">
                <div class="text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-xs text-gray-500">Photo non disponible</p>
                </div>
            </div>
        @endif
        
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
    

    </div>
    
    <div class="px-1">
        <h3 class="text-sm font-bold text-gray-800 mb-1 group-hover:text-blue-600 transition-colors duration-300 line-clamp-2 leading-tight">
            {{ $actor['name'] }}
        </h3>
        <p class="text-xs text-gray-600 italic line-clamp-2 leading-relaxed">
            {{ $actor['character'] }}
        </p>
    </div>
</div>