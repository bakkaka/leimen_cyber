<?php
// src/Twig/ImageExtension.php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageExtension extends AbstractExtension
{
    // Cloudinary (exemple) - remplacez par votre CDN
    private string $cdnUrl = 'https://res.cloudinary.com/cyber-formation/image/upload/';
    
    public function getFunctions(): array
    {
        return [
            new TwigFunction('optimize_image', [$this, 'optimizeImage'], ['is_safe' => ['html']]),
            new TwigFunction('youtube_thumbnail', [$this, 'getYoutubeThumbnail']),
            new TwigFunction('vimeo_thumbnail', [$this, 'getVimeoThumbnail']),
        ];
    }
    
    /**
     * Génère une image optimisée avec différentes tailles
     */
    public function optimizeImage(string $path, int $width = 800, int $height = 0, string $format = 'webp'): string
    {
        $transformations = [
            'f_auto', // Format automatique (WebP si supporté)
            'q_auto', // Qualité automatique
            "w_{$width}",
        ];
        
        if ($height > 0) {
            $transformations[] = "h_{$height}";
            $transformations[] = 'c_fill';
        }
        
        $transformation = implode(',', $transformations);
        
        return "{$this->cdnUrl}{$transformation}/{$path}";
    }
    
    /**
     * Récupère la miniature YouTube (sans charger la vidéo)
     */
    public function getYoutubeThumbnail(string $videoId, string $quality = 'maxresdefault'): string
    {
        $qualities = [
            'maxresdefault' => 'maxresdefault.jpg',  // 1280x720
            'sddefault'     => 'sddefault.jpg',      // 640x480
            'hqdefault'     => 'hqdefault.jpg',      // 480x360
            'mqdefault'     => 'mqdefault.jpg',      // 320x180
            'default'       => 'default.jpg',        // 120x90
        ];
        
        $qualityFile = $qualities[$quality] ?? 'hqdefault.jpg';
        
        return "https://img.youtube.com/vi/{$videoId}/{$qualityFile}";
    }
    
    /**
     * Récupère la miniature Vimeo (API)
     */
    public function getVimeoThumbnail(string $videoId): string
    {
        // Cache pour éviter trop d'appels API
        $cacheKey = "vimeo_thumbnail_{$videoId}";
        
        // En production, chargez depuis le cache ou une valeur par défaut
        return "https://vumbnail.com/{$videoId}.jpg";
    }
}

// Enregistrez l'extension dans config/services.yaml