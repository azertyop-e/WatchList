<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class ImageHelper
{
    public static function getPosterUrl($posterPath)
    {
        if (!$posterPath) {
            return null;
        }

        $localPath = 'poster/' . $posterPath;
        
        if (Storage::disk('public')->exists($localPath)) {
            return asset(Storage::url($localPath));
        }
    
        return 'https://image.tmdb.org/t/p/w500/' . $posterPath;
    }

    public static function getLogoUrl($logoPath, $size = 'w92')
    {
        if (!$logoPath) {
            return null;
        }
        
        $localPath = 'logo/' . $logoPath;
        
        if (Storage::disk('public')->exists($localPath)) {
            return asset(Storage::url($localPath));
        }
        
        return 'https://image.tmdb.org/t/p/' . $size . '/' . $logoPath;
    }

    public static function getProfileUrl($profilePath)
    {
        if (!$profilePath) {
            return null;
        }

        $localPath = 'profile/' . $profilePath;
        
        if (Storage::disk('public')->exists($localPath)) {
            return asset(Storage::url($localPath));
        }
    
        return 'https://image.tmdb.org/t/p/w185/' . $profilePath;
    }
}
