<?php

namespace App\Helpers;

class TranslationHelper
{
    /**
     * Traduit le statut d'une série ou d'un film
     * 
     * @param string $status Le statut en anglais
     * @param string $mediaType Le type de média ('movie' ou 'series')
     * 
     * @return string Le statut traduit en français
     */
    public static function translateStatus(string $status, string $mediaType = 'movie'): string
    {
        $translations = [
            'Released' => 'Sortie',
            'In Production' => 'En production',
            'Post Production' => 'Post-production',
            'Planned' => 'Prévu',
            'Rumored' => 'Rumeur',
            'Canceled' => 'Annulé',
            'Ended' => 'Terminé',
            
            'Returning Series' => 'Série en cours',
            'Pilot' => 'Pilote',
            
            'Post Production' => 'Post-production',
            'In Production' => 'En production',
        ];

        return $translations[$status] ?? $status;
    }

    /**
     * Traduit le type de média
     * 
     * @param string $mediaType Le type de média
     * 
     * @return string Le type traduit
     */
    public static function translateMediaType(string $mediaType): string
    {
        $translations = [
            'movie' => 'Film',
            'tv' => 'Série',
            'series' => 'Série',
        ];

        return $translations[$mediaType] ?? $mediaType;
    }

    /**
     * Traduit les genres
     * 
     * @param string $genre Le genre en anglais
     * 
     * @return string Le genre traduit
     */
    public static function translateGenre(string $genre): string
    {
        $translations = [
            'Action' => 'Action',
            'Adventure' => 'Aventure',
            'Animation' => 'Animation',
            'Comedy' => 'Comédie',
            'Crime' => 'Crime',
            'Documentary' => 'Documentaire',
            'Drama' => 'Drame',
            'Family' => 'Famille',
            'Fantasy' => 'Fantasy',
            'History' => 'Histoire',
            'Horror' => 'Horreur',
            'Music' => 'Musique',
            'Mystery' => 'Mystère',
            'Romance' => 'Romance',
            'Science Fiction' => 'Science-fiction',
            'TV Movie' => 'Téléfilm',
            'Thriller' => 'Thriller',
            'War' => 'Guerre',
            'Western' => 'Western',
            'Reality' => 'Téléréalité',
            'Talk Show' => 'Talk-show',
            'News' => 'Actualités',
            'Soap' => 'Feuilleton',
            'Miniseries' => 'Mini-série',
        ];

        return $translations[$genre] ?? $genre;
    }
}
