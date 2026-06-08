<?php
namespace App\Helpers;

class HtmlSanitizer
{
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><ul><ol><li><h2><h3><a><blockquote>';

    public static function sanitizeBlogHtml(string $html): string
    {
        $clean = strip_tags($html, self::ALLOWED_TAGS);
        $clean = preg_replace('/\s*on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? $clean;
        $clean = preg_replace('/javascript\s*:/i', '', $clean) ?? $clean;
        return trim($clean);
    }

    public static function containsHtml(string $text): bool
    {
        return $text !== strip_tags($text);
    }
}
