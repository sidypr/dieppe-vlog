<?php

namespace App\Service;

class VideoUrlTransformer
{
    public function transform(string $url): string
    {
        // YouTube
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
        
        // Vimeo
        if (preg_match('/vimeo\.com\/(?:.*#|.*/videos?/)?([0-9]+)/', $url, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[1];
        }

        // Si l'URL est déjà une URL d'embed, la retourner telle quelle
        if (strpos($url, 'youtube.com/embed/') !== false || strpos($url, 'player.vimeo.com/video/') !== false) {
            return $url;
        }

        // Si l'URL ne correspond à aucun format connu, la retourner telle quelle
        return $url;
    }
} 