<?php
namespace App\Helpers;

class ImageUrl
{
    private const PLACEHOLDER = 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=600&q=80';

    public static function resolve(?string $path, int $width = 600): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return self::withWidth(self::PLACEHOLDER, $width);
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return BASE_URL . $path;
        }

        return "https://images.unsplash.com/{$path}?w={$width}&q=80";
    }

    private static function withWidth(string $url, int $width): string
    {
        if (str_contains($url, 'unsplash.com') && !str_contains($url, '?')) {
            return $url . "?w={$width}&q=80";
        }
        return $url;
    }
}
