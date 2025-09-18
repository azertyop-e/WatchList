@extends('base')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            Films Populaires
        </h1>

        @if(session('success'))
            <x-toast type="success" message="{{ session('success') }}" />
        @endif

        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            Découvrez les films les plus populaires du moment, sélectionnés pour vous
        </p>
    </div>

    <div class="mb-8">
        <x-search-bar />
    </div>

    @if(isset($moviesData['results']) && count($moviesData['results']) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($moviesData['results'] as $movie)
                <x-movie-card :movie="$movie" :showSaveButton="true" />
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8 mb-4 flex flex-col lg:flex-row justify-between items-center space-y-4 lg:space-y-0">
            <nav aria-label="Pagination" class="flex justify-center items-center text-gray-600">
                @if($currentPage > 1)
                    <a href="{{ route('movie.popular', ['page' => $currentPage - 1, 'per_page' => $perPage]) }}" 
                        class="p-2 mr-4 rounded hover:bg-gray-100 transition-colors duration-200"
                        aria-label="Page précédente">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                @else
                    <span class="p-2 mr-4 rounded text-gray-600 cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </span>
                @endif

                 @php
                     $showFirst = true;
                     $showLast = $totalPages > 1;
                     $showPrevious = $currentPage > 1 && $currentPage > 2;
                     $showNext = $currentPage < $totalPages && $currentPage < $totalPages - 1;
                 @endphp

                 @if($showFirst)
                     @if($currentPage == 1)
                         <span class="px-4 py-2 rounded bg-gray-200 text-gray-900 font-medium cursor-default">1</span>
                     @else
                         <a href="{{ route('movie.popular', ['page' => 1, 'per_page' => $perPage]) }}" 
                             class="px-4 py-2 rounded hover:bg-gray-100 transition-colors duration-200">1</a>
                     @endif
                 @endif

                 @if($currentPage > 3)
                     <span class="px-4 py-2 text-gray-400">...</span>
                 @endif

                 @if($showPrevious)
                     <a href="{{ route('movie.popular', ['page' => $currentPage - 1, 'per_page' => $perPage]) }}" 
                         class="px-4 py-2 rounded hover:bg-gray-100 transition-colors duration-200">{{ $currentPage - 1 }}</a>
                 @endif

                 @if($currentPage != 1 && $currentPage != $totalPages)
                     <span class="px-4 py-2 rounded bg-gray-200 text-gray-900 font-medium cursor-default">{{ $currentPage }}</span>
                 @endif

                 @if($showNext)
                     <a href="{{ route('movie.popular', ['page' => $currentPage + 1, 'per_page' => $perPage]) }}" 
                         class="px-4 py-2 rounded hover:bg-gray-100 transition-colors duration-200">{{ $currentPage + 1 }}</a>
                 @endif

                 @if($currentPage < $totalPages - 2)
                     <span class="px-4 py-2 text-gray-400">...</span>
                 @endif

                 @if($showLast && $totalPages != 1)
                     @if($currentPage == $totalPages)
                         <span class="px-4 py-2 rounded bg-gray-200 text-gray-900 font-medium cursor-default">{{ $totalPages }}</span>
                     @else
                         <a href="{{ route('movie.popular', ['page' => $totalPages, 'per_page' => $perPage]) }}" 
                             class="px-4 py-2 rounded hover:bg-gray-100 transition-colors duration-200">{{ $totalPages }}</a>
                     @endif
                 @endif

                @if($currentPage < $totalPages)
                    <a href="{{ route('movie.popular', ['page' => $currentPage + 1, 'per_page' => $perPage]) }}" 
                        class="p-2 ml-4 rounded hover:bg-gray-100 transition-colors duration-200"
                        aria-label="Page suivante">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @else
                    <span class="p-2 ml-4 rounded text-gray-300 cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                @endif
            </nav>
        </div>
    @else
        <div class="text-center py-16">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">
                Aucun film trouvé
            </h3>
            <p class="text-gray-600 mb-8">
                Nous n'avons trouvé aucun film populaire pour le moment.
            </p>
            <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200">
                Retour à l'accueil
            </a>
        </div>
    @endif
</div>
@endsection