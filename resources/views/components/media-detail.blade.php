@props(['mediaData', 'mediaType' => 'movie'])

<div class="min-h-screen relative py-8">
    @if(isset($mediaData['poster_path']) && $mediaData['poster_path'])
        <div class="fixed inset-0 z-0 mt-[64px]">
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat bg-fixed" 
                 style="background-image: url('{{ \App\Helpers\ImageHelper::getPosterUrl($mediaData['poster_path']) }}'); filter: blur(10px); transform: scale(1.1); background-size: cover; background-repeat: no-repeat; background-position: center center;"></div>
            <div class="absolute inset-0 bg-black bg-opacity-60"></div>
        </div>
    @else
        <div class="fixed inset-0 z-0 bg-gray-900 mt-[64px]">
            <div class="absolute inset-0 bg-black bg-opacity-60"></div>
        </div>
    @endif
    
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="bg-white bg-opacity-95 backdrop-blur-sm rounded-lg shadow-xl overflow-hidden mb-8">
            <div class="md:flex">
                
                <div class="md:w-1/3">
                    @if(isset($mediaData['poster_path']) && $mediaData['poster_path'])
                        <img src="{{ \App\Helpers\ImageHelper::getPosterUrl($mediaData['poster_path']) }}" 
                             alt="{{ $mediaData['title'] ?? $mediaData['name'] }}" 
                             class="w-full h-auto object-cover">
                    @else
                        <div class="w-full h-96 bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-500">Aucune image disponible</span>
                        </div>
                    @endif
                </div>

                <div class="md:w-2/3 p-6">
                    <div class="mb-4">
                        <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ $mediaData['title'] ?? $mediaData['name'] }}</h1>
                        @if(isset($mediaData['original_title']) && $mediaData['original_title'] !== ($mediaData['title'] ?? $mediaData['name']))
                            <p class="text-xl text-gray-600 italic">{{ $mediaData['original_title'] }}</p>
                        @elseif(isset($mediaData['original_name']) && $mediaData['original_name'] !== ($mediaData['title'] ?? $mediaData['name']))
                            <p class="text-xl text-gray-600 italic">{{ $mediaData['original_name'] }}</p>
                        @endif
                        @if(isset($mediaData['tagline']) && $mediaData['tagline'])
                            <p class="text-lg text-gray-700 mt-2 italic">"{{ $mediaData['tagline'] }}"</p>
                        @endif
                    </div>

                    
                    @if(isset($mediaData['vote_average']) && $mediaData['vote_average'])
                        <div class="flex items-center mb-6">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= ($mediaData['vote_average'] / 2))
                                        <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    @endif
                                @endfor
                            </div>
                            <span class="ml-2 text-lg font-semibold text-gray-700">{{ number_format($mediaData['vote_average'], 1) }}/10</span>
                            @if(isset($mediaData['vote_count']))
                                <span class="ml-2 text-sm text-gray-500">({{ number_format($mediaData['vote_count']) }} votes)</span>
                            @endif
                        </div>
                    @endif

                    @if(isset($mediaData['genres']) && is_array($mediaData['genres']))
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">Genres</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($mediaData['genres'] as $genre)
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">{{ \App\Helpers\TranslationHelper::translateGenre($genre['name']) }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        @if(isset($mediaData['release_date']))
                            <div>
                                <span class="text-sm font-semibold text-gray-700">Date de sortie :</span>
                                <span class="ml-2 text-gray-600">{{ \Carbon\Carbon::parse($mediaData['release_date'])->format('d/m/Y') }}</span>
                            </div>
                        @elseif(isset($mediaData['first_air_date']))
                            <div>
                                <span class="text-sm font-semibold text-gray-700">Première diffusion :</span>
                                <span class="ml-2 text-gray-600">{{ \Carbon\Carbon::parse($mediaData['first_air_date'])->format('d/m/Y') }}</span>
                            </div>
                        @endif
                        
                        @if($mediaType === 'movie' && isset($mediaData['runtime']) && $mediaData['runtime'])
                            <div>
                                <span class="text-sm font-semibold text-gray-700">Durée :</span>
                                <span class="ml-2 text-gray-600">{{ $mediaData['runtime'] }} minutes</span>
                            </div>
                        @elseif($mediaType === 'series' && isset($mediaData['episode_run_time']) && is_array($mediaData['episode_run_time']) && count($mediaData['episode_run_time']) > 0)
                            <div>
                                <span class="text-sm font-semibold text-gray-700">Durée des épisodes :</span>
                                <span class="ml-2 text-gray-600">{{ implode(', ', $mediaData['episode_run_time']) }} minutes</span>
                            </div>
                        @endif
                        
                        @if(isset($mediaData['original_language']))
                            <div>
                                <span class="text-sm font-semibold text-gray-700">Langue originale :</span>
                                <span class="ml-2 text-gray-600">{{ strtoupper($mediaData['original_language']) }}</span>
                            </div>
                        @endif
                        
                        @if(isset($mediaData['status']))
                            <div>
                                <span class="text-sm font-semibold text-gray-700">Statut :</span>
                                <span class="ml-2 text-gray-600">{{ \App\Helpers\TranslationHelper::translateStatus($mediaData['status'], $mediaType) }}</span>
                            </div>
                        @endif
                        
                        @if($mediaType === 'series' && isset($mediaData['number_of_seasons']))
                            <div>
                                <span class="text-sm font-semibold text-gray-700">Nombre de saisons :</span>
                                <span class="ml-2 text-gray-600">{{ $mediaData['number_of_seasons'] }}</span>
                            </div>
                        @endif
                        
                        @if($mediaType === 'series' && isset($mediaData['number_of_episodes']))
                            <div>
                                <span class="text-sm font-semibold text-gray-700">Nombre d'épisodes :</span>
                                <span class="ml-2 text-gray-600">{{ $mediaData['number_of_episodes'] }}</span>
                            </div>
                        @endif

                        @if($mediaType === 'series' && isset($mediaData['next_episode_to_watch']) && $mediaData['next_episode_to_watch'] && isset($mediaData['is_saved']) && $mediaData['is_saved'])
                            <div>
                                <span class="text-sm font-semibold text-gray-700">Prochain épisode :</span>
                                <span class="ml-2 text-gray-600">
                                    @if(isset($mediaData['next_episode_to_watch']->season))
                                        S{{ $mediaData['next_episode_to_watch']->season->season_number ?? 'N/A' }}
                                    @else
                                        S{{ $mediaData['next_episode_to_watch']->season_number ?? 'N/A' }}
                                    @endif
                                    E{{ $mediaData['next_episode_to_watch']->episode_number ?? 'N/A' }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        @if(isset($mediaData['overview']) && $mediaData['overview'])
            <div class="bg-white bg-opacity-95 backdrop-blur-sm rounded-lg shadow-xl p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Résumé</h2>
                <p class="text-gray-700 leading-relaxed">{{ $mediaData['overview'] }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white bg-opacity-95 backdrop-blur-sm rounded-lg shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Informations de production</h2>
                
                @if(isset($mediaData['production_companies']) && is_array($mediaData['production_companies']))
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3">Sociétés de production</h3>
                        <ul class="space-y-2">
                            @foreach($mediaData['production_companies'] as $company)
                                <li class="flex items-center">
                                    @if(isset($company['logo_path']) && $company['logo_path'])
                                        <img src="{{ \App\Helpers\ImageHelper::getLogoUrl($company['logo_path']) }}" 
                                             alt="{{ $company['name'] }}" 
                                             class="w-8 h-8 mr-3 object-contain">
                                    @endif
                                    <span class="text-gray-600">{{ $company['name'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(isset($mediaData['production_countries']) && is_array($mediaData['production_countries']))
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3">Pays de production</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($mediaData['production_countries'] as $country)
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">{{ $country['name'] }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(isset($mediaData['spoken_languages']) && is_array($mediaData['spoken_languages']))
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-3">Langues parlées</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($mediaData['spoken_languages'] as $language)
                                <span class="px-3 py-1 bg-purple-100 text-purple-800 text-sm rounded-full">{{ $language['english_name'] }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="bg-white bg-opacity-95 backdrop-blur-sm rounded-lg shadow-xl p-8">
                @if($mediaType === 'movie')
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Informations financières</h2>
                    
                    @if(isset($mediaData['budget']) && $mediaData['budget'] > 0)
                        <div class="mb-4">
                            <span class="text-lg font-semibold text-gray-700">Budget :</span>
                            <span class="ml-2 text-lg text-gray-600">${{ number_format($mediaData['budget']) }}</span>
                        </div>
                    @endif

                    @if(isset($mediaData['revenue']) && $mediaData['revenue'] > 0)
                        <div class="mb-4">
                            <span class="text-lg font-semibold text-gray-700">Recettes :</span>
                            <span class="ml-2 text-lg text-gray-600">${{ number_format($mediaData['revenue']) }}</span>
                        </div>
                    @endif

                    @if(isset($mediaData['budget']) && isset($mediaData['revenue']) && $mediaData['budget'] > 0 && $mediaData['revenue'] > 0)
                        @php
                            $profit = $mediaData['revenue'] - $mediaData['budget'];
                            $roi = ($profit / $mediaData['budget']) * 100;
                        @endphp
                        <div class="mb-4">
                            <span class="text-lg font-semibold text-gray-700">Bénéfice :</span>
                            <span class="ml-2 text-lg {{ $profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ${{ number_format($profit) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-lg font-semibold text-gray-700">ROI :</span>
                            <span class="ml-2 text-lg {{ $roi >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($roi, 1) }}%
                            </span>
                        </div>
                    @endif

                    @if(isset($mediaData['popularity']))
                        @php
                            $hasFinancialInfo = (isset($mediaData['budget']) && $mediaData['budget'] > 0) 
                                             || (isset($mediaData['revenue']) && $mediaData['revenue'] > 0);
                        @endphp
                        <div class="mt-6 {{ $hasFinancialInfo ? 'pt-6 border-t border-gray-200' : '' }}">
                            <span class="text-lg font-semibold text-gray-700">Popularité :</span>
                            <span class="ml-2 text-lg text-gray-600">{{ number_format($mediaData['popularity'], 1) }}</span>
                        </div>
                    @endif
                @else
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Informations de la série</h2>
                    
                    @if(isset($mediaData['networks']) && is_array($mediaData['networks']))
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-700 mb-3">Chaînes de diffusion</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($mediaData['networks'] as $network)
                                    <div class="flex items-center">
                                        @if(isset($network['logo_path']) && $network['logo_path'])
                                            <img src="{{ \App\Helpers\ImageHelper::getLogoUrl($network['logo_path']) }}" 
                                                 alt="{{ $network['name'] }}" 
                                                 class="w-6 h-6 mr-2 object-contain">
                                        @endif
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">{{ $network['name'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    @if(isset($mediaData['created_by']) && is_array($mediaData['created_by']))
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-700 mb-3">Créé par</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($mediaData['created_by'] as $creator)
                                    <span class="px-3 py-1 bg-purple-100 text-purple-800 text-sm rounded-full">{{ $creator['name'] }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    @if(isset($mediaData['popularity']))
                        <div>
                            <span class="text-lg font-semibold text-gray-700">Popularité :</span>
                            <span class="ml-2 text-lg text-gray-600">{{ number_format($mediaData['popularity'], 1) }}</span>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        @if(isset($mediaData['cast']) && is_array($mediaData['cast']) && count($mediaData['cast']) > 0)
            <div class="bg-white bg-opacity-95 backdrop-blur-sm rounded-lg shadow-xl p-8 mt-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Casting</h2>
                    <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                        {{ count($mediaData['cast']) }} acteurs
                    </span>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
                    @foreach($mediaData['cast'] as $index => $actor)
                        <x-cast-card :actor="$actor" :index="$index" />
                    @endforeach
                </div>
                
                @if(count($mediaData['cast']) >= 20)
                    <div class="mt-8 text-center">
                        <div class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm font-medium">
                                Affichage des 20 premiers acteurs principaux
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @if(isset($mediaData['belongs_to_collection']) && $mediaData['belongs_to_collection'])
            <div class="bg-white bg-opacity-95 backdrop-blur-sm rounded-lg shadow-xl p-8 mt-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Fait partie de la collection</h2>
                <div class="flex items-center">
                    @if(isset($mediaData['belongs_to_collection']['poster_path']) && $mediaData['belongs_to_collection']['poster_path'])
                        <img src="{{ \App\Helpers\ImageHelper::getPosterUrl($mediaData['belongs_to_collection']['poster_path']) }}" 
                             alt="{{ $mediaData['belongs_to_collection']['name'] }}" 
                             class="w-16 h-24 mr-4 object-cover rounded">
                    @endif
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800">{{ $mediaData['belongs_to_collection']['name'] }}</h3>
                    </div>
                </div>
            </div>
        @endif

        @if($mediaType === 'series')
            <x-episodes-selector :series="$mediaData" />
        @endif
    </div>
</div>
