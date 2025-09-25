<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watchlist</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="bg-white shadow-lg border-b border-gray-200 relative z-50" style="z-index: 9999;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">

                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-gray-900 hover:text-blue-600 transition-colors duration-200">
                        Watchlist
                    </a>
                </div>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('popular') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->is('popular') ? 'bg-blue-50 text-blue-600' : '' }}">
                        Les populaires
                    </a>
                    <a href="{{ route('movie.top') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->is('movie/top') ? 'bg-blue-50 text-blue-600' : '' }}">
                        Top Films
                    </a>
                    <a href="{{ route('series.top') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->is('series/top') ? 'bg-blue-50 text-blue-600' : '' }}">
                        Top Séries
                    </a>
                    <a href="{{ route('seen') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->is('seen') ? 'bg-blue-50 text-blue-600' : '' }}">
                        Médias Vus
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <main class="min-h-screen bg-gray-50">
        @yield('content')
    </main>
    <footer class="bg-white border-t border-gray-200 relative z-50" style="z-index: 9999;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-row md:flex-row gap-8">
                
                <div class="flex-1">
                    <h3 class="text-2xl text-gray-900 font-bold mb-4">Watchlist</h3>
                    <p class="text-gray-600 mb-4">
                        Découvrez, sauvegardez et organisez vos films préférés. 
                        Créez votre liste personnalisée et ne ratez plus jamais un bon film !
                    </p>
                    <div class="flex gap-3">
                        <a href="#" class="text-gray-500 hover:text-blue-600 transition-colors duration-200" title="Twitter">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-500 hover:text-blue-600 transition-colors duration-200" title="Facebook">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-500 hover:text-blue-600 transition-colors duration-200" title="Instagram">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.746-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001.012.001z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-500 hover:text-blue-600 transition-colors duration-200" title="YouTube">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="flex-1">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Navigation</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('popular') }}" class="text-gray-600 hover:text-blue-600 transition-colors duration-200 {{ request()->is('popular') ? 'bg-blue-50 text-blue-600 px-2 py-1 rounded-md' : '' }}">Les populaires</a></li>
                        <li><a href="{{ route('movie.top') }}" class="text-gray-600 hover:text-blue-600 transition-colors duration-200 {{ request()->is('movie/top') ? 'bg-blue-50 text-blue-600 px-2 py-1 rounded-md' : '' }}">Top Films</a></li>
                        <li><a href="{{ route('movie.search') }}" class="text-gray-600 hover:text-blue-600 transition-colors duration-200 {{ request()->is('movie/search') ? 'bg-blue-50 text-blue-600 px-2 py-1 rounded-md' : '' }}">Recherche</a></li>
                        <li><a href="{{ route('series.top') }}" class="text-gray-600 hover:text-blue-600 transition-colors duration-200 {{ request()->is('series/top') ? 'bg-blue-50 text-blue-600 px-2 py-1 rounded-md' : '' }}">Top Séries</a></li>
                        <li><a href="{{ route('seen') }}" class="text-gray-600 hover:text-blue-600 transition-colors duration-200 {{ request()->is('seen') ? 'bg-blue-50 text-blue-600 px-2 py-1 rounded-md' : '' }}">Médias Vus</a></li>
                        <li><a href="{{ route('home') }}" class="text-gray-600 hover:text-blue-600 transition-colors duration-200 {{ request()->is('/') ? 'bg-blue-50 text-blue-600 px-2 py-1 rounded-md' : '' }}">Ma Liste</a></li>
                    </ul>
                </div>

                <div class="flex-1">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Informations</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 hover:text-blue-600 transition-colors duration-200">À propos</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-blue-600 transition-colors duration-200">Contact</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-blue-600 transition-colors duration-200">Mentions légales</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-blue-600 transition-colors duration-200">Politique de confidentialité</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-300 mt-8 pt-8">
                <div class="flex flex-row md:flex-row justify-between items-center">
                    <div class="text-gray-500 text-sm mb-4 md:mb-0">
                        © {{ date('Y') }} Watchlist. Tous droits réservés.
                    </div>
                    <div class="text-gray-500 text-sm">
                        Propulsé par <a href="https://www.themoviedb.org/" target="_blank" class="text-blue-600 hover:text-blue-700 transition-colors duration-200">TMDB</a>
                    </div>
                </div>
            </div>
        </div>
    </footer> 
</body>
</html>
